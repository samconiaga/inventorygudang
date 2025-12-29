@extends('layouts.app')

@include('hak-akses.create')
@include('hak-akses.edit')

@section('content')
<div class="section-header">
    <h1>Hak Akses</h1>
    <div class="ml-auto">
        <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_role">
            <i class="fa fa-plus"></i> Tambah Role
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_role" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:70px">No</th>
                                <th style="width:180px">Role</th>
                                <th>Deskripsi</th>
                                <th style="width:220px">Departemen</th>
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
let DT_ROLE = null;

function initRoleTable(){
    if ($.fn.DataTable.isDataTable('#table_role')) {
        $('#table_role').DataTable().destroy();
    }

    DT_ROLE = $('#table_role').DataTable({
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
        order: [],
        columns: [
            { data: 'no', orderable:false },
            { data: 'role' },
            { data: 'deskripsi' },
            { data: 'department' },
            { data: 'opsi', orderable:false, searchable:false }
        ]
    });
}

function renderRoleTable(data){
    if(!DT_ROLE) initRoleTable();

    DT_ROLE.clear();

    let no = 1;
    (data || []).forEach(function(r){
        const dept = r.department ? ((r.department.code || '') + ' - ' + (r.department.name || '')) : '-';

        DT_ROLE.row.add({
            no: no++,
            role: r.role ?? '-',
            deskripsi: r.deskripsi ?? '-',
            department: dept,
            opsi: `
                <a href="javascript:void(0)" class="btn btn-icon btn-warning btn-lg mb-2 button_edit_role" data-id="${r.id}">
                    <i class="far fa-edit"></i>
                </a>
                <a href="javascript:void(0)" class="btn btn-icon btn-danger btn-lg mb-2 button_hapus_role" data-id="${r.id}">
                    <i class="fas fa-trash"></i>
                </a>
            `
        });
    });

    DT_ROLE.draw(false);
}

function reloadRoleTable(){
    $.ajax({
        url: "{{ route('hak-akses.get-data') }}",
        type: "GET",
        dataType: "JSON",
        cache: false,
        success: function(res){
            renderRoleTable(res.data || []);
        },
        error: function(xhr){
            console.error('GET ROLE ERROR:', xhr.status, xhr.responseText);
            Swal.fire({ icon:'error', title:'Error', text:'Gagal memuat data role.' });
        }
    });
}

$(function(){
    initRoleTable();
    reloadRoleTable();
});

/**
 * =========================================================
 * TAMBAH ROLE
 * =========================================================
 */
$('body').on('click', '#button_tambah_role', function(){
    // reset form
    $('#role').val('');
    $('#deskripsi').val('');
    $('#department_id').val('');

    // reset alert create
    $('#alert-role,#alert-deskripsi,#alert-department')
        .addClass('d-none').removeClass('d-block').html('');

    $('#modal_tambah_role').modal('show');
});

$(document).on('click', '#store', function(e){
    e.preventDefault();

    let role          = $('#role').val();
    let deskripsi     = $('#deskripsi').val();
    let department_id = $('#department_id').val();

    let fd = new FormData();
    fd.append('role', role);
    fd.append('deskripsi', deskripsi);
    fd.append('department_id', department_id);

    $.ajax({
        url: "{{ route('hak-akses.store') }}",
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        success: function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer: 2500, showConfirmButton:true });
            $('#modal_tambah_role').modal('hide');
            reloadRoleTable();
        },
        error: function(err){
            const e = err.responseJSON || {};

            $('#alert-role,#alert-deskripsi,#alert-department')
                .addClass('d-none').removeClass('d-block').html('');

            if (e.role?.[0]) {
                $('#alert-role').removeClass('d-none').addClass('d-block').html(e.role[0]);
            }
            if (e.deskripsi?.[0]) {
                $('#alert-deskripsi').removeClass('d-none').addClass('d-block').html(e.deskripsi[0]);
            }
            if (e.department_id?.[0]) {
                $('#alert-department').removeClass('d-none').addClass('d-block').html(e.department_id[0]);
            }
        }
    });
});

/**
 * =========================================================
 * EDIT ROLE
 * =========================================================
 */
$('body').on('click', '.button_edit_role', function(){
    const id = $(this).data('id');

    // reset alert edit (pastikan di modal edit id alertnya berbeda)
    $('#alert-edit_role,#alert-edit_deskripsi,#alert-edit_department')
        .addClass('d-none').removeClass('d-block').html('');

    $.ajax({
        url: `/hak-akses/${id}/edit`,
        type: "GET",
        cache: false,
        success: function(res){
            const d = res.data || {};
            $('#role_id').val(d.id);
            $('#edit_role').val(d.role ?? '');
            $('#edit_deskripsi').val(d.deskripsi ?? '');
            $('#edit_department_id').val(d.department_id ?? '');

            $('#modal_edit_role').modal('show');
        },
        error: function(xhr){
            console.error('EDIT ROLE ERROR:', xhr.status, xhr.responseText);
            Swal.fire({ icon:'error', title:'Error', text:'Gagal mengambil data role.' });
        }
    });
});

$(document).on('click', '#update', function(e){
    e.preventDefault();

    const id           = $('#role_id').val();
    const role         = $('#edit_role').val();
    const deskripsi    = $('#edit_deskripsi').val();
    const departmentId = $('#edit_department_id').val();

    let fd = new FormData();
    fd.append('role', role);
    fd.append('deskripsi', deskripsi);
    fd.append('department_id', departmentId);
    fd.append('_method', 'PUT');

    $.ajax({
        url: `/hak-akses/${id}`,
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        success: function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer: 2500, showConfirmButton:true });
            $('#modal_edit_role').modal('hide');
            reloadRoleTable();
        },
        error: function(err){
            const e = err.responseJSON || {};

            $('#alert-edit_role,#alert-edit_deskripsi,#alert-edit_department')
                .addClass('d-none').removeClass('d-block').html('');

            if (e.role?.[0]) {
                $('#alert-edit_role').removeClass('d-none').addClass('d-block').html(e.role[0]);
            }
            if (e.deskripsi?.[0]) {
                $('#alert-edit_deskripsi').removeClass('d-none').addClass('d-block').html(e.deskripsi[0]);
            }
            if (e.department_id?.[0]) {
                $('#alert-edit_department').removeClass('d-none').addClass('d-block').html(e.department_id[0]);
            }
        }
    });
});

/**
 * =========================================================
 * HAPUS ROLE
 * =========================================================
 */
$('body').on('click', '.button_hapus_role', function(){
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
            url: `/hak-akses/${id}`,
            type: "DELETE",
            data: { _token: token },
            success: function(res){
                Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer: 2500, showConfirmButton:true });
                reloadRoleTable();
            },
            error: function(xhr){
                console.error('DELETE ROLE ERROR:', xhr.status, xhr.responseText);

                // biasanya FK constraint (role kepakai user)
                let msg = 'Gagal menghapus role.';
                if (xhr.status === 500) msg = 'Role tidak bisa dihapus karena masih dipakai user (FK constraint).';

                Swal.fire({ icon:'error', title:'Error', text: msg });
            }
        });
    });
});
</script>
@endpush
