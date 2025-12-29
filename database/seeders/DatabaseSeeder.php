<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\Customer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * =========================================================
         * 1. DEPARTMENTS
         * =========================================================
         */
        $mis = Department::create([
            'code' => 'MIS',
            'name' => 'Management Information System',
        ]);

        $teknik = Department::create([
            'code' => 'TEKNIK',
            'name' => 'Teknik',
        ]);

        $produksi = Department::create([
            'code' => 'PROD',
            'name' => 'Produksi',
        ]);

        $ga = Department::create([
            'code' => 'GA',
            'name' => 'General Affairs',
        ]);

        /**
         * =========================================================
         * 2. ROLES
         * =========================================================
         */
        $superadminRole = Role::create([
            'role'          => 'superadmin',
            'deskripsi'     => 'Superadmin memiliki kendali penuh pada aplikasi',
            'department_id' => null,
        ]);

        $kepalaGudangRole = Role::create([
            'role'      => 'kepala gudang',
            'deskripsi' => 'Kepala gudang mengelola & approve transaksi',
        ]);

        $adminGudangRole = Role::create([
            'role'      => 'admin gudang',
            'deskripsi' => 'Admin gudang mengelola transaksi harian',
        ]);

        /**
         * =========================================================
         * 3. USERS
         * =========================================================
         */
        User::create([
            'name'          => 'Super Admin',
            'email'         => 'superadmin@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $superadminRole->id,
            'department_id' => null,
        ]);

        // ===== PRODUKSI =====
        User::create([
            'name'          => 'Kepala Gudang Produksi',
            'email'         => 'kepalagudangproduksi@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $kepalaGudangRole->id,
            'department_id' => $produksi->id,
        ]);

        User::create([
            'name'          => 'Admin Gudang Produksi',
            'email'         => 'adminproduksi@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $adminGudangRole->id,
            'department_id' => $produksi->id,
        ]);

        // ===== TEKNIK =====
        User::create([
            'name'          => 'Kepala Gudang Teknik',
            'email'         => 'kepalagudangteknik@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $kepalaGudangRole->id,
            'department_id' => $teknik->id,
        ]);

        User::create([
            'name'          => 'Admin Gudang Teknik',
            'email'         => 'adminteknik@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $adminGudangRole->id,
            'department_id' => $teknik->id,
        ]);

        // ===== MIS =====
        User::create([
            'name'          => 'Kepala Gudang MIS',
            'email'         => 'kepalagudangmis@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $kepalaGudangRole->id,
            'department_id' => $mis->id,
        ]);

        User::create([
            'name'          => 'Admin Gudang MIS',
            'email'         => 'adminmis@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $adminGudangRole->id,
            'department_id' => $mis->id,
        ]);

        // ===== GA =====
        User::create([
            'name'          => 'Kepala Gudang GA',
            'email'         => 'kepalagudangga@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $kepalaGudangRole->id,
            'department_id' => $ga->id,
        ]);

        User::create([
            'name'          => 'Admin Gudang GA',
            'email'         => 'adminga@gmail.com',
            'password'      => Hash::make('1234'),
            'role_id'       => $adminGudangRole->id,
            'department_id' => $ga->id,
        ]);

        /**
         * =========================================================
         * 4. MASTER DATA
         * =========================================================
         */
        Jenis::insert([
            ['jenis_barang' => 'Pupuk Cair',  'user_id' => 1],
            ['jenis_barang' => 'Pupuk Kimia', 'user_id' => 1],
        ]);

        Satuan::insert([
            ['satuan' => 'Kwintal', 'user_id' => 1],
            ['satuan' => 'Liter',   'user_id' => 1],
        ]);

        Supplier::insert([
            [
                'supplier' => 'PT Petrokimia Gresik',
                'alamat'   => 'Gresik, Jawa Timur',
                'user_id'  => 1,
            ],
            [
                'supplier' => 'PT Pupuk Indonesia',
                'alamat'   => 'Jakarta',
                'user_id'  => 1,
            ],
        ]);

        /**
         * =========================================================
         * 5. CUSTOMER (dipakai sebagai "DEPARTMENT" di tampilan)
         * =========================================================
         */
        Customer::insert([
            [
                'customer'      => 'MIS',
                'alamat'        => '-',
                'user_id'       => 1,
                'department_id' => $mis->id,
            ],
            [
                'customer'      => 'Produksi',
                'alamat'        => '-',
                'user_id'       => 1,
                'department_id' => $produksi->id,
            ],
            [
                'customer'      => 'GA',
                'alamat'        => '-',
                'user_id'       => 1,
                'department_id' => $ga->id,
            ],
            [
                'customer'      => 'Teknik',
                'alamat'        => '-',
                'user_id'       => 1,
                'department_id' => $teknik->id,
            ],
        ]);
    }
}
