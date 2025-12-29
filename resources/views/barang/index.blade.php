@extends('layouts.app')

@include('barang.create')
@include('barang.edit')
@include('barang.show')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+39&display=swap" rel="stylesheet">

<style>
  .barcode39{ font-family:'Libre Barcode 39', cursive; font-size:42px; line-height:1; color:#000; white-space:nowrap; }
  .barcodeText{ font-size:11px; letter-spacing:2px; color:#6b7280; font-weight:600; margin-top:4px; }
  .barcodeCell{ min-width:220px; }

  /* =========================
     FIX CLICK MODAL (STISLA / STACKING CONTEXT)
     ========================= */
  .modal { z-index: 1060 !important; }
  .modal-backdrop { z-index: 1050 !important; }
  .swal2-container { z-index: 20000 !important; }

  /* Kadang ada overlay search stisla yang “nangkep” klik */
  .search-backdrop { z-index: 1045 !important; }
</style>

<div class="section-header">
  <h1>Data Barang</h1>

  <div class="ml-auto d-flex" style="gap:8px;">
    <a href="javascript:void(0)" class="btn btn-outline-primary" id="button_import_excel">
      <i class="fa fa-file-excel"></i> Import Excel
    </a>

    <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_barang">
      <i class="fa fa-plus"></i> Tambah Barang
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="table_id" class="display">
            <thead>
              <tr>
                <th>No</th>
                <th>Barcode</th>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Stok</th>
                <th>Opsi</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- MODAL IMPORT EXCEL --}}
<div class="modal fade" id="modal_import_excel" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Barang dari Excel</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="alert alert-info">
          Dibaca hanya <b>Nama</b> & <b>Stok</b>.
          Sistem akan cari header (Nama Barang/Stok/Qty/Jumlah/Saldo). Jika tidak ketemu, dianggap <b>A=Nama</b>, <b>B=Stok</b>.
        </div>

        <div class="form-group">
          <label>File Excel (.xlsx / .xls)</label>
          <input type="file" id="import_file" class="form-control" accept=".xlsx,.xls">
          <div class="text-danger mt-1 d-none" id="alert-import_file"></div>
        </div>

        <div class="row">
          <div class="col-md-4">
            <label>Jenis (default untuk semua item)</label>
            <select id="import_jenis_id" class="form-control">
              <option value="">-- Pilih Jenis --</option>
              @foreach($jenis_barangs as $j)
                <option value="{{ $j->id }}">{{ $j->jenis_barang }}</option>
              @endforeach
            </select>
            <div class="text-danger mt-1 d-none" id="alert-import_jenis_id"></div>
          </div>

          <div class="col-md-4">
            <label>Satuan (default untuk semua item)</label>
            <select id="import_satuan_id" class="form-control">
              <option value="">-- Pilih Satuan --</option>
              @foreach($satuans as $s)
                <option value="{{ $s->id }}">{{ $s->satuan }}</option>
              @endforeach
            </select>
            <div class="text-danger mt-1 d-none" id="alert-import_satuan_id"></div>
          </div>

          <div class="col-md-4">
            <label>Stok Minimum (default)</label>
            <input type="number" id="import_stok_minimum" class="form-control" value="0" min="0">
            <div class="text-danger mt-1 d-none" id="alert-import_stok_minimum"></div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn_do_import">
          <i class="fa fa-upload"></i> Import
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
/* =========================================================
   FIX UTAMA: MODAL DIPINDAH KE <body>
   Karena kalau modal berada di dalam container Stisla,
   backdrop/overlay bisa ketumpuk dan modal jadi ga bisa diklik.
   ========================================================= */
function moveModalsToBody() {
  const ids = ['#modal_import_excel', '#modal_tambah_barang', '#modal_edit_barang', '#modal_detail_barang'];
  ids.forEach(id => {
    if ($(id).length) $(id).appendTo('body');
  });
}

/* Bersihin overlay stisla (kadang search overlay nyangkut) */
function clearStislaOverlay(){
  $('body').removeClass('search-show');
  $('.search-backdrop').hide().removeClass('show active');
}

/* Bersihin backdrop bootstrap yang nyangkut */
function cleanBootstrapBackdrop(){
  $('.modal-backdrop').remove();
  $('body').removeClass('modal-open').css('padding-right','');
}

/* Optional: stisla kadang enforceFocus bikin input file/select ga fokus */
if ($.fn.modal && $.fn.modal.Constructor) {
  $.fn.modal.Constructor.prototype._enforceFocus = function() {};
}

$(document).ready(function() {
  // CSRF untuk semua ajax
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // pindahin modal ke body
  moveModalsToBody();

  // DataTable init sekali
  if (!$.fn.DataTable.isDataTable('#table_id')) {
    $('#table_id').DataTable({ paging: true });
  }
  loadTableBarang();
});

