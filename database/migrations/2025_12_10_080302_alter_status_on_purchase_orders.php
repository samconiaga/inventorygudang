<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // kalau sebelumnya ENUM, ganti jadi string bebas
            $table->string('status', 50)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // fallback kalau mau balik enum lagi
            // sesuaikan dengan kondisi awal kamu
            $table->enum('status', ['pending', 'partial', 'completed'])
                  ->default('pending')
                  ->change();
        });
    }
};

