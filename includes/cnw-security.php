<?php

// Ensure ACF is active and initialized
add_action('acf/include_field_types', 'cnw_initialize_security_settings');

// Define the main function to initialize security settings
function cnw_initialize_security_settings() {
    $security_settings_value = get_field('security_settings', 'option');

    if (is_array($security_settings_value)) {
        if (in_array('cnw-hidewp', $security_settings_value)) {
			add_filter('the_generator', '__return_empty_string');
		}

        if (in_array('cnw-comments', $security_settings_value)) {
			add_action('admin_init', 'disable_comments_post_types_support');
			add_filter('comments_open', 'disable_comments_status', 20, 2);
			add_filter('pings_open', 'disable_comments_status', 20, 2);
			add_filter('comments_array', 'disable_comments_hide_existing_comments', 10, 2);
			add_action('admin_menu', 'disable_comments_admin_menu');
			add_action('admin_init', 'disable_comments_admin_menu_redirect');
			add_action('admin_init', 'disable_comments_dashboard');
			add_action('init', 'disable_comments_admin_bar');

        }

        if (in_array('cnw-xmlrpc', $security_settings_value)) {
			add_filter( 'xmlrpc_methods', 'remove_xmlrpc_pingback_ping' );
			add_action( 'init', 'disable_xmlrpc', 999 );
			add_filter( 'pings_open', '__return_false' );

        }

        if (in_array('cnw-tpeditors', $security_settings_value)) {
			add_action( 'init', 'disable_theme_plugin_editors' );
        }

        if (in_array('cnw-blacklist', $security_settings_value)) {
			add_action('wp_loaded', 'update_comment_blacklist');
			add_action('wp', 'schedule_comment_blacklist_update');
			add_action('update_comment_blacklist_hook', 'update_comment_blacklist');
        }

        if (in_array('cnw-userenumeration', $security_settings_value)) {
			add_filter('init', 'block_user_enumeration');
			add_filter('rest_authentication_errors', 'restrict_rest_api_user_enumeration');
			add_filter('wp_sitemaps_add_provider', 'remove_user_sitemap', 10, 2);
		}

		if (in_array('cnw-limit-login', $security_settings_value)) {
			add_action('login_init', 'limit_login_attempts');
			add_action('wp_dashboard_setup', 'limitlogin_dashboard_widgets');
		}


    }
}


// Disable Comments
function disable_comments_post_types_support() {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}

function disable_comments_status() {
    return false;
}

function disable_comments_hide_existing_comments($comments) {
    $comments = array();
    return $comments;
}

function disable_comments_admin_menu() {
    remove_menu_page('edit-comments.php');
}

function disable_comments_admin_menu_redirect() {
    global $pagenow;
    if ($pagenow === 'edit-comments.php') {
        wp_redirect(admin_url()); exit;
    }
}

function disable_comments_dashboard() {
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}

function disable_comments_admin_bar() {
	if (is_admin_bar_showing()) {
		remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
	}
}


// Disable XML-RPC
function remove_xmlrpc_pingback_ping( $methods ) {
	unset( $methods['pingback.ping'] );
	return $methods;
}

function disable_xmlrpc() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		wp_die( 'XML-RPC services are disabled on this site.', 'XML-RPC services disabled', array( 'response' => 403 ) );
	}
}


// Disable Theme and Plugin Editors
function disable_theme_plugin_editors() {
    if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
		define( 'DISALLOW_FILE_EDIT', true );
	}
}


// Automatically update comment blacklist
function update_comment_blacklist() {
    // Define the URL of the comment blacklist file on GitHub
    $blacklist_url = 'https://raw.githubusercontent.com/splorp/wordpress-comment-blacklist/master/blacklist.txt';

    // Fetch the latest blacklist content
    $blacklist_content = wp_remote_get($blacklist_url);

    if (is_wp_error($blacklist_content)) {
        // Handle error, e.g., log it or display a message
        error_log('Error fetching comment blacklist: ' . $blacklist_content->get_error_message());
    } else {
        // Save the fetched content to the WordPress option
        update_option('disallowed_keys', $blacklist_content['body']);
    }
}

// Schedule the update to run every 24 hours
function schedule_comment_blacklist_update() {
    if (!wp_next_scheduled('update_comment_blacklist_hook')) {
        wp_schedule_event(time(), 'daily', 'update_comment_blacklist_hook');
    }
}


// Block User Enumeration
function block_user_enumeration() {
	if ( isset( $_REQUEST['author'] )
		&& preg_match( '/\\d/', $_REQUEST['author'] ) > 0
		&& ! is_user_logged_in()
	) {
		wp_die( 'forbidden - number in author name not allowed = ' . esc_html( $_REQUEST['author'] ) );
	}
}


