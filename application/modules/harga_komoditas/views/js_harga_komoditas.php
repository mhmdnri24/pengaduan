<script src="<?= base_url('assets/style/bower_components/chart.js/Chart.js'); ?>"></script>
<script>
$(document).ready(function() {
    // CSRF Token
    var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
    
    function updateCsrfToken(response) {
        if (response.csrf_hash) {
            csrfHash = response.csrf_hash;
            $("meta[name='csrf-token-hash']").attr("content", response.csrf_hash);
        }
    }

    function get_post_data(data) {
        data = data || {};
        data[csrfName] = csrfHash;
        return data;
    }

    // Initialize DataTable
    var table = $('#dataTable').DataTable({
        "processing": true,
        "serverSide": true,
        "pagingType": "full",
        "ajax": {
            "url": "<?= base_url('harga_komoditas/ajax_data') ?>",
            "type": "POST",
            "data": function(d) {
                // Add filter parameters
                d.tanggal_dari = $('#filter_tanggal_dari').val();
                d.tanggal_sampai = $('#filter_tanggal_sampai').val();
                d.komoditas_id = $('#filter_komoditas').val();
                d.pasar_id = $('#filter_pasar').val();
                d.status = $('#filter_status').val();
                
                return get_post_data(d);
            },
            "dataSrc": function(json) {
                updateCsrfToken(json);
                return json.data;
            }
        },
        "columnDefs": [
            { "targets": [0, 11], "orderable": false }
        ],
        "order": [[1, 'desc']],
        "language": {
            "processing": "Memuat data...",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "zeroRecords": "Data tidak ditemukan",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        }
    });

    function tableReload() {
        setTimeout(function() {
            table.ajax.reload();
        }, 500);
    }

    // Form submission
    $('#formHarga').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var url = $('#harga_id').val() ? 
            '<?= base_url('harga_komoditas/save/') ?>' + $('#harga_id').val() : 
            '<?= base_url('harga_komoditas/save') ?>';
        
        $.ajax({
            url: url,
            type: 'POST',
            data: get_post_data($(this).serializeArray().reduce(function(obj, item) {
                obj[item.name] = item.value;
                return obj;
            }, {})),
            dataType: 'json',
            beforeSend: function() {
                $('#btn-save').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                updateCsrfToken(response);
                
                if (response.status) {
                    toastr.success(response.message);
                    $('#formHarga')[0].reset();
                    $('#harga_id').val('');
                    $('#harga_tanggal').val('<?= date('Y-m-d'); ?>');
                    $('#duplicate-warning').hide();
                    tableReload();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('Terjadi kesalahan: ' + error);
            },
            complete: function() {
                $('#btn-save').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan');
            }
        });
    });

    // Reset form
    $('#btn-reset').on('click', function() {
        $('#formHarga')[0].reset();
        $('#harga_id').val('');
        $('#harga_tanggal').val('<?= date('Y-m-d'); ?>');
        $('#duplicate-warning').hide();
        $('#komoditas_id').trigger('change');
        $('#pasar_id').trigger('change');
    });

    // Edit button
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/get_by_id') ?>',
            type: 'POST',
            data: get_post_data({id: id}),
            dataType: 'json',
            success: function(response) {
                updateCsrfToken(response);
                
                if (response.status) {
                    var data = response.data;
                    $('#harga_id').val(data.harga_id);
                    $('#harga_tanggal').val(data.harga_tanggal);
                    $('#komoditas_id').val(data.komoditas_id).trigger('change');
                    $('#pasar_id').val(data.pasar_id).trigger('change');
                    $('#harga_beli').val(data.harga_beli);
                    $('#harga_jual').val(data.harga_jual);
                    $('#stok_tersedia').val(data.stok_tersedia);
                    $('#kualitas').val(data.kualitas);
                    $('#catatan').val(data.catatan);
                    $('#harga_status').val(data.harga_status);
                    
                    // Scroll to form
                    $('html, body').animate({
                        scrollTop: $('#formHarga').offset().top - 100
                    }, 500);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Gagal mengambil data');
            }
        });
    });

    // Copy button
    $(document).on('click', '.btn-copy', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Copy Data Harga',
            text: 'Data akan dicopy untuk tanggal hari ini. Lanjutkan?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Copy!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('harga_komoditas/copy') ?>',
                    type: 'POST',
                    data: get_post_data({id: id}),
                    dataType: 'json',
                    success: function(response) {
                        updateCsrfToken(response);
                        
                        if (response.status) {
                            toastr.success(response.message);
                            tableReload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Gagal copy data');
                    }
                });
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'Hapus Data Harga',
            text: 'Data yang dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('harga_komoditas/delete') ?>',
                    type: 'POST',
                    data: get_post_data({id: id}),
                    dataType: 'json',
                    success: function(response) {
                        updateCsrfToken(response);
                        
                        if (response.status) {
                            toastr.success(response.message);
                            tableReload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Gagal menghapus data');
                    }
                });
            }
        });
    });

    // Filter functionality
    $('#btn-filter').on('click', function() {
        $('#filter-panel').slideToggle();
    });

    $('#btn-apply-filter').on('click', function() {
        tableReload();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter_tanggal_dari').val('<?= date('Y-m-01'); ?>');
        $('#filter_tanggal_sampai').val('<?= date('Y-m-d'); ?>');
        $('#filter_komoditas').val('').trigger('change');
        $('#filter_pasar').val('').trigger('change');
        tableReload();
    });

    // Kategori komoditas change
    $('#kategori_komoditas').on('change', function() {
        var kategori_id = $(this).val();
        
        if (kategori_id) {
            $.ajax({
                url: '<?= base_url('harga_komoditas/get_komoditas_by_kategori') ?>',
                type: 'POST',
                data: get_post_data({kategori_id: kategori_id}),
                dataType: 'json',
                success: function(response) {
                    updateCsrfToken(response);
                    
                    if (response.status) {
                        var options = '<option value="">Pilih Komoditas</option>';
                        $.each(response.data, function(index, item) {
                            var satuan = item.komoditas_satuan ? ' (' + item.komoditas_satuan + ')' : '';
                            options += '<option value="' + item.komoditas_id + '" data-satuan="' + item.komoditas_satuan + '">' + 
                                      item.komoditas_nama + satuan + '</option>';
                        });
                        $('#komoditas_id').html(options).trigger('change');
                    }
                }
            });
        } else {
            // Reset to all komoditas
            location.reload();
        }
    });

    // Check duplicate when form fields change
    function checkDuplicate() {
        var tanggal = $('#harga_tanggal').val();
        var komoditas_id = $('#komoditas_id').val();
        var pasar_id = $('#pasar_id').val();
        var exclude_id = $('#harga_id').val();
        
        if (tanggal && komoditas_id && pasar_id) {
            $.ajax({
                url: '<?= base_url('harga_komoditas/check_duplicate') ?>',
                type: 'POST',
                data: get_post_data({
                    tanggal: tanggal,
                    komoditas_id: komoditas_id,
                    pasar_id: pasar_id,
                    exclude_id: exclude_id
                }),
                dataType: 'json',
                success: function(response) {
                    updateCsrfToken(response);
                    
                    if (response.status && response.is_duplicate) {
                        $('#duplicate-warning').show();
                    } else {
                        $('#duplicate-warning').hide();
                    }
                }
            });
        }
    }

    $('#harga_tanggal, #komoditas_id, #pasar_id').on('change', checkDuplicate);

    // Validate harga beli vs harga jual
    $('#harga_beli, #harga_jual').on('input', function() {
        var harga_beli = parseFloat($('#harga_beli').val()) || 0;
        var harga_jual = parseFloat($('#harga_jual').val()) || 0;
        
        if (harga_beli > 0 && harga_jual > 0 && harga_beli >= harga_jual) {
            toastr.warning('Harga beli harus lebih kecil dari harga jual');
            $(this).focus();
        }
    });

    // Chart functionality
    var priceChart = null;
    
    $('#btn-load-chart').on('click', function() {
        var komoditas_id = $('#chart_komoditas').val();
        var pasar_id = $('#chart_pasar').val();
        
        if (!komoditas_id) {
            toastr.warning('Pilih komoditas terlebih dahulu');
            return;
        }
        
        $.ajax({
            url: '<?= base_url('harga_komoditas/ajax_trend_harga') ?>',
            type: 'POST',
            data: get_post_data({
                komoditas_id: komoditas_id,
                pasar_id: pasar_id,
                days: 30
            }),
            dataType: 'json',
            beforeSend: function() {
                $('#btn-load-chart').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memuat...');
            },
            success: function(response) {
                updateCsrfToken(response);
                
                if (response.status) {
                    // Destroy existing chart (Chart.js v1.x method)
                    if (priceChart) {
                        priceChart.clear();
                        priceChart = null;
                    }

                    // Clear canvas
                    var canvas = document.getElementById('priceChart');
                    var ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);

                    // Create new chart (Chart.js v1.x syntax)
                    priceChart = new Chart(ctx).Line({
                        labels: response.labels,
                        datasets: [{
                            label: 'Harga Rata-rata (Rp)',
                            fillColor: 'rgba(60, 141, 188, 0.1)',
                            strokeColor: '#3c8dbc',
                            pointColor: '#3c8dbc',
                            pointStrokeColor: '#fff',
                            pointHighlightFill: '#fff',
                            pointHighlightStroke: '#3c8dbc',
                            data: response.data
                        }]
                    }, {
                        responsive: true,
                        maintainAspectRatio: false,
                        scaleBeginAtZero: false,
                        scaleLabel: function(label) {
                            return 'Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        },
                        tooltipTemplate: function(label) {
                            return 'Harga: Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        },
                        multiTooltipTemplate: function(label) {
                            return 'Harga: Rp ' + parseInt(label.value).toLocaleString('id-ID');
                        },
                        scaleFontColor: '#666',
                        scaleGridLineColor: 'rgba(0,0,0,0.1)',
                        scaleShowGridLines: true,
                        bezierCurve: true,
                        bezierCurveTension: 0.4,
                        pointDot: true,
                        pointDotRadius: 4,
                        pointDotStrokeWidth: 2,
                        datasetStroke: true,
                        datasetStrokeWidth: 2,
                        datasetFill: true,
                        animation: true,
                        animationSteps: 60,
                        animationEasing: 'easeOutQuart'
                    });
                    
                    toastr.success('Grafik berhasil dimuat');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Gagal memuat data grafik');
            },
            complete: function() {
                $('#btn-load-chart').prop('disabled', false).html('<i class="fa fa-line-chart"></i> Tampilkan Grafik');
            }
        });
    });

    // Show chart modal
    $(document).on('click', '.btn-chart', function() {
        $('#modalChart').modal('show');
    });

    // Export functionality
    $('#btn-export').on('click', function() {
        var filters = {
            tanggal_dari: $('#filter_tanggal_dari').val(),
            tanggal_sampai: $('#filter_tanggal_sampai').val(),
            komoditas_id: $('#filter_komoditas').val(),
            pasar_id: $('#filter_pasar').val()
        };
        
        var queryString = $.param(filters);
        window.open('<?= base_url('harga_komoditas/export_excel') ?>?' + queryString, '_blank');
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto refresh every 5 minutes
    setInterval(function() {
        tableReload();
    }, 300000); // 5 minutes
});
</script>
