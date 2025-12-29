<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Supplier;
use App\Models\User;
use App\Models\BarangMasuk;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Notifications\InventoryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
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

    private function authorizePo(PurchaseOrder $po): void
    {
        if ($this->isSuperadmin()) return;

        if ((int) $po->department_id !== (int) $this->myDepartmentId()) {
            abort(403, 'Anda tidak punya akses ke PO departemen lain.');
        }
    }

    private function authorizeBarangMasuk(BarangMasuk $barangMasuk): void
    {
        if ($this->isSuperadmin()) return;

        if ((int) $barangMasuk->department_id !== (int) $this->myDepartmentId()) {
            abort(403, 'Anda tidak punya akses ke data departemen lain.');
        }
    }

    /**
     * Halaman index Barang Masuk (histori penerimaan dari PO).
     */
    public function index()
    {
        return view('barang-masuk.index');
    }

    /**
     * API: Data Barang Masuk untuk DataTables.
     */
    public function getDataBarangMasuk()
    {
        $barangMasuk = $this->scopeByDepartment(
            BarangMasuk::with('supplier')
        )
            ->latest()
            ->get();

        return response()->json([
            'success'  => true,
            'data'     => $barangMasuk,
            'supplier' => Supplier::all(),
        ]);
    }

    /**
     * Hapus Barang Masuk + rollback stok barang master.
     */
    public function destroy(BarangMasuk $barangMasuk)
    {
        $this->authorizeBarangMasuk($barangMasuk);

        $jumlahMasuk = (int) $barangMasuk->jumlah_masuk;
        $namaBarang  = $barangMasuk->nama_barang;
        $deptId      = (int) $barangMasuk->department_id;

        $barangMasuk->delete();

        // rollback stok di master barang
        $barang = Barang::where('nama_barang', $namaBarang)->first();
        if ($barang) {
            $barang->stok -= $jumlahMasuk;
            if ($barang->stok < 0) $barang->stok = 0;
            $barang->save();

            $barang->refresh();
            $this->checkStockAlerts($barang, $deptId);
        }

        // NOTIF scoped
        $this->notifyScopedUsers(
            $deptId,
            'Barang Masuk Dihapus',
            "Data barang masuk '{$namaBarang}' dihapus. Stok dikembalikan {$jumlahMasuk}.",
            'warning',
            url('/barang-masuk'),
            ['nama_barang' => $namaBarang, 'qty' => $jumlahMasuk]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data Barang Masuk berhasil dihapus & stok dikembalikan.',
        ]);
    }

    // =========================================================
    // RECEIVE GOODS DARI PURCHASE ORDER (PO)
    // =========================================================

    /**
     * Form Receive Goods dari PO tertentu.
     */
    public function createFromPo(PurchaseOrder $po)
    {
        $this->authorizePo($po);

        $po->load(['supplier', 'department', 'items.barang']);

        return view('barang-masuk.receive-from-po', compact('po'));
    }

    /**
     * Simpan Receive Goods dari PO.
     */
    public function storeFromPo(Request $request, PurchaseOrder $po)
    {
        $this->authorizePo($po);

        $request->validate([
            'tanggal_masuk'       => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.id'          => 'required|exists:purchase_order_items,id',
            'items.*.qty_receive' => 'nullable|integer|min:0',
        ], [
            'tanggal_masuk.required' => 'Tanggal penerimaan wajib diisi.',
        ]);

        DB::beginTransaction();

        $changedBarangIds = [];

        try {
            $tanggalMasuk  = $request->tanggal_masuk;
            $hasAnyReceive = false;

            foreach ($request->items as $row) {
                $qtyInput = (int) ($row['qty_receive'] ?? 0);
                if ($qtyInput <= 0) continue;

                /** @var PurchaseOrderItem $poItem */
                $poItem = PurchaseOrderItem::lockForUpdate()->findOrFail($row['id']);

                // anti tembak ID
                if ((int) $poItem->purchase_order_id !== (int) $po->id) {
                    abort(403, 'Item PO tidak valid untuk PO ini.');
                }

                $remaining = (int) $poItem->qty - (int) $poItem->qty_received;
                if ($remaining <= 0) continue;

                $qtyReceive = min($qtyInput, $remaining);
                if ($qtyReceive <= 0) continue;

                $hasAnyReceive = true;

                $barang     = $poItem->barang; // bisa null
                $namaBarang = $barang->nama_barang ?? $poItem->item_name;

                // ✅ simpan barang masuk + department_id
                BarangMasuk::create([
                    'kode_transaksi' => $this->generateKodeTransaksi(),
                    'tanggal_masuk'  => $tanggalMasuk,
                    'nama_barang'    => $namaBarang,
                    'jumlah_masuk'   => $qtyReceive,
                    'supplier_id'    => $po->supplier_id,
                    'user_id'        => auth()->id(),
                    'department_id'  => $po->department_id,
                ]);

                // Update stok master barang
                if ($barang) {
                    $barang->increment('stok', $qtyReceive);
                    $changedBarangIds[] = $barang->id;
                }

                // Update qty_received PO Item
                $poItem->qty_received = (int) $poItem->qty_received + $qtyReceive;
                $poItem->save();
            }

            // Update status PO
            $po->load('items');

            $totalQty      = (int) $po->items->sum('qty');
            $totalReceived = (int) $po->items->sum('qty_received');

            if (!$hasAnyReceive || $totalReceived === 0) {
                $po->status = 'pending';
            } else {
                $allFulfilled = $po->items->every(function ($item) {
                    return (int) $item->qty_received >= (int) $item->qty;
                });

                if ($allFulfilled && $totalReceived >= $totalQty) {
                    $po->status = 'completed';
                } else {
                    $po->status = 'pending_review';
                }
            }

            $po->save();

            DB::commit();

            // NOTIF scoped
            $poLabel = $po->po_number ?? ('PO#' . $po->id);
            $this->notifyScopedUsers(
                (int) $po->department_id,
                'Barang Masuk',
                "Penerimaan barang dari {$poLabel} berhasil disimpan.",
                'success',
                url('/barang-masuk'),
                ['po_id' => $po->id]
            );

            // cek stok minimum/habis
            $changedBarangIds = array_values(array_unique($changedBarangIds));
            if (!empty($changedBarangIds)) {
                $barangs = Barang::whereIn('id', $changedBarangIds)->get();
                foreach ($barangs as $b) {
                    $this->checkStockAlerts($b, (int) $po->department_id);
                }
            }

            return redirect()
                ->route('purchase-orders.show', $po->id)
                ->with('success', 'Penerimaan barang dari PO berhasil disimpan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return back()
                ->with('error', 'Gagal menyimpan penerimaan barang: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Helper: generate kode transaksi.
     */
    protected function generateKodeTransaksi(): string
    {
        $tanggal = now()->format('Ymd');
        $random  = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        return 'TRX-IN-' . $tanggal . '-' . $random;
    }

    // =========================================================
    // NOTIFICATION HELPERS (DATABASE) - SCOPED
    // =========================================================

    /**
     * Kirim notif ke: superadmin + user departemen tertentu.
     */
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

    /**
     * Cek stok minimum/habis (threshold <=10) untuk departemen tertentu.
     */
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
