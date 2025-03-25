<?php
/**
 * Plugin Name: DB HubSpot for Breakdance
 * Description: A clean, Zapier-free HubSpot integration for Breakdance Forms in WordPress.
 * Version: 1.0.0
 * Author: Digital Bullet
 * Author URI: https://digitalbullet.com
 * Text Domain: db-hubspot-breakdance
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DB_HUBSPOT_BREAKDANCE_VERSION', '1.0.0');
define('DB_HUBSPOT_BREAKDANCE_FILE', __FILE__);
define('DB_HUBSPOT_BREAKDANCE_PATH', plugin_dir_path(__FILE__));
define('DB_HUBSPOT_BREAKDANCE_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class DB_HubSpot_Breakdance {
    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load dependencies
        $this->load_dependencies();
        
        // Initialize hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Initialize HubSpot integration
        add_action('breakdance_form_submitted', array($this, 'handle_form_submission'), 10, 2);
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        // Admin settings page
        require_once DB_HUBSPOT_BREAKDANCE_PATH . 'admin/settings-page.php';
        
        // HubSpot API integration
        require_once DB_HUBSPOT_BREAKDANCE_PATH . 'includes/hubspot-api.php';
        
        // Form hooks
        require_once DB_HUBSPOT_BREAKDANCE_PATH . 'includes/form-hooks.php';
    }

    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_options_page(
            __('DB HubSpot for Breakdance', 'db-hubspot-breakdance'),
            __('DB HubSpot', 'db-hubspot-breakdance'),
            'manage_options',
            'db-hubspot-breakdance',
            'db_hubspot_breakdance_settings_page'
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('db_hubspot_breakdance', 'db_hubspot_api_token');
        register_setting('db_hubspot_breakdance', 'db_hubspot_enabled_forms');
        register_setting('db_hubspot_breakdance', 'db_hubspot_field_mapping');
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ('settings_page_db-hubspot-breakdance' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'db-hubspot-breakdance-admin',
            DB_HUBSPOT_BREAKDANCE_URL . 'assets/admin.css',
            array(),
            DB_HUBSPOT_BREAKDANCE_VERSION
        );

        wp_enqueue_script(
            'db-hubspot-breakdance-admin',
            DB_HUBSPOT_BREAKDANCE_URL . 'assets/admin.js',
            array('jquery'),
            DB_HUBSPOT_BREAKDANCE_VERSION,
            true
        );

        wp_localize_script('db-hubspot-breakdance-admin', 'db_hubspot_breakdance', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('db_hubspot_breakdance_nonce'),
        ));
    }

    /**
     * Handle Breakdance form submission
     */
    public function handle_form_submission($form_data, $form_id) {
        // Forward to the form hooks handler
        db_hubspot_handle_submission($form_data, $form_id);
    }
}

/**
 * Activation hook
 */
function db_hubspot_breakdance_activate() {
    // Create default options if they don't exist
    if (!get_option('db_hubspot_enabled_forms')) {
        add_option('db_hubspot_enabled_forms', array());
    }
    
    if (!get_option('db_hubspot_field_mapping')) {
        add_option('db_hubspot_field_mapping', '{}');
    }
}
register_activation_hook(DB_HUBSPOT_BREAKDANCE_FILE, 'db_hubspot_breakdance_activate');

/**
 * Deactivation hook
 */
function db_hubspot_breakdance_deactivate() {
    // We don't delete options on deactivation for data persistence
}
register_deactivation_hook(DB_HUBSPOT_BREAKDANCE_FILE, 'db_hubspot_breakdance_deactivate');

/**
 * Initialize the plugin
 */
function db_hubspot_breakdance_init() {
    DB_HubSpot_Breakdance::get_instance();
}
add_action('plugins_loaded', 'db_hubspot_breakdance_init'); 