// database/migrations/2025_12_09_000002_add_purchase_order_id_to_barang_masuks_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')
                  ->nullable()
                  ->after('id');

            $table->foreign('purchase_order_id')
                  ->references('id')->on('purchase_orders')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('barang_masuks', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });
    }
};
