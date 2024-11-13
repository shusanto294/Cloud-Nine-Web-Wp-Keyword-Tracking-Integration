<?php
/*
 * Redirect handler for generating one-time login codes.
 * This file should be placed in the same directory as the main plugin file.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Retrieve User ID and API Key from options
$user_id = get_option('analytics_user_id', ''); // Get stored User ID
$api_key = get_option('analytics_api_key', ''); // Get stored API Key

if (empty($user_id) || empty($api_key)) {
    echo 'User ID or API Key is not set. Please configure the plugin settings.';
    exit;
}

// Generate one-time login code
$response = wp_remote_post("https://analytics.cloudnineweb.co/admin-api/users/$user_id/one-time-login-code", array(
    'headers' => array(
        'Authorization' => $api_key,
        'Content-Type' => 'application/json'
    ),
));

if (is_wp_error($response)) {
    echo 'Unable to generate one-time login code.';
    exit;
}

$data = json_decode(wp_remote_retrieve_body($response), true);
if (!empty($data['data']['url'])) {
    // Redirect to the generated URL
    wp_redirect($data['data']['url']);
    exit;
} else {
    echo 'No valid URL found in response.';
    exit;
}