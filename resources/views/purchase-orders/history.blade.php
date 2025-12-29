{{-- resources/views/purchase-orders/history.blade.php --}}
@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="section-header">
    <h1>History Purchase Order (Completed)</h1>
    <div class="ml-auto">
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
            &larr; Kembali ke PO Aktif
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
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_id" class="display">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No PO</th>
                                <th>Supplier</th>
                                <th>Departemen</th>
                                <th>Estimate Date</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrders as $po)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $po->po_number }}</td>
                                    <td>{{ $po->supplier->supplier ?? '-' }}</td>
                                    <td>{{ $po->department->name ?? '-' }}</td>
                                    <td>
                                        {{ $po->estimate_date
                                            ? Carbon::parse($po->estimate_date)->format('d-m-Y')
                                            : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-success">
                                            {{ ucfirst($po->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $po->creator->name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-sm btn-info">
                                            Detail
                                        </a>
                                        {{-- history: ga ada tombol hapus --}}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#table_id').DataTable({
            paging: true
        });
    });
</script>
@endsection
