@extends('layouts.app')

@php
  use Carbon\Carbon;
@endphp

@section('content')

<div class="section-header">
  <h1>Permintaan Barang</h1>

  <div class="ml-auto d-flex" style="gap:8px;">
    <a href="{{ route('permintaan.create') }}" class="btn btn-primary">
      <i class="fa fa-plus"></i> Buat Permintaan Baru
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
          <table id="table_permintaan" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th>Kode</th>
                <th style="width:160px">Tanggal</th>
                <th style="width:160px">Status</th>
                <th>Catatan</th>
                <th style="width:150px">Opsi</th>
              </tr>
            </thead>

            <tbody>
              @forelse($permintaans as $permintaan)

                @php
                  $badgeClass = match($permintaan->status) {
                    'pending'   => 'warning',
                    'approved'  => 'success',
                    'processed' => 'primary',
                    'rejected'  => 'danger',
                    default     => 'secondary',
                  };

                  $labelStatus = [
                    'pending'   => 'Pending',
                    'approved'  => 'Approved',
                    'processed' => 'Processed',
                    'rejected'  => 'Rejected',
                  ][$permintaan->status] ?? ucfirst((string)$permintaan->status);

                  $tanggal = Carbon::parse($permintaan->created_at)->format('d-m-Y');
                @endphp

                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $permintaan->kode }}</td>
                  <td>{{ $tanggal }}</td>
                  <td>
                    <span class="badge badge-{{ $badgeClass }}">
                      {{ $labelStatus }}
                    </span>
                  </td>
                  <td>{{ \Illuminate\Support\Str::limit($permintaan->note, 60) }}</td>
                  <td>
                    <a href="{{ route('permintaan.show', $permintaan->id) }}" 
                       class="btn btn-sm btn-info">
                      Detail
                    </a>
                  </td>
                </tr>

              @empty
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td class="text-center text-muted">
                    Belum ada permintaan.
                  </td>
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

  if ($.fn.DataTable.isDataTable('#table_permintaan')) {
    $('#table_permintaan').DataTable().destroy();
  }

  $('#table_permintaan').DataTable({
    paging: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false,
    responsive: false
  });

});
</script>
@endpush