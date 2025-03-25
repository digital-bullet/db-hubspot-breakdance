<?php
/**
 * Form Hooks for DB HubSpot for Breakdance
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle Breakdance form submission
 * 
 * @param array $form_data The form submission data
 * @param int $form_id The form ID
 */
function db_hubspot_handle_submission($form_data, $form_id) {
    // Check if this form is enabled for HubSpot integration
    $enabled_forms = get_option('db_hubspot_enabled_forms', array());
    if (!in_array($form_id, $enabled_forms)) {
        return;
    }
    
    // Get field values from the form data
    $fields = array();
    
    // Parse the form data
    // Note: Adjust this based on how Breakdance delivers form data
    foreach ($form_data as $key => $value) {
        // Skip non-field values
        if (in_array($key, array('form_id', 'post_id', 'referrer'))) {
            continue;
        }
        
        // Add field to our collection
        $fields[$key] = $value;
    }
    
    // Log form submission for debugging (optional)
    db_hubspot_log_submission($form_id, $fields);
    
    // Send to HubSpot
    $result = db_hubspot_send_to_hubspot($fields);
    
    // Log the result (optional)
    db_hubspot_log_api_result($form_id, $result);
    
    // You can hook into this to perform additional actions based on the HubSpot result
    do_action('db_hubspot_submission_result', $result, $form_id, $fields);
}

/**
 * Log form submission for debugging
 * 
 * @param int $form_id The form ID
 * @param array $fields The form fields
 */
function db_hubspot_log_submission($form_id, $fields) {
    // Simple file logging - only enable for debugging!
    // Uncomment the following to enable logging:
    
    /*
    $log_dir = DB_HUBSPOT_BREAKDANCE_PATH . 'logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/submissions.log';
    $timestamp = current_time('mysql');
    
    $log_entry = sprintf(
        "[%s] Form #%d Submission: %s\n",
        $timestamp,
        $form_id,
        json_encode($fields)
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    */
}

/**
 * Log API result for debugging
 * 
 * @param int $form_id The form ID
 * @param array $result The API result
 */
function db_hubspot_log_api_result($form_id, $result) {
    // Simple file logging - only enable for debugging!
    // Uncomment the following to enable logging:
    
    /*
    $log_dir = DB_HUBSPOT_BREAKDANCE_PATH . 'logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/api_results.log';
    $timestamp = current_time('mysql');
    
    $log_entry = sprintf(
        "[%s] Form #%d Result: %s\n",
        $timestamp,
        $form_id,
        json_encode($result)
    );
    
    file_put_contents($log_file, $log_entry, FILE_APPEND);
    */
} 