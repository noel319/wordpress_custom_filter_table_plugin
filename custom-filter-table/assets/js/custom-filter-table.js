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
        
        // Filter change handler
        function handleFilterChange() {
            var municipio = $('#filter-municipio').val();
            var situacao = $('#filter-situacao').val();
            var dataInicio = $('#filter-data-inicio').val();
            var dataFim = $('#filter-data-fim').val();
            
            // Show loading
            $('#custom-filter-table-results .loading').show();
            
            // Send AJAX request
            $.ajax({
                url: custom_filter_table_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'custom_filter_table_ajax_filter',
                    nonce: custom_filter_table_vars.nonce,
                    municipio: municipio,
                    situacao: situacao,
                    data_inicio: dataInicio,
                    data_fim: dataFim
                },
                success: function(response) {
                    // Hide loading
                    $('#custom-filter-table-results .loading').hide();
                    
                    // Update results
                    $('#custom-filter-table-results').html(response);
                },
                error: function() {
                    // Hide loading
                    $('#custom-filter-table-results .loading').hide();
                    
                    // Show error message
                    $('#custom-filter-table-results').html('<p class="no-results">Erro ao carregar resultados. Por favor, tente novamente.</p>');
                }
            });
        }
        
        // Attach event handlers
        $('#filter-municipio, #filter-situacao').on('change', handleFilterChange);
        
        // Handle datepicker changes
        $('.datepicker').on('change', function() {
            // Validate date format
            var dateStr = $(this).val();
            if (dateStr && !isValidDate(dateStr)) {
                alert('Por favor, insira uma data válida no formato DD/MM/AAAA.');
                $(this).val('');
                return;
            }
            
            handleFilterChange();
        });
        
        // Initial load
        setTimeout(function() {
            handleFilterChange();
        }, 100);
        
        // Helper function to validate date
        function isValidDate(dateStr) {
            // Check format
            if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateStr)) {
                return false;
            }
            
            // Parse date parts
            var parts = dateStr.split('/');
            var day = parseInt(parts[0], 10);
            var month = parseInt(parts[1], 10) - 1;
            var year = parseInt(parts[2], 10);
            
            // Create date object and check if valid
            var date = new Date(year, month, day);
            return date.getFullYear() === year && 
                   date.getMonth() === month && 
                   date.getDate() === day;
        }
    });
    
})(jQuery);