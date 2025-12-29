@extends('layouts.app')

@include('supplier.create')
@include('supplier.edit')

@section('content')
<div class="section-header">
  <h1>Data Supplier</h1>
  <div class="ml-auto">
    <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_supplier">
      <i class="fa fa-plus"></i> + Supplier
    </a>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="table_supplier" class="table table-bordered table-striped" style="width:100%">
            <thead>
              <tr>
                <th style="width:70px">No</th>
                <th>Nama Perusahaan</th>
                <th>Alamat</th>
                <th style="width:160px">Opsi</th>
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

  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
  });

  // ==========================
  // DATATABLE AJAX
  // ==========================
  const table = $('#table_supplier').DataTable({
    paging: true,
    autoWidth: false,
    processing: true,
    ajax: {
      url: "/supplier/get-data",
      type: "GET",
      dataSrc: function (json) {
        return json.data || [];
      },
      error: function(xhr){
        console.error("GET DATA SUPPLIER ERROR:", xhr.status, xhr.responseText);
      }
    },
    columns: [
      { data: null, render: function (d, t, r, meta) { return meta.row + 1; } },
      { data: "supplier", defaultContent: "-" },
      { data: "alamat", defaultContent: "-" },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (row) {
          return `
            <a href="javascript:void(0)" data-id="${row.id}"
              class="btn btn-icon btn-warning btn-sm mb-1 btn-edit-supplier">
              <i class="far fa-edit"></i>
            </a>
            <a href="javascript:void(0)" data-id="${row.id}"
              class="btn btn-icon btn-danger btn-sm mb-1 btn-hapus-supplier">
              <i class="fas fa-trash"></i>
            </a>
          `;
        }
      }
    ]
  });

  function reloadTable(){
    table.ajax.reload(null, false);
  }

  // ==========================
  // TAMBAH
  // ==========================
  $(document).on('click', '#button_tambah_supplier', function(){
    $('#alert-supplier, #alert-alamat').addClass('d-none').removeClass('d-block').html('');
    $('#supplier').val('');
    $('#alamat').val('');
    $('#modal_tambah_supplier').modal('show');
  });

  $(document).on('click', '#store', function(e){
    e.preventDefault();

    $('#alert-supplier, #alert-alamat').addClass('d-none').removeClass('d-block').html('');

    $.ajax({
      url: '/supplier',
      type: 'POST',
      data: {
        supplier: $('#supplier').val(),
        alamat: $('#alamat').val()
      },
      success: function(res){
        Swal.fire({ icon:'success', title: res.message || 'Berhasil' });
        $('#modal_tambah_supplier').modal('hide');
        reloadTable();
      },
      error: function(xhr){
        if(xhr.status === 422){
          const err = xhr.responseJSON || {};
          if(err.supplier?.[0]) $('#alert-supplier').removeClass('d-none').addClass('d-block').html(err.supplier[0]);
          if(err.alamat?.[0]) $('#alert-alamat').removeClass('d-none').addClass('d-block').html(err.alamat[0]);
        } else {
          console.error("STORE SUPPLIER ERROR:", xhr.status, xhr.responseText);
          Swal.fire({ icon:'error', title:'Error', text:'Gagal menyimpan supplier.' });
        }
      }
    });
  });

  // ==========================
  // EDIT
  // ==========================
  $(document).on('click', '.btn-edit-supplier', function(){
    const id = $(this).data('id');

    $('#alert-edit_supplier, #alert-edit_alamat').addClass('d-none').removeClass('d-block').html('');

    $.get(`/supplier/${id}/edit`, function(res){
      $('#supplier_id').val(res.data.id);
      $('#edit_supplier').val(res.data.supplier ?? '');
      $('#edit_alamat').val(res.data.alamat ?? '');
      $('#modal_edit_supplier').modal('show');
    }).fail(function(xhr){
      console.error("EDIT SUPPLIER GET ERROR:", xhr.status, xhr.responseText);
    });
  });

  $(document).on('click', '#update', function(e){
    e.preventDefault();

    const id = $('#supplier_id').val();
    $('#alert-edit_supplier, #alert-edit_alamat').addClass('d-none').removeClass('d-block').html('');

    $.ajax({
      url: `/supplier/${id}`,
      type: 'POST',
      data: {
        supplier: $('#edit_supplier').val(),
        alamat: $('#edit_alamat').val(),
        _method: 'PUT'
      },
      success: function(res){
        Swal.fire({ icon:'success', title: res.message || 'Berhasil' });
        $('#modal_edit_supplier').modal('hide');
        reloadTable();
      },
      error: function(xhr){
        if(xhr.status === 422){
          const err = xhr.responseJSON || {};
          if(err.supplier?.[0]) $('#alert-edit_supplier').removeClass('d-none').addClass('d-block').html(err.supplier[0]);
          if(err.alamat?.[0]) $('#alert-edit_alamat').removeClass('d-none').addClass('d-block').html(err.alamat[0]);
        } else {
          console.error("UPDATE SUPPLIER ERROR:", xhr.status, xhr.responseText);
          Swal.fire({ icon:'error', title:'Error', text:'Gagal update supplier.' });
        }
      }
    });
  });

  // ==========================
  // HAPUS
  // ==========================
  $(document).on('click', '.btn-hapus-supplier', function(){
    const id = $(this).data('id');

    Swal.fire({
      title: 'Apakah Kamu Yakin?',
      text: 'Ingin menghapus data ini!',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'TIDAK',
      confirmButtonText: 'YA, HAPUS!'
    }).then((r)=>{
      if(!r.isConfirmed) return;

      $.ajax({
        url: `/supplier/${id}`,
        type: 'POST',
        data: { _method:'DELETE' },
        success: function(res){
          Swal.fire({ icon:'success', title: res.message || 'Terhapus' });
          reloadTable();
        },
        error: function(xhr){
          console.error("DELETE SUPPLIER ERROR:", xhr.status, xhr.responseText);
          Swal.fire({ icon:'error', title:'Error', text:'Gagal menghapus supplier.' });
        }
      });
    });
  });

});
</script>
@endpush
