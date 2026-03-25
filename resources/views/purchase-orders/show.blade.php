{{-- resources/views/purchase-orders/show.blade.php --}}
@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="section-header">
    <h1>Detail PO: {{ $po->po_number }}</h1>
    <div class="ml-auto">
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
            &larr; Kembali
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4>Header PO</h4></div>

            <div class="card-body">
                @php
                    $badgeClass = match($po->status) {
                        'pending'         => 'warning',
                        'pending_review'  => 'info',
                        'partial'         => 'primary',
                        'completed'       => 'success',
                        default           => 'secondary',
                    };

                    $labelStatus = [
                        'pending'         => 'Pending',
                        'pending_review'  => 'Pending Purchasing Review',
                        'partial'         => 'Partial',
                        'completed'       => 'Completed',
                    ][$po->status] ?? ucfirst($po->status);
                @endphp

                <dl class="row mb-0">

                    <dt class="col-sm-4">No PO</dt>
                    <dd class="col-sm-8">{{ $po->po_number }}</dd>

                    <dt class="col-sm-4">Supplier</dt>
                    <dd class="col-sm-8">{{ $po->supplier->supplier ?? '-' }}</dd>

                    <dt class="col-sm-4">Departemen</dt>
                    <dd class="col-sm-8">
                        {{ $po->department->code ?? '' }} {{ $po->department->name ?? '' }}
                    </dd>

                    <dt class="col-sm-4">Estimate Date</dt>
                    <dd class="col-sm-8">
                        {{ $po->estimate_date
                            ? Carbon::parse($po->estimate_date)->format('d-m-Y')
                            : '-' }}
                    </dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-{{ $badgeClass }}">
                            {{ $labelStatus }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Dibuat Oleh</dt>
                    <dd class="col-sm-8">{{ $po->creator->name ?? '-' }}</dd>

                    <dt class="col-sm-4">Catatan</dt>
                    <dd class="col-sm-8">{{ $po->notes ?? '-' }}</dd>
                </dl>
            </div>
        </div>

        @if($po->status === 'pending_review')
            @php
                $totalQty      = $po->items->sum('qty');
                $totalReceived = $po->items->sum('qty_received');
                $remaining     = $totalQty - $totalReceived;
            @endphp

            <div class="card mt-3">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0">Purchasing Review</h4>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        Qty diterima tidak sama dengan Qty PO. Silakan tentukan tindakan:
                    </p>

                    <ul class="mb-3">
                        <li><strong>Qty PO</strong> : {{ $totalQty }}</li>
                        <li><strong>Total Received</strong> : {{ $totalReceived }}</li>
                        <li><strong>Selisih</strong> : {{ $remaining }}</li>
                    </ul>

                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('purchase-orders.approve', $po->id) }}" method="POST" class="mr-2">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                ✔ Approve – lanjutkan transaksi
                            </button>
                        </form>

                        <form action="{{ route('purchase-orders.force-close', $po->id) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin menutup PO ini? Sisa qty tidak akan dipesan lagi.');">
                            @csrf
                            <button type="submit" class="btn btn-danger">
                                ✖ Force Close – tutup PO
                            </button>
                        </form>
                    </div>

                    <small class="text-muted d-block mt-2">
                        Approve: PO tetap aktif (status Partial / Completed).<br>
                        Force Close: PO ditutup, sisa qty dianggap batal.
                    </small>
                </div>
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><h4>Item PO</h4></div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Item</th>
                                <th>Unit</th>
                                <th>Qty PO</th>
                                <th>Qty Received</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($po->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>
                                        {{ $item->item_name }}

                                        @if($item->barang)
                                            <br>
                                            <small class="text-muted">
                                                [{{ $item->barang->nama_barang }}]
                                            </small>
                                        @endif

                                        @if($item->barcode)
                                            <br>
                                            <small class="text-primary">
                                                Barcode: {{ $item->barcode }}
                                            </small>
                                        @endif
                                    </td>

                                    <td>{{ $item->unit }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ $item->qty_received }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Belum ada item.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($po->status !== 'completed')
                    <div class="alert alert-info mt-2 mb-0 p-0 border-0 bg-transparent">
                        <a href="{{ route('barang-masuk.create-from-po', $po->id) }}"
                           class="btn btn-primary btn-block">
                            Integrasi ke modul <strong>Barang Masuk</strong> (Receive Goods) klik di sini.
                        </a>
                    </div>
                @else
                    <div class="alert alert-success mt-2 mb-0">
                        PO ini sudah <strong>Completed</strong>. Tidak bisa melakukan penerimaan lagi.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection