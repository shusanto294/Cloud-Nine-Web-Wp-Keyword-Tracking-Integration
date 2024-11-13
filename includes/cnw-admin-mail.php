<?php

// cnw-admin-mail.php

// Include your existing functions from cnw-admin-mail-functions.php
include_once(plugin_dir_path(__FILE__) . 'cnw-admin-mail-functions.php');

// Add the TinyMCE editor form at the end of the file
function cnw_custom_email_form() {
    global $pagenow;

    if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'cnw-email-users-settings') {
        if (!current_user_can('administrator') || !function_exists('get_field')) {
            return;
        }

        // Get the email signature
        $email_signature = get_field('cnw_email_signature', 'option');
        $default_content = '<br>--<br>' . $email_signature;

        // Start output buffering
		ob_start();
		wp_editor($default_content, 'email_content', ['textarea_name' => 'email_content', 'textarea_rows' => 10, 'wpautop' => false]);
		$editor_html = ob_get_clean();

		echo '<div class="postbox cnw-email-send">
				<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Compose Email</div>
				<div class="inside acf-fields">
				<form action="' . admin_url('admin-post.php') . '" method="post">
                <input type="hidden" name="action" value="cnw_send_email">
                <label for="email_subject">Email Subject:</label><br>
                <input type="text" id="email_subject" name="email_subject"><br><br>'
			. $editor_html .
			'<br><label for="user_roles">User Roles:</label>
                <fieldset id="user_roles" class="cnw-user-roles-checkboxes"></fieldset><br><br>
                <input type="submit" value="Send Email" class="button button-primary button-large">
              </form>
			  </div>
			  </div>';
    }
}


// Enqueue Scripts and Styles
add_action('admin_enqueue_scripts', function($hook) {
    if ('toplevel_page_cnw-email-users-settings' !== $hook) {
        return;
    }

    wp_enqueue_script(
        'cnw-email-form-script',
        plugin_dir_url(__FILE__) . '../js/cnw-email-form.js',
        array('jquery'),
        '1.0.0',
        true
    );

	// Get user roles and their counts
	$user_roles_with_counts = [];
	$roles = cnw_get_user_roles();
foreach ($roles as $role_key => $role_name) {
	$users = get_users(['role' => $role_key]);
	$user_count = count($users);

	if ($user_count > 0) {
		$user_roles_with_counts[$role_key] = $role_name . '' . count($users) . '';
	}
}
	// Localize the script with new data
	$cnwAdminData = array(
		'adminUrl' => admin_url('admin-post.php'),
		'userRoles' => json_encode($user_roles_with_counts), // Replace with your actual user roles data
	);
	wp_localize_script('cnw-email-form-script', 'cnwAdminData', $cnwAdminData);

	wp_enqueue_style(
		'cnw-admin-email-style',
		plugin_dir_url(__FILE__) . '../css/admin-email-style.css',
		array(),
		'1.0.0'
	);
});


// Hook the function to the admin_footer action
add_action('admin_footer', 'cnw_custom_email_form');



