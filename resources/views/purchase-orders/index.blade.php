{{-- resources/views/purchase-orders/index.blade.php --}}
@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@section('content')
<div class="section-header">
  <h1>Purchase Order (Aktif)</h1>

  <div class="ml-auto d-flex" style="gap:8px;">
    <a href="{{ route('purchase-orders.history') }}" class="btn btn-outline-secondary">
      <i class="fa fa-history"></i> History
    </a>

    <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
      <i class="fa fa-plus"></i> Buat PO Baru
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
          <table id="table_po" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th>No PO</th>
                <th>Supplier</th>
                <th>Departemen</th>
                <th style="width:140px">Estimate Date</th>
                <th style="width:160px">Status</th>
                <th>Dibuat Oleh</th>
                <th style="width:180px">Opsi</th>
              </tr>
            </thead>

            <tbody>
              @forelse($purchaseOrders as $po)
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
                  ][$po->status] ?? ucfirst((string)$po->status);

                  $estimate = $po->estimate_date
                    ? Carbon::parse($po->estimate_date)->format('d-m-Y')
                    : '-';

                  $supplierName   = optional($po->supplier)->supplier ?? '-';
                  $departmentName = optional($po->department)->name ?? '-';
                  $creatorName    = optional($po->creator)->name ?? '-';
                @endphp

                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $po->po_number }}</td>
                  <td>{{ $supplierName }}</td>
                  <td>{{ $departmentName }}</td>
                  <td>{{ $estimate }}</td>
                  <td>
                    <span class="badge badge-{{ $badgeClass }}">
                      {{ $labelStatus }}
                    </span>
                  </td>
                  <td>{{ $creatorName }}</td>
                  <td>
                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="btn btn-sm btn-info">
                      Detail
                    </a>

                    @if($po->status === 'pending')
                      <form action="{{ route('purchase-orders.destroy', $po->id) }}"
                            method="POST"
                            class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus PO ini?')">
                          Hapus
                        </button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-center text-muted">Belum ada Purchase Order aktif.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
              @endforelse
            </tbody>

          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
  if ($.fn.DataTable.isDataTable('#table_po')) {
    $('#table_po').DataTable().destroy();
  }

  $('#table_po').DataTable({
    paging: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
    responsive: false
  });
});
</script>
@endpush