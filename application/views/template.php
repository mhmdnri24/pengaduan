<?php
defined('BASEPATH') or exit('No direct script access allowed');
$user_id = $this->session->userdata('id_user');
if (!$user_id) {
    redirect('user/login');
    exit;
}
$usr = $this->user_m->user_by_id($user_id);
if (!$usr) {
    redirect('user/login');
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= ce_opsi('nama_situs'); ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="csrf-token-name" content="<?= $this->security->get_csrf_token_name(); ?>">
    <meta name="csrf-token-hash" content="<?= $this->security->get_csrf_hash(); ?>">
    <link rel="shortcut icon" href="<?= base_url('' . ce_opsi('favicon')); ?>">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet"
        href="<?= base_url('assets/style/bower_components/bootstrap/dist/css/bootstrap.min.css'); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="<?= base_url('assets/style/bower_components/font-awesome/css/font-awesome.min.css'); ?>">
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= base_url('assets/style/bower_components/Ionicons/css/ionicons.min.css'); ?>">
    <!-- DataTables -->
    <link rel="stylesheet"
        href="<?= base_url('assets/style/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css'); ?>">
    <!-- datetimepicker -->
    <link rel="stylesheet"
        href="<?= base_url('assets/style/bower_components/jquery-datetimepicker/build/jquery.datetimepicker.min.css'); ?>">
    <!-- iCheck for checkboxes and radio inputs -->
    <link rel="stylesheet" href="<?= base_url('assets/style/plugins/iCheck/all.css'); ?>">
    <!-- Select2 -->
    <link rel="stylesheet" href="<?= base_url('assets/style/bower_components/select2/dist/css/select2.min.css'); ?>">
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('assets/style/dist/css/AdminLTE.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/style/dist/css/skins/_all-skins.min.css'); ?>">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet"
        href="<?= base_url('assets/style/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css'); ?>">
    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/style/dropzone.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/style/apps.css'); ?>">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Lightbox2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Custom CSS for Forms -->
    <style>
        .status-toggle {
            cursor: pointer;
            transition: transform 0.2s;
        }
        .status-toggle:hover {
            transform: scale(1.1);
        }
        .form-section-header {
            margin-top: 0;
            margin-bottom: 5px;
            font-weight: 600;
            color: #3c8dbc;
        }
        .modal-xl {
            width: 100%;
        }
        .mt-0 {
            margin-top: 0;
        }
        #form-siswa .form-group {
            margin-bottom: 15px;
        }
        #form-siswa label {
            font-weight: 600;
        }
        #form-siswa .select2-container {
            width: 100% !important;
        }
        #form-siswa hr {
            margin-bottom: 20px;
            border-top: 1px solid #eee;
        }
        #form-siswa textarea {
            resize: vertical;
        }
        /* Badge styles */
        .badge {
            display: inline-block;
            white-space: nowrap;
            margin-bottom: 3px;
            font-size: 11px;
            padding: 4px 6px;
        }
        .badge-primary {
            background-color: #3c8dbc;
        }
        .badge-info {
            background-color: #00c0ef;
        }
        /* Ensure table cells with badges wrap content properly */
        #dataTable td {
            white-space: normal !important;
            vertical-align: middle !important;
        }
        .tableData td {
            white-space: normal !important;
            vertical-align: middle !important;
        }
        /* Modal improvements */
        .modal-header {
            background-color: #3c8dbc;
            color: white;
            border-radius: 5px 5px 0 0;
        }
        .modal-title {
            font-weight: 600;
        }
        .modal-content {
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,.5);
            width: 100%;
        }
        .form-section-header {
            color: #3c8dbc;
            font-weight: 600;
            border-bottom: 2px solid #3c8dbc;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3c8dbc;
            border-color: #367fa9;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fff;
        }
        /* Form input styling */
        .form-control {
            border-radius: 4px;
            box-shadow: none;
            border-color: #d2d6de;
            height: 34px;
            padding: 6px 12px;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus {
            border-color: #3c8dbc;
            box-shadow: none;
            outline: 0;
        }
        .input-group-addon {
            border-radius: 4px 0 0 4px;
            background-color: #eee;
            border: 1px solid #d2d6de;
            border-right: 0;
        }
        .radio-inline {
            margin-right: 15px;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .modal-xl {
                width: 100%;
                margin: 10px;
            }
            .row {
                margin-right: -10px;
                margin-left: -10px;
            }
            .col-md-6 {
                padding-right: 10px;
                padding-left: 10px;
            }
        }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js');?>"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js');?>"></script>
  <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <script>
        function printPage(url) {
            let printWindow = window.open(url, '_blank');
            printWindow.onload = function() {
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            };
        }
    </script>
</head>

