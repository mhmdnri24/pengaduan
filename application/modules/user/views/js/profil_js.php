<script>
$(document).ready(function() {
    // Open edit profile modal
    $('#btn-edit-profil').on('click', function() {
        $.ajax({
            url: '<?= base_url('user/ajax_get_profil_edit_form') ?>',
            type: 'GET',
            dataType: 'html',
            success: function(response) {
                $('#editProfilModalBody').html(response);
                $('#editProfilModal').modal('show');
            }
        });
    });

    // Submit edit profile form
    $('body').on('submit', '#form-edit-profil', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var originalButtonText = submitButton.html();
        var formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function() {
                submitButton.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');
            },
            success: function(response) {
                if (response.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload(); // Reload page to see changes
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        html: response.message,
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan koneksi.',
                });
            },
            complete: function() {
                submitButton.prop('disabled', false).html(originalButtonText);
            }
        });
    });

    // Image preview handler
    $('body').on('change', '#foto', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#foto-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>