<!-- Modal Denah Unit -->
<div class="modal fade" id="modalDenahUnit" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Denah Unit - <span id="blok-nama"></span></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Info Blok -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-th"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Unit</span>
                                <span class="info-box-number" id="total-unit">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tersedia</span>
                                <span class="info-box-number" id="unit-tersedia">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Terisi</span>
                                <span class="info-box-number" id="unit-terisi">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-wrench"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Maintenance</span>
                                <span class="info-box-number" id="unit-maintenance">0</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter dan Actions -->
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-3">
                        <select class="form-control" id="filter-unit-status">
                            <option value="">Semua Status</option>
                            <option value="TERSEDIA">Tersedia</option>
                            <option value="TERISI">Terisi</option>
                            <option value="MAINTENANCE">Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-9 text-right">
                        <button type="button" class="btn btn-info" id="btn-generate-units">
                            <i class="fa fa-magic"></i> Generate Unit
                        </button>
                        <button type="button" class="btn btn-primary" id="btn-add-unit">
                            <i class="fa fa-plus"></i> Tambah Unit
                        </button>
                    </div>
                </div>
                
                <!-- Grid Unit -->
                <div class="denah-unit-container">
                    <div class="units-grid" id="units-grid">
                        <!-- Unit akan dimuat di sini via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Unit -->
<div class="modal fade" id="modalUnit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formUnit">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title" id="modal-unit-title">Tambah Unit</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="unit-id" name="id">
                    <input type="hidden" id="unit-blok-id" name="pasar_blok_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit-nomor">Nomor Unit <span class="text-red">*</span></label>
                                <input type="text" class="form-control" id="unit-nomor" name="pasar_unit_nomor" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit-status">Status</label>
                                <select class="form-control" id="unit-status" name="pasar_unit_status">
                                    <option value="TERSEDIA">TERSEDIA</option>
                                    <option value="TERISI">TERISI</option>
                                    <option value="MAINTENANCE">MAINTENANCE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit-lebar">Lebar (m)</label>
                                <input type="number" class="form-control" id="unit-lebar" name="pasar_unit_lebar" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit-panjang">Panjang (m)</label>
                                <input type="number" class="form-control" id="unit-panjang" name="pasar_unit_panjang" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="unit-luas">Luas (m²)</label>
                                <input type="number" class="form-control" id="unit-luas" name="pasar_unit_luas" step="0.01" min="0" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit-harga">Harga Sewa</label>
                                <input type="number" class="form-control" id="unit-harga" name="pasar_unit_harga_sewa" min="0">
                            </div>
                        </div>
                        <div class="col-md-6" id="field-penyewa" style="display: none;">
                            <div class="form-group">
                                <label for="unit-penyewa">Nama Penyewa</label>
                                <input type="text" class="form-control" id="unit-penyewa" name="pasar_unit_penyewa">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="field-tanggal" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit-tanggal-sewa">Tanggal Sewa</label>
                                <input type="date" class="form-control" id="unit-tanggal-sewa" name="pasar_unit_tanggal_sewa">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit-tanggal-jatuh-tempo">Tanggal Jatuh Tempo</label>
                                <input type="date" class="form-control" id="unit-tanggal-jatuh-tempo" name="pasar_unit_tanggal_jatuh_tempo">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="unit-keterangan">Keterangan</label>
                                <textarea class="form-control" id="unit-keterangan" name="pasar_unit_keterangan" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-simpan-unit">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Generate Units -->
<div class="modal fade" id="modalGenerateUnits" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="formGenerateUnits">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Generate Multiple Units</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="generate-blok-id" name="blok_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start-nomor">Nomor Awal <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="start-nomor" name="start_nomor" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end-nomor">Nomor Akhir <span class="text-red">*</span></label>
                                <input type="number" class="form-control" id="end-nomor" name="end_nomor" min="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="generate-lebar">Lebar (m)</label>
                                <input type="number" class="form-control" id="generate-lebar" name="lebar" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="generate-panjang">Panjang (m)</label>
                                <input type="number" class="form-control" id="generate-panjang" name="panjang" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="generate-harga">Harga Sewa</label>
                                <input type="number" class="form-control" id="generate-harga" name="harga" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-generate">
                        <i class="fa fa-magic"></i> Generate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>