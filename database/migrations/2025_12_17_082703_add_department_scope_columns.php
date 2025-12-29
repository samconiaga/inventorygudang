<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * =========================================================
         * USERS: department_id (nullable, superadmin boleh null)
         * =========================================================
         */
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
                $table->index('department_id');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullOnDelete();
            });
        }

        /**
         * =========================================================
         * ROLES: department_id (nullable, role superadmin boleh null)
         * =========================================================
         */
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'department_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('deskripsi');
                $table->index('department_id');
            });

            Schema::table('roles', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullOnDelete();
            });
        }

        /**
         * =========================================================
         * CUSTOMERS: department_id (buat scope barang keluar)
         * =========================================================
         */
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'department_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('customer');
                $table->index('department_id');
            });

            Schema::table('customers', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullOnDelete();
            });
        }

        /**
         * =========================================================
         * BARANGS: department_id (stok per department)
         * =========================================================
         */
        if (Schema::hasTable('barangs') && !Schema::hasColumn('barangs', 'department_id')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('id');
                $table->index('department_id');
            });

            Schema::table('barangs', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullOnDelete();
            });
        }

        /**
         * =========================================================
         * BARANG_MASUKS: department_id (histori masuk per dept)
         * =========================================================
         */
        if (Schema::hasTable('barang_masuks') && !Schema::hasColumn('barang_masuks', 'department_id')) {
            Schema::table('barang_masuks', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('user_id');
                $table->index('department_id');
            });

            Schema::table('barang_masuks', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullOnDelete();
            });
        }

        /**
         * =========================================================
         * BARANG_KELUARS: department_id (histori keluar per dept)
         * =========================================================
         */
        if (Schema::hasTable('barang_keluars') && !Schema::hasColumn('barang_keluars', 'department_id')) {
            Schema::table('barang_keluars', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('user_id');
                $table->index('department_id');
            });

            Schema::table('barang_keluars', function (Blueprint $table) {
                $table->foreign('department_id')
                    ->references('id')->on('departments')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        // barang_keluars
        if (Schema::hasTable('barang_keluars') && Schema::hasColumn('barang_keluars', 'department_id')) {
            Schema::table('barang_keluars', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // barang_masuks
        if (Schema::hasTable('barang_masuks') && Schema::hasColumn('barang_masuks', 'department_id')) {
            Schema::table('barang_masuks', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // barangs
        if (Schema::hasTable('barangs') && Schema::hasColumn('barangs', 'department_id')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // customers
        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'department_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // roles
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'department_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        // users
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex(['department_id']);
                $table->dropColumn('department_id');
            });
        }
    }
};
