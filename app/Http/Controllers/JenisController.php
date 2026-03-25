<?php

namespace App\Http\Controllers;

use App\Models\Jenis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JenisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('jenis-barang.index', [
            'jenisBarangs' => Jenis::all()
        ]);
    }

    /**
     * API: get all jenis (JSON)
     */
    public function getDataJenisBarang()
    {
        return response()->json([
            'success' => true,
            'data'    => Jenis::orderBy('id')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_barang'  => 'required|string|max:255'
        ],[
            'jenis_barang.required' => 'Form Jenis Barang Wajib Di Isi !'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenisBarang = Jenis::create([
            'jenis_barang' => $request->jenis_barang,
            'user_id'      => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Disimpan !',
            'data'    => $jenisBarang
        ]);
    }

    /**
     * Show the form for editing the specified resource (AJAX).
     */
    public function edit($id)
    {
        $jenis = Jenis::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Edit Data Jenis',
            'data'    => $jenis
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $jenis = Jenis::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'jenis_barang'  => 'required|string|max:255',
        ],[
            'jenis_barang.required' => 'Form Jenis Barang Tidak Boleh Kosong'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenis->update([
            'jenis_barang' => $request->jenis_barang,
            'user_id'      => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Terupdate',
            'data'    => $jenis
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $jenis = Jenis::findOrFail($id);
        $jenis->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus!'
        ]);
    }
}