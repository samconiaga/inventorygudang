@extends('layouts.app')

@section('content')
<div class="section-header">
  <h1>Laporan Barang Masuk</h1>
  <div class="ml-auto">
    <a href="javascript:void(0)" class="btn btn-danger" id="btn_print_barang_masuk">
      <i class="fas fa-print"></i> Print PDF
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">

    {{-- FILTER --}}
    <div class="card">
      <div class="card-body">
        <form id="filter_form_barang_masuk" class="row">
          <div class="col-md-5">
            <label>Pilih Tanggal Mulai :</label>
            <input type="date" class="form-control" id="tanggal_mulai">
          </div>

          <div class="col-md-5">
            <label>Pilih Tanggal Selesai :</label>
            <input type="date" class="form-control" id="tanggal_selesai">
          </div>

          <div class="col-md-2 d-flex align-items-end" style="gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;">Filter</button>
            <button type="button" class="btn btn-danger" id="btn_refresh_barang_masuk" style="flex:1;">
              Refresh
            </button>
          </div>
        </form>
      </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="table_barang_masuk" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th style="width:170px">Kode Transaksi</th>
                <th style="width:150px">Tanggal Masuk</th>
                <th>Nama Barang</th>
                <th style="width:140px">Jumlah Masuk</th>
                <th style="width:220px">Supplier</th>
              </tr>
            </thead>
            <tbody></tbody>
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
  // init datatable sekali
  if ($.fn.DataTable.isDataTable('#table_barang_masuk')) {
    $('#table_barang_masuk').DataTable().destroy();
  }

  const table = $('#table_barang_masuk').DataTable({
    paging: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false
  });

  // load awal
  loadData();

  // filter submit
  $('#filter_form_barang_masuk').on('submit', function(e){
    e.preventDefault();
    loadData();
  });

  // refresh
  $('#btn_refresh_barang_masuk').on('click', function(){
    $('#tanggal_mulai').val('');
    $('#tanggal_selesai').val('');
    loadData();
  });

  // print
  $('#btn_print_barang_masuk').on('click', function(){
    const mulai = $('#tanggal_mulai').val();
    const selesai = $('#tanggal_selesai').val();

    let url = `/laporan-barang-masuk/print-barang-masuk`;
    if (mulai && selesai) {
      url += `?tanggal_mulai=${encodeURIComponent(mulai)}&tanggal_selesai=${encodeURIComponent(selesai)}`;
    }
    window.location.href = url;
  });

  async function getSupplierMap(){
    // ambil sekali aja
    try {
      const res = await fetch(`{{ route('api.supplier') }}`, { headers: { 'Accept':'application/json' }});
      const suppliers = await res.json();
      const map = {};
      (suppliers || []).forEach(s => map[String(s.id)] = s.supplier);
      return map;
    } catch(e){
      return {};
    }
  }

  async function loadData(){
    const mulai = $('#tanggal_mulai').val();
    const selesai = $('#tanggal_selesai').val();

    table.clear().draw();

    const supplierMap = await getSupplierMap();

    $.ajax({
      url: "{{ route('laporan-barang-masuk.get-data') }}", // /laporan-barang-masuk/get-data
      type: "GET",
      dataType: "JSON",
      data: {
        tanggal_mulai: mulai,
        tanggal_selesai: selesai
      },
      success: function(response){
        // response bisa array langsung atau {data:[]}
        const items = Array.isArray(response) ? response : (response.data || []);

        if (!items.length){
          table.row.add(['', 'Tidak ada data yang tersedia.', '', '', '', '']).draw(false);
          return;
        }

        let no = 1;
        items.forEach(function(item){
          const supplierName = supplierMap[String(item.supplier_id)] || '-';

          table.row.add([
            no++,
            item.kode_transaksi ?? '-',
            item.tanggal_masuk ?? '-',
            item.nama_barang ?? '-',
            item.jumlah_masuk ?? 0,
            supplierName
          ]);
        });

        table.draw(false);
      },
      error: function(xhr){
        console.error('LAPORAN BARANG MASUK ERROR:', xhr.status, xhr.responseText);
        Swal.fire({ icon:'error', title:'Error', text:'Gagal memuat laporan barang masuk.' });
      }
    });
  }
});
</script>
@endpush
