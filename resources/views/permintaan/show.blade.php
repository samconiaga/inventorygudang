@extends('layouts.app')

@php use Carbon\Carbon; @endphp

@section('content')

<div class="section-header">
  <h1>Detail Permintaan</h1>

  <div class="ml-auto">
    <a href="{{ route('permintaan.index') }}" class="btn btn-outline-secondary">
      <i class="fa fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">

    {{-- INFO CARD --}}
    <div class="card mb-3">
      <div class="card-body">

        @php
          $badgeClass = match($permintaan->status) {
            'pending'   => 'warning',
            'approved'  => 'success',
            'processed' => 'primary',
            'rejected'  => 'danger',
            default     => 'secondary',
          };
        @endphp

        <div class="row">
          <div class="col-md-4">
            <strong>Kode:</strong><br>
            {{ $permintaan->kode }}
          </div>

          <div class="col-md-4">
            <strong>Pemohon:</strong><br>
            {{ optional($permintaan->pemohon)->name ?? '-' }}
          </div>

          <div class="col-md-4">
            <strong>Tanggal:</strong><br>
            {{ Carbon::parse($permintaan->created_at)->format('d-m-Y H:i') }}
          </div>
        </div>

        <div class="mt-3">
          <strong>Status:</strong><br>
          <span class="badge badge-{{ $badgeClass }}">
            {{ ucfirst($permintaan->status) }}
          </span>
        </div>

        <div class="mt-3">
          <strong>Catatan:</strong><br>
          {{ $permintaan->note ?? '-' }}
        </div>

      </div>
    </div>


    {{-- ITEMS TABLE --}}
    <div class="card">
      <div class="card-body">

        <div class="table-responsive">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Barang</th>
                <th style="width:120px">Qty</th>
                <th style="width:150px">Stok Saat Ini</th>
              </tr>
            </thead>

            <tbody>
              @foreach($permintaan->items as $item)
                <tr>
                  <td>{{ optional($item->barang)->nama_barang ?? '-' }}</td>
                  <td>{{ $item->qty }}</td>
                  <td>{{ optional($item->barang)->stok ?? '-' }}</td>
                </tr>
              @endforeach
            </tbody>

          </table>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="mt-3">

          @if($permintaan->status === 'pending')
            <form action="{{ route('permintaan.approve', $permintaan->id) }}" method="POST" class="d-inline">
              @csrf
              <button class="btn btn-success">
                <i class="fa fa-check"></i> Approve
              </button>
            </form>

            <form action="{{ route('permintaan.reject', $permintaan->id) }}" method="POST" class="d-inline">
              @csrf
              <button class="btn btn-danger">
                <i class="fa fa-times"></i> Reject
              </button>
            </form>
          @endif

          @if(in_array($permintaan->status, ['approved','pending']))
            <form action="{{ route('permintaan.process', $permintaan->id) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Proses keluar barang? Stok akan dikurangi.')">
              @csrf
              <button class="btn btn-primary">
                <i class="fa fa-box"></i> Proses Keluar
              </button>
            </form>
          @endif

        </div>

      </div>
    </div>

  </div>
</div>

@endsection