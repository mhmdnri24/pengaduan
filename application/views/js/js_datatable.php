<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
var table = $('#dataTable').DataTable({
    "processing": true,
    "serverSide": true,
    "pagingType": "full",
    "ajax": {
        "url": "<?= $ajax_url; ?>",
        "type": "POST",
        "data": function(d) {
            // Try to get CSRF token from meta tag first, then from input field
            var csrfName = $("meta[name='csrf-token-name']").attr("content");
            var csrfHash = $("meta[name='csrf-token-hash']").attr("content");
            
            // Fallback to input field if meta tag is not available
            if (!csrfHash) {
                var csrfInput = $('input[name^="csrf_"]').first();
                if (csrfInput.length) {
                    csrfName = csrfInput.attr('name');
                    csrfHash = csrfInput.val();
                }
            }
            
            if (csrfName && csrfHash) {
                d[csrfName] = csrfHash;
            }
        },
        "dataSrc": function(json) {
            // Update CSRF token from JSON response
            if (json.csrf_hash) {
                // Update meta tag
                $("meta[name='csrf-token-hash']").attr("content", json.csrf_hash);
                
                // Update input field if exists
                var csrfInput = $('input[name^="csrf_"]').first();
                if (csrfInput.length) {
                    csrfInput.val(json.csrf_hash);
                }
            }
            return json.data;
        },
        "complete": function(xhr) {
            // Update CSRF token from header as fallback
            let newCsrfHash = xhr.getResponseHeader("X-CSRF-Hash");
            if (newCsrfHash) {
                $("meta[name='csrf-token-hash']").attr("content", newCsrfHash);
                
                // Update input field if exists
                var csrfInput = $('input[name^="csrf_"]').first();
                if (csrfInput.length) {
                    csrfInput.val(newCsrfHash);
                }
            }
            // console.log(xhr);
        }
    }
});

function tableReload() {
    setTimeout(function() {
        table.ajax.reload();
    }, 500);
}
</script>