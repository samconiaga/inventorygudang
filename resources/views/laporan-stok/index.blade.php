{{-- resources/views/laporan-stok/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="section-header">
  <h1>Laporan Stok</h1>
  <div class="ml-auto">
    <a href="javascript:void(0)" class="btn btn-danger" id="btn_print_stok">
      <i class="fas fa-print"></i> Print PDF
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">

    {{-- FILTER --}}
    <div class="card">
      <div class="card-body">
        <div class="form-group mb-0">
          <label for="opsi_laporan_stok">Filter Stok Berdasarkan :</label>
          <select class="form-control" id="opsi_laporan_stok">
            <option value="semua" selected>Semua</option>
            <option value="minimum">Batas Minimum</option>
            <option value="stok-habis">Stok Habis</option>
          </select>
        </div>
      </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="table_stok" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th style="width:160px">Kode Barang</th>
                <th>Nama Barang</th>
                <th style="width:120px">Stok</th>
              </tr>
            </thead>
            <tbody>
              {{-- via ajax --}}
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
  // init datatable sekali (anti dobel init)
  if ($.fn.DataTable.isDataTable('#table_stok')) {
    $('#table_stok').DataTable().destroy();
  }

  const table = $('#table_stok').DataTable({
    paging: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false
  });

  // load awal
  loadData($('#opsi_laporan_stok').val());

  // change filter
  $('#opsi_laporan_stok').on('change', function () {
    loadData(this.value);
  });

  // print
  $('#btn_print_stok').on('click', function () {
    const opsi = $('#opsi_laporan_stok').val();
    // pakai URL yang kamu sudah punya
    window.location.href = `/laporan-stok/print-stok?opsi=${encodeURIComponent(opsi)}`;
  });

  function loadData(opsi) {
    $.ajax({
      url: "{{ route('laporan-stok.get-data') }}", // /laporan-stok/get-data
      type: "GET",
      dataType: "JSON",
      data: { opsi: opsi },
      success: function (response) {
        table.clear();

        // response bisa array langsung atau {data:[]}
        const items = Array.isArray(response) ? response : (response.data || []);
        let no = 1;

        items.forEach(function (item) {
          table.row.add([
            no++,
            item.kode_barang ?? '-',
            item.nama_barang ?? '-',
            item.stok ?? 0
          ]);
        });

        table.draw(false);
      },
      error: function (xhr) {
        console.error('LAPORAN STOK ERROR:', xhr.status, xhr.responseText);
        table.clear().draw();

        Swal.fire({
          icon: 'error',
          title: 'Gagal memuat data',
          text: 'Cek console / endpoint / response backend.'
        });
      }
    });
  }
});
</script>
@endpush
