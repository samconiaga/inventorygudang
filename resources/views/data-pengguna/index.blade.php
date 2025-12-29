@extends('layouts.app')

@include('data-pengguna.create')
@include('data-pengguna.edit')

@section('content')
<div class="section-header">
    <h1>Data Pengguna</h1>
    <div class="ml-auto">
        <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_pengguna">
            <i class="fa fa-plus"></i> Tambah Pengguna
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table_pengguna" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                        <tr>
                            <th style="width:70px">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th style="width:180px">Role</th>
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
/**
 * =========================================================
 * CONFIG
 * GANTI SUPERADMIN_ROLE_ID kalau id superadmin bukan 1
 * =========================================================
 */
const SUPERADMIN_ROLE_ID = 1;

function toggleDepartmentByRole(roleId, deptWrapperSelector, deptSelectSelector){
    const isSuperadmin = parseInt(roleId || 0) === SUPERADMIN_ROLE_ID;

    const $wrap = $(deptWrapperSelector);
    const $dept = $(deptSelectSelector);

    if (isSuperadmin){
        $dept.val('');
        $dept.prop('disabled', true);
        $wrap.hide();
    } else {
        $dept.prop('disabled', false);
        $wrap.show();
    }
}

let DT_PENGGUNA = null;

function initTablePengguna(){
    // destroy kalau sudah pernah init
    if ($.fn.DataTable.isDataTable('#table_pengguna')) {
        $('#table_pengguna').DataTable().destroy();
    }

    DT_PENGGUNA = $('#table_pengguna').DataTable({
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
        order: [],
        columns: [
            { data: 'no', orderable:false },
            { data: 'name' },
            { data: 'email' },
            { data: 'role_name' },
            { data: 'dept_name' },
            { data: 'opsi', orderable:false, searchable:false }
        ]
    });
}

function renderTablePengguna(rows){
    if(!DT_PENGGUNA) initTablePengguna();

    DT_PENGGUNA.clear();

    let no = 1;
    (rows || []).forEach(function(u){
        const roleName = u.role ? (u.role.role || '-') : '-';
        const deptName = u.department ? ((u.department.code || '') + ' - ' + (u.department.name || '')) : '-';

        DT_PENGGUNA.row.add({
            no: no++,
            name: u.name ?? '-',
            email: u.email ?? '-',
            role_name: roleName,
            dept_name: deptName,
            opsi: `
                <a href="javascript:void(0)" class="btn btn-icon btn-warning btn-lg mb-2 button_edit_pengguna" data-id="${u.id}">
                    <i class="far fa-edit"></i>
                </a>
                <a href="javascript:void(0)" class="btn btn-icon btn-danger btn-lg mb-2 button_hapus_pengguna" data-id="${u.id}">
                    <i class="fas fa-trash"></i>
                </a>
            `
        });
    });

    DT_PENGGUNA.draw(false);
}

function reloadTablePengguna(){
    $.ajax({
        url: "{{ route('data-pengguna.get-data') }}",
        type: "GET",
        dataType: "JSON",
        cache: false,
        success: function(res){
            renderTablePengguna(res.data || []);
        },
        error: function(xhr){
            console.error('GET DATA PENGGUNA ERROR:', xhr.status, xhr.responseText);
            Swal.fire({ icon:'error', title:'Error', text:'Gagal memuat data pengguna.' });
        }
    });
}

$(function(){
    initTablePengguna();
    reloadTablePengguna();
});

/**
 * =========================================================
 * TAMBAH PENGGUNA
 * =========================================================
 */
$('body').on('click', '#button_tambah_pengguna', function() {
    // reset form
    $('#name,#email,#password').val('');
    $('#role_id').val('');
    $('#department_id').val('');

    // reset alert
    $('#alert-name-create,#alert-email-create,#alert-password-create,#alert-role-create,#alert-department-create')
        .addClass('d-none').removeClass('d-block').html('');

    // default tampil departemen
    toggleDepartmentByRole($('#role_id').val(), '#department_wrapper_create', '#department_id');

    $('#modal_tambah_pengguna').modal('show');
});

$(document).on('change', '#role_id', function(){
    toggleDepartmentByRole($(this).val(), '#department_wrapper_create', '#department_id');
});

