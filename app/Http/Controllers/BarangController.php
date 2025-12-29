<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use PhpOffice\PhpSpreadsheet\IOFactory;

class BarangController extends Controller
{
    public function index()
    {
        return view('barang.index', [
            'barangs'       => Barang::all(),
            'jenis_barangs' => Jenis::all(),
            'satuans'       => Satuan::all(),
        ]);
    }

    public function getDataBarang()
    {
        $barangs = Barang::all();

        return response()->json([
            'success' => true,
            'data'    => $barangs,
        ]);
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang'   => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'gambar'        => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'stok_minimum'  => 'required|numeric|min:0',
            'jenis_id'      => 'required|exists:jenis,id',
            'satuan_id'     => 'required|exists:satuans,id',
            'barcode'       => 'nullable|string|max:64|unique:barangs,barcode',
        ], [
            'nama_barang.required'   => 'Form Nama Barang Wajib Di Isi !',
            'stok_minimum.required'  => 'Form Stok Minimum Wajib Di Isi !',
            'stok_minimum.numeric'   => 'Gunakan Angka Untuk Mengisi Form Ini !',
            'jenis_id.required'      => 'Pilih Jenis Barang !',
            'satuan_id.required'     => 'Pilih Satuan Barang !',
            'barcode.unique'         => 'Barcode sudah digunakan barang lain.',
            'gambar.mimes'           => 'Gunakan Gambar Yang Memiliki Format jpeg, png, jpg !',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload gambar (optional)
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $path = 'gambar-barang';
            $file = $request->file('gambar');

            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs($path, $fileName, 'public');
            $gambarPath = $path . '/' . $fileName;
        }

        // Create dulu supaya dapat ID
        $barang = Barang::create([
            'kode_barang'  => 'TEMP',
            'barcode'      => $request->barcode ? strtoupper(trim($request->barcode)) : null,
            'nama_barang'  => $request->nama_barang,
            'deskripsi'    => $request->deskripsi,
            'gambar'       => $gambarPath,
            'stok_minimum' => $request->stok_minimum,
            'stok'         => 0,
            'jenis_id'     => $request->jenis_id,
            'satuan_id'    => $request->satuan_id,
            'user_id'      => auth()->id(),
        ]);

        // kode_barang dari ID
        $kode_barang = 'BRG-' . str_pad((string)$barang->id, 6, '0', STR_PAD_LEFT);

        // barcode default = kode_barang jika kosong
        $barcode = $barang->barcode;
        if (empty($barcode)) {
            $barcode = $kode_barang;
        }
        $barcode = strtoupper(trim($barcode));

