<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\Customer;
use App\Models\BarangKeluar;
use App\Models\User;
use App\Notifications\InventoryNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LaporanBarangKeluarController extends Controller
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

    private function scopeKeluarQuery($query)
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
        return view('laporan-barang-keluar.index');
    }

    /**
     * Get Data (AJAX)
     */
    public function getData(Request $request)
    {
        $tanggalMulai   = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $q = $this->scopeKeluarQuery(BarangKeluar::query());

        if ($tanggalMulai && $tanggalSelesai) {
            $q->whereBetween('tanggal_keluar', [$tanggalMulai, $tanggalSelesai]);
        }

        $data = $q->get();
        return response()->json($data);
    }

    /**
     * Print DomPDF (PDF) + NOTIF scoped
     */
    public function printBarangKeluar(Request $request)
    {
        $tanggalMulai   = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $q = $this->scopeKeluarQuery(BarangKeluar::query());

        if ($tanggalMulai && $tanggalSelesai) {
            $q->whereBetween('tanggal_keluar', [$tanggalMulai, $tanggalSelesai]);
        }

        $data = $q->get();

        // ✅ NOTIF scoped
        $deptId = $this->isSuperadmin() ? null : $this->myDepartmentId();
        $range  = ($tanggalMulai && $tanggalSelesai) ? "{$tanggalMulai} s/d {$tanggalSelesai}" : 'semua tanggal';
        $this->notifyScopedUsers(
            $deptId,
            'Cetak Laporan Barang Keluar',
            "Laporan barang keluar ({$range}) dicetak oleh " . (Auth::user()->name ?? 'user') . ".",
            'info',
            url('/laporan-barang-keluar'),
            ['tanggal_mulai' => $tanggalMulai, 'tanggal_selesai' => $tanggalSelesai]
        );

        // Generate PDF
        $dompdf = new Dompdf();
        $html = view('/laporan-barang-keluar/print-barang-keluar', compact('data', 'tanggalMulai', 'tanggalSelesai'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('print-barang-keluar.pdf', ['Attachment' => false]);
    }

    /**
     * Get Customer (Departemen) - scoped
     */
    public function getCustomer()
    {
        $q = Customer::query();

        if (!$this->isSuperadmin()) {
            $q->where('department_id', $this->myDepartmentId());
        }

        return response()->json($q->get());
    }
}
