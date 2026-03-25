@extends('layouts.app')

@include('jenis-barang.create')
@include('jenis-barang.edit')

@section('content')
<div class="section-header">
    <h1>Data Jenis Barang</h1>
    <div class="ml-auto">
        <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_jenis_barang"><i class="fa fa-plus"></i> Jenis Barang</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_jenis" class="display table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Jenis Barang</th>
                                <th style="width:150px">Opsi</th>
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
$(function(){
    // inisialisasi DataTable
    let table = $('#table_jenis').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        columns: [
            { data: 'no' },
            { data: 'jenis_barang' },
            { data: 'opsi', orderable: false, searchable: false }
        ]
    });

    // load data awal
    function loadJenis(){
        $.ajax({
            url: '/jenis-barang/get-data',
            type: 'GET',
            dataType: 'json'
        }).done(function(res){
            if(!res.success) return;
            table.clear();
            let no = 1;
            res.data.forEach(function(item){
                table.row.add({
                    no: no++,
                    jenis_barang: item.jenis_barang,
                    opsi: `
                        <a href="javascript:void(0)" class="btn btn-icon btn-warning btn-edit-jenis" data-id="${item.id}" title="Edit"><i class="far fa-edit"></i></a>
                        <a href="javascript:void(0)" class="btn btn-icon btn-danger btn-delete-jenis" data-id="${item.id}" title="Hapus"><i class="fas fa-trash"></i></a>
                    `
                });
            });
            table.draw(false);
        }).fail(function(err){
            console.error('Gagal load jenis:', err);
        });
    }

    loadJenis();

    // buka modal tambah
    $('body').on('click', '#button_tambah_jenis_barang', function(){
        $('#jenis_barang').val('');
        $('#alert-jenis_barang').removeClass('d-block').addClass('d-none').html('');
        $('#modal_tambah_jenis_barang').modal('show');
    });

    // simpan
    $('body').on('click', '#store-jenis', function(e){
        e.preventDefault();
        let val = $('#jenis_barang').val();
        let fd = new FormData();
        fd.append('jenis_barang', val);
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: '/jenis-barang',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false
        }).done(function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer:1500 });
            $('#modal_tambah_jenis_barang').modal('hide');
            loadJenis();
        }).fail(function(xhr){
            if(xhr.status === 422 && xhr.responseJSON?.jenis_barang){
                $('#alert-jenis_barang').removeClass('d-none').addClass('d-block').html(xhr.responseJSON.jenis_barang[0]);
            } else {
                console.error(xhr);
            }
        });
    });

    // open edit modal (delegated)
    $('body').on('click', '.btn-edit-jenis', function(){
        let id = $(this).data('id');
        $.ajax({
            url: `/jenis-barang/${id}/edit`,
            type: 'GET'
        }).done(function(res){
            $('#jenis_id').val(res.data.id);
            $('#edit_jenis_barang').val(res.data.jenis_barang);
            $('#alert-edit_jenis_barang').removeClass('d-block').addClass('d-none').html('');
            $('#modal_edit_jenis_barang').modal('show');
        }).fail(function(err){
            console.error(err);
        });
    });

    // update
    $('body').on('click', '#update-jenis', function(e){
        e.preventDefault();
        let id = $('#jenis_id').val();
        let val = $('#edit_jenis_barang').val();
        let fd = new FormData();
        fd.append('jenis_barang', val);
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        fd.append('_method', 'PUT');

        $.ajax({
            url: `/jenis-barang/${id}`,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false
        }).done(function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer:1500 });
            $('#modal_edit_jenis_barang').modal('hide');
            loadJenis();
        }).fail(function(xhr){
            if(xhr.status === 422 && xhr.responseJSON?.jenis_barang){
                $('#alert-edit_jenis_barang').removeClass('d-none').addClass('d-block').html(xhr.responseJSON.jenis_barang[0]);
            } else {
                console.error(xhr);
            }
        });
    });

    // hapus
    $('body').on('click', '.btn-delete-jenis', function(){
        const id = $(this).data('id');
        const token = $('meta[name="csrf-token"]').attr('content');

        Swal.fire({
            title: 'Apakah Kamu Yakin?',
            text: "ingin menghapus data ini !",
            icon: 'warning',
            showCancelButton: true,
            cancelButtonText: 'TIDAK',
            confirmButtonText: 'YA, HAPUS!'
        }).then((res) => {
            if(!res.isConfirmed) return;
            $.ajax({
                url: `/jenis-barang/${id}`,
                type: 'DELETE',
                data: { _token: token }
            }).done(function(resp){
                Swal.fire({ icon:'success', title: resp.message || 'Terhapus', timer:1500 });
                loadJenis();
            }).fail(function(err){
                console.error(err);
                Swal.fire({ icon:'error', title: 'Gagal menghapus' });
            });
        });
    });

});
</script>
@endpush