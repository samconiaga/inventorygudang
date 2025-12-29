{{-- resources/views/barang-masuk/receive-from-po.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="section-header">
    <h1>Receive Goods dari PO: {{ $po->po_number }}</h1>
    <div class="ml-auto">
        <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-secondary">
            &larr; Kembali ke Detail PO
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

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

<form action="{{ route('barang-masuk.store-from-po', $po->id) }}" method="POST">
    @csrf

    {{-- HEADER --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">1. Header PO</h4>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">No PO</dt>
                <dd class="col-sm-9">{{ $po->po_number }}</dd>

                <dt class="col-sm-3">Supplier</dt>
                <dd class="col-sm-9">{{ $po->supplier->supplier ?? '-' }}</dd>

                <dt class="col-sm-3">Departemen</dt>
                <dd class="col-sm-9">
                    {{ $po->department->code ?? '' }}
                    {{ $po->department->name ?? '' }}
                </dd>

                <dt class="col-sm-3">Status PO</dt>
                <dd class="col-sm-9">
                    <span class="badge badge-{{ $po->status == 'pending' ? 'warning' : ($po->status == 'completed' ? 'success' : 'info') }}">
                        {{ ucfirst($po->status) }}
                    </span>
                </dd>

                <dt class="col-sm-3">Tanggal Penerimaan</dt>
                <dd class="col-sm-9">
                    <input type="date"
                           name="tanggal_masuk"
                           class="form-control form-control-sm w-auto"
                           value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}">
                </dd>
            </dl>
        </div>
    </div>

    {{-- ITEMS --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">2. Item PO yang Diterima</h4>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 40px">No</th>
                            <th>Item</th>
                            <th style="width: 120px">Unit</th>
                            <th style="width: 90px" class="text-right">Qty PO</th>
                            <th style="width: 110px" class="text-right">Qty Received</th>
                            <th style="width: 130px" class="text-right">Qty Diterima<br>(sekarang)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($po->items as $index => $item)
                            @php
                                $sisa = max($item->qty - $item->qty_received, 0);
                            @endphp
                            <tr>
                                <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                <td class="align-middle">
                                    {{ $item->item_name }}
                                    @if($item->barang)
                                        <br><small class="text-muted">[{{ $item->barang->nama_barang }}]</small>
                                    @endif
                                    @if($item->barcode)
                                        <br><small class="text-primary">Barcode: {{ $item->barcode }}</small>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $item->unit }}</td>
                                <td class="align-middle text-right">{{ $item->qty }}</td>
                                <td class="align-middle text-right">{{ $item->qty_received }}</td>
                                <td class="align-middle text-right">
                                    <input type="hidden"
                                           name="items[{{ $index }}][id]"
                                           value="{{ $item->id }}">
                                    <input type="number"
                                           name="items[{{ $index }}][qty_receive]"
                                           class="form-control form-control-sm text-right"
                                           min="0"
                                           max="{{ $sisa }}"
                                           value="{{ old("items.$index.qty_receive", $sisa) }}"
                                           {{ $sisa == 0 ? 'readonly' : '' }}>
                                    @if($sisa == 0)
                                        <small class="text-muted">Sudah full diterima</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Tidak ada item pada PO ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-3 py-2 text-muted small">
                <i class="fa fa-info-circle"></i>
                Isi <strong>Qty Diterima</strong> hanya untuk item yang benar-benar datang.
                Sistem akan otomatis:
                - menambah stok barang master,
                - mengupdate Qty Received di PO,
                - mengubah status PO (Pending / Partial / Completed).
            </div>
        </div>
    </div>

    <div class="text-right">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-check mr-1"></i> Submit Receiving
        </button>
    </div>
</form>
@endsection
