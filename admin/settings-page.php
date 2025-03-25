<?php
/**
 * Admin Settings Page for DB HubSpot for Breakdance
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the settings page
 */
function db_hubspot_breakdance_settings_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Save settings if form is submitted
    if (isset($_POST['db_hubspot_save_settings']) && check_admin_referer('db_hubspot_settings_nonce')) {
        // Sanitize and save API token
        if (isset($_POST['db_hubspot_api_token'])) {
            update_option('db_hubspot_api_token', sanitize_text_field($_POST['db_hubspot_api_token']));
        }
        
        // Sanitize and save enabled forms
        if (isset($_POST['db_hubspot_enabled_forms'])) {
            $enabled_forms = array_map('intval', (array) $_POST['db_hubspot_enabled_forms']);
            update_option('db_hubspot_enabled_forms', $enabled_forms);
        } else {
            update_option('db_hubspot_enabled_forms', array());
        }
        
        // Sanitize and save field mapping
        if (isset($_POST['db_hubspot_field_mapping'])) {
            $field_mapping = sanitize_textarea_field($_POST['db_hubspot_field_mapping']);
            // Validate JSON
            json_decode($field_mapping);
            if (json_last_error() === JSON_ERROR_NONE) {
                update_option('db_hubspot_field_mapping', $field_mapping);
            }
        }
        
        // Show success message
        add_settings_error(
            'db_hubspot_messages',
            'db_hubspot_message',
            __('Settings Saved', 'db-hubspot-breakdance'),
            'updated'
        );
    }
    
    // Get current settings
    $api_token = get_option('db_hubspot_api_token', '');
    $enabled_forms = get_option('db_hubspot_enabled_forms', array());
    $field_mapping = get_option('db_hubspot_field_mapping', '{}');
    
    // Get all Breakdance forms
    $breakdance_forms = db_hubspot_get_breakdance_forms();
    
    // Settings page HTML
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <?php settings_errors('db_hubspot_messages'); ?>
        
        <div class="db-hubspot-admin-container">
            <div class="db-hubspot-admin-main">
                <form method="post" action="">
                    <?php wp_nonce_field('db_hubspot_settings_nonce'); ?>
                    
                    <div class="db-hubspot-section">
                        <h2><?php _e('HubSpot Connection', 'db-hubspot-breakdance'); ?></h2>
                        <p class="description">
                            <?php _e('Enter your HubSpot Private App Token to connect.', 'db-hubspot-breakdance'); ?>
                            <a href="https://developers.hubspot.com/docs/api/private-apps" target="_blank">
                                <?php _e('Learn how to create a Private App Token', 'db-hubspot-breakdance'); ?>
                            </a>
                        </p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="db_hubspot_api_token">
                                        <?php _e('Private App Token', 'db-hubspot-breakdance'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="password" 
                                        name="db_hubspot_api_token" 
                                        id="db_hubspot_api_token" 
                                        value="<?php echo esc_attr($api_token); ?>" 
                                        class="regular-text"
                                        autocomplete="off"
                                    />
                                    <button type="button" 
                                        id="db_hubspot_test_connection" 
                                        class="button button-secondary">
                                        <?php _e('Test Connection', 'db-hubspot-breakdance'); ?>
                                    </button>
                                    <p class="description">
                                        <?php _e('Your private app token is stored securely in your WordPress database.', 'db-hubspot-breakdance'); ?>
                                    </p>
                                    <div id="db_hubspot_connection_status"></div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="db-hubspot-section">
                        <h2><?php _e('Form Selection', 'db-hubspot-breakdance'); ?></h2>
                        <p class="description">
                            <?php _e('Select which Breakdance forms should send data to HubSpot.', 'db-hubspot-breakdance'); ?>
                        </p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <?php _e('Enabled Forms', 'db-hubspot-breakdance'); ?>
                                </th>
                                <td>
                                    <?php if (empty($breakdance_forms)) : ?>
                                        <p>
                                            <?php _e('No Breakdance forms found. Create forms in Breakdance first.', 'db-hubspot-breakdance'); ?>
                                        </p>
                                    <?php else : ?>
                                        <fieldset>
                                            <legend class="screen-reader-text">
                                                <?php _e('Enabled Forms', 'db-hubspot-breakdance'); ?>
                                            </legend>
                                            <?php foreach ($breakdance_forms as $form_id => $form_name) : ?>
                                                <label>
                                                    <input type="checkbox" 
                                                        name="db_hubspot_enabled_forms[]" 
                                                        value="<?php echo esc_attr($form_id); ?>"
                                                        <?php checked(in_array($form_id, $enabled_forms)); ?>
                                                    />
                                                    <?php echo esc_html($form_name); ?>
                                                </label><br>
                                            <?php endforeach; ?>
                                        </fieldset>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="db-hubspot-section">
                        <h2><?php _e('Field Mapping', 'db-hubspot-breakdance'); ?></h2>
                        <p class="description">
                            <?php _e('Map Breakdance form fields to HubSpot contact properties.', 'db-hubspot-breakdance'); ?>
                            <?php _e('Use JSON format: {"breakdance_field_name": "hubspot_property_name"}', 'db-hubspot-breakdance'); ?>
                        </p>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="db_hubspot_field_mapping">
                                        <?php _e('Field Mapping (JSON)', 'db-hubspot-breakdance'); ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea 
                                        name="db_hubspot_field_mapping" 
                                        id="db_hubspot_field_mapping" 
                                        rows="10" 
                                        class="large-text code"
                                    ><?php echo esc_textarea($field_mapping); ?></textarea>
                                    <p class="description">
                                        <?php _e('Example: {"name": "firstname", "email": "email", "company": "company", "message": "message"}', 'db-hubspot-breakdance'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" 
                            name="db_hubspot_save_settings" 
                            id="db_hubspot_save_settings" 
                            class="button button-primary" 
                            value="<?php _e('Save Settings', 'db-hubspot-breakdance'); ?>"
                        />
                    </p>
                </form>
            </div>
            
            <div class="db-hubspot-admin-sidebar">
                <div class="db-hubspot-help-box">
                    <h3><?php _e('Help & Documentation', 'db-hubspot-breakdance'); ?></h3>
                    <p>
                        <?php _e('This plugin connects your Breakdance forms directly to HubSpot without Zapier or other third-party services.', 'db-hubspot-breakdance'); ?>
                    </p>
                    <h4><?php _e('Getting Started', 'db-hubspot-breakdance'); ?></h4>
                    <ol>
                        <li><?php _e('Create a Private App in HubSpot with at least contact scopes', 'db-hubspot-breakdance'); ?></li>
                        <li><?php _e('Copy your Private App Token and paste it above', 'db-hubspot-breakdance'); ?></li>
                        <li><?php _e('Select which Breakdance forms to connect', 'db-hubspot-breakdance'); ?></li>
                        <li><?php _e('Map your form fields to HubSpot properties', 'db-hubspot-breakdance'); ?></li>
                    </ol>
                    <p>
                        <a href="https://developers.hubspot.com/docs/api/private-apps" target="_blank">
                            <?php _e('HubSpot Private App Documentation →', 'db-hubspot-breakdance'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get all Breakdance forms
 * 
 * @return array Associative array of form IDs and names
 */
function db_hubspot_get_breakdance_forms() {
    global $wpdb;
    
    $forms = array();
    
    // Query to find Breakdance forms in post content
    $query = "
        SELECT ID, post_title 
        FROM {$wpdb->posts} 
        WHERE post_type = 'page' 
        AND post_status = 'publish'
    ";
    
    $pages = $wpdb->get_results($query);
    
    // Check each page for Breakdance forms
    foreach ($pages as $page) {
        $form_ids = db_hubspot_find_breakdance_forms_in_page($page->ID);
        
        foreach ($form_ids as $form_id) {
            $forms[$form_id] = sprintf(
                __('Form #%1$s (Page: %2$s)', 'db-hubspot-breakdance'),
                $form_id,
                $page->post_title
            );
        }
    }
    
    return $forms;
}

/**
 * Find Breakdance forms in a page
 * 
 * @param int $page_id The page ID to check
 * @return array Array of form IDs found in the page
 */
function db_hubspot_find_breakdance_forms_in_page($page_id) {
    $form_ids = array();
    
    // This is a basic example. In a real implementation, you would need to:
    // 1. Check if Breakdance stores form data in post meta or a custom table
    // 2. Query that data source to get the form IDs
    
    // For now, as a placeholder, we'll return some dummy IDs
    // Replace this logic with actual Breakdance form detection
    if (function_exists('breakdance_get_forms_in_page')) {
        // Use Breakdance's API if available
        $form_ids = breakdance_get_forms_in_page($page_id);
    } else {
        // Fallback to manually detecting forms
        // This is just a placeholder - you'll need to implement this based on how Breakdance stores forms
        $content = get_post_meta($page_id, '_breakdance_data', true);
        if ($content) {
            // Parse the content to find form IDs
            // This is a simplified example - you'll need to adapt it based on Breakdance's structure
            preg_match_all('/"formId":(\d+)/', $content, $matches);
            if (!empty($matches[1])) {
                $form_ids = array_unique($matches[1]);
            }
        }
    }
    
    return $form_ids;
} 