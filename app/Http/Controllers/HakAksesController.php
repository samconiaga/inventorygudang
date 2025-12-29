<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class HakAksesController extends Controller
{
    /**
     * Halaman index hak akses.
     */
    public function index()
    {
        return view('hak-akses.index', [
            'roles'       => Role::with('department')->get(),
            'departments' => Department::all(),   // untuk dropdown di create & edit
        ]);
    }

    /**
     * Data role (JSON) utk Datatable / AJAX.
     */
    public function getDataRole()
    {
        $roles = Role::with('department')->get();

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }

    /**
     * Form create (kalau dipakai langsung, bukan modal).
     */
    public function create()
    {
        return view('hak-akses.create', [
            'departments' => Department::all(),
        ]);
    }

    /**
     * Simpan role baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role'          => 'required',
            'deskripsi'     => 'required',
            'department_id' => 'required|exists:departments,id',
        ], [
            'role.required'          => 'Form Role Wajib Di Isi !',
            'deskripsi.required'     => 'Form Deskripsi Wajib Di Isi !',
            'department_id.required' => 'Form Departemen Wajib Di Isi !',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role = Role::create([
            'role'          => $request->role,
            'deskripsi'     => $request->deskripsi,
            'department_id' => $request->department_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Tersimpan',
            'data'    => $role,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Ambil data 1 role untuk edit.
     */
    public function edit($id)
    {
        $role = Role::with('department')->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Edit Data Role',
            'data'    => $role,
        ]);
    }

    /**
     * Update role.
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role'          => 'required',
            'deskripsi'     => 'required',
            'department_id' => 'required|exists:departments,id',
        ], [
            'role.required'          => 'Form Role Wajib Di Isi !',
            'deskripsi.required'     => 'Form Deskripsi Wajib Di Isi !',
            'department_id.required' => 'Form Departemen Wajib Di Isi !',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $role->update([
            'role'          => $request->role,
            'deskripsi'     => $request->deskripsi,
            'department_id' => $request->department_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Terupdate',
            'data'    => $role,
        ]);
    }

    /**
     * Hapus role.
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        if (! $role) {
            return response()->json([
                'success' => false,
                'message' => 'Role tidak ditemukan!',
            ], 404);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus!',
        ]);
    }
}
