@extends('layouts.app')

@section('content')
<div class="section-header">
  <h1>Laporan Barang Keluar</h1>
  <div class="ml-auto">
    <a href="javascript:void(0)" class="btn btn-danger" id="btn_print_barang_keluar">
      <i class="fas fa-print"></i> Print PDF
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">

    {{-- FILTER --}}
    <div class="card">
      <div class="card-body">
        <form id="filter_form_barang_keluar" class="row">
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
            <button type="button" class="btn btn-danger" id="btn_refresh_barang_keluar" style="flex:1;">
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
          <table id="table_barang_keluar" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th style="width:170px">Kode Transaksi</th>
                <th style="width:150px">Tanggal Keluar</th>
                <th>Nama Barang</th>
                <th style="width:140px">Jumlah Keluar</th>
                <th style="width:220px">Departemen</th>
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
  if ($.fn.DataTable.isDataTable('#table_barang_keluar')) {
    $('#table_barang_keluar').DataTable().destroy();
  }

  const table = $('#table_barang_keluar').DataTable({
    paging: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false
  });

  loadData();

  $('#filter_form_barang_keluar').on('submit', function(e){
    e.preventDefault();
    loadData();
  });

  $('#btn_refresh_barang_keluar').on('click', function(){
    $('#tanggal_mulai').val('');
    $('#tanggal_selesai').val('');
    loadData();
  });

  $('#btn_print_barang_keluar').on('click', function(){
    const mulai = $('#tanggal_mulai').val();
    const selesai = $('#tanggal_selesai').val();

    let url = `/laporan-barang-keluar/print-barang-keluar`;
    if (mulai && selesai) {
      url += `?tanggal_mulai=${encodeURIComponent(mulai)}&tanggal_selesai=${encodeURIComponent(selesai)}`;
    }
    window.location.href = url;
  });

  async function getCustomerMap(){
    try {
      const res = await fetch(`{{ route('api.customer') }}`, { headers: { 'Accept':'application/json' }});
      const customers = await res.json();
      const map = {};
      (customers || []).forEach(c => map[String(c.id)] = c.customer);
      return map;
    } catch(e){
      return {};
    }
  }

  async function loadData(){
    const mulai = $('#tanggal_mulai').val();
    const selesai = $('#tanggal_selesai').val();

    table.clear().draw();

    const customerMap = await getCustomerMap();

    $.ajax({
      url: "{{ route('laporan-barang-keluar.get-data') }}", // /laporan-barang-keluar/get-data
      type: "GET",
      dataType: "JSON",
      data: {
        tanggal_mulai: mulai,
        tanggal_selesai: selesai
      },
      success: function(response){
        const items = Array.isArray(response) ? response : (response.data || []);

        if (!items.length){
          table.row.add(['', 'Tidak ada data yang tersedia.', '', '', '', '']).draw(false);
          return;
        }

        let no = 1;
        items.forEach(function(item){
          const deptName = customerMap[String(item.customer_id)] || '-';

          table.row.add([
            no++,
            item.kode_transaksi ?? '-',
            item.tanggal_keluar ?? '-',
            item.nama_barang ?? '-',
            item.jumlah_keluar ?? 0,
            deptName
          ]);
        });

        table.draw(false);
      },
      error: function(xhr){
        console.error('LAPORAN BARANG KELUAR ERROR:', xhr.status, xhr.responseText);
        Swal.fire({ icon:'error', title:'Error', text:'Gagal memuat laporan barang keluar.' });
      }
    });
  }
});
</script>
@endpush