/* ============ UTIL BARCODE ============ */
function toCode39(value){
  if(!value) return { code39:'', human:'-' };
  let v = String(value).toUpperCase().trim();
  if(!v) return { code39:'', human:'-' };
  return { code39: `*${v}*`, human: v };
}

function renderPreview(inputSel, previewSel, humanSel){
  const raw = $(inputSel).val();
  const b = toCode39(raw);
  $(previewSel).text(b.code39);
  $(humanSel).text(b.human === '-' ? 'Menunggu input...' : b.human);
}

/* ============ LOAD TABLE ============ */
function loadTableBarang() {
  $.ajax({
    url: "/barang/get-data",
    type: "GET",
    dataType: "JSON",
    success: function(response) {
      let table = $('#table_id').DataTable();
      table.clear();

      let counter = 1;
      (response.data || []).forEach(value => {
        let stok = (value.stok !== null && value.stok !== '') ? value.stok : "Stok Kosong";
        let b = toCode39(value.barcode || '');

        let barcodeHtml = `
          <div class="barcodeCell">
            <div class="barcode39">${b.code39}</div>
            <div class="barcodeText">${b.human}</div>
          </div>
        `;

        table.row.add([
          counter++,
          barcodeHtml,
          (value.kode_barang ?? '-'),
          (value.nama_barang ?? '-'),
          stok,
          `
          <a href="javascript:void(0)" data-id="${value.id}"
             class="btn btn-icon btn-success btn-lg mb-2 btn-detail-barang"><i class="far fa-eye"></i></a>
          <a href="javascript:void(0)" data-id="${value.id}"
             class="btn btn-icon btn-warning btn-lg mb-2 btn-edit-barang"><i class="far fa-edit"></i></a>
          <a href="javascript:void(0)" data-id="${value.id}"
             class="btn btn-icon btn-danger btn-lg mb-2 btn-hapus-barang"><i class="fas fa-trash"></i></a>
          `
        ]);
      });

      table.draw(false);
    },
    error: function(xhr){
      console.error('LOAD TABLE ERROR:', xhr.status, xhr.responseText);
    }
  });
}

/* =========================================================
   OPEN MODAL SAFE (ANTI GELAP GA BISA KLIK)
   ========================================================= */
function openModalSafe(modalId){
  clearStislaOverlay();
  cleanBootstrapBackdrop();
  moveModalsToBody();

  // backdrop static biar ga ketutup overlay aneh
  $(modalId).modal({
    backdrop: 'static',
    keyboard: false
  });

  $(modalId).modal('show');

  // pastikan z-index modal > backdrop
  setTimeout(() => {
    const $m = $(modalId);
    const $b = $('.modal-backdrop').last();
    $b.css('z-index', 1050);
    $m.css('z-index', 1060);
  }, 10);
}

// bersihin backdrop setiap modal ditutup (biar ga nyangkut)
$(document).on('hidden.bs.modal', '.modal', function(){
  cleanBootstrapBackdrop();
  clearStislaOverlay();
});

/* ============ TAMBAH ============ */
$(document).on('click', '#button_tambah_barang', function() {
  clearStislaOverlay();
  cleanBootstrapBackdrop();
  moveModalsToBody();

  $('#alert-nama_barang, #alert-stok_minimum, #alert-jenis_id, #alert-satuan_id, #alert-deskripsi, #alert-barcode')
    .removeClass('d-block').addClass('d-none').html('');

  $('#nama_barang').val('');
  $('#barcode').val('');
  $('#stok_minimum').val('');
  $('#deskripsi').val('');

  openModalSafe('#modal_tambah_barang');
  renderPreview('#barcode', '#barcodePreviewCreate', '#barcodeHumanCreate');
});

$(document).on('input', '#barcode', function(){
  this.value = this.value.toUpperCase();
  renderPreview('#barcode', '#barcodePreviewCreate', '#barcodeHumanCreate');
});

