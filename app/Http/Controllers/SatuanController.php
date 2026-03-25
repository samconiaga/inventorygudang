<?php

namespace App\Http\Controllers;

use App\Models\Satuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SatuanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('satuan-barang.index', [
            'satuans' => Satuan::all()
        ]);
    }

    /**
     * API: get all satuan (JSON)
     */
    public function getDataSatuanBarang()
    {
        return response()->json([
            'success' => true,
            'data'    => Satuan::orderBy('id')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'satuan'  => 'required|string|max:255'
        ],[
            'satuan.required' => 'Form Satuan Barang Wajib Di Isi !'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $satuan = Satuan::create([
            'satuan'  => $request->satuan,
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Disimpan !',
            'data'    => $satuan
        ]);
    }

    /**
     * Show the form for editing the specified resource (AJAX).
     */
    public function edit($id)
    {
        $satuan = Satuan::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Edit Data Satuan',
            'data'    => $satuan
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $satuan = Satuan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'satuan' => 'required|string|max:255'
        ],[
            'satuan.required' => 'Form Satuan Barang Tidak Boleh Kosong'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $satuan->update([
            'satuan'  => $request->satuan,
            'user_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Terupdate',
            'data'    => $satuan
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $satuan = Satuan::findOrFail($id);
        $satuan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus'
        ]);
    }
}