<div class="modal fade" tabindex="-1" role="dialog" id="modal_detail_barang">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Detail Data Barang</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form>
        <div class="modal-body">
          <input type="hidden" id="detail_barang_id">

          <div class="row">

            <div class="col-md-6">
              <label class="d-block">Barcode</label>
              <div class="p-3 barcode-box"
                   style="border:1px dashed #d1d5db;border-radius:12px;background:#fafafa;min-height:275px;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                <!-- svg untuk render discan-able (tidak meluber) -->
                <svg id="barcodePreviewDetail" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"></svg>
                <div id="barcodeHumanDetail" style="font-size:12px;letter-spacing:0.4px;color:#6b7280;font-weight:600;margin-top:8px;">-</div>
              </div>
            </div>

            <div class="col-md-6">

              <div class="form-group">
                <label>Kode Barang</label>
                <input type="text" class="form-control" id="detail_kode_barang" disabled>
              </div>

              <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" class="form-control" id="detail_nama_barang" disabled>
              </div>

              <div class="form-group">
                <label>Barcode (raw)</label>
                <input type="text" class="form-control" id="detail_barcode" disabled>
              </div>

              <div class="form-group">
                <label>Jenis Barang</label>
                <select class="form-control" id="detail_jenis_id" disabled>
                  @foreach ($jenis_barangs as $jenis)
                    <option value="{{ $jenis->id }}">{{ $jenis->jenis_barang }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label>Satuan Barang</label>
                <select class="form-control" id="detail_satuan_id" disabled>
                  @foreach ($satuans as $satuan)
                    <option value="{{ $satuan->id }}">{{ $satuan->satuan }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="text" class="form-control" id="detail_stok" disabled>
              </div>

              <div class="form-group">
                <label>Stok Minimum</label>
                <input type="number" class="form-control" id="detail_stok_minimum" disabled>
              </div>

              <div class="form-group">
                <label>Deskripsi</label>
                <textarea class="form-control" id="detail_deskripsi" disabled rows="3"></textarea>
              </div>

            </div>

          </div>
        </div>

        <div class="modal-footer bg-whitesmoke br">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Keluar</button>
        </div>
      </form>

    </div>
  </div>
</div>

@push('scripts')
<style>
/* batasi svg supaya tidak keluar kotak dan tetap discan-able */
#barcodePreviewDetail { width:100%; max-width:380px; height:48px; display:block; }
.modal .barcode-box { width:100%; align-items:center; justify-content:center; overflow:hidden; }
@media (max-width:991px) {
  #barcodePreviewDetail { max-width:280px; }
}
</style>

<script>
$(document).ready(function(){
  function barcodeFromName(name){
    if(!name) return 'ITEM';
    return String(name).toUpperCase().trim()
      .replace(/\s+/g,'-')
      .replace(/[^A-Z0-9\-]/g,'')
      .replace(/\-+/g,'-')
      .replace(/^\-+|\-+$/g,'') || 'ITEM';
  }

  function renderDetailPreviewFromName(name){
    const v = barcodeFromName(name);
    try {
      if (window.JsBarcode) {
        JsBarcode('#barcodePreviewDetail', v, {
          format: "CODE128",
          displayValue: false,
          height: 48,
          margin: 0
        });
      } else {
        $('#barcodePreviewDetail').text(v);
      }
    } catch(e) {
      $('#barcodePreviewDetail').text(v);
      console.error('JsBarcode render error (detail):', e);
    }
    $('#barcodeHumanDetail').text(name || '-');
  }

  // Pastikan ketika modal detail dibuka (index.js mengisi field) preview dibuat dari nama
  $(document).on('shown.bs.modal', '#modal_detail_barang', function(){
    const nama = $('#detail_nama_barang').val() || $('#detail_barcode').val() || '';
    // prioritas: nama_barang; fallback ke barcode raw jika nama kosong
    renderDetailPreviewFromName(nama);
  });
});
</script>
@endpush