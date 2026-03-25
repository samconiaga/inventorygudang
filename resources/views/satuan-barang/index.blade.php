@extends('layouts.app')

@include('satuan-barang.create')
@include('satuan-barang.edit')

@section('content')
<div class="section-header">
    <h1>Satuan Barang</h1>
    <div class="ml-auto">
        <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_satuan"><i class="fa fa-plus"></i> Satuan Barang</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_satuan" class="display table table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:60px">No</th>
                                <th>Satuan Barang</th>
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
    let table = $('#table_satuan').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        columns: [
            { data: 'no' },
            { data: 'satuan' },
            { data: 'opsi', orderable: false, searchable: false }
        ]
    });

    function loadSatuan(){
        $.ajax({
            url: '/satuan-barang/get-data',
            type: 'GET',
            dataType: 'json'
        }).done(function(res){
            if(!res.success) return;
            table.clear();
            let no = 1;
            res.data.forEach(function(item){
                table.row.add({
                    no: no++,
                    satuan: item.satuan,
                    opsi: `
                        <a href="javascript:void(0)" class="btn btn-icon btn-warning btn-edit-satuan" data-id="${item.id}" title="Edit"><i class="far fa-edit"></i></a>
                        <a href="javascript:void(0)" class="btn btn-icon btn-danger btn-delete-satuan" data-id="${item.id}" title="Hapus"><i class="fas fa-trash"></i></a>
                    `
                });
            });
            table.draw(false);
        }).fail(function(err){
            console.error('Gagal load satuan:', err);
        });
    }

    loadSatuan();

    // show add modal
    $('body').on('click', '#button_tambah_satuan', function(){
        $('#satuan').val('');
        $('#alert-satuan').removeClass('d-block').addClass('d-none').html('');
        $('#modal_tambah_satuan').modal('show');
    });

    // store
    $('body').on('click', '#store-satuan', function(e){
        e.preventDefault();
        let val = $('#satuan').val();
        let fd = new FormData();
        fd.append('satuan', val);
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: '/satuan-barang',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false
        }).done(function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer:1500 });
            $('#modal_tambah_satuan').modal('hide');
            loadSatuan();
        }).fail(function(xhr){
            if(xhr.status === 422 && xhr.responseJSON?.satuan){
                $('#alert-satuan').removeClass('d-none').addClass('d-block').html(xhr.responseJSON.satuan[0]);
            } else {
                console.error(xhr);
            }
        });
    });

    // open edit
    $('body').on('click', '.btn-edit-satuan', function(){
        let id = $(this).data('id');
        $.ajax({
            url: `/satuan-barang/${id}/edit`,
            type: 'GET'
        }).done(function(res){
            $('#satuan_id').val(res.data.id);
            $('#edit_satuan').val(res.data.satuan);
            $('#alert-edit_satuan').removeClass('d-block').addClass('d-none').html('');
            $('#modal_edit_satuan').modal('show');
        }).fail(function(err){
            console.error(err);
        });
    });

    // update
    $('body').on('click', '#update-satuan', function(e){
        e.preventDefault();
        let id = $('#satuan_id').val();
        let val = $('#edit_satuan').val();
        let fd = new FormData();
        fd.append('satuan', val);
        fd.append('_token', $('meta[name="csrf-token"]').attr('content'));
        fd.append('_method', 'PUT');

        $.ajax({
            url: `/satuan-barang/${id}`,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false
        }).done(function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer:1500 });
            $('#modal_edit_satuan').modal('hide');
            loadSatuan();
        }).fail(function(xhr){
            if(xhr.status === 422 && xhr.responseJSON?.satuan){
                $('#alert-edit_satuan').removeClass('d-none').addClass('d-block').html(xhr.responseJSON.satuan[0]);
            } else {
                console.error(xhr);
            }
        });
    });

    // delete
    $('body').on('click', '.btn-delete-satuan', function(){
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
                url: `/satuan-barang/${id}`,
                type: 'DELETE',
                data: { _token: token }
            }).done(function(resp){
                Swal.fire({ icon:'success', title: resp.message || 'Terhapus', timer:1500 });
                loadSatuan();
            }).fail(function(err){
                console.error(err);
                Swal.fire({ icon:'error', title: 'Gagal menghapus' });
            });
        });
    });
});
</script>
@endpush