$(document).on('click', '#store', function(e){
    e.preventDefault();

    let name          = $('#name').val();
    let email         = $('#email').val();
    let password      = $('#password').val();
    let role_id       = $('#role_id').val();
    let department_id = $('#department_id').val();

    // kalau superadmin -> paksa null
    if (parseInt(role_id || 0) === SUPERADMIN_ROLE_ID) department_id = '';

    let fd = new FormData();
    fd.append('name', name);
    fd.append('email', email);
    fd.append('password', password);
    fd.append('role_id', role_id);
    fd.append('department_id', department_id);

    $.ajax({
        url: "{{ route('data-pengguna.store') }}",
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        success: function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer: 2500, showConfirmButton:true });
            $('#modal_tambah_pengguna').modal('hide');
            reloadTablePengguna();
        },
        error: function(err){
            const e = err.responseJSON || {};

            $('#alert-name-create,#alert-email-create,#alert-password-create,#alert-role-create,#alert-department-create')
                .addClass('d-none').removeClass('d-block').html('');

            if (e.name?.[0]) $('#alert-name-create').removeClass('d-none').addClass('d-block').html(e.name[0]);
            if (e.email?.[0]) $('#alert-email-create').removeClass('d-none').addClass('d-block').html(e.email[0]);
            if (e.password?.[0]) $('#alert-password-create').removeClass('d-none').addClass('d-block').html(e.password[0]);
            if (e.role_id?.[0]) $('#alert-role-create').removeClass('d-none').addClass('d-block').html(e.role_id[0]);
            if (e.department_id?.[0]) $('#alert-department-create').removeClass('d-none').addClass('d-block').html(e.department_id[0]);
        }
    });
});

/**
 * =========================================================
 * EDIT PENGGUNA
 * =========================================================
 */
$('body').on('click', '.button_edit_pengguna', function(){
    const id = $(this).data('id');

    $('#alert-name-edit,#alert-email-edit,#alert-password-edit,#alert-role-edit,#alert-department-edit')
        .addClass('d-none').removeClass('d-block').html('');

    $.ajax({
        url: `/data-pengguna/${id}/edit`,
        type: "GET",
        cache: false,
        success: function(res){
            const u = res.data || {};

            $('#pengguna_id').val(u.id);
            $('#edit_name').val(u.name || '');
            $('#edit_email').val(u.email || '');
            $('#edit_password').val('');
            $('#edit_role_id').val(u.role_id || '');
            $('#edit_department_id').val(u.department_id ?? '');

            toggleDepartmentByRole($('#edit_role_id').val(), '#department_wrapper_edit', '#edit_department_id');

            $('#modal_edit_pengguna').modal('show');
        },
        error: function(xhr){
            console.error('EDIT GET ERROR:', xhr.status, xhr.responseText);
            Swal.fire({ icon:'error', title:'Error', text:'Gagal mengambil data pengguna.' });
        }
    });
});

$(document).on('change', '#edit_role_id', function(){
    toggleDepartmentByRole($(this).val(), '#department_wrapper_edit', '#edit_department_id');
});

$(document).on('click', '#update', function(e){
    e.preventDefault();

    const id           = $('#pengguna_id').val();
    let name           = $('#edit_name').val();
    let email          = $('#edit_email').val();
    let password       = $('#edit_password').val();
    let role_id        = $('#edit_role_id').val();
    let department_id  = $('#edit_department_id').val();

    if (parseInt(role_id || 0) === SUPERADMIN_ROLE_ID) department_id = '';

    let fd = new FormData();
    fd.append('name', name);
    fd.append('email', email);
    fd.append('role_id', role_id);
    fd.append('department_id', department_id);
    fd.append('_method', 'PUT');

    if (password && password !== '') fd.append('password', password);

    $.ajax({
        url: `/data-pengguna/${id}`,
        type: "POST",
        data: fd,
        contentType: false,
        processData: false,
        success: function(res){
            Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer: 2500, showConfirmButton:true });
            $('#modal_edit_pengguna').modal('hide');
            reloadTablePengguna();
        },
        error: function(err){
            const e = err.responseJSON || {};

            $('#alert-name-edit,#alert-email-edit,#alert-password-edit,#alert-role-edit,#alert-department-edit')
                .addClass('d-none').removeClass('d-block').html('');

            if (e.name?.[0]) $('#alert-name-edit').removeClass('d-none').addClass('d-block').html(e.name[0]);
            if (e.email?.[0]) $('#alert-email-edit').removeClass('d-none').addClass('d-block').html(e.email[0]);
            if (e.password?.[0]) $('#alert-password-edit').removeClass('d-none').addClass('d-block').html(e.password[0]);
            if (e.role_id?.[0]) $('#alert-role-edit').removeClass('d-none').addClass('d-block').html(e.role_id[0]);
            if (e.department_id?.[0]) $('#alert-department-edit').removeClass('d-none').addClass('d-block').html(e.department_id[0]);
        }
    });
});

/**
 * =========================================================
 * HAPUS PENGGUNA
 * =========================================================
 */
$('body').on('click', '.button_hapus_pengguna', function(){
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
            url: `/data-pengguna/${id}`,
            type: "DELETE",
            data: { _token: token },
            success: function(res){
                Swal.fire({ icon:'success', title: res.message || 'Berhasil', timer: 2500, showConfirmButton:true });
                reloadTablePengguna();
            },
            error: function(xhr){
                console.error('DELETE ERROR:', xhr.status, xhr.responseText);
                Swal.fire({ icon:'error', title:'Error', text:'Gagal menghapus pengguna.' });
            }
        });
    });
});
</script>
@endpush
