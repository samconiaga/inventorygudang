{{-- resources/views/barang-masuk/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="section-header">
  <h1>Barang Masuk</h1>
  {{-- Tidak ada tombol + Barang Masuk, karena semua barang masuk dari PO --}}
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="table_bm" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th>Kode Transaksi</th>
                <th style="width:140px">Tanggal Masuk</th>
                <th>Nama Barang</th>
                <th style="width:120px">Stok Masuk</th>
                <th>Supplier</th>
                <th style="width:120px">Opsi</th>
              </tr>
            </thead>
            <tbody>
              {{-- via AJAX --}}
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
  // init datatable sekali
  if ($.fn.DataTable.isDataTable('#table_bm')) {
    $('#table_bm').DataTable().destroy();
  }

  $('#table_bm').DataTable({
    paging: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    autoWidth: false
  });

  loadBarangMasuk();
});

function getSupplierName(suppliers, supplierId) {
  if (!Array.isArray(suppliers)) return '-';
  // hati-hati: supplierId bisa string dari JSON
  const sid = String(supplierId);
  const s = suppliers.find(x => String(x.id) === sid);
  return s ? (s.supplier ?? '-') : '-';
}

function fmtDate(dateStr){
  // kalau backend sudah format, biarin aja
  if (!dateStr) return '-';
  return dateStr;
}

function loadBarangMasuk() {
  $.ajax({
    url: "{{ route('barang-masuk.get-data') }}",
    type: "GET",
    dataType: "JSON",
    success: function (response) {
      const table = $('#table_bm').DataTable();
      table.clear();

      const rows = response.data || [];
      const suppliers = response.supplier || [];

      let counter = 1;

      rows.forEach(function (value) {
        const supplier = getSupplierName(suppliers, value.supplier_id);

        const rowHtml = `
          <tr id="index_${value.id}">
            <td>${counter++}</td>
            <td>${value.kode_transaksi ?? '-'}</td>
            <td>${fmtDate(value.tanggal_masuk)}</td>
            <td>${value.nama_barang ?? '-'}</td>
            <td>${value.jumlah_masuk ?? 0}</td>
            <td>${supplier}</td>
            <td>
              <button type="button"
                      class="btn btn-icon btn-danger btn-sm btn-hapus"
                      data-id="${value.id}">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `;

        table.row.add($(rowHtml));
      });

      table.draw(false);
    },
    error: function (xhr) {
      console.error('LOAD BARANG MASUK ERROR:', xhr.status, xhr.responseText);
    }
  });
}

// Hapus Barang Masuk
$(document).on('click', '.btn-hapus', function () {
  const id = $(this).data('id');
  const token = $("meta[name='csrf-token']").attr("content");

  Swal.fire({
    title: 'Apakah Kamu Yakin?',
    text: "ingin menghapus data ini!",
    icon: 'warning',
    showCancelButton: true,
    cancelButtonText: 'TIDAK',
    confirmButtonText: 'YA, HAPUS!'
  }).then((result) => {
    if (!result.isConfirmed) return;

    $.ajax({
      url: `/barang-masuk/${id}`,
      type: "DELETE",
      data: { _token: token },
      success: function (response) {
        Swal.fire({
          icon: 'success',
          title: response.message || 'Berhasil dihapus',
          showConfirmButton: true,
          timer: 2500
        });
        loadBarangMasuk();
      },
      error: function (xhr) {
        let msg = 'Gagal menghapus data.';
        if (xhr.status === 419) msg = 'CSRF token expired. Refresh halaman lalu coba lagi.';
        if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
        Swal.fire({ icon:'error', title:'Error', text: msg });
        console.error('DELETE ERROR:', xhr.status, xhr.responseText);
      }
    });
  });
});
</script>
@endpush
