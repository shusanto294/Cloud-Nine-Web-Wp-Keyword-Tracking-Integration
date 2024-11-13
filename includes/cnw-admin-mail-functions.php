<?php

// Define a custom cron schedule that runs every minute
add_filter('cron_schedules', function($schedules) {
    if (!isset($schedules['every_minute'])) {
        $schedules['every_minute'] = array(
            'interval' => 60, // Interval in seconds
            'display'  => __('Every Minute')
        );
    }
    return $schedules;
});

// Check if 'cnw-email-users' is enabled in 'enable_user_features'
function cnw_is_email_users_enabled() {
	$user_settings = get_field('enable_user_features', 'option');
    return is_array($user_settings) && in_array('cnw-email-users', $user_settings);
}

// Get all WordPress user roles
function cnw_get_user_roles() {
    $roles = wp_roles()->get_names();
    return array_combine(array_keys($roles), array_values($roles));
}

// Queue emails for sending and return the count of emails queued
function cnw_queue_emails($subject, $roles, $content) {
    if (!cnw_is_email_users_enabled()) {
        return 0;
    }

    $email_count = 0;
    foreach ($roles as $role) {
        $users = get_users(['role' => $role]);
        $email_count += count($users);
        foreach ($users as $user) {
            $queue = get_option('cnw_email_queue', []);
            $queue[] = [
                'email' => $user->user_email,
                'subject' => $subject,
                'content' => $content
            ];
            update_option('cnw_email_queue', $queue);
        }
    }

    if (!wp_next_scheduled('cnw_process_email_queue')) {
        wp_schedule_event(time(), 'every_minute', 'cnw_process_email_queue');
    }

    return $email_count;
}

// Process the email queue
add_action('cnw_process_email_queue', 'cnw_process_email_queue');
function cnw_process_email_queue() {
	if (!cnw_is_email_users_enabled()) {
		wp_clear_scheduled_hook('cnw_process_email_queue');
		return;
	}

	$queue = get_option('cnw_email_queue', []);

	for ($i = 0; $i < 10 && !empty($queue); $i++) {
		$email_info = array_shift($queue);
		$headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($email_info['email'], $email_info['subject'], $email_info['content'], $headers);
	}

	update_option('cnw_email_queue', $queue);

	if (empty($queue)) {
		wp_clear_scheduled_hook('cnw_process_email_queue');
	}
}

// Handle email form submission
add_action('admin_post_cnw_send_email', 'cnw_handle_email_form_submission');
function cnw_handle_email_form_submission() {
    if (!cnw_is_email_users_enabled() || !current_user_can('administrator')) {
        return;
    }

    $subject = sanitize_text_field($_POST['email_subject']);
    $roles = $_POST['user_roles']; // Ensure this is properly validated and sanitized
    $content = wp_kses_post($_POST['email_content']);
    $content = stripslashes($content); // Remove escaped backslashes

    $email_count = cnw_queue_emails($subject, $roles, $content);

    set_transient('cnw_email_sent_count', $email_count, 60); // Store the count

    wp_redirect(add_query_arg(['cnw_email_sent' => '1'], admin_url('admin.php?page=cnw-email-users-settings')));
    exit;
}

// Add admin notice for successful email queue
add_action('admin_notices', function() {
    if (isset($_GET['cnw_email_sent'])) {
        $email_count = get_transient('cnw_email_sent_count');
        delete_transient('cnw_email_sent_count');

        echo '<div class="notice notice-success is-dismissible">
        <p><strong>Mail Sent!</strong></p>
        <p>Emails queued: ' . esc_html($email_count) . '</p>
      </div>';

	}
});
