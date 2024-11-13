<?php

add_action('acf/include_field_types', 'cnw_initialize_enable_notification_features');

function cnw_initialize_enable_notification_features() {
	$enable_notification_features = get_field('enable_notification_features', 'option');

	if (!is_array($enable_notification_features)) {
		return;
	}
	
	// Disable Admin Email Confirm
	if (in_array('cnw-admin-email-check', $enable_notification_features)) {
		add_filter( 'admin_email_check_interval', '__return_false' );
	}

	// Disable Update Emails
	if (in_array('cnw-update-emails', $enable_notification_features)) {
		add_filter( 'send_core_update_notification_email', '__return_false' );
		add_filter( 'auto_plugin_update_send_email', '__return_false' );
		add_filter( 'auto_theme_update_send_email', '__return_false' );
	}

	// Disable New User Emails
	if (in_array('cnw-newuser-emails', $enable_notification_features)) {
		add_filter( 'wp_send_new_user_notification_to_admin', '__return_false' );
	}

	// Disable Password Reset Emails
	if (in_array('cnw-passwordreset-emails', $enable_notification_features)) {
		remove_action( 'after_password_reset', 'wp_password_change_notification' );
	}
}