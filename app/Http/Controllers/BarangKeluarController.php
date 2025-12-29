<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Customer;
use App\Models\User;
use App\Models\BarangKeluar;
use App\Notifications\InventoryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BarangKeluarController extends Controller
{
    // =========================
    // Helpers scope department
    // =========================
    private function isSuperadmin(): bool
    {
        $u = Auth::user();
        $role = $u?->role?->role ?? null;
        return strtolower(trim((string) $role)) === 'superadmin';
    }

    private function myDepartmentId(): ?int
    {
        return Auth::user()?->department_id;
    }

    private function scopeByDepartment($query)
    {
        if ($this->isSuperadmin()) return $query;
        return $query->where('department_id', $this->myDepartmentId());
    }

    private function authorizeCustomer(Customer $customer): void
    {
        if ($this->isSuperadmin()) return;

        $deptId = (int) ($customer->department_id ?? 0);
        if ($deptId !== (int) $this->myDepartmentId()) {
            abort(403, 'Anda tidak punya akses ke departemen lain.');
        }
    }

    private function authorizeBarangKeluar(BarangKeluar $barangKeluar): void
    {
        if ($this->isSuperadmin()) return;

        if ((int) $barangKeluar->department_id !== (int) $this->myDepartmentId()) {
            abort(403, 'Anda tidak punya akses ke data departemen lain.');
        }
    }

    /**
     * Halaman Outbound Transaction (Cart) – pakai scan barcode.
     */
    public function index()
    {
        $customersQ = Customer::orderBy('customer');

        if (!$this->isSuperadmin()) {
            $customersQ->where('department_id', $this->myDepartmentId());
        }

        return view('barang-keluar.index', [
            'customers' => $customersQ->get(),
        ]);
    }

    /**
     * Ajax: data barang keluar (untuk history / datatable).
     */
    public function getDataBarangKeluar()
    {
        $data = $this->scopeByDepartment(BarangKeluar::query())->get();

        $customersQ = Customer::query();
        if (!$this->isSuperadmin()) {
            $customersQ->where('department_id', $this->myDepartmentId());
        }

        return response()->json([
            'success'  => true,
            'data'     => $data,
            'customer' => $customersQ->get(),
        ]);
    }

    // =========================================================
    //  A. SCAN BARCODE
    // =========================================================
    public function scanBarcode(Request $request)
    {
        $barcode = trim($request->get('barcode', ''));

        if ($barcode === '') {
            return response()->json([
                'success' => false,
                'message' => 'Barcode kosong.',
            ], 422);
        }

        $barang = Barang::where('barcode', $barcode)->first();

        if (!$barang) {
            return response()->json([
                'success' => false,
                'message' => 'Barang dengan barcode tersebut tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => $barang->id,
                'nama_barang' => $barang->nama_barang,
                'barcode'     => $barang->barcode,
                'stok'        => $barang->stok,
            ],
        ]);
    }

    // =========================================================
    //  B. FINALIZE OUTBOUND CART
    // =========================================================
    public function finalize(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_keluar'      => 'required|date',
            'customer_id'         => 'required|exists:customers,id',
            'items'               => 'required|array|min:1',
            'items.*.id'          => 'required|exists:barangs,id',
            'items.*.qty'         => 'required|integer|min:1',
        ], [
            'tanggal_keluar.required' => 'Tanggal wajib diisi.',
            'customer_id.required'    => 'Departemen/Customer wajib dipilih.',
            'items.required'          => 'Minimal 1 item harus ada di cart.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        $changedBarangIds = [];
        $customer = null;
        $kodeTransaksi = null;

        try {
            $tanggalKeluar = $request->tanggal_keluar;
            $customerId    = (int) $request->customer_id;

            $customer = Customer::findOrFail($customerId);

            // ✅ pastikan customer sesuai dept user (kecuali superadmin)
            $this->authorizeCustomer($customer);

            $deptId = (int) ($customer->department_id ?? 0);

            // 1) Validasi stok dulu (lock row)
            foreach ($request->items as $row) {
                $barang = Barang::lockForUpdate()->findOrFail($row['id']);
                $qty    = (int) $row['qty'];

                if ($qty > (int) $barang->stok) {
                    throw new \Exception(
                        "Stok barang '{$barang->nama_barang}' tidak mencukupi. Stok: {$barang->stok}, diminta: {$qty}."
                    );
                }
            }

            // 2) Generate kode transaksi
            $kodeTransaksi = $this->generateKodeTransaksi();

            // 3) Simpan per item + kurangi stok
            foreach ($request->items as $row) {
                $barang = Barang::lockForUpdate()->findOrFail($row['id']);
                $qty    = (int) $row['qty'];

                BarangKeluar::create([
                    'kode_transaksi' => $kodeTransaksi,
                    'tanggal_keluar' => $tanggalKeluar,
                    'nama_barang'    => $barang->nama_barang,
                    'jumlah_keluar'  => $qty,
                    'customer_id'    => $customerId,
                    'user_id'        => Auth::id(),
                    'department_id'  => $deptId, // ✅ penting untuk scope
                ]);

                $barang->stok = (int) $barang->stok - $qty;
                if ($barang->stok < 0) $barang->stok = 0;
                $barang->save();

                $changedBarangIds[] = $barang->id;
            }

            DB::commit();

            // NOTIF scoped
            $customerLabel = $customer->customer ?? ('Customer#' . $customerId);

            $this->notifyScopedUsers(
                $deptId,
                'Barang Keluar',
                "Transaksi barang keluar {$kodeTransaksi} berhasil disimpan untuk {$customerLabel}.",
                'success',
                url('/barang-keluar'),
                ['kode_transaksi' => $kodeTransaksi, 'customer_id' => $customerId]
            );

            // cek stok minimum/habis
            $changedBarangIds = array_values(array_unique($changedBarangIds));
            if (!empty($changedBarangIds)) {
                $barangs = Barang::whereIn('id', $changedBarangIds)->get();
                foreach ($barangs as $b) {
                    $this->checkStockAlerts($b, $deptId);
                }
            }

            return redirect()
                ->route('barang-keluar.index')
                ->with('success', "Transaksi barang keluar {$kodeTransaksi} berhasil disimpan.");
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal finalisasi barang keluar: ' . $th->getMessage())
                ->withInput();
        }
    }

    protected function generateKodeTransaksi(): string
    {
        $tanggal = now()->format('Ymd');
        $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        return 'TRX-OUT-' . $tanggal . '-' . $random;
    }

    // =========================================================
    //  METHOD LAMA (biarin)
    // =========================================================
    public function create()
    {
        return view('barang-keluar.create', [
            'barangs' => Barang::all()
        ]);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function show(BarangKeluar $barangKeluar)
    {
        //
    }

    public function edit(BarangKeluar $barangKeluar)
    {
        $this->authorizeBarangKeluar($barangKeluar);

        return response()->json([
            'success' => true,
            'message' => 'Edit Data Barang',
            'data'    => $barangKeluar
        ]);
    }

    public function update(Request $request, BarangKeluar $barangKeluar)
    {
        //
    }

    /**
     * Hapus 1 row barang keluar + rollback stok.
     */
    public function destroy(BarangKeluar $barangKeluar)
    {
        $this->authorizeBarangKeluar($barangKeluar);

        $jumlahKeluar = (int) $barangKeluar->jumlah_keluar;
        $namaBarang   = $barangKeluar->nama_barang;
        $deptId       = (int) $barangKeluar->department_id;

        $barangKeluar->delete();

        $barang = Barang::where('nama_barang', $namaBarang)->first();
        if ($barang) {
            $barang->stok = (int) $barang->stok + $jumlahKeluar;
            $barang->save();

            $barang->refresh();
            $this->checkStockAlerts($barang, $deptId);
        }

        $this->notifyScopedUsers(
            $deptId,
            'Barang Keluar Dihapus',
            "Data barang keluar '{$namaBarang}' dihapus. Stok dikembalikan {$jumlahKeluar}.",
            'warning',
            url('/barang-keluar'),
            ['nama_barang' => $namaBarang, 'qty' => $jumlahKeluar]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus!'
        ]);
    }

    // autocomplete lama
    public function getAutoCompleteData(Request $request)
    {
        $barang = Barang::where('nama_barang', $request->nama_barang)->first();

        if ($barang) {
            return response()->json([
                'nama_barang'   => $barang->nama_barang,
                'stok'          => $barang->stok,
                'satuan_id'     => $barang->satuan_id,
            ]);
        }
    }

    public function getStok(Request $request)
    {
        $namaBarang = $request->input('nama_barang');
        $barang = Barang::where('nama_barang', $namaBarang)->select('stok', 'satuan_id')->first();

        return response()->json([
            'stok'      => $barang->stok,
            'satuan_id' => $barang->satuan_id
        ]);
    }

    public function getSatuan()
    {
        $satuans = Satuan::all();
        return response()->json($satuans);
    }

    public function getBarangs(Request $request)
    {
        if ($request->has('q')) {
            $barangs = Barang::where('nama_barang', 'like', '%' . $request->input('q') . '%')->get();
            return response()->json($barangs);
        }

        return response()->json([]);
    }

    // =========================================================
    // NOTIFICATION HELPERS (DATABASE) - SCOPED
    // =========================================================
    protected function notifyScopedUsers(?int $departmentId, string $title, string $message, string $type = 'info', ?string $url = null, array $meta = []): void
    {
        $q = User::query()->with('role')
            ->whereHas('role', function ($r) {
                $r->whereRaw('LOWER(TRIM(role)) = ?', ['superadmin']);
            });

        if ($departmentId) {
            $q->orWhere('department_id', $departmentId);
        }

        $q->chunk(200, function ($users) use ($title, $message, $type, $url, $meta) {
            foreach ($users as $user) {
                $user->notify(new InventoryNotification($title, $message, $type, $url, $meta));
            }
        });
    }

    protected function checkStockAlerts(?Barang $barang, ?int $departmentId): void
    {
        if (!$barang) return;

        if ((int) $barang->stok <= 0) {
            $this->notifyScopedUsers(
                $departmentId,
                'Stok Habis',
                "Stok barang '{$barang->nama_barang}' HABIS (0).",
                'danger',
                url('/laporan-stok?opsi=stok-habis'),
                ['barang_id' => $barang->id, 'stok' => (int) $barang->stok]
            );
            return;
        }

        if ((int) $barang->stok <= 10) {
            $this->notifyScopedUsers(
                $departmentId,
                'Stok Minimum',
                "Stok barang '{$barang->nama_barang}' mencapai batas minimum ({$barang->stok}).",
                'warning',
                url('/laporan-stok?opsi=minimum'),
                ['barang_id' => $barang->id, 'stok' => (int) $barang->stok]
            );
        }
    }
}
