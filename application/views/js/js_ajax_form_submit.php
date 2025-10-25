<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>
<script>
function formSubmit(event, callback) {
    let url = $(event).attr('action');
    let formData = $(event).serializeArray();
    
    // Make sure the CSRF token is included
    let csrfName = $("meta[name='csrf-token-name']").attr("content");
    let csrfHash = $("meta[name='csrf-token-hash']").attr("content");
    
    // Check if CSRF token is already in the form
    let csrfExists = false;
    for (let i = 0; i < formData.length; i++) {
        if (formData[i].name === csrfName) {
            formData[i].value = csrfHash; // Update with the latest token
            csrfExists = true;
            break;
        }
    }
    
    // If not, add it
    if (!csrfExists) {
        formData.push({
            name: csrfName,
            value: csrfHash
        });
    }
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        beforeSend: function(xhr) {
            // Also set the CSRF token in the header for redundancy
            xhr.setRequestHeader('X-CSRF-Token', csrfHash);
        },
        complete: function(xhr) {
            // Get and update CSRF token from response header
            let newCsrfHash = xhr.getResponseHeader("X-CSRF-Hash");
            if (newCsrfHash) {
                $("meta[name='csrf-token-hash']").attr("content", newCsrfHash);
                // Update any CSRF token inputs in all forms
                $('input[name="' + csrfName + '"]').val(newCsrfHash);
            }
        },
        success: function(data) {
            if (data && data.csrf_hash) {
                // Also check for CSRF token in response data
                $("meta[name='csrf-token-hash']").attr("content", data.csrf_hash);
                $('input[name="' + csrfName + '"]').val(data.csrf_hash);
            }
            callback(data);
        },
        error: function(xhr, status, error) {
            console.error("Form submission error:", status, error);
            
            if (xhr.status === 403) {
                alert("Sesi Anda mungkin telah kedaluwarsa. Halaman akan dimuat ulang.");
                window.location.reload();
            } else {
                alert("Terjadi kesalahan saat mengirim data. Silakan coba lagi.");
            }
        }
    });
    return false;
}
</script>