$(document).on('click', '#store', function(e) {
  e.preventDefault();

  let formData = new FormData();
  formData.append('nama_barang', $('#nama_barang').val());
  formData.append('barcode', $('#barcode').val());
  formData.append('stok_minimum', $('#stok_minimum').val());
  formData.append('jenis_id', $('#jenis_id').val());
  formData.append('satuan_id', $('#satuan_id').val());
  formData.append('deskripsi', $('#deskripsi').val());

  $.ajax({
    url: '/barang',
    type: "POST",
    cache: false,
    data: formData,
    contentType: false,
    processData: false,
    success: function(response) {
      Swal.fire({ icon:'success', title: response.message || 'Berhasil', timer: 1800, showConfirmButton: true });
      loadTableBarang();
      $('#modal_tambah_barang').modal('hide');
    },
    error: function(error) {
      let res = error.responseJSON || {};
      if (res.nama_barang?.[0]) $('#alert-nama_barang').removeClass('d-none').addClass('d-block').html(res.nama_barang[0]);
      if (res.stok_minimum?.[0]) $('#alert-stok_minimum').removeClass('d-none').addClass('d-block').html(res.stok_minimum[0]);
      if (res.jenis_id?.[0]) $('#alert-jenis_id').removeClass('d-none').addClass('d-block').html(res.jenis_id[0]);
      if (res.satuan_id?.[0]) $('#alert-satuan_id').removeClass('d-none').addClass('d-block').html(res.satuan_id[0]);
      if (res.deskripsi?.[0]) $('#alert-deskripsi').removeClass('d-none').addClass('d-block').html(res.deskripsi[0]);
      if (res.barcode?.[0]) $('#alert-barcode').removeClass('d-none').addClass('d-block').html(res.barcode[0]);
    }
  });
});

/* ============ DETAIL ============ */
$(document).on('click', '.btn-detail-barang', function() {
  let barang_id = $(this).data('id');

  $.ajax({
    url: `/barang/${barang_id}/`,
    type: "GET",
    cache: false,
    success: function(response) {
      const d = response.data;

      $('#detail_barang_id').val(d.id);
      $('#detail_kode_barang').val(d.kode_barang ?? '-');
      $('#detail_nama_barang').val(d.nama_barang ?? '-');
      $('#detail_barcode').val(d.barcode ?? '-');
      $('#detail_jenis_id').val(d.jenis_id);
      $('#detail_satuan_id').val(d.satuan_id);
      $('#detail_stok').val((d.stok !== null && d.stok !== '') ? d.stok : 'Stok Kosong');
      $('#detail_stok_minimum').val(d.stok_minimum ?? 0);
      $('#detail_deskripsi').val(d.deskripsi ?? '');

      let b = toCode39(d.barcode || '');
      $('#barcodePreviewDetail').text(b.code39);
      $('#barcodeHumanDetail').text(b.human);

      openModalSafe('#modal_detail_barang');
    },
    error: function(xhr){
      console.error('DETAIL ERROR:', xhr.status, xhr.responseText);
    }
  });
});

/* ============ EDIT ============ */
$(document).on('click', '.btn-edit-barang', function() {
  let barang_id = $(this).data('id');

  $('#alert-edit_nama_barang, #alert-edit_stok_minimum, #alert-edit_deskripsi, #alert-edit_barcode, #alert-edit_jenis_id, #alert-edit_satuan_id')
    .removeClass('d-block').addClass('d-none').html('');

  $.ajax({
    url: `/barang/${barang_id}/edit`,
    type: "GET",
    cache: false,
    success: function(response) {
      const d = response.data;

      $('#barang_id').val(d.id);
      $('#edit_nama_barang').val(d.nama_barang ?? '');
      $('#edit_barcode').val((d.barcode ?? '').toUpperCase());
      $('#edit_stok_minimum').val(d.stok_minimum ?? 0);
      $('#edit_jenis_id').val(d.jenis_id);
      $('#edit_satuan_id').val(d.satuan_id);
      $('#edit_deskripsi').val(d.deskripsi ?? '');

      renderPreview('#edit_barcode', '#barcodePreviewEdit', '#barcodeHumanEdit');
      openModalSafe('#modal_edit_barang');
    },
    error: function(xhr){
      console.error('EDIT GET ERROR:', xhr.status, xhr.responseText);
    }
  });
});

$(document).on('input', '#edit_barcode', function(){
  this.value = this.value.toUpperCase();
  renderPreview('#edit_barcode', '#barcodePreviewEdit', '#barcodeHumanEdit');
});

