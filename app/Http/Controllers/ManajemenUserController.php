<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ManajemenUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('data-pengguna.index', [
            'penggunas'   => User::with(['role', 'department'])->get(),
            'roles'       => Role::all(),
            'departments' => Department::all(),
        ]);
    }

    /**
     * Get data pengguna (JSON) untuk DataTable / AJAX.
     */
    public function getDataPengguna()
    {
        $penggunas = User::with(['role', 'department'])->get();

        return response()->json([
            'success' => true,
            'data'    => $penggunas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('data-pengguna.create', [
            'penggunas'   => User::all(),
            'roles'       => Role::all(),
            'departments' => Department::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Anggap role_id 1 = superadmin (kalau id superadmin beda, ganti di sini)
        $isSuperadmin = ((int) $request->role_id === 1);

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'role_id'  => 'required|exists:roles,id',
        ];

        // selain superadmin wajib punya departemen
        if (! $isSuperadmin) {
            $rules['department_id'] = 'required|exists:departments,id';
        } else {
            $rules['department_id'] = 'nullable';
        }

        $messages = [
            'name.required'          => 'Form Nama Wajib Di isi !',
            'email.required'         => 'Form Email Wajib Di isi !',
            'email.email'            => 'Format Email tidak valid !',
            'email.unique'           => 'Email sudah digunakan !',
            'password.required'      => 'Form Password Wajib Di isi !',
            'password.min'           => 'Password Minimal 4 Huruf/Angka/Karakter !',
            'role_id.required'       => 'Tentukan Role/Hak Akses !',
            'role_id.exists'         => 'Role tidak valid !',
            'department_id.required' => 'Tentukan Departemen !',
            'department_id.exists'   => 'Departemen tidak valid !',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengguna = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role_id'       => (int) $request->role_id,
            'department_id' => $isSuperadmin ? null : (int) $request->department_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Tersimpan',
            'data'    => $pengguna->load(['role', 'department']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $pengguna = User::with(['role', 'department'])->find($id);

        return response()->json([
            'success' => true,
            'message' => 'Edit Data Pengguna',
            'data'    => $pengguna,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pengguna = User::find($id);

        if (! $pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan',
            ], 404);
        }

        $isSuperadmin = ((int) $request->role_id === 1);

        $rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $pengguna->id,
            'role_id' => 'required|exists:roles,id',
        ];

        if (! $isSuperadmin) {
            $rules['department_id'] = 'required|exists:departments,id';
        } else {
            $rules['department_id'] = 'nullable';
        }

        // password optional saat update
        if (! empty($request->password)) {
            $rules['password'] = 'min:4';
        }

        $messages = [
            'name.required'          => 'Form Nama Wajib Di isi !',
            'email.required'         => 'Form Email Wajib Di isi !',
            'email.email'            => 'Format Email tidak valid !',
            'email.unique'           => 'Email sudah digunakan !',
            'role_id.required'       => 'Tentukan Role/Hak Akses !',
            'role_id.exists'         => 'Role tidak valid !',
            'department_id.required' => 'Tentukan Departemen !',
            'department_id.exists'   => 'Departemen tidak valid !',
            'password.min'           => 'Password minimal 4 Huruf/Angka/Karakter !',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userData = [
            'name'          => $request->name,
            'email'         => $request->email,
            'role_id'       => (int) $request->role_id,
            'department_id' => $isSuperadmin ? null : (int) $request->department_id,
        ];

        if (! empty($request->password)) {
            $userData['password'] = Hash::make($request->password);
        }

        $pengguna->update($userData);

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Terupdate',
            'data'    => $pengguna->fresh()->load(['role', 'department']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pengguna = User::find($id);

        if (! $pengguna) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pengguna tidak ditemukan!',
            ], 404);
        }

        $pengguna->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Berhasil Dihapus!',
        ]);
    }

    /**
     * Get Role (API untuk select2 dsb).
     */
    public function getRole()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    /**
     * Get Departments (API)
     */
    public function getDepartments()
    {
        $departments = Department::all();
        return response()->json($departments);
    }
}
