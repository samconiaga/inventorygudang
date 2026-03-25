<div class="modal fade" tabindex="-1" role="dialog" id="modal_tambah_barang">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Tambah Barang</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form>
        <div class="modal-body">
          <div class="row">

            {{-- LEFT: BARCODE PREVIEW --}}
            <div class="col-md-6">
              <label class="d-block">Preview Barcode (Code128)</label>
              <div class="p-3"
                   style="border:1px dashed #d1d5db;border-radius:12px;background:#fafafa;min-height:275px;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                <!-- gunakan SVG agar JsBarcode dapat merender barcode yang discan-able -->
                <svg id="barcodePreviewCreate" xmlns="http://www.w3.org/2000/svg"></svg>
                <div id="barcodeHumanCreate" style="font-size:12px;letter-spacing:0.4px;color:#6b7280;font-weight:600;margin-top:8px;">
                  Menunggu input...
                </div>
              </div>
              <small class="text-muted d-block mt-2">
                Barcode optional. Kalau kosong, sistem akan auto-generate barcode dari <b>nama barang</b>.
              </small>
              <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-barcode"></div>
            </div>

            {{-- RIGHT: FORM --}}
            <div class="col-md-6">

              <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" class="form-control" name="nama_barang" id="nama_barang" autocomplete="off">
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-nama_barang"></div>
              </div>

              <div class="form-group">
                <label>Barcode (optional)</label>
                <input type="text" class="form-control" name="barcode" id="barcode"
                       placeholder="Scan / tempel barcode di sini (opsional)" autocomplete="off">
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-barcode"></div>
              </div>

              <div class="form-group">
                <label>Jenis Barang</label>
                <select class="form-control" name="jenis_id" id="jenis_id">
                  @foreach ($jenis_barangs as $jenis)
                    <option value="{{ $jenis->id }}">{{ $jenis->jenis_barang }}</option>
                  @endforeach
                </select>
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-jenis_id"></div>
              </div>

              <div class="form-group">
                <label>Satuan Barang</label>
                <select class="form-control" name="satuan_id" id="satuan_id">
                  @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}">{{ $satuan->satuan }}</option>
                  @endforeach
                </select>
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-satuan_id"></div>
              </div>

              <div class="form-group">
                <label>Stok Minimum</label>
                <input type="number" class="form-control" name="stok_minimum" id="stok_minimum" min="0" value="0">
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-stok_minimum"></div>
              </div>

              <div class="form-group">
                <label>Deskripsi (optional)</label>
                <textarea class="form-control" name="deskripsi" id="deskripsi" rows="3"></textarea>
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-deskripsi"></div>
              </div>

            </div>
          </div>
        </div>

        <div class="modal-footer bg-whitesmoke br">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Keluar</button>
          <button type="button" class="btn btn-primary" id="store">Tambah</button>
        </div>
      </form>

    </div>
  </div>
</div>

{{-- Script lokal: jika user mengetik Nama Barang dan field Barcode kosong,
     tampilkan preview barcode yang di-generate dari Nama Barang. --}}
@push('scripts')
<script>
$(document).ready(function(){
  // ketika nama barang diinput, dan barcode field kosong -> preview dari nama
  $('#nama_barang').on('input', function(){
    const nama = $(this).val() || '';
    if ($('#barcode').val().toString().trim() === '') {
      // renderPreview dapat menerima inputSel sebagai elemen nama
      // dan akan menampilkan human-readable dari nama
      if (typeof renderPreview === 'function') {
        renderPreview('#nama_barang', '#barcodePreviewCreate', '#barcodeHumanCreate');
      } else {
        // fallback: langsung render via JsBarcode kalau tersedia
        try {
          const val = (typeof toBarcodeValue === 'function') ? toBarcodeValue(nama) : (nama.toUpperCase().replace(/\s+/g,'-'));
          if (window.JsBarcode) {
            JsBarcode('#barcodePreviewCreate', val || 'ITEM', { format: "CODE128", displayValue: false, height: 40, margin: 0 });
            $('#barcodeHumanCreate').text(nama || 'Menunggu input...');
          } else {
            $('#barcodePreviewCreate').text(nama || '');
            $('#barcodeHumanCreate').text(nama || 'Menunggu input...');
          }
        } catch(e) {
          $('#barcodeHumanCreate').text(nama || 'Menunggu input...');
        }
      }
    } else {
      // jika user sudah isi barcode, biarkan handler global (#barcode input) yang update preview
    }
  });

  // juga bila user menulis manual di #barcode, preview akan otomatis di-handle
  // oleh event global yang sudah ada di index.blade (renderPreview bound pada #barcode).
});
</script>
@endpush