<?php

use Illuminate\Support\Facades\Route;

// ==========================
// CONTROLLERS
// ==========================
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\JenisController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController; // nanti bisa rename ke DepartmentController
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\LaporanBarangMasukController;
use App\Http\Controllers\LaporanBarangKeluarController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UbahPasswordController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PermintaanController;

// ✅ NOTIF (pakai NotificationController yang sudah ada)
use App\Http\Controllers\NotificationController;

/*
|---------------------------------------------------------------------------
| WEB ROUTES – INVENTORY GUDANG
|---------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |---------------------------------------------------------------------------
    | 0) COMMON (SEMUA ROLE LOGIN)
    |---------------------------------------------------------------------------
    */

    // HOME/DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // UBAH PASSWORD
    Route::get('/ubah-password',  [UbahPasswordController::class, 'index'])->name('ubah-password.index');
    Route::post('/ubah-password', [UbahPasswordController::class, 'changePassword'])->name('ubah-password.store');

    // ======================
    // NOTIFICATIONS (SEMUA ROLE)
    // ======================
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/unread', [NotificationController::class, 'unread'])
        ->name('notifications.unread');

    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    /*
    |---------------------------------------------------------------------------
    | 1) SUPERADMIN ONLY
    |---------------------------------------------------------------------------
    */
    Route::middleware('checkRole:superadmin')->group(function () {

        // Manajemen User
        Route::get('/data-pengguna/get-data', [ManajemenUserController::class, 'getDataPengguna'])
            ->name('data-pengguna.get-data');

        Route::get('/api/role', [ManajemenUserController::class, 'getRole'])
            ->name('api.role');

        Route::resource('/data-pengguna', ManajemenUserController::class);

        // Hak Akses / Role
        Route::get('/hak-akses/get-data', [HakAksesController::class, 'getDataRole'])
            ->name('hak-akses.get-data');

        Route::resource('/hak-akses', HakAksesController::class);
    });

    /*
    |---------------------------------------------------------------------------
    | 2) SUPERADMIN + KEPALA GUDANG
    |---------------------------------------------------------------------------
    */
    Route::middleware('checkRole:superadmin,kepala gudang')->group(function () {
        Route::resource('/aktivitas-user', ActivityLogController::class);
    });

    /*
    |---------------------------------------------------------------------------
    | 3) DASHBOARD + LAPORAN
    | Roles: superadmin, kepala gudang, admin gudang
    |---------------------------------------------------------------------------
    */
    Route::middleware('checkRole:superadmin,kepala gudang,admin gudang')->group(function () {

        // ===== LAPORAN STOK =====
        Route::get('/laporan-stok/get-data', [LaporanStokController::class, 'getData'])
            ->name('laporan-stok.get-data');

        Route::get('/laporan-stok/print-stok', [LaporanStokController::class, 'printStok'])
            ->name('laporan-stok.print');

        Route::get('/api/satuan', [LaporanStokController::class, 'getSatuan'])
            ->name('api.satuan');

        Route::resource('/laporan-stok', LaporanStokController::class)->only(['index']);

        // ===== LAPORAN BARANG MASUK =====
        Route::get('/laporan-barang-masuk/get-data', [LaporanBarangMasukController::class, 'getData'])
            ->name('laporan-barang-masuk.get-data');

        Route::get('/laporan-barang-masuk/print-barang-masuk', [LaporanBarangMasukController::class, 'printBarangMasuk'])
            ->name('laporan-barang-masuk.print');

        Route::get('/api/supplier', [LaporanBarangMasukController::class, 'getSupplier'])
            ->name('api.supplier');

        Route::resource('/laporan-barang-masuk', LaporanBarangMasukController::class)->only(['index']);

        // ===== LAPORAN BARANG KELUAR =====
        Route::get('/laporan-barang-keluar/get-data', [LaporanBarangKeluarController::class, 'getData'])
            ->name('laporan-barang-keluar.get-data');

        Route::get('/laporan-barang-keluar/print-barang-keluar', [LaporanBarangKeluarController::class, 'printBarangKeluar'])
            ->name('laporan-barang-keluar.print');

        Route::get('/api/customer', [LaporanBarangKeluarController::class, 'getCustomer'])
            ->name('api.customer');

        Route::resource('/laporan-barang-keluar', LaporanBarangKeluarController::class)->only(['index']);
    });

    /*
    |---------------------------------------------------------------------------
    | 4) DATA MASTER + TRANSAKSI GUDANG
    | Roles: superadmin, admin gudang
    |---------------------------------------------------------------------------
    */
    Route::middleware('checkRole:superadmin,admin gudang,user')->group(function () {

        // ======================
        // MASTER DATA
        // ======================

        // Barang
Route::get('/barang/get-data', [BarangController::class, 'getDataBarang'])->name('barang.get-data');
Route::post('/barang/import-excel', [BarangController::class, 'importExcel'])->name('barang.import-excel');
Route::resource('/barang', BarangController::class);

        // Jenis Barang
        Route::get('/jenis-barang/get-data', [JenisController::class, 'getDataJenisBarang'])->name('jenis-barang.get-data');
        Route::resource('/jenis-barang', JenisController::class);

        // Satuan
        Route::get('/satuan-barang/get-data', [SatuanController::class, 'getDataSatuanBarang'])->name('satuan-barang.get-data');
        Route::resource('/satuan-barang', SatuanController::class);

        // Supplier (khusus PO)
        Route::get('/supplier/get-data', [SupplierController::class, 'getDataSupplier'])->name('supplier.get-data');
        Route::resource('/supplier', SupplierController::class);

       Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');

// endpoint ajax data
Route::get('/customer/get-data', [CustomerController::class, 'getDataCustomer'])->name('customer.getData');

Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
Route::get('/customer/{customer}/edit', [CustomerController::class, 'edit'])->name('customer.edit');
Route::put('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');
Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

        // ======================
        // BARANG MASUK (FROM PO)
        // ======================

        Route::get('/barang-masuk/receive-po/{po}', [BarangMasukController::class, 'createFromPo'])
            ->name('barang-masuk.create-from-po');

        Route::post('/barang-masuk/receive-po/{po}', [BarangMasukController::class, 'storeFromPo'])
            ->name('barang-masuk.store-from-po');

        Route::get('/barang-masuk/get-data', [BarangMasukController::class, 'getDataBarangMasuk'])
            ->name('barang-masuk.get-data');

        Route::resource('/barang-masuk', BarangMasukController::class)->only(['index', 'destroy']);

        // ======================
        // BARANG KELUAR (OUTBOUND – SCAN BARCODE)
        // ======================

        Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])
            ->name('barang-keluar.index');

        Route::get('/barang-keluar/scan-barcode', [BarangKeluarController::class, 'scanBarcode'])
            ->name('barang-keluar.scan-barcode');

        Route::post('/barang-keluar/finalize', [BarangKeluarController::class, 'finalize'])
            ->name('barang-keluar.finalize');

        Route::get('/barang-keluar/get-data', [BarangKeluarController::class, 'getDataBarangKeluar'])
            ->name('barang-keluar.get-data');

        Route::delete('/barang-keluar/{barangKeluar}', [BarangKeluarController::class, 'destroy'])
            ->name('barang-keluar.destroy');
            /*
|--------------------------------------------------------------------------
| PERMINTAAN BARANG (sementara superadmin saja)
|--------------------------------------------------------------------------
*/

