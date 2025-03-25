/**
 * Admin JavaScript for DB HubSpot for Breakdance
 */
jQuery(document).ready(function($) {
    
    // Test HubSpot connection
    $('#db_hubspot_test_connection').on('click', function() {
        const apiToken = $('#db_hubspot_api_token').val();
        const statusEl = $('#db_hubspot_connection_status');
        
        if (!apiToken) {
            statusEl.removeClass('success').addClass('error')
                .text('Please enter a HubSpot Private App Token')
                .show();
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).text('Testing...');
        statusEl.removeClass('success error').text('').hide();
        
        $.ajax({
            url: db_hubspot_breakdance.ajax_url,
            type: 'POST',
            data: {
                action: 'db_hubspot_test_connection',
                nonce: db_hubspot_breakdance.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusEl.removeClass('error').addClass('success')
                        .text(response.data.message)
                        .show();
                } else {
                    statusEl.removeClass('success').addClass('error')
                        .text(response.data.message)
                        .show();
                }
            },
            error: function() {
                statusEl.removeClass('success').addClass('error')
                    .text('Connection failed. Please check your network connection.')
                    .show();
            },
            complete: function() {
                $('#db_hubspot_test_connection').prop('disabled', false).text('Test Connection');
            }
        });
    });
    
    // JSON validation for field mapping
    $('#db_hubspot_field_mapping').on('change', function() {
        const $this = $(this);
        const value = $this.val();
        
        try {
            if (value) {
                JSON.parse(value);
                $this.css('border-color', '');
            }
        } catch (e) {
            $this.css('border-color', '#d63638');
        }
    });
    
}); 