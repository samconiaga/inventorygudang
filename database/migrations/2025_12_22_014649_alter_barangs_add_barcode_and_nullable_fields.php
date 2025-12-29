<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            // 1) Tambah barcode unik (kalau belum ada)
            if (!Schema::hasColumn('barangs', 'barcode')) {
                $table->string('barcode', 64)->nullable()->unique()->after('kode_barang');
            }

            // 2) Opsional: biar deskripsi & gambar tidak wajib (karena nanti gambar mau diganti barcode text)
            // Kalau kamu masih butuh gambar, biarkan saja. Tapi minimal jangan required di DB.
            $table->string('deskripsi')->nullable()->change();
            $table->string('gambar')->nullable()->change();

            // 3) stok_minimum aman kalau default 0 (opsional)
            // kalau sekarang ada data lama, lebih aman jangan ubah drastis dulu.
            // $table->integer('stok_minimum')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            if (Schema::hasColumn('barangs', 'barcode')) {
                $table->dropUnique(['barcode']);
                $table->dropColumn('barcode');
            }

            // balikkan kalau perlu (opsional)
            // $table->string('deskripsi')->nullable(false)->change();
            // $table->string('gambar')->nullable(false)->change();
        });
    }
};
