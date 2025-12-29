<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\User;
use App\Notifications\InventoryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LaporanStokController extends Controller
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

    private function scopeBarangQuery($query)
    {
        if ($this->isSuperadmin()) return $query;
        return $query->where('department_id', $this->myDepartmentId());
    }

    // =========================
    // NOTIF scoped helper
    // =========================
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
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('laporan-stok.index');
    }

    /**
     * Get Data (AJAX)
     */
    public function getData(Request $request)
    {
        $selectedOption = $request->input('opsi');

        $q = $this->scopeBarangQuery(Barang::query());

        if ($selectedOption === 'minimum') {
            $q->where('stok', '<=', 10);
        } elseif ($selectedOption === 'stok-habis') {
            $q->where('stok', 0);
        }

        $barangs = $q->get();
        return response()->json($barangs);
    }

    /**
     * Print Data (PDF) + NOTIF scoped
     */
    public function printStok(Request $request)
    {
        $selectedOption = $request->input('opsi');

        $q = $this->scopeBarangQuery(Barang::query());

        if ($selectedOption === 'minimum') {
            $q->where('stok', '<=', 10);
        } elseif ($selectedOption === 'stok-habis') {
            $q->where('stok', 0);
        }

        $barangs = $q->get();

        // ✅ NOTIF scoped: superadmin + departemen terkait
        $deptId = $this->isSuperadmin() ? null : $this->myDepartmentId();
        $label  = $selectedOption ?: 'semua';
        $this->notifyScopedUsers(
            $deptId,
            'Cetak Laporan Stok',
            "Laporan stok ({$label}) dicetak oleh " . (Auth::user()->name ?? 'user') . ".",
            'info',
            url('/laporan-stok'),
            ['opsi' => $label]
        );

        // Generate PDF
        $dompdf = new Dompdf();
        $html = view('/laporan-stok/print-stok', compact('barangs', 'selectedOption'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('print-stok.pdf', ['Attachment' => false]);
    }

    /**
     * Get Satuan
     */
    public function getSatuan()
    {
        $satuans = Satuan::all();
        return response()->json($satuans);
    }
}
