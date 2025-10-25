<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<style>
    #modalFileList .modal-dialog {
        width: 90%;
        max-width: 1200px;
    }
    .file-card {
        border: 1px solid #ddd;
        padding: 10px;
        margin-bottom: 15px;
        height: 320px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .file-card .file-name {
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-card .file-thumb {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
    }
    .file-card .file-thumb img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }
    .file-card .file-thumb .fa {
        font-size: 5em;
        color: #ccc;
    }
    .file-card .file-info {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .breadcrumb {
        margin-bottom: 10px;
    }
    #modalPreview .modal-dialog {
        width: 90%;
        max-width: 1200px;
    }
    #modalPreview .modal-body {
        padding: 20px;
        max-height: 85vh;
        overflow: auto;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">File Manager</h3>
                <div class="box-tools pull-right">
                    <?= ce_button('admin.filemanager.add', 'button', '<i class="fa fa-plus"></i> Buat Folder', 'class="btn btn-success btn-sm" id="btn-create-folder"'); ?>
                    <?= ce_button('admin.filemanager.add', 'button', '<i class="fa fa-upload"></i> Upload File', 'class="btn btn-primary btn-sm" id="btn-upload-file"'); ?>
                </div>
            </div>
            <div class="box-body">
                <div id="breadcrumb-container" class="breadcrumb-container">
                    <ol class="breadcrumb" id="directory-breadcrumb">
                        <li><a href="#" data-dir="uploads"><i class="fa fa-home"></i> Root</a></li>
                    </ol>
                </div>
                <div class="table-responsive">
                    <table id="dataTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th style="width:10px;">No</th>
                                <th width="50px">Tipe</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal File List -->
<div class="modal fade" id="modalFileList">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalFileListLabel">Upload File</h4>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <form id="formUploadFile" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="dir" id="uploadDir">
                        <input type="file" name="file" id="file-input" class="form-control" multiple>
                    </form>
                    <button type="button" id="btn-trigger-upload" class="btn btn-primary"><i class="fa fa-upload"></i> Pilih File</button>
                    <small class="text-muted" style="display: block; margin-top: 10px;">
                        Tipe file yang diizinkan: gif, jpg, jpeg, png, pdf, doc, docx, xls, xlsx, ppt, pptx, zip, rar, txt. Ukuran maksimal: 10MB.
                        <br>Anda dapat memilih beberapa file sekaligus.
                    </small>
                </div>
                <div id="upload-progress-container" class="progress" style="display: none; margin-top: 10px;">
                    <div id="upload-progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                        <span id="upload-progress-text">0%</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Folder -->
<div class="modal fade" id="modalCreateFolder">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Buat Folder Baru</h4>
            </div>
            <div class="modal-body">
                <form id="formCreateFolder">
                    <input type="hidden" name="parent_dir" id="parentDir">
                    <div class="form-group">
                        <label for="folder-name">Nama Folder</label>
                        <input type="text" name="folder_name" id="folder-name" class="form-control" placeholder="Masukkan nama folder">
                        <small class="text-muted">
                            Gunakan hanya huruf, angka, underscore dan dash.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" id="btn-submit-folder" class="btn btn-primary">Buat Folder</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview (untuk gambar dan dokumen) -->
<div class="modal fade" id="modalPreview">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalPreviewLabel">Preview</h4>
            </div>
            <div class="modal-body" id="preview-content" style="text-align: center;">
                <!-- Konten preview (iframe atau img) akan dimuat di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <span id="preview-download-btn"></span>
            </div>
        </div>
    </div>
</div>