// database/migrations/2025_12_09_000001_create_purchase_order_items_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('barang_id')->nullable(); // link ke master barang
            $table->string('item_name');      // snapshot nama barang
            $table->string('unit')->nullable();
            $table->integer('qty')->default(0);
            $table->string('barcode')->nullable(); // kalau mau isi dari master
            $table->integer('qty_received')->default(0); // buat tracking penerimaan
            $table->timestamps();

            $table->foreign('purchase_order_id')
                  ->references('id')->on('purchase_orders')
                  ->onDelete('cascade');

            $table->foreign('barang_id')
                  ->references('id')->on('barangs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
