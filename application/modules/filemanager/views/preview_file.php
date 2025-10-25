<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $file_name ?></h3>
                <div class="box-tools pull-right">
                    <a href="<?= site_url('filemanager/download_file/' . $file_id) ?>" class="btn btn-primary btn-sm"><i class="fa fa-download"></i> Download File</a>
                    <a href="<?= site_url('filemanager') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
                </div>
            </div>
            <div class="box-body">
                <div class="text-center">
                <?php
                // Tampilkan preview berdasarkan jenis file
                $pdf_types = ['pdf'];
                $office_types = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
                $text_types = ['txt'];
                
                if (in_array($file_ext, $pdf_types)) {
                    // Preview PDF
                    echo '<iframe src="' . site_url('filemanager/view_file/' . $file_id) . '" style="width:100%; height:600px;" frameborder="0"></iframe>';
                } elseif (in_array($file_ext, $office_types)) {
                    // Preview dokumen Office dengan Google Docs Viewer
                    $file_url = urlencode(site_url('filemanager/view_file/' . $file_id));
                    echo '<iframe src="https://docs.google.com/viewer?url=' . $file_url . '&embedded=true" style="width:100%; height:600px;" frameborder="0"></iframe>';
                    echo '<div class="alert alert-info mt-3">
                            <i class="fa fa-info-circle"></i> Jika dokumen tidak tampil dengan benar, silakan <a href="' . site_url('filemanager/download_file/' . $file_id) . '" class="alert-link">download file</a> untuk melihatnya.
                          </div>';
                } elseif (in_array($file_ext, $text_types)) {
                    // Untuk file teks, tampilkan konten dalam pre
                    $file_path = $this->session->userdata('file_' . $file_id);
                    if ($file_path && file_exists($file_path)) {
                        $content = htmlspecialchars(file_get_contents($file_path));
                        echo '<pre style="text-align:left; white-space: pre-wrap; height:600px; overflow:auto;">' . $content . '</pre>';
                    } else {
                        echo '<div class="alert alert-danger">File tidak dapat ditampilkan.</div>';
                    }
                } else {
                    // Untuk file lain yang tidak dapat di-preview
                    echo '<div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Preview tidak tersedia untuk jenis file ini. Silakan <a href="' . site_url('filemanager/download_file/' . $file_id) . '" class="alert-link">download file</a> untuk melihatnya.
                          </div>';
                    echo '<div class="text-center mt-4">
                            <i class="fa fa-file-o" style="font-size: 120px; color: #ccc;"></i>
                            <h4 class="mt-3">' . $file_name . '</h4>
                          </div>';
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div> 