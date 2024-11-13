<?php

// Ensure ACF is active and initialized
add_action('acf/include_field_types', 'cnw_initialize_enable_performance_features');

function cnw_initialize_enable_performance_features() {
    $enable_performance_features = get_field('enable_performance_features', 'option');

    if (!is_array($enable_performance_features)) {
        return;
    }

    // LiteSpeed Quick Purge URL
    if (in_array('cnw-quickpurge', $enable_performance_features)) {
        add_action('init', 'cnw_clear_litespeed_cache_on_demand');
    }

    // Disable Author Archives
	if (in_array('cnw-disableauthorarchives', $enable_performance_features)) {
        add_action('template_redirect', 'cnw_disable_author_archives');
		add_action('the_author_posts_link', 'cnw_disable_author_posts_link');
    }

    // Disable Image Compression
    if (in_array('cnw-disablecompression', $enable_performance_features)) {
        add_filter('jpeg_quality', 'cnw_set_jpeg_quality');
        add_filter('wp_editor_set_quality', 'cnw_set_jpeg_quality');
        add_filter('wp_generate_attachment_metadata', 'cnw_set_metadata_quality');
    }

}

// Functions for each feature below:

function cnw_clear_litespeed_cache_on_demand() {
    $clear_value = sanitize_text_field($_GET['clear'] ?? '');
    if ($clear_value === date('mdY') . 'cnw') {
        if (function_exists('do_action')) {
            do_action('litespeed_purge_all');
            echo 'Cache cleared successfully.';
            exit;
        } else {
            echo 'LiteSpeed Cache is not installed or active.';
            exit;
        }
    }
}


// Disable Author Archives
function cnw_disable_author_archives() { 
	if ( is_author() || isset( $_GET['author'] ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

function cnw_disable_author_posts_link ( $link ) {
	if ( ! is_admin() ) {
		return get_the_author();
	}
	return $link;
}

function cnw_set_jpeg_quality() {
	return 100;
}

function cnw_set_metadata_quality($metadata) {
	if (isset($metadata['file'])) {
		$type = wp_check_filetype($metadata['file']);
        if ($type['type'] === 'image/jpeg') {
            $metadata['quality'] = 100;
        }
    }
    return $metadata;
}