<body class="hold-transition skin-blue sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">

        <header class="main-header">
            <a href="<?= base_url(); ?>" class="logo">
                <span class="logo-mini"><b>CMS</b></span>
                <span class="logo-lg"><b>Admin</b>Panel</span>
            </a>
            <nav class="navbar navbar-static-top">
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                        <li>
                            <?= anchor('', '<i class="fa fa-dashboard"></i>', ' data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Beranda"'); ?>
                        </li>
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="<?= base_url('user/foto/' . $usr->id_user) ?>" class="user-image"
                                    alt="avatar">
                                <span class="hidden-xs"><?= $usr->nama; ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header">
                                    <img src="<?= base_url('user/foto/' . $usr->id_user) ?>" class="img-circle"
                                        alt="avatar">

                                    <p>
                                        <?= $usr->nama . ' - ' . $usr->level; ?>
                                        <small><?= mdate('Masuk pada %d %M %Y %H:%s %A', $this->session->userdata('tanggal_login')); ?></small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <?= anchor('user/profil', 'Profil', 'class="btn btn-default btn-flat"'); ?>
                                    </div>
                                    <div class="pull-right">
                                        <?= anchor('user/logout', 'Keluar', 'class="btn btn-default btn-flat"'); ?>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <!-- =============================================== -->

        <!-- Left side column. contains the sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <?php $this->load->view('side_menu', array('usr' => $usr)); ?>
            </section>
            <!-- /.sidebar -->
        </aside>

        <!-- =============================================== -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper ce-background">
            <?php if (isset($header)): ?>
                <section class="content-header">
                    <h1><?= $header; ?></h1>
                </section>
            <?php endif; ?>
            <!-- Main content -->
            <section class="content">
                <?php $this->load->view($halaman); ?>

                <!-- ajax-modal -->
                <div class="modal fade modal-xl" id="ajax-modal">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content" id="ajax-modal-content">
                        </div>
                    </div>
                </div>
                <!-- /#ajax-modal -->

            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            Copyright &copy; <?= date('Y'); ?> <?= anchor('', ce_opsi('nama_situs')); ?>. All rights
            reserved.
        </footer>

        <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery 3 -->
    <script src="<?= base_url('assets/style/bower_components/jquery/dist/jquery.min.js'); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/colresizable@1.6.0/colResizable-1.6.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="<?= base_url('assets/style/bower_components/bootstrap/dist/js/bootstrap.min.js'); ?>"></script>
    <!-- SlimScroll -->
    <script src="<?= base_url('assets/style/bower_components/jquery-slimscroll/jquery.slimscroll.min.js'); ?>"></script>
    <!-- FastClick -->
    <script src="<?= base_url('assets/style/bower_components/fastclick/lib/fastclick.js'); ?>"></script>
    <!-- DataTables -->
    <script src="<?= base_url('assets/style/bower_components/datatables.net/js/jquery.dataTables.min.js'); ?>"></script>
    <script src="<?= base_url('assets/style/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js'); ?>"></script>
    <!-- datetimepicker -->
    <script
        src="<?= base_url('assets/style/bower_components/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js'); ?>">
    </script>
    <!-- iCheck 1.0.1 -->
    <script src="<?= base_url('assets/style/plugins/iCheck/icheck.min.js'); ?>"></script>
    <!-- Select2 -->
    <script src="<?= base_url('assets/style/bower_components/select2/dist/js/select2.full.min.js'); ?>"></script>
    <!-- AdminLTE App -->
    <script src="<?= base_url('assets/style/dist/js/adminlte.min.js'); ?>"></script>
    <!-- Bootstrap WYSIHTML5 -->
    <script src="<?= base_url('assets/style/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js'); ?>"></script>
    
    <!-- Custom JavaScript -->

    <?php
    if (isset($javascript)) {
        foreach ($javascript as $js => $param) {
            if ($param != null) {
                $this->load->view($js, $param);
            } else {
                $this->load->view($js);
            }
        }
    }
    $this->load->view('js/js_option_kota');
    ?>
    <script>
        function delete_confirm() {
            var choice = confirm("Apakah Anda yakin akan menghapus data ini?");
            if (choice)
                return true;
            else
                return false;
        }

        function ajaxModal(url) {
            $('#ajax-modal-content').html(
                '<div class="modal-body" style="padding:25px;"><div class="overlay"><i class="fa fa-refresh fa-spin"></i></div></div>'
            );
            $('#ajax-modal').modal('toggle');
            $.get(url, function(data) {
                $('#ajax-modal-content').html(data);
            });
            return false;
        }
        $(document).ready(function() {
            $('.select2').select2();
            $('.sidebar-menu').tree();
            $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });

            $('[data-toggle="tooltip"]').tooltip();
            $('.datepicker').datetimepicker({
                timepicker: false,
                format: 'Y-m-d'
            });
            $('.datetimepicker').datetimepicker({
                format: 'Y-m-d H:i'
            });
            $('.textarea').wysihtml5();
            $('.tableData').DataTable({
                'pagingType': 'full',
            });
            $('.tableData2').DataTable({
                'lengthChange': false,
                'searching': false,
                'pagingType': 'full'
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".currency").forEach(function(input) {
                input.addEventListener("input", function(e) {
                    e.target.value = formatCurrency(e.target.value);
                });
            });
        });

        function formatCurrency(value) {
            value = String(value).replace(/[^0-9]/g, ""); // Hanya angka
            return new Intl.NumberFormat("id-ID", {
                minimumFractionDigits: 0
            }).format(value);
        }

        function parseCurrency(formattedValue) {
            return Number(String(formattedValue).replace(/[^\d]/g, ""));
        }
    </script>
    <!-- AdminLTE for demo purposes -->
    <script src="<?= base_url('assets/style/dist/js/demo.js'); ?>"></script>
   <!-- Dropzone JS -->
   <script src="<?= base_url('assets/js/dropzone.min.js'); ?>"></script>
   <script>
       // Prevent Dropzone from auto discovering this element:
       Dropzone.autoDiscover = false;
   </script>
   <!-- SweetAlert2 -->
   <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Lightbox2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        // Configure toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
    </script>
</body>

</html>