        $barang->update([
            'kode_barang' => $kode_barang,
            'barcode'     => $barcode,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Disimpan !',
            'data'    => $barang->fresh(),
        ]);
    }

    public function show(Barang $barang)
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail Data Barang',
            'data'    => $barang,
        ]);
    }

    public function edit(Barang $barang)
    {
        return response()->json([
            'success' => true,
            'message' => 'Edit Data Barang',
            'data'    => $barang,
        ]);
    }

    public function update(Request $request, Barang $barang)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang'   => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'gambar'        => 'nullable|mimes:jpeg,png,jpg|max:2048',
            'stok_minimum'  => 'required|numeric|min:0',
            'jenis_id'      => 'required|exists:jenis,id',
            'satuan_id'     => 'required|exists:satuans,id',
            'barcode'       => 'nullable|string|max:64|unique:barangs,barcode,' . $barang->id,
        ], [
            'nama_barang.required'   => 'Form Nama Barang Wajib Di Isi !',
            'stok_minimum.required'  => 'Form Stok Minimum Wajib Di Isi !',
            'stok_minimum.numeric'   => 'Gunakan Angka Untuk Mengisi Form Ini !',
            'jenis_id.required'      => 'Pilih Jenis Barang !',
            'satuan_id.required'     => 'Pilih Satuan Barang !',
            'barcode.unique'         => 'Barcode sudah digunakan barang lain.',
            'gambar.mimes'           => 'Gunakan Gambar Yang Memiliki Format jpeg, png, jpg !',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Handle gambar (optional)
        $gambarPath = $barang->gambar;
        if ($request->hasFile('gambar')) {
            if ($barang->gambar && Storage::disk('public')->exists($barang->gambar)) {
                Storage::disk('public')->delete($barang->gambar);
            }

            $path = 'gambar-barang';
            $file = $request->file('gambar');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs($path, $fileName, 'public');
            $gambarPath = $path . '/' . $fileName;
        }

        // barcode optional: kalau dikosongkan -> keep old
        $newBarcode = $request->barcode;
        if ($newBarcode !== null) {
            $newBarcode = strtoupper(trim($newBarcode));
            if ($newBarcode === '') $newBarcode = $barang->barcode;
        } else {
            $newBarcode = $barang->barcode;
        }

        $barang->update([
            'nama_barang'   => $request->nama_barang,
            'deskripsi'     => $request->deskripsi,
            'stok_minimum'  => $request->stok_minimum,
            'jenis_id'      => $request->jenis_id,
            'satuan_id'     => $request->satuan_id,
            'gambar'        => $gambarPath,
            'barcode'       => $newBarcode,
            'user_id'       => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Terupdate',
            'data'    => $barang->fresh(),
        ]);
    }

    public function destroy(Barang $barang)
    {
        if ($barang->gambar && Storage::disk('public')->exists($barang->gambar)) {
            Storage::disk('public')->delete($barang->gambar);
        }

        $barang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Barang Berhasil Dihapus!',
        ]);
    }

    // =========================================================
    // IMPORT EXCEL - Ambil "Nama Barang" & "Stok" dari kolom manapun
    // =========================================================
    public function importExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file'         => 'required|file|mimes:xlsx,xls|max:10240',
            'jenis_id'     => 'required|exists:jenis,id',
            'satuan_id'    => 'required|exists:satuans,id',
            'stok_minimum' => 'required|numeric|min:0',
        ], [
            'file.required' => 'File Excel wajib diupload.',
            'file.mimes'    => 'File harus .xlsx / .xls',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // helper normalize header
        $normalize = function ($v) {
            $v = strtolower(trim((string)$v));
            $v = preg_replace('/\s+/', ' ', $v);
            $v = str_replace(['_', '-', '.', ':'], ' ', $v);
            $v = preg_replace('/\s+/', ' ', $v);
            return trim($v);
        };

        // helper cek "nama barang"
        $isNamaHeader = function (string $h) {
            // banyak variasi yang umum di excel
            $keys = [
                'nama', 'nama barang', 'nama_barang', 'barang', 'item', 'nama item',
                'description', 'deskripsi', 'material', 'nama produk', 'produk'
            ];
            $h = str_replace('_', ' ', $h);
            $h = trim($h);
            return in_array($h, $keys, true);
        };

        // helper cek "stok"
        $isStokHeader = function (string $h) {
            $keys = [
                'stok', 'stock', 'qty', 'quantity', 'jumlah', 'saldo',
                'sisa', 'available', 'persediaan', 'on hand', 'onhand'
            ];
            $h = str_replace('_', ' ', $h);
            $h = trim($h);
            return in_array($h, $keys, true);
        };

        // helper numeric?
        $isNumeric = function ($v) {
            if ($v === null) return false;
            if (is_int($v) || is_float($v)) return true;
            $s = trim((string)$v);
            if ($s === '') return false;

            // buang pemisah ribuan
            $s = str_replace(['.', ','], ['', '.'], $s); // hati2: ini kompromi
            // kalau jadi "12.5" ok
            return is_numeric($s);
        };

        // parse number aman (boleh "1,000" / "1.000" / "12,5")
        $toNumber = function ($v) {
            if ($v === null) return 0;
            if (is_int($v) || is_float($v)) return (float)$v;

            $s = trim((string)$v);
            if ($s === '') return 0;

            // hilangkan spasi
            $s = preg_replace('/\s+/', '', $s);

            // kasus umum:
            // - "1.000" (ID) => 1000
            // - "1,000" (EN) => 1000
            // - "12,5" (ID decimal) => 12.5
            // kita coba heuristik:
            if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $s)) {
                // format ID: 1.000 atau 1.000,5
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $s)) {
                // format EN: 1,000 atau 1,000.5
                $s = str_replace(',', '', $s);
            } else {
                // fallback: jika ada koma tapi tidak ada titik -> anggap koma decimal
                if (strpos($s, ',') !== false && strpos($s, '.') === false) {
                    $s = str_replace(',', '.', $s);
                } else {
                    // kalau ada titik dan koma, biarin heuristik di atas yang handle.
                }
            }

            return is_numeric($s) ? (float)$s : 0;
        };

        try {
            $path = $request->file('file')->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet = $spreadsheet->getActiveSheet();

            // A,B,C... keys
            $rows = $sheet->toArray(null, true, true, true);

            if (!$rows || count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File kosong / tidak ada data.',
                ], 422);
            }

            // ========= 1) DETEKSI HEADER (scan sampai 30 baris) =========
            $headerRowIndex = null;
            $colNama = null;
            $colStok = null;

            $scanMax = min(30, count($rows));
            for ($i = 1; $i <= $scanMax; $i++) {
                $row = $rows[$i] ?? [];
                $foundNama = null;
                $foundStok = null;

                foreach ($row as $col => $val) {
                    $h = $normalize($val);

                    if ($foundNama === null && $isNamaHeader($h)) {
                        $foundNama = $col;
                    }
                    if ($foundStok === null && $isStokHeader($h)) {
                        $foundStok = $col;
                    }
                }

                if ($foundNama && $foundStok) {
                    $headerRowIndex = $i;
                    $colNama = $foundNama;
                    $colStok = $foundStok;
                    break;
                }
            }

            // ========= 2) FALLBACK CERDAS (kalau header ga ketemu) =========
            // Cari baris data pertama yang "masuk akal", lalu tebak kolom:
            // - kolom nama: yang paling banyak isinya string
            // - kolom stok: yang paling banyak numeric
            if ($headerRowIndex === null) {
                $dataStart = 1;

                // cari baris yang berisi "data"
                for ($i = 1; $i <= $scanMax; $i++) {
                    $row = $rows[$i] ?? [];
                    $nonEmpty = 0;
                    foreach ($row as $val) {
                        if (trim((string)$val) !== '') $nonEmpty++;
                    }
                    if ($nonEmpty >= 2) { // minimal ada 2 kolom isi
                        $dataStart = $i;
                        break;
                    }
                }

                // ambil semua col yang ada di sheet dari baris itu
                $cols = array_keys($rows[$dataStart] ?? []);

                // scoring tiap kolom
                $nameScore = [];
                $numScore  = [];
                foreach ($cols as $c) {
                    $nameScore[$c] = 0;
                    $numScore[$c]  = 0;
                }

                // lihat 20 baris setelahnya untuk menentukan pola
                $look = min($dataStart + 20, count($rows));
                for ($r = $dataStart; $r <= $look; $r++) {
                    $row = $rows[$r] ?? [];
                    foreach ($cols as $c) {
                        $val = $row[$c] ?? null;
                        $s = trim((string)$val);

                        if ($s === '') continue;

                        // numeric?
                        if ($isNumeric($val)) {
                            $numScore[$c] += 2;
                        } else {
                            // string panjang dianggap nama
                            if (mb_strlen($s) >= 3) $nameScore[$c] += 2;
                            else $nameScore[$c] += 1;
                        }
                    }
                }

                // ambil kandidat nama = score string tertinggi
                arsort($nameScore);
                $colNama = array_key_first($nameScore);

                // ambil kandidat stok = score numeric tertinggi tapi bukan colNama
                arsort($numScore);
                $colStok = null;
                foreach ($numScore as $c => $sc) {
                    if ($c === $colNama) continue;
                    if ($sc > 0) {
                        $colStok = $c;
                        break;
                    }
                }

                // kalau masih null (file aneh), fallback terakhir: B=nama, C=stok (umum: No, Nama, Stok)
                if (!$colNama) $colNama = 'B';
                if (!$colStok) $colStok = 'C';

                // karena ini bukan header, data mulai dari baris dataStart (bukan +1)
                $headerRowIndex = $dataStart - 1; // supaya loop mulai dataStart
            }

            $jenisId  = (int) $request->jenis_id;
            $satuanId = (int) $request->satuan_id;
            $stokMin  = (float) $request->stok_minimum;

            $inserted = 0;
            $updated  = 0;
            $skipped  = 0;

            DB::beginTransaction();

            for ($i = $headerRowIndex + 1; $i <= count($rows); $i++) {
                $r = $rows[$i] ?? [];

                $nama = trim((string)($r[$colNama] ?? ''));

                // skip kalau kosong / cuma angka "No"
                if ($nama === '' || preg_match('/^\d+$/', $nama)) {
                    $skipped++;
                    continue;
                }

                $stokRaw = $r[$colStok] ?? 0;
                $stok = $toNumber($stokRaw);

                // kalau nama sudah ada -> tambah stok
                $existing = Barang::whereRaw('LOWER(nama_barang) = ?', [mb_strtolower($nama)])->first();
                if ($existing) {
                    $existing->stok = (float)$existing->stok + (float)$stok;
                    $existing->save();
                    $updated++;
                    continue;
                }

                // create -> kode & barcode auto dari id
                $barang = Barang::create([
                    'kode_barang'  => 'TEMP',
                    'barcode'      => null,
                    'nama_barang'  => $nama,
                    'deskripsi'    => null,
                    'gambar'       => null,
                    'stok_minimum' => $stokMin,
                    'stok'         => (float)$stok,
                    'jenis_id'     => $jenisId,
                    'satuan_id'    => $satuanId,
                    'user_id'      => auth()->id(),
                ]);

                $kode_barang = 'BRG-' . str_pad((string)$barang->id, 6, '0', STR_PAD_LEFT);
                $barcode = strtoupper($kode_barang);

                $barang->update([
                    'kode_barang' => $kode_barang,
                    'barcode'     => $barcode,
                ]);

                $inserted++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Import selesai. Insert: {$inserted}, Update: {$updated}, Skip: {$skipped}. (Kolom Nama={$colNama}, Stok={$colStok})",
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
