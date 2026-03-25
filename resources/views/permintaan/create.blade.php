@extends('layouts.app')

@section('content')

<div class="section-header">
  <h1>Buat Permintaan Barang</h1>

  <div class="ml-auto">
    <a href="{{ route('permintaan.index') }}" class="btn btn-outline-secondary">
      <i class="fa fa-arrow-left"></i> Kembali
    </a>
  </div>
</div>

@if ($errors->any())
  <div class="alert alert-danger">
      {{ $errors->first() }}
  </div>
@endif

<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">

        <form method="POST" action="{{ route('permintaan.store') }}">
          @csrf

          <div id="items-wrapper">

            <div class="item-row row mb-3">

              <div class="col-md-5">
                <label>Barang</label>
                <select name="barang_id[]" class="form-control barang-select" required>
                  <option value="">-- Pilih Barang --</option>

                  @foreach($barangs as $b)
                    <option value="{{ $b->id }}"
                            data-satuan="{{ $b->satuan->satuan ?? '' }}">
                      {{ $b->nama_barang }} (Stock: {{ $b->stok }})
                    </option>
                  @endforeach

                </select>
              </div>

              <div class="col-md-2">
                <label>Qty</label>
                <input type="number" name="qty[]" class="form-control" min="1" value="1" required>
              </div>

              <div class="col-md-3">
                <label>Satuan</label>
                <input type="text" name="satuan[]" 
                       class="form-control satuan-input" 
                       readonly>
              </div>

              <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-item w-100">
                  <i class="fa fa-trash"></i>
                </button>
              </div>

            </div>

          </div>

          <button type="button" id="add-item" class="btn btn-secondary mb-3">
            <i class="fa fa-plus"></i> Tambah Item
          </button>

          <div class="form-group">
            <label>Catatan</label>
            <textarea name="note" class="form-control" rows="3"></textarea>
          </div>

          <div class="mt-3">
            <button type="submit" class="btn btn-primary">
              <i class="fa fa-paper-plane"></i> Kirim Permintaan
            </button>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection


@push('scripts')
<script>

// 🔥 AUTO ISI SATUAN SAAT PILIH BARANG
document.addEventListener('change', function(e) {

    if (e.target.classList.contains('barang-select')) {

        let selected = e.target.options[e.target.selectedIndex];
        let satuan = selected.getAttribute('data-satuan');

        let row = e.target.closest('.item-row');
        row.querySelector('.satuan-input').value = satuan ?? '';

    }

});


// 🔥 TAMBAH ITEM
document.getElementById('add-item').addEventListener('click', function(){

    const wrapper = document.getElementById('items-wrapper');
    const firstRow = wrapper.querySelector('.item-row');
    const newRow = firstRow.cloneNode(true);

    newRow.querySelector('select').selectedIndex = 0;
    newRow.querySelector('input[type=number]').value = 1;
    newRow.querySelector('.satuan-input').value = '';

    wrapper.appendChild(newRow);
});


// 🔥 HAPUS ITEM
document.addEventListener('click', function(e){
    if (e.target.closest('.remove-item')) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length > 1) {
            e.target.closest('.item-row').remove();
        }
    }
});

</script>
@endpush