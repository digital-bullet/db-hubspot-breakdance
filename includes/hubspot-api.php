<?php
/**
 * HubSpot API Integration for DB HubSpot for Breakdance
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Send form data to HubSpot
 * 
 * @param array $form_data The form submission data
 * @return array The response from HubSpot API
 */
function db_hubspot_send_to_hubspot($form_data) {
    // Get API token
    $api_token = get_option('db_hubspot_api_token', '');
    if (empty($api_token)) {
        return array(
            'success' => false,
            'message' => __('HubSpot API token is not configured.', 'db-hubspot-breakdance')
        );
    }
    
    // Get field mapping
    $field_mapping_json = get_option('db_hubspot_field_mapping', '{}');
    $field_mapping = json_decode($field_mapping_json, true);
    if (empty($field_mapping) || json_last_error() !== JSON_ERROR_NONE) {
        return array(
            'success' => false,
            'message' => __('Field mapping is not configured correctly.', 'db-hubspot-breakdance')
        );
    }
    
    // Map form fields to HubSpot properties
    $properties = array();
    foreach ($field_mapping as $form_field => $hubspot_property) {
        if (isset($form_data[$form_field])) {
            $properties[$hubspot_property] = $form_data[$form_field];
        }
    }
    
    // If no mappable properties found, don't proceed
    if (empty($properties)) {
        return array(
            'success' => false,
            'message' => __('No mappable form fields found.', 'db-hubspot-breakdance')
        );
    }
    
    // Check for required email property
    if (!isset($properties['email'])) {
        return array(
            'success' => false,
            'message' => __('Email field is required for HubSpot contact creation.', 'db-hubspot-breakdance')
        );
    }
    
    // Prepare request data
    $hubspot_data = array(
        'properties' => $properties
    );
    
    // API endpoint for contact creation/update
    $endpoint = 'https://api.hubapi.com/crm/v3/objects/contacts';
    
    // Prepare request arguments
    $args = array(
        'method'      => 'POST',
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.1',
        'headers'     => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_token
        ),
        'body'        => json_encode($hubspot_data),
        'cookies'     => array()
    );
    
    // Send request to HubSpot
    $response = wp_remote_post($endpoint, $args);
    
    // Check for errors
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }
    
    // Parse response
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Check for successful response
    if ($response_code >= 200 && $response_code < 300) {
        return array(
            'success' => true,
            'message' => __('Contact successfully sent to HubSpot', 'db-hubspot-breakdance'),
            'data'    => $response_body
        );
    } else {
        // Error response from HubSpot
        $error_message = __('HubSpot API Error', 'db-hubspot-breakdance');
        if (isset($response_body['message'])) {
            $error_message = $response_body['message'];
        }
        
        return array(
            'success'      => false,
            'message'      => $error_message,
            'status_code'  => $response_code,
            'response'     => $response_body
        );
    }
}

/**
 * Test HubSpot API connection
 * 
 * @return array Test result with success status and message
 */
function db_hubspot_test_connection() {
    // Get API token
    $api_token = get_option('db_hubspot_api_token', '');
    if (empty($api_token)) {
        return array(
            'success' => false,
            'message' => __('HubSpot API token is not configured.', 'db-hubspot-breakdance')
        );
    }
    
    // API endpoint for account info
    $endpoint = 'https://api.hubapi.com/account-info/v3/details';
    
    // Prepare request arguments
    $args = array(
        'method'      => 'GET',
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.1',
        'headers'     => array(
            'Authorization' => 'Bearer ' . $api_token
        )
    );
    
    // Send request to HubSpot
    $response = wp_remote_get($endpoint, $args);
    
    // Check for errors
    if (is_wp_error($response)) {
        return array(
            'success' => false,
            'message' => $response->get_error_message()
        );
    }
    
    // Parse response
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Check for successful response
    if ($response_code >= 200 && $response_code < 300) {
        $account_name = isset($response_body['portalId']) ? $response_body['portalId'] : '';
        
        return array(
            'success' => true,
            'message' => sprintf(
                __('Successfully connected to HubSpot (Account ID: %s)', 'db-hubspot-breakdance'),
                $account_name
            ),
            'data'    => $response_body
        );
    } else {
        // Error response from HubSpot
        $error_message = __('HubSpot API Error', 'db-hubspot-breakdance');
        if (isset($response_body['message'])) {
            $error_message = $response_body['message'];
        }
        
        return array(
            'success'      => false,
            'message'      => $error_message,
            'status_code'  => $response_code,
            'response'     => $response_body
        );
    }
}

/**
 * Setup AJAX endpoint for testing HubSpot connection
 */
function db_hubspot_ajax_test_connection() {
    // Check for admin capabilities and nonce
    if (!current_user_can('manage_options') || !check_ajax_referer('db_hubspot_breakdance_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('You do not have permission to perform this action.', 'db-hubspot-breakdance')
        ));
    }
    
    // Test connection
    $result = db_hubspot_test_connection();
    
    if ($result['success']) {
        wp_send_json_success(array(
            'message' => $result['message']
        ));
    } else {
        wp_send_json_error(array(
            'message' => $result['message']
        ));
    }
}
add_action('wp_ajax_db_hubspot_test_connection', 'db_hubspot_ajax_test_connection'); 