Route::get('/permintaan', [PermintaanController::class, 'index'])
    ->name('permintaan.index');

Route::get('/permintaan/create', [PermintaanController::class, 'create'])
    ->name('permintaan.create');

Route::post('/permintaan', [PermintaanController::class, 'store'])
    ->name('permintaan.store');

Route::get('/permintaan/{permintaan}', [PermintaanController::class, 'show'])
    ->name('permintaan.show');

// Approve
Route::post('/permintaan/{permintaan}/approve', [PermintaanController::class, 'approve'])
    ->name('permintaan.approve');

// Reject
Route::post('/permintaan/{permintaan}/reject', [PermintaanController::class, 'reject'])
    ->name('permintaan.reject');

// Proses jadi Barang Keluar (kurangi stok & masuk histori)
Route::post('/permintaan/{permintaan}/process', [PermintaanController::class, 'processToKeluar'])
    ->name('permintaan.process');

// Halaman admin / histori (lihat semua permintaan)
Route::get('/permintaan-admin', [PermintaanController::class, 'adminIndex'])
    ->name('permintaan.admin');

        // ======================
        // PURCHASE ORDER (PO)
        // ======================

        Route::get('/purchase-orders/history', [PurchaseOrderController::class, 'history'])
            ->name('purchase-orders.history');

        Route::post('/purchase-orders/{po}/approve', [PurchaseOrderController::class, 'approve'])
            ->name('purchase-orders.approve');

        Route::post('/purchase-orders/{po}/force-close', [PurchaseOrderController::class, 'forceClose'])
            ->name('purchase-orders.force-close');

        Route::resource('/purchase-orders', PurchaseOrderController::class);
    });
});

// AUTH ROUTES (Laravel Breeze)
require __DIR__ . '/auth.php';
