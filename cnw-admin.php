<?php

/*
Plugin Name: CNW Admin
Description: Adds custom styling, security, performance and functionality to Cloud Nine Web managed sites
Version: 0.6.1.9
Author: <a href="https://cloudnineweb.co" target="_blank">Cloud Nine Web</a>
*/

namespace CNW_Admin;

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
include(plugin_dir_path(__FILE__) . 'cloudnine-analytics.php');


/**
 * Class CNW_Main
 * Main class responsible for handling the plugin's functionality
 */
class CNW_Main {

    public function __construct() {
        // Load all necessary styles and scripts
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles']);
		
        add_action('admin_head', [$this, 'hide_cnw_menu']);


        // Include necessary files
        $this->include_files();

        // Check if the ACF plugin is active, if so deactivate it
        $this->maybe_deactivate_acf_free();

        // Initialize ACF Pro functionality
        $this->initialize_acf_pro();

        // Handle plugin updates
        $this->handle_updates();
        // Check if 'cnw-email-users' is enabled
        $cnw_email_users_enabled = get_option('enable_admin_settings');
            if ($cnw_email_users_enabled) {
            // Only execute this code if 'cnw-email-users' is enabled
            add_action('admin_menu', [$this, 'add_email_users_admin_page']);
            add_action('admin_post_send_emails', [$this, 'send_emails_to_users']);
        }
    }

    /**
     * Enqueue necessary styles
     */
    public function enqueue_styles() {
        wp_enqueue_style('cnw-admin-general', plugin_dir_url(__FILE__) . 'css/general.css', array(), '0.1');
    }

    /**
     * If ACF plugin is active, deactivate it
     */
    private function maybe_deactivate_acf_free() {
        if(in_array('advanced-custom-fields/acf.php', get_option( 'active_plugins', array() ), true )){
            add_action( 'admin_init', function(){
                deactivate_plugins('advanced-custom-fields/acf.php');
            } );
        }
    }

    /**
     * Initialize ACF Pro functionality
     */
    private function initialize_acf_pro() {
        global $cnw_acf_already_exists;
        
        // Check if Advanced Themer is active
        if (is_plugin_active('bricks-advanced-themer/bricks-advanced-themer.php')) {
            return;  // Exit early if Advanced Themer is active
        }
    
        if (!class_exists('ACF')) {
            $cnw_acf_already_exists = false;
            // Define path and URL to the ACF plugin.
            define('CNW_ACF_PATH', plugin_dir_path(__FILE__) . '/includes/acf/');
            define('CNW_ACF_URL', plugin_dir_url(__FILE__) . '/includes/acf/');
            
            // Include the ACF plugin.
            include_once(CNW_ACF_PATH . 'acf.php');

            // Setup ACF settings and filters
            $this->setup_acf_settings();
        }
        
        // Set JSON Save Point for ACF
        add_filter('acf/settings/save_json', [$this, 'acf_json_save_point']);
    }
    
    /**
     * Setup ACF settings and filters
     */
    private function setup_acf_settings() {
        add_filter('acf/settings/show_updates', '__return_false', 100);
        add_filter('acf/settings/path', [$this, 'acf_settings_path']);
        add_filter('acf/settings/dir', [$this, 'acf_settings_dir']);
        add_filter('site_transient_update_plugins', [$this, 'stop_acf_update_notifications'], 11);
        add_filter('acf/settings/load_json', [$this, 'cnw_acf_json_load_point']); // Load JSON

        // Customize the url setting to fix incorrect asset URLs.
        add_filter('acf/settings/url', [$this, 'acf_settings_url']);
    }

    public function cnw_acf_json_load_point( $paths ) {
        unset($paths[0]); // Remove original path (optional)
        $paths[] = plugin_dir_path( __FILE__ ) . 'acf-json';
        return $paths;
    }

    public function acf_settings_path() {
        return CNW_ACF_PATH;
    }

    public function acf_settings_dir() {
        return CNW_ACF_URL;
    }

    public function stop_acf_update_notifications($value) {
        unset($value->response[ plugin_basename(__FILE__) ]);
        return $value;
    }

    public function acf_settings_url($url) {
        return CNW_ACF_URL;
    }

    public function acf_json_save_point($path) {
        return plugin_dir_path(__FILE__) . 'acf-json';
    }

    public function hide_cnw_menu() {
        if (!current_user_can('administrator')) {
            echo '<style>
                    li#toplevel_page_cnw-settings { 
                        display: none; 
                    }
                  </style>';
		}
	}

	/**
     * Include necessary files
     */
	private function include_files() {
        include(plugin_dir_path(__FILE__) . 'includes/acffields.php');
        include(plugin_dir_path(__FILE__) . 'includes/cnw-security.php');
        include(plugin_dir_path(__FILE__) . 'includes/cnw-admin-settings.php');
        include(plugin_dir_path(__FILE__) . 'includes/cnw-admin-ui.php');
        include(plugin_dir_path(__FILE__) . 'includes/cnw-performance.php');
		include(plugin_dir_path(__FILE__) . 'includes/cnw-seo-settings.php');
		include(plugin_dir_path(__FILE__) . 'includes/cnw-users.php');
		include(plugin_dir_path(__FILE__) . 'includes/cnw-forms.php');
		include(plugin_dir_path(__FILE__) . 'includes/cnw-notifications.php');
        include(plugin_dir_path(__FILE__) . 'includes/keyword-tracking.php');
    }

    /**
     * Handle plugin updates
     */
    private function handle_updates() {
        $plugin_slug = 'cnw-admin'; // Ensure this matches the actual plugin slug
        $remote_json_url = 'https://updates.cloudnineweb.co/cnw-admin-update.json';
    
        add_filter('pre_set_site_transient_update_plugins', function($transient) use ($remote_json_url, $plugin_slug) {
            if (empty($transient->checked)) {
                return $transient;
            }
    
            $plugin_basename = plugin_basename(__FILE__); // Plugin base name
            $current_version = get_plugin_data(__FILE__)['Version'];
            $response = wp_safe_remote_get($remote_json_url);
    
            if ($response['response']['code'] == 200) {
                $remote_data = json_decode($response['body']);
    
                if ($remote_data && version_compare($current_version, $remote_data->version, '<')) {
                    $transient->response[$plugin_basename] = (object)[
                        'slug' => $plugin_slug,
                        'new_version' => $remote_data->version,
                        'url' => $remote_data->author_profile, // or any relevant URL
                        'package' => $remote_data->download_url,
                    ];
                }
            }
    
            return $transient;
        });
    }
    
}

new CNW_Main();


