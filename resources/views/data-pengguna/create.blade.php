<div class="modal fade" tabindex="-1" role="dialog" id="modal_tambah_pengguna">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Pengguna</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form enctype="multipart/form-data">
        <div class="modal-body">

          <div class="form-group">
            <label>Nama</label>
            <input type="text" class="form-control" name="name" id="name">
            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-name-create"></div>
          </div>

          <div class="form-group">
            <label>Email</label>
            <input type="text" class="form-control" name="email" id="email">
            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-email-create"></div>
          </div>

          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="password" id="password">
            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-password-create"></div>
          </div>

          <div class="form-group">
            <label>Pilih Role</label>
            <select class="form-control" name="role_id" id="role_id" style="width: 100%">
              <option value="">Pilih Role</option>
              @foreach ($roles as $role)
                <option value="{{ $role->id }}">{{ $role->role }}</option>
              @endforeach
            </select>
            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-role-create"></div>
          </div>

          <div class="form-group" id="department_wrapper_create">
            <label>Pilih Departemen</label>
            <select class="form-control" name="department_id" id="department_id" style="width: 100%">
              <option value="">Pilih Departemen</option>
              @foreach ($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->code }} - {{ $dept->name }}</option>
              @endforeach
            </select>
            <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-department-create"></div>
          </div>

          <small class="text-muted d-block">
            * Jika role <b>superadmin</b>, departemen otomatis kosong.
          </small>

        </div>

        <div class="modal-footer bg-whitesmoke br">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Keluar</button>
          <button type="button" class="btn btn-primary" id="store">Tambah</button>
        </div>
      </form>

    </div>
  </div>
</div>
