<div class="modal fade" tabindex="-1" role="dialog" id="modal_edit_barang">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Data Barang</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form>
        <div class="modal-body">
          <input type="hidden" id="barang_id">

          <div class="row">

            {{-- LEFT: BARCODE PREVIEW --}}
            <div class="col-md-6">
              <label class="d-block">Preview Barcode (Code128)</label>
              <div class="p-3 barcode-box"
                   style="border:1px dashed #d1d5db;border-radius:12px;background:#fafafa;min-height:275px;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                <!-- svg agar JsBarcode render (discan-able) -->
                <svg id="barcodePreviewEdit" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"></svg>
                <div id="barcodeHumanEdit" style="font-size:12px;letter-spacing:0.4px;color:#6b7280;font-weight:600;margin-top:8px;">-</div>
              </div>
              <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_barcode"></div>
            </div>

            {{-- RIGHT: FORM --}}
            <div class="col-md-6">

              <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" class="form-control" name="nama_barang" id="edit_nama_barang" autocomplete="off">
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_nama_barang"></div>
              </div>

              <div class="form-group">
                <label>Barcode (optional)</label>
                <input type="text" class="form-control" name="barcode" id="edit_barcode"
                       placeholder="Scan / tempel barcode di sini (opsional)" autocomplete="off">
                <small class="form-text text-muted">Field ini tetap untuk input manual. Preview di kiri dibuat dari <b>Nama Barang</b>.</small>
              </div>

              <div class="form-group">
                <label>Jenis Barang</label>
                <select class="form-control" name="jenis_id" id="edit_jenis_id">
                  @foreach ($jenis_barangs as $jenis)
                    <option value="{{ $jenis->id }}">{{ $jenis->jenis_barang }}</option>
                  @endforeach
                </select>
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_jenis_id"></div>
              </div>

              <div class="form-group">
                <label>Satuan Barang</label>
                <select class="form-control" name="satuan_id" id="edit_satuan_id">
                  @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}">{{ $satuan->satuan }}</option>
                  @endforeach
                </select>
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_satuan_id"></div>
              </div>

              <div class="form-group">
                <label>Stok Minimum</label>
                <input type="number" class="form-control" name="stok_minimum" id="edit_stok_minimum" min="0">
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_stok_minimum"></div>
              </div>

              <div class="form-group">
                <label>Deskripsi (optional)</label>
                <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_deskripsi"></div>
              </div>

            </div>
          </div>
        </div>

        <div class="modal-footer bg-whitesmoke br">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Keluar</button>
          <button type="button" class="btn btn-primary" id="update">Update</button>
        </div>
      </form>

    </div>
  </div>
</div>

@push('scripts')
<style>
/* batasi ukuran SVG agar tidak meluber; center di kontainer */
#barcodePreviewEdit { width:100%; max-width:360px; height:48px; display:block; }
.modal .barcode-box { width:100%; align-items:center; justify-content:center; overflow:hidden; }
@media (max-width:991px) {
  #barcodePreviewEdit { max-width:280px; }
}
</style>

<script>
$(document).ready(function(){
  // helper kecil: normalisasi nama -> nilai barcode (slug uppercase)
  function barcodeFromName(name){
    if(!name) return 'ITEM';
    return String(name).toUpperCase().trim()
      .replace(/\s+/g,'-')        // spasi -> dash
      .replace(/[^A-Z0-9\-]/g,'') // buang char selain A-Z0-9 dan dash
      .replace(/\-+/g,'-')
      .replace(/^\-+|\-+$/g,'') || 'ITEM';
  }

  // render svg JsBarcode dari nama (selalu gunakan nama_barang sebagai sumber preview)
  function renderEditPreviewFromName(name){
    const v = barcodeFromName(name);
    // jika JsBarcode tersedia gunakan untuk svg; jika tidak, tampilkan teks fallback
    try {
      if (window.JsBarcode) {
        JsBarcode('#barcodePreviewEdit', v, {
          format: "CODE128",
          displayValue: false,
          height: 48,
          margin: 0
        });
      } else {
        // fallback: tulis plain text ke dalam svg element
        $('#barcodePreviewEdit').text(v);
      }
    } catch(e) {
      $('#barcodePreviewEdit').text(v);
      console.error('JsBarcode render error (edit):', e);
    }
    // human readable text selalu nama barang
    $('#barcodeHumanEdit').text(name || '-');
  }

  // Saat nama barang di edit modal berubah => preview dari nama
  $(document).on('input', '#edit_nama_barang', function(){
    const nama = $(this).val() || '';
    renderEditPreviewFromName(nama);
  });

  // Ketika modal edit diisi oleh Ajax (handler sudah ada di index.js),
  // panggil ulang render dari nama agar preview sesuai
  // (pastikan event ini terpanggil setelah index.js mengisi field)
  $(document).on('shown.bs.modal', '#modal_edit_barang', function(){
    const nama = $('#edit_nama_barang').val() || '';
    renderEditPreviewFromName(nama);
  });

  // Jika user mengetik manual di field barcode, jangan ubah preview:
  // preview tetap berasal dari nama (sesuai permintaan).
});
</script>
@endpush