$(document).on('click', '#update', function(e) {
  e.preventDefault();

  let barang_id = $('#barang_id').val();
  let formData = new FormData();

  formData.append('nama_barang', $('#edit_nama_barang').val());
  formData.append('barcode', $('#edit_barcode').val());
  formData.append('stok_minimum', $('#edit_stok_minimum').val());
  formData.append('deskripsi', $('#edit_deskripsi').val());
  formData.append('jenis_id', $('#edit_jenis_id').val());
  formData.append('satuan_id', $('#edit_satuan_id').val());
  formData.append('_method', 'PUT');

  $.ajax({
    url: `/barang/${barang_id}`,
    type: "POST",
    cache: false,
    data: formData,
    contentType: false,
    processData: false,
    success: function(response) {
      Swal.fire({ icon:'success', title: response.message || 'Berhasil', timer: 1800, showConfirmButton: true });
      loadTableBarang();
      $('#modal_edit_barang').modal('hide');
    },
    error: function(error) {
      let res = error.responseJSON || {};
      if (res.nama_barang?.[0]) $('#alert-edit_nama_barang').removeClass('d-none').addClass('d-block').html(res.nama_barang[0]);
      if (res.stok_minimum?.[0]) $('#alert-edit_stok_minimum').removeClass('d-none').addClass('d-block').html(res.stok_minimum[0]);
      if (res.deskripsi?.[0]) $('#alert-edit_deskripsi').removeClass('d-none').addClass('d-block').html(res.deskripsi[0]);
      if (res.barcode?.[0]) $('#alert-edit_barcode').removeClass('d-none').addClass('d-block').html(res.barcode[0]);
      if (res.jenis_id?.[0]) $('#alert-edit_jenis_id').removeClass('d-none').addClass('d-block').html(res.jenis_id[0]);
      if (res.satuan_id?.[0]) $('#alert-edit_satuan_id').removeClass('d-none').addClass('d-block').html(res.satuan_id[0]);
    }
  });
});

/* ============ HAPUS ============ */
$(document).on('click', '.btn-hapus-barang', function() {
  let barang_id = $(this).data('id');

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
      url: `/barang/${barang_id}`,
      type: "POST",
      dataType: "json",
      data: { _method: "DELETE" },
      success: function(response) {
        Swal.fire({ icon:'success', title: response.message || 'Berhasil dihapus', timer: 1800, showConfirmButton: true });
        loadTableBarang();
      },
      error: function(xhr) {
        let msg = 'Gagal menghapus data.';
        if (xhr.status === 419) msg = 'CSRF token expired. Refresh halaman lalu coba lagi.';
        if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
        if (xhr.status === 500) msg = 'Barang tidak bisa dihapus karena sudah dipakai di transaksi (FK constraint).';

        Swal.fire({ icon:'error', title:'Error', text: msg });
        console.error('DELETE ERROR:', xhr.status, xhr.responseText);
      }
    });
  });
});

/* ================== IMPORT EXCEL ================== */
$(document).on('click', '#button_import_excel', function(){
  $('#alert-import_file,#alert-import_jenis_id,#alert-import_satuan_id,#alert-import_stok_minimum')
    .addClass('d-none').removeClass('d-block').html('');

  $('#import_file').val('');
  $('#import_jenis_id').val('');
  $('#import_satuan_id').val('');
  $('#import_stok_minimum').val(0);

  openModalSafe('#modal_import_excel');
});

$(document).on('click', '#btn_do_import', function(e){
  e.preventDefault();

  let file = $('#import_file')[0].files[0];
  let jenisId = $('#import_jenis_id').val();
  let satuanId = $('#import_satuan_id').val();
  let stokMin = $('#import_stok_minimum').val();

  $('#alert-import_file,#alert-import_jenis_id,#alert-import_satuan_id,#alert-import_stok_minimum')
    .addClass('d-none').removeClass('d-block').html('');

  let fd = new FormData();
  fd.append('file', file);
  fd.append('jenis_id', jenisId);
  fd.append('satuan_id', satuanId);
  fd.append('stok_minimum', stokMin);

  Swal.fire({
    title: 'Import berjalan...',
    text: 'Jangan tutup halaman.',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  $.ajax({
    url: "{{ route('barang.import-excel') }}",
    method: "POST",
    data: fd,
    processData: false,
    contentType: false,
    success: function(res){
      Swal.fire({ icon:'success', title:'Berhasil', text: res.message || 'Import selesai' });
      $('#modal_import_excel').modal('hide');
      loadTableBarang();
    },
    error: function(xhr){
      Swal.close();

      if (xhr.status === 422) {
        let err = xhr.responseJSON || {};
        if (err.file?.[0]) $('#alert-import_file').removeClass('d-none').addClass('d-block').html(err.file[0]);
        if (err.jenis_id?.[0]) $('#alert-import_jenis_id').removeClass('d-none').addClass('d-block').html(err.jenis_id[0]);
        if (err.satuan_id?.[0]) $('#alert-import_satuan_id').removeClass('d-none').addClass('d-block').html(err.satuan_id[0]);
        if (err.stok_minimum?.[0]) $('#alert-import_stok_minimum').removeClass('d-none').addClass('d-block').html(err.stok_minimum[0]);
        return;
      }

      let msg = 'Gagal import.';
      if (xhr.responseJSON?.message) msg = xhr.responseJSON.message;
      Swal.fire({ icon:'error', title:'Error', text: msg });
      console.error(xhr.status, xhr.responseText);
    }
  });
});
</script>
@endpush
