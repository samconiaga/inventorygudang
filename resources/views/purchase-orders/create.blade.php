{{-- resources/views/purchase-orders/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Purchase Order (PO)</h1>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item">
            <a href="{{ route('purchase-orders.index') }}">Purchase Order</a>
        </div>
        <div class="breadcrumb-item active">Buat PO Baru</div>
    </div>
</div>

<div class="section-body">

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
        @csrf

        {{-- ===================== 1. HEADER PO ===================== --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">1. Header Purchase Order</h4>
                    <small class="text-muted">
                        Lengkapi informasi vendor dan departemen peminta.
                    </small>
                </div>
            </div>

            <div class="card-body pb-2">
                <div class="form-row">
                    {{-- Supplier --}}
                    <div class="form-group col-md-4">
                        <label class="font-weight-semibold">
                            Vendor / Supplier <span class="text-danger">*</span>
                        </label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                    {{ $sup->supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Departemen --}}
                    <div class="form-group col-md-4">
                        <label class="font-weight-semibold">
                            Departemen Peminta <span class="text-danger">*</span>
                        </label>

                        @if($isSuper)
                            {{-- Superadmin boleh pilih semua customer (departemen) --}}
                            <select name="department_id" class="form-control" required>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" {{ old('department_id') == $cust->id ? 'selected' : '' }}>
                                        {{ $cust->customer }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            {{-- Non-superadmin: tampilkan nama departemen dan simpan id di input hidden --}}
                            @php $dept = $customers->first(); @endphp
                            <input type="text"
                                   class="form-control"
                                   value="{{ $dept?->customer ?? '-' }}"
                                   readonly>
                            <input type="hidden" name="department_id" value="{{ $dept?->id ?? '' }}">
                        @endif
                    </div>

                    {{-- Estimate Date --}}
                    <div class="form-group col-md-4">
                        <label class="font-weight-semibold">Estimate Date</label>
                        <input type="date"
                               name="estimate_date"
                               class="form-control"
                               value="{{ old('estimate_date') }}">
                    </div>
                </div>

                <div class="form-group mb-1">
                    <label class="font-weight-semibold">Catatan (optional)</label>
                    <textarea name="notes"
                              class="form-control"
                              rows="2"
                              placeholder="Contoh: segera dibutuhkan untuk stok bulanan">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ===================== 2. DETAIL ITEM PO ===================== --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">2. Item Purchase Order</h4>
                    <small class="text-muted">
                        Tambahkan daftar barang yang akan dipesan.
                    </small>
                </div>
                <button type="button" class="btn btn-sm btn-primary" id="btnAddRow">
                    <i class="fa fa-plus mr-1"></i> Tambah Item
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="itemsTable">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width: 40px">No</th>
                                <th style="width: 32%">Item (Master) &amp; Jenis</th>
                                <th style="width: 14%">Unit</th>
                                <th class="text-right" style="width: 110px">Qty</th>
                                <th style="width: 22%">Barcode</th>
                                <th class="text-center" style="width: 60px">
                                    <i class="fa fa-cog"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Baris item akan ditambahkan via JS --}}
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pt-3 pb-3 text-muted small">
                    <i class="fa fa-info-circle"></i>
                    Minimal 1 item pada setiap PO. Jika barang sudah memiliki barcode dari master,
                    akan otomatis terisi ketika pilih barang di kolom Item. Jika barcode tidak ada,
                    server akan menghasilkan barcode dari nama produk sehingga barcode tidak kosong.
                </div>
            </div>
        </div>

        {{-- ===================== ACTION BUTTONS ===================== --}}
        <div class="text-right">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-light mr-2">
                Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save mr-1"></i> Simpan PO
            </button>
        </div>
    </form>

</div> {{-- .section-body --}}
@endsection

@push('scripts')
@php
    // siapkan data master barang di sisi server, baru di-json-kan
    $masterBarangs = $barangs->map(function ($b) {
        return [
            'id'      => $b->id,
            'nama'    => $b->nama_barang,
            'jenis'   => optional($b->jenis)->jenis_barang,
            'satuan'  => optional($b->satuan)->satuan,
            'barcode' => $b->barcode,
        ];
    })->values();
@endphp

<script>
    // ==== DATA MASTER BARANG (untuk auto isi jenis, unit, barcode) ====
    const masterBarangs = @json($masterBarangs);

    let rowCounter = 0;

    function addRow(item = {}) {
        rowCounter++;
        const tbody = document.querySelector('#itemsTable tbody');

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="align-middle text-center">${rowCounter}</td>
            <td>
                <select name="items[${rowCounter}][barang_id]"
                        class="form-control form-control-sm barang-select mb-1">
                    <option value="">-- Pilih Barang (master / optional) --</option>
                    @foreach($barangs as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_barang }}</option>
                    @endforeach
                </select>

                <input type="text"
                       class="form-control form-control-sm mt-1 jenis-field"
                       name="items[${rowCounter}][item_name]"
                       placeholder="Nama / jenis barang"
                       value="${(item.item_name ?? '').replace(/"/g,'&quot;')}"
                       required>
            </td>
            <td>
                <input type="text"
                       class="form-control form-control-sm unit-field"
                       name="items[${rowCounter}][unit]"
                       placeholder="Unit"
                       value="${(item.unit || '')}">
            </td>
            <td>
                <input type="number"
                       min="1"
                       class="form-control form-control-sm text-right"
                       name="items[${rowCounter}][qty]"
                       value="${item.qty || 1}"
                       required>
            </td>
            <td>
                <input type="text"
                       class="form-control form-control-sm barcode-field"
                       name="items[${rowCounter}][barcode]"
                       value="${(item.barcode || '')}"
                       placeholder="Barcode (akan terisi otomatis bila tersedia)">
            </td>
            <td class="text-center align-middle">
                <button type="button"
                        class="btn btn-sm btn-outline-danger btnRemoveRow"
                        title="Hapus baris">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);
    }

    // Helper cari master barang dari id
    function findMasterBarang(id) {
        return masterBarangs.find(b => String(b.id) === String(id));
    }

    document.addEventListener('DOMContentLoaded', function () {
        // minimal 1 baris default
        addRow();

        // Tambah baris baru
        document.getElementById('btnAddRow').addEventListener('click', function () {
            addRow();
        });

        const table = document.querySelector('#itemsTable');

        // Hapus baris
        table.addEventListener('click', function (e) {
            if (e.target.closest('.btnRemoveRow')) {
                const row = e.target.closest('tr');
                row.remove();

                // reset nomor
                rowCounter = 0;
                document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
                    rowCounter++;
                    tr.querySelector('td:first-child').innerText = rowCounter;
                });
            }
        });

        // Saat pilih barang → isi jenis, unit, barcode otomatis
        table.addEventListener('change', function (e) {
            const select = e.target.closest('.barang-select');
            if (!select) return;

            const row        = select.closest('tr');
            const jenisField = row.querySelector('.jenis-field');
            const unitField  = row.querySelector('.unit-field');
            const bcField    = row.querySelector('.barcode-field');

            const barangId = select.value;
            const barang   = findMasterBarang(barangId);

            if (barang) {
            if (barang) {
    jenisField.value = barang.nama || barang.jenis || '';
    unitField.value  = barang.satuan || '';

    // 🔥 BARCODE GENERATE DARI NAMA BARANG (BUKAN DARI MASTER)
    let s = (barang.nama || '').toUpperCase();
    s = s.replace(/\s+/g, '-');
    s = s.replace(/[^A-Z0-9\-]/g, '');

    bcField.value = s;
}
            } else {
                jenisField.value = '';
                unitField.value  = '';
                bcField.value    = '';
            }
        });
    });
</script>
@endpush