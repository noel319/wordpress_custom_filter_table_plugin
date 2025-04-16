(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize datepickers
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: '2000:+5'
            });
        }
        
        // Media uploader for file selection
        $('#cft_upload_button').on('click', function(e) {
            e.preventDefault();
            
            var custom_uploader = wp.media({
                title: 'Select File',
                button: {
                    text: 'Use this file'
                },
                multiple: false
            });
            
            custom_uploader.on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                $('#cft_arquivo_url').val(attachment.url);
            });
            
            custom_uploader.open();
        });
        
        // CSV Import
        $('#cft_import_button').on('click', function() {
            var file = $('#csv_file')[0].files[0];
            
            if (!file) {
                alert('Please select a CSV file to import.');
                return;
            }
            
            if (file.type !== 'text/csv' && !file.name.match(/\.csv$/i)) {
                alert('Please select a valid CSV file.');
                return;
            }
            
            if (!confirm(custom_filter_table_admin.confirm_import)) {
                return;
            }
            
            var formData = new FormData();
            formData.append('action', 'custom_filter_table_import_csv');
            formData.append('nonce', custom_filter_table_admin.nonce);
            formData.append('csv_file', file);
            
            // Show spinner
            $('.import-spinner').addClass('is-active');
            
            // Send AJAX request
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    // Hide spinner
                    $('.import-spinner').removeClass('is-active');
                    
                    if (response.success) {
                        $('#import_results').html('<div class="notice notice-success"><p>' + custom_filter_table_admin.import_success + response.data.message + '</p></div>');
                    } else {
                        $('#import_results').html('<div class="notice notice-error"><p>' + custom_filter_table_admin.import_error + response.data + '</p></div>');
                    }
                },
                error: function() {
                    // Hide spinner
                    $('.import-spinner').removeClass('is-active');
                    
                    $('#import_results').html('<div class="notice notice-error"><p>' + custom_filter_table_admin.import_error + 'Server error</p></div>');
                }
            });
        });
    });
})(jQuery);