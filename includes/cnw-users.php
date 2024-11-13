<?php

// Ensure ACF is active and initialized
add_action('acf/include_field_types', 'cnw_initialize_enable_user_features');

function cnw_initialize_enable_user_features() {
    $enable_user_features = get_field('enable_user_features', 'option');

    if (!is_array($enable_user_features)) {
        return;
    }

    // Remove admin bar from all users, except admins & editors
    if (in_array('cnw-removeab', $enable_user_features)) {
        add_action('acf/init', 'remove_admin_bar');
    }
	
    // Display User Role Management Page
    if (in_array('cnw-role-management', $enable_user_features)) {
        include(plugin_dir_path(__FILE__) . 'cnw-user-role-management.php');
    }

    // Add Member Role
    if (in_array('cnw-member-role', $enable_user_features)) {
        add_action('init', 'create_member_role');
    } else {
		add_action('init', 'remove_member_role');
		}

    // Hide Uneccessary User Roles
    if (in_array('cnw-hide-roles', $enable_user_features)) {
        add_action('admin_menu', 'hide_user_role_submenu_page');
		add_filter('editable_roles', 'hide_user_role_filter_editable_roles');
		add_filter('views_users', 'hide_user_role_filter_editable_roles');
		add_filter('wp_dropdown_users_args', 'hide_user_role_filter_editable_roles');
    }
	
	// Enable Emails Users by Role
	if (in_array('cnw-email-users', $enable_user_features)) {
		include(plugin_dir_path(__FILE__) . 'cnw-admin-mail.php');
	}
}

	// Remove Admin Bar
function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
		show_admin_bar(false);
	}
}

// Add Member Role
function create_member_role() {
    $subscriber = get_role('subscriber');
    if (null === get_role('member')) {
        add_role('member', 'Member', $subscriber->capabilities);
    }
}

// Remove Member Role
function remove_member_role() {
        if (null !== get_role('member')) {
            remove_role('member');
        }
}

// Hide Unnecessary User Roles
function hide_user_role_submenu_page() {
    add_submenu_page(
        'users.php',             
        'Hide User Roles',       
        'Hide User Roles',       
        'manage_options',        
        'hide-user-roles',       
        'hide_user_role_page_callback' 
    );
}

function hide_user_role_page_callback() {
		// Check if the form is submitted
		if (isset($_POST['hide_roles_submit'])) {
			// Nonce field for security
			check_admin_referer('hide_roles_action', 'hide_roles_nonce_field');

			// Process form data and update options
			$roles = wp_roles()->roles;
			unset($roles['administrator']);
			foreach ($roles as $role_key => $role_info) {
				$option_name = 'hide_role_' . $role_key;
				if (isset($_POST[$option_name])) {
					update_option($option_name, 'yes');
				} else {
					update_option($option_name, 'no');
				}
			}

			// Add an admin notice on successful save
			add_settings_error('hide_roles_messages', 'hide_roles_message', 'Settings Saved', 'updated');
		}

		// Display possible admin notices
		settings_errors('hide_roles_messages');

		// Form to toggle roles
		echo '<div class="wrap"><h1>Hide User Roles</h1>';
		echo '<form method="post">';
		wp_nonce_field('hide_roles_action', 'hide_roles_nonce_field');

		echo '<table class="form-table">';
		echo '<tbody>';

		$roles = wp_roles()->roles;
		unset($roles['administrator']);
		foreach ($roles as $role_key => $role_info) {
			$option_name = 'hide_role_' . $role_key;
			$is_checked = get_option($option_name) === 'yes' ? 'checked' : '';

			echo '<tr>';
			echo '<th scope="row">' . esc_html($role_info['name']) . '</th>';
			echo '<td>';
			echo '<label class="checkbox-wrapper">';
			echo '<input type="checkbox" name="' . esc_attr($option_name) . '" ' . $is_checked . '>';
			echo '</label>';
			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody>';
		echo '</table>';

		submit_button('Save Changes', 'primary', 'hide_roles_submit');
		echo '</form></div>'; // Close the form and the wrapper div
	}

	function hide_user_role_filter_editable_roles($roles) {
		$filtered_roles = $roles;
		foreach ($roles as $role_key => $role_info) {
			$option_name = 'hide_role_' . $role_key;
			if (get_option($option_name) === 'yes') {
				unset($filtered_roles[$role_key]);
			}
		}
		return $filtered_roles;
	}