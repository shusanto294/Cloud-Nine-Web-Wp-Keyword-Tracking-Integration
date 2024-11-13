<?php

// Ensure ACF is active and initialized
add_action('acf/include_field_types', 'cnw_initialize_enable_form_features');

function cnw_initialize_enable_form_features() {
	$enable_form_features = get_field('enable_form_features', 'option');

	if (!is_array($enable_form_features)) {
		return;
	}

	// Gravity Forms Validate Against Comment Blacklist
	if (in_array('cnw-gf-blacklist', $enable_form_features)) {
		add_action('gform_validation', 'validate_against_disallowed_keywords');
	}

	// Force Gravity Forms Honeypot enabled
	if (in_array('cnw-force-gf-honeypot', $enable_form_features)) {
		add_filter('gform_form_post_get_meta', 'enable_gf_honeypot');
	}

	// Enable Cache Buster
	if (in_array('cnw-cache-buster', $enable_form_features)) {
		include( plugin_dir_path( __FILE__ ) . 'gw-cache-buster.php' );
		add_filter('acf/validate_value/name=gravity_form_ids_exclude', 'validate_gravity_form_ids', 10, 4);
	}
}
// Check Gravity Forms submissions against Disallowed Comment Keys
function validate_against_disallowed_keywords($validation_result) {
	$form = $validation_result['form'];

	// Get disallowed keywords from WordPress
	$disallowed_keys = explode("\n", get_option('disallowed_keys'));

	// Check each field in the form
	foreach ($form['fields'] as &$field) {
		// Assuming you're checking text-based fields like text, textarea
		if (isset($field['type']) && in_array($field['type'], ['text', 'textarea'])) {
			$field_value = rgpost("input_{$field['id']}");

			// Compare against each disallowed keyword
			foreach ($disallowed_keys as $word) {
				if (stripos($field_value, trim($word)) !== false) {
					// Fail the validation for this field
					$field->failed_validation = true;
					$field->validation_message = 'Sorry, but your submission appears to be spam. Your IP address has been logged.';

					// Update the form validation result
					$validation_result['is_valid'] = false;
					// Optionally, log the IP address here

					// Track failed attempts
					$user_attempts = get_user_meta(get_current_user_id(), 'spam_attempts', true);
					$user_attempts = empty($user_attempts) ? 1 : ($user_attempts + 1);
					update_user_meta(get_current_user_id(), 'spam_attempts', $user_attempts);

					// If more than 2 attempts, submit to spam entries
					if ($user_attempts > 2) {
						add_filter('gform_entry_is_spam', '__return_true');
					}

					break;
				}
			}
		}

		if (!$validation_result['is_valid']) {
			break;
		}
	}

	// Assign modified $form object back to the validation result
	$validation_result['form'] = $form;
	return $validation_result;
}

// Force Gravity Forms Honeypot on all Forms
function enable_gf_honeypot($form) {
	$form['enableHoneypot'] = true;
	return $form;
}

// Validate Cache Buster Fields
function validate_gravity_form_ids($valid, $value, $field, $input) {
	// Check if the field value is set and not empty
	if ($value && !preg_match('/^[\d,]+$/', $value)) {
		$valid = 'Please enter a valid list of IDs (only numbers and commas allowed)';
	}

	return $valid;
}
