<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Supplier;
use App\Models\Customer; // <- ambil departemen dari table customers
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Notifications\PurchaseOrderCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends Controller
{
    // =========================
    // Helpers (Role + Dept Scope)
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

    private function authorizePo(PurchaseOrder $po): void
    {
        if ($this->isSuperadmin()) return;

        if ((int) $po->department_id !== (int) $this->myDepartmentId()) {
            abort(403, 'Anda tidak punya akses ke PO departemen lain.');
        }
    }

    /**
     * LIST PO AKTIF
     */
    public function index()
    {
        $purchaseOrders = $this->scopeByDepartment(
            PurchaseOrder::with(['supplier', 'department', 'creator'])
        )
            ->whereIn('status', ['pending', 'pending_review', 'partial'])
            ->latest()
            ->get();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    /**
     * HISTORY PO
     */
    public function history()
    {
        $purchaseOrders = $this->scopeByDepartment(
            PurchaseOrder::with(['supplier', 'department', 'creator'])
        )
            ->where('status', 'completed')
            ->latest()
            ->get();

        return view('purchase-orders.history', compact('purchaseOrders'));
    }

    /**
     * Form Create PO.
     * - superadmin: boleh pilih departemen
     * - selain superadmin: departemen otomatis (tapi kita tetap kirim data utk view)
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('supplier')->get();

        $isSuper = $this->isSuperadmin();
        $myDeptId = $this->myDepartmentId();

        // ambil customers (dipakai sebagai "departemen" sesuai permintaan)
        if ($isSuper) {
            $customers = Customer::orderBy('customer')->get();
        } else {
            // hanya kirim departemen user sendiri (untuk ditampilkan & diisi hidden)
            $customers = Customer::where('id', $myDeptId)->get();
        }

        $barangs = Barang::with(['jenis', 'satuan'])
            ->orderBy('nama_barang')
            ->get();

        return view('purchase-orders.create', compact('suppliers', 'customers', 'barangs', 'isSuper'));
    }

    /**
     * Simpan PO baru.
     * - department_id dipaksa sesuai user (kecuali superadmin)
     * - NOTIF: superadmin + user yang department_id sama dengan PO
     */
   public function store(Request $request)
{
    $isSuper  = $this->isSuperadmin();
    $myDeptId = $this->myDepartmentId();

    // final dept:
    $deptIdFinal = $isSuper ? $request->department_id : $myDeptId;

    $rules = [
        'supplier_id'    => 'required|exists:suppliers,id',
        'estimate_date'  => 'nullable|date',
        'notes'          => 'nullable|string',

        'items'               => 'required|array|min:1',
        'items.*.barang_id'   => 'nullable|exists:barangs,id',
        'items.*.item_name'   => 'required|string',
        'items.*.unit'        => 'nullable|string',
        'items.*.qty'         => 'required|integer|min:1',
        'items.*.barcode'     => 'nullable|string|max:100',
    ];

    if ($isSuper) {
        $rules['department_id'] = 'required|exists:customers,id';
    } else {
        if (!$myDeptId) {
            return back()->with('error', 'Akun Anda belum memiliki departemen. Silakan set departemen pada Data Pengguna.');
        }
    }

    $messages = [
        'supplier_id.required'   => 'Supplier wajib dipilih',
        'department_id.required' => 'Departemen peminta wajib dipilih',
        'items.required'         => 'Minimal harus ada 1 item di PO',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();

    try {

        $poNumber = $this->generatePoNumber();

        $po = PurchaseOrder::create([
            'po_number'     => $poNumber,
            'supplier_id'   => $request->supplier_id,
            'department_id' => $deptIdFinal,
            'estimate_date' => $request->estimate_date,
            'status'        => 'pending',
            'created_by'    => Auth::id(),
            'notes'         => $request->notes,
        ]);

        foreach ($request->items as $item) {

            $barangId     = $item['barang_id'] ?? null;
            $itemName     = trim((string) ($item['item_name'] ?? ''));
            $unit         = $item['unit'] ?? null;
            $qty          = (int) ($item['qty'] ?? 0);
            $inputBarcode = trim((string) ($item['barcode'] ?? ''));

            // =============================
            // FINAL BARCODE - AUTO FROM NAMA BARANG
            // =============================

            $finalBarcode = null;

            // 1️⃣ kalau user isi manual → pakai itu
            if ($inputBarcode !== '') {
                $finalBarcode = strtoupper($inputBarcode);
            }

            // 2️⃣ kalau kosong → generate dari nama barang
            if (empty($finalBarcode)) {

                $sourceName = $itemName;

                // kalau item_name kosong tapi pilih master barang
                if ($sourceName === '' && $barangId) {
                    $master = Barang::find($barangId);
                    if ($master) {
                        $sourceName = $master->nama_barang;
                    }
                }

                $s = strtoupper($sourceName ?: 'ITEM');

                // spasi jadi dash
                $s = preg_replace('/\s+/', '-', $s);

                // hanya huruf & angka
                $s = preg_replace('/[^A-Z0-9\-]/', '', $s);

                if ($s === '') {
                    $s = 'ITEM-' . time();
                }

                $finalBarcode = $s;
            }

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'barang_id'         => $barangId,
                'item_name'         => $itemName,
                'unit'              => $unit,
                'qty'               => $qty,
                'barcode'           => $finalBarcode,
                'qty_received'      => 0,
            ]);
        }

        DB::commit();

        // NOTIF: superadmin + user dept PO
        $targets = User::query()
            ->with('role')
            ->whereHas('role', function ($q) {
                $q->whereRaw('LOWER(TRIM(role)) = ?', ['superadmin']);
            })
            ->orWhere('department_id', $po->department_id)
            ->get()
            ->unique('id');

        foreach ($targets as $u) {
            $u->notify(new PurchaseOrderCreatedNotification($po));
        }

        return redirect()
            ->route('purchase-orders.show', $po->id)
            ->with('success', 'PO berhasil dibuat dengan nomor: ' . $poNumber);

    } catch (\Throwable $th) {

        DB::rollBack();

        return back()->with(
            'error',
            'Terjadi kesalahan saat menyimpan PO: ' . $th->getMessage()
        );
    }
}

    /**
     * Detail PO.
     */
    public function show($id)
    {
        $po = PurchaseOrder::with(['supplier', 'department', 'creator', 'items.barang'])
            ->findOrFail($id);

        $this->authorizePo($po);

        return view('purchase-orders.show', compact('po'));
    }

    /**
     * Hapus PO.
     */
    public function destroy($id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->authorizePo($po);

        if ($po->status !== 'pending') {
            return redirect()->back()->with(
                'error',
                'PO yang sudah ada penerimaan (pending_review / partial / completed) tidak bisa dihapus.'
            );
        }

        $po->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'PO berhasil dihapus.');
    }

    /**
     * Purchasing APPROVE
     */
    public function approve(PurchaseOrder $po)
    {
        $this->authorizePo($po);

        if ($po->status !== 'pending_review') {
            return back()->with('error', 'PO ini tidak dalam status Pending Purchasing Review.');
        }

        $po->load('items');

        $totalQty      = (int) $po->items->sum('qty');
        $totalReceived = (int) $po->items->sum('qty_received');

        if ($totalReceived === 0) {
            return back()->with('error', 'Belum ada barang yang diterima untuk PO ini.');
        }

        $po->status = ($totalReceived < $totalQty) ? 'partial' : 'completed';
        $po->save();

        return back()->with('success', 'Purchasing telah menyetujui penerimaan barang pada PO ini.');
    }

    /**
     * FORCE CLOSE
     */
    public function forceClose(PurchaseOrder $po)
    {
        $this->authorizePo($po);

        if (!in_array($po->status, ['pending_review', 'partial'])) {
            return back()->with('error', 'PO ini tidak bisa di-force close.');
        }

        $po->status = 'completed';
        $po->save();

        return back()->with('success', 'PO telah di-force close. Sisa qty dianggap batal.');
    }

    /**
     * Helper: Generate nomor PO.
     * Format: PO-YYYYMMDD-XXXX
     */
    protected function generatePoNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';

        $lastPo = PurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        $next = 1;
        if ($lastPo) {
            $lastNumber = (int) str_replace($prefix, '', $lastPo->po_number);
            $next = $lastNumber + 1;
        }

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}