@extends('layouts.app')

@section('content')
<div class="section-header">
  <h1>Outbound Transaction (Cart)</h1>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-body">

        {{-- SCAN BARCODE --}}
        <div class="form-group mb-4">
          <label>
            <i class="fas fa-barcode mr-1"></i>
            Scan Barcode (Tekan Enter untuk Add)
          </label>
          <input type="text" id="scan_barcode" class="form-control"
                 placeholder="Scan barcode di sini..." autocomplete="off">
          <small class="text-muted">
            Setiap scan + Enter akan menambah qty otomatis (qty +1).
          </small>
        </div>

        {{-- FORM OUTBOUND --}}
        <form id="form_outbound" method="POST" action="{{ route('barang-keluar.finalize') }}">
          @csrf

          {{-- tanggal otomatis hari ini --}}
          <input type="hidden" name="tanggal_keluar" value="{{ now()->format('Y-m-d') }}">

          <div class="row mb-3">
            <div class="col-md-4">
              <label>Departemen yang Membutuhkan</label>
              <select name="customer_id" class="form-control" required>
                <option value="">-- Pilih Departemen/Customer --</option>
                @foreach($customers as $c)
                  <option value="{{ $c->id }}">{{ $c->customer }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-4">
              <label>PIC (Person in Charge)</label>
              <input type="text" name="pic" class="form-control"
                     placeholder="Opsional (tidak disimpan ke DB)">
            </div>
            <div class="col-md-4">
              <label>Keperluan</label>
              <input type="text" name="purpose" class="form-control"
                     placeholder="Opsional (untuk catatan saja)">
            </div>
          </div>

          {{-- TABEL CART --}}
          <div class="table-responsive">
            <table class="table table-bordered" id="cart_table">
              <thead class="thead-light">
                <tr>
                  <th style="width:40px;">No</th>
                  <th>Barcode</th>
                  <th>Nama Barang</th>
                  <th style="width:120px;">Stok Tersedia</th>
                  <th style="width:150px;">Qty Keluar (Auto)</th>
                  <th style="width:140px;">Qty Sisa Setelah</th>
                  <th style="width:130px;">Status Validasi</th>
                  <th style="width:120px;">Action</th>
                </tr>
              </thead>
              <tbody>
                {{-- diisi via JS --}}
              </tbody>
            </table>
          </div>

          <div class="text-right mt-3">
            <button type="submit" class="btn btn-primary" id="btn_finalize" disabled>
              <i class="fas fa-check mr-1"></i> Finalisasi Outbound
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  let cartItems = [];
  const $tableBody    = $('#cart_table tbody');
  const $btnFinalize  = $('#btn_finalize');
  const $formOutbound = $('#form_outbound');
  const $scanInput    = $('#scan_barcode');

  function focusScan(){
    setTimeout(() => $scanInput.trigger('focus'), 50);
  }

  function toastError(msg){
    if (window.Swal) {
      Swal.fire({ icon:'error', title:'Gagal', text: msg || 'Terjadi kesalahan' });
    } else {
      alert(msg || 'Terjadi kesalahan');
    }
  }

  function renderTable() {
    $tableBody.empty();

    // bersihin hidden items[*] sebelum isi ulang
    $formOutbound.find('input[name^="items["]').remove();

    if (cartItems.length === 0) {
      $tableBody.append(`
        <tr>
          <td colspan="8" class="text-center text-muted">
            Belum ada item. Scan barcode untuk menambahkan.
          </td>
        </tr>
      `);
      $btnFinalize.prop('disabled', true);
      return;
    }

    let allValid = true;

    cartItems.forEach((item, index) => {
      const qtySisa = item.stok - item.qty;
      const isValid = (item.qty > 0 && item.qty <= item.stok);

      if (!isValid) allValid = false;

      const statusBadge = isValid
        ? '<span class="badge badge-success">OK</span>'
        : '<span class="badge badge-danger">Stok Kurang</span>';

      const rowHtml = `
        <tr data-index="${index}">
          <td>${index + 1}</td>
          <td>${item.barcode}</td>
          <td>${item.nama_barang}</td>
          <td class="text-right">${item.stok}</td>

          {{-- Qty Auto (tanpa input) + tombol +/- --}}
          <td>
            <div class="d-flex align-items-center" style="gap:8px;">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-dec" data-index="${index}" title="Kurangi 1">
                <i class="fas fa-minus"></i>
              </button>

              <span class="font-weight-bold" style="min-width:30px; display:inline-block; text-align:center;">
                ${item.qty}
              </span>

              <button type="button" class="btn btn-sm btn-outline-secondary btn-inc" data-index="${index}" title="Tambah 1">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </td>

          <td class="text-right">${qtySisa < 0 ? '-' : qtySisa}</td>
          <td>${statusBadge}</td>

          <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove" data-index="${index}" title="Hapus Item">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `;

      $tableBody.append(rowHtml);

      // hidden untuk kirim ke backend
      $('<input>')
        .attr('type', 'hidden')
        .attr('name', `items[${index}][id]`)
        .val(item.id)
        .appendTo($formOutbound);

      $('<input>')
        .attr('type', 'hidden')
        .attr('name', `items[${index}][qty]`)
        .val(item.qty)
        .appendTo($formOutbound);
    });

    $btnFinalize.prop('disabled', !allValid);
  }

  // ====== SCAN BARCODE + ENTER ======
  $scanInput.on('keypress', function(e){
    if (e.which !== 13) return;

    e.preventDefault();
    const barcode = $(this).val().trim();
    if (!barcode) return;

    $.ajax({
      url: '{{ route('barang-keluar.scan-barcode') }}',
      type: 'GET',
      data: { barcode: barcode },
      success: function(res){
        if (!res.success) {
          toastError(res.message || 'Gagal membaca barcode.');
          return;
        }

        const data = res.data;

        // kalau sudah ada di cart -> qty +1, kalau belum -> push qty=1
        const idx = cartItems.findIndex(i => i.id === data.id);
        if (idx >= 0) {
          cartItems[idx].qty += 1;
        } else {
          cartItems.push({
            id: data.id,
            nama_barang: data.nama_barang,
            barcode: data.barcode,
            stok: parseInt(data.stok || 0, 10),
            qty: 1,
          });
        }

        $scanInput.val('');
        renderTable();
        focusScan();
      },
      error: function(xhr){
        let msg = 'Gagal scan barcode.';
        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
        toastError(msg);
        focusScan();
      }
    });
  });

  // ====== Tambah qty manual via tombol (+) (tanpa input) ======
  $(document).on('click', '.btn-inc', function(){
    const index = $(this).data('index');
    if (cartItems[index]) {
      cartItems[index].qty += 1;
      renderTable();
      focusScan();
    }
  });

  // ====== Kurangi qty via tombol (-). Kalau jadi 0 -> auto hapus ======
  $(document).on('click', '.btn-dec', function(){
    const index = $(this).data('index');
    if (!cartItems[index]) return;

    cartItems[index].qty -= 1;
    if (cartItems[index].qty <= 0) {
      cartItems.splice(index, 1);
    }
    renderTable();
    focusScan();
  });

  // ====== Hapus item ======
  $(document).on('click', '.btn-remove', function(){
    const index = $(this).data('index');
    cartItems.splice(index, 1);
    renderTable();
    focusScan();
  });

  // initial
  renderTable();
  focusScan();
</script>
@endpush
@endsection
