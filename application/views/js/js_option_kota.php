<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
$(function() {
    $('.optprov').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.id != '') {
            $.get('<?= base_url('kota/get-option/'); ?>' + data.id, function(result) {
                $('.optkot').html(result);
                $('.optkot').select2();
            });
        }
    });
    $('.optkot').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.id != '') {
            $.get('<?= base_url('kecamatan/get-option/'); ?>' + data.id, function(result) {
                $('.optkec').html(result);
                $('.optkec').select2();
            });
        }
    });
    $('.optkec').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.id != '') {
            $.get('<?= base_url('kelurahan/get-option/'); ?>' + data.id, function(result) {
                $('.optkel').html(result);
                $('.optkel').select2();
            });
        }
    });
    $('.optkel').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.id != '') {
            $.get('<?= base_url('tps/get-option/'); ?>' + data.id, function(result) {
                $('.opttps').html(result);
                $('.opttps').select2();
            });
        }
    });
    $('.optkel').on('select2:select', function(e) {
        var data = e.params.data;
        if (data.id != '') {
            $.get('<?= base_url('rt/get-option/'); ?>' + data.id, function(result) {
                $('.optrt').html(result);
                $('.optrt').select2();
            });
        }
    });
});

function opt_get_kota(provID, optkot, selectedID) {
    if (provID != '') {
        $.get('<?= base_url('kota/get-option/'); ?>' + provID, function(result) {
            $('#' + optkot).html(result);
            $('#' + optkot).val(selectedID);
            $('#' + optkot).select2();
        });
    }
}

function opt_get_kecamatan(kotaID, optkec, selectedID) {
    if (kotaID != '') {
        $.get('<?= base_url('kecamatan/get-option/'); ?>' + kotaID, function(result) {
            $('#' + optkec).html(result);
            if (typeof selectedID == 'object') {
                $('#' + optkec).val(selectedID).trigger('change');
            } else {
                $('#' + optkec).val(selectedID);
                $('#' + optkec).select2();
            }
        });
    }
}

function opt_get_kelurahan(kecID, optkel, selectedID) {
    if (kecID != '') {
        $.get('<?= base_url('kelurahan/get-option/'); ?>' + kecID, function(result) {
            $('#' + optkel).html(result);
            $('#' + optkel).val(selectedID);
            $('#' + optkel).select2();
        });
    }
}

function opt_get_tps(kelID, opttps, selectedID) {
    if (kelID != '') {
        $.get('<?= base_url('tps/get-option/'); ?>' + kelID, function(result) {
            $('#' + opttps).html(result);
            $('#' + opttps).val(selectedID);
            $('#' + opttps).select2();
        });
    }
}
</script>