function restrict_rest_api_user_enumeration($access) {
	if ( is_user_logged_in() ) {
		return $access;
	}

	if ( ( preg_match( '/users/i', $_SERVER['REQUEST_URI'] ) !== 0 )
		|| ( isset( $_REQUEST['rest_route'] ) && ( preg_match( '/users/i', $_REQUEST['rest_route'] ) !== 0 ) )
	) {
		return new \WP_Error(
			'rest_cannot_access',
			'Only authenticated users can access the User endpoint REST API.',
			[
				'status' => rest_authorization_required_code()
			]
		);
	}

	return $access;
}

function remove_user_sitemap($provider, $name) {
	if ( 'users' === $name ) {
		return false;
	}

	return $provider;
}

// Limit Login Attempts
function limit_login_attempts() {
	$max_login_attempts = 5;
	$temp_block_time = 300; // 5 minutes for temporary block

	$transient_name = 'attempted_login_' . $_SERVER['REMOTE_ADDR'];
	$attempts = get_transient($transient_name);

	if ($attempts >= $max_login_attempts) {
		wp_die('You have been temporarily blocked due to too many login attempts. Please try again after ' . ceil($temp_block_time / 60) . ' minutes.');
	}

	if (isset($_POST['wp-submit'])) {
		if (!is_wp_error(wp_signon())) {
			delete_transient($transient_name);
        } else {
            $attempts = $attempts ? $attempts + 1 : 1;
            set_transient($transient_name, $attempts, $temp_block_time);
            // Record IP in blocklist with temporary status
            if ($attempts >= $max_login_attempts) {
                record_ip($_SERVER['REMOTE_ADDR']);
            }
        }
    }
}

// Record IP Address with Status, Timestamp, and Country
function record_ip($ip_address) {
    $blocklist = get_option('cnw_blocklist', []);

    // Fetch country information using doapi.us
    $country_info = wp_remote_get("https://www.doapi.us/ip/$ip_address");
    $country = 'Unknown';

    if (!is_wp_error($country_info) && wp_remote_retrieve_response_code($country_info) == 200) {
        $body = wp_remote_retrieve_body($country_info);
        $data = json_decode($body, true); // Decode as an associative array

        if (is_array($data) && isset($data['country'])) {
            $country = $data['country'];
        }
    }

    $blocklist[$ip_address] = [
        'timestamp' => time(), 
        'country' => $country
    ];

    update_option('cnw_blocklist', $blocklist);
}

// Update IP Block Status
function update_block_status($ip_address, $status) {
	$blocklist = get_option('cnw_blocklist', []);
	if (isset($blocklist[$ip_address])) {
		// Toggle status between 1 (unblocked) and 2 (permanently blocked)
		$blocklist[$ip_address]['status'] = $status == 1 ? 2 : 1;
		update_option('cnw_blocklist', $blocklist);
	}
}

// Dashboard Widget Function
function blocked_ips_dashboard_widget_function() {
	// Handle clearing of IP log
	if (isset($_POST['clear_ip_log'])) {
		check_admin_referer('clear_ip_log');
		clear_ip_log();
		echo "<p>IP log has been cleared.</p>";
	}

	$blocklist = get_option('cnw_blocklist', []);
	if (!empty($blocklist)) {
		echo '<ul>';
        foreach ($blocklist as $ip => $data) {
            $timestamp = date('m-d-Y @ g:i A', $data['timestamp']);
            $country = $data['country'];
            echo '<li><strong>' . esc_html($ip) . '</strong> <br>Blocked on: ' . $timestamp . ' <br>Country: ' . $country . '</li>';
        }
        echo '</ul>';
    } else {
        echo "<p>No IPs recorded.</p>";
    }

    // Add 'Clear IP Log' button
    echo '<form method="post">' . wp_nonce_field('clear_ip_log') . '<input type="submit" class="button" name="clear_ip_log" value="Clear IP Log"></form>';
}

function clear_ip_log() {
    delete_option('cnw_blocklist'); // Completely clears the blocklist
}

function clear_unblocked_ips() {
	$blocklist = get_option('cnw_blocklist', []);
	foreach ($blocklist as $ip => $data) {
		if ($data['status'] === 1) { // If IP is unblocked
			unset($blocklist[$ip]); // Remove from blocklist
		}
	}
	update_option('cnw_blocklist', $blocklist); // Update the blocklist
}
// Register the Dashboard Widget
function limitlogin_dashboard_widgets() {
	add_meta_box('blocked_ips_dashboard_widget', 'Limit Login Attempts | IP Management', 'blocked_ips_dashboard_widget_function', 'dashboard', 'side', 'low');
}