<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\PermintaanItem;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermintaanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FORM CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        // eager load satuan
        $barangs = Barang::with('satuan')
            ->orderBy('nama_barang')
            ->get();

        return view('permintaan.create', compact('barangs'));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE (AUTO AMBIL SATUAN DARI DATABASE)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id.*' => 'required|exists:barangs,id',
            'qty.*'       => 'required|integer|min:1',
            'note'        => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();

        try {

            $kode = 'REQ-' . date('YmdHis') . '-' . mt_rand(100, 999);

            $permintaan = Permintaan::create([
                'kode'    => $kode,
                'user_id' => Auth::id(),
                'note'    => $request->note,
                'status'  => 'pending'
            ]);

            $barangIds = $request->barang_id ?? [];
            $qtys      = $request->qty ?? [];

            foreach ($barangIds as $i => $barangId) {

                if (!$barangId) continue;

                $barang = Barang::with('satuan')->find($barangId);

                if (!$barang) continue;

                PermintaanItem::create([
                    'permintaan_id' => $permintaan->id,
                    'barang_id'     => $barangId,
                    'qty'           => max(1, (int) $qtys[$i]),
                    'satuan'        => $barang->satuan->satuan ?? null, // 🔥 AUTO DB
                    'note'          => null
                ]);
            }

            DB::commit();

            return redirect()
                ->route('permintaan.index')
                ->with('success', 'Permintaan berhasil dikirim.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors('Gagal membuat permintaan: ' . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX USER
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $permintaans = Permintaan::where('user_id', Auth::id())
            ->with('items.barang')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('permintaan.index', compact('permintaans'));
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN INDEX
    |--------------------------------------------------------------------------
    */
    public function adminIndex()
    {
        $permintaans = Permintaan::with('pemohon')
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('permintaan.admin_index', compact('permintaans'));
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show(Permintaan $permintaan)
    {
        $permintaan->load('items.barang.satuan', 'pemohon', 'approver');
        return view('permintaan.show', compact('permintaan'));
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */
    public function approve(Permintaan $permintaan)
    {
        if ($permintaan->status !== 'pending') {
            return back()->withErrors('Permintaan sudah diproses.');
        }

        $permintaan->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return back()->with('success', 'Permintaan disetujui.');
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */
    public function reject(Permintaan $permintaan)
    {
        if ($permintaan->status !== 'pending') {
            return back()->withErrors('Permintaan sudah diproses.');
        }

        $permintaan->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return back()->with('success', 'Permintaan ditolak.');
    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS KE BARANG KELUAR
    |--------------------------------------------------------------------------
    */
    public function processToKeluar(Permintaan $permintaan)
    {
        if (!in_array($permintaan->status, ['approved', 'pending'])) {
            return back()->withErrors('Permintaan tidak bisa diproses.');
        }

        DB::beginTransaction();

        try {

            $items = $permintaan->items()->with('barang')->get();

            // 🔥 CEK STOK (pakai kolom stok, bukan stock)
            $insufficient = [];

            foreach ($items as $item) {
                if ($item->barang->stok < $item->qty) {
                    $insufficient[] = $item;
                }
            }

            if (count($insufficient) > 0) {

                DB::rollBack();

                $namaBarang = implode(', ', $insufficient->map(function ($x) {
                    return $x->barang->nama_barang ?? $x->barang_id;
                })->toArray());

                return back()->withErrors('Stok tidak cukup untuk: ' . $namaBarang);
            }

            // 🔥 KURANGI STOK
            foreach ($items as $item) {
                $item->barang->decrement('stok', $item->qty);
            }

            // 🔥 INSERT KE BARANG KELUAR (JIKA ADA)
            if (Schema::hasTable('barang_keluars')) {

                $bkId = DB::table('barang_keluars')->insertGetId([
                    'kode'       => 'BK-' . date('YmdHis') . '-' . mt_rand(100, 999),
                    'user_id'    => Auth::id(),
                    'note'       => 'Keluar dari permintaan: ' . $permintaan->kode,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (Schema::hasTable('barang_keluar_items')) {

                    foreach ($items as $item) {

                        DB::table('barang_keluar_items')->insert([
                            'barang_keluar_id' => $bkId,
                            'barang_id'        => $item->barang_id,
                            'qty'              => $item->qty,
                            'satuan'           => $item->satuan,
                            'created_at'       => now(),
                            'updated_at'       => now(),
                        ]);
                    }
                }
            }

            // 🔥 UPDATE STATUS
            $permintaan->update([
                'status'      => 'processed',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('permintaan.admin')
                ->with('success', 'Permintaan diproses dan stok dikurangi.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()->withErrors('Gagal memproses: ' . $e->getMessage());
        }
    }
}