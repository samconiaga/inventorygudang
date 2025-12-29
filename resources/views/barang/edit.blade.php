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
              <label class="d-block">Preview Barcode (Code39)</label>
              <div class="p-3"
                   style="border:1px dashed #d1d5db;border-radius:12px;background:#fafafa;min-height:275px;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                <div id="barcodePreviewEdit" style="font-family:'Libre Barcode 39', cursive;font-size:60px;line-height:1;color:#000;"></div>
                <div id="barcodeHumanEdit" style="font-size:12px;letter-spacing:2px;color:#6b7280;font-weight:600;margin-top:8px;">-</div>
              </div>
              <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_barcode"></div>
            </div>

            {{-- RIGHT: FORM --}}
            <div class="col-md-6">

              <div class="form-group">
                <label>Nama Barang</label>
                <input type="text" class="form-control" name="nama_barang" id="edit_nama_barang">
                <div class="alert alert-danger mt-2 d-none" role="alert" id="alert-edit_nama_barang"></div>
              </div>

              <div class="form-group">
                <label>Barcode (optional)</label>
                <input type="text" class="form-control" name="barcode" id="edit_barcode"
                       placeholder="Scan / tempel barcode di sini (opsional)">
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
