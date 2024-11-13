<?php

	// Helper function to check if ACF is active
	function is_acf_active() {
	return function_exists('get_field');
}

add_action('acf/init', 'cnw_initialize_enable_seo_features');

function cnw_initialize_enable_seo_features() {
	if (!is_acf_active()) {
		return;
	}

	$enable_seo_features = get_field('enable_seo_features', 'option');

	if (!is_array($enable_seo_features)) {
		return;
	}

	if (in_array('cnw-disable-attachments', $enable_seo_features)) {
		add_action('add_attachment', 'change_attachment_slug');
		add_action('template_redirect', 'attachment_page_404');
	}

	if (in_array('cnw-blur-alt-images', $enable_seo_features)) {
		add_action('wp_enqueue_scripts', 'blur_images_without_alt_for_admins_editors');
	}
	// Enable CNW Plausible Analytics
	if (in_array('cnw-plausible', $enable_seo_features)) {
		add_action('wp_head', 'enqueue_custom_analytics_script');
		add_action('wp_dashboard_setup', 'plausible_dashboard_widgets');
	}

}

// Enables SEO Disabled badge on Admin Bar (auto enabled)
add_action('admin_bar_menu', 'check_search_engine_visibility_warning', 999);

// Checks if SEO is Disabled
function check_search_engine_visibility_warning($wp_admin_bar) {
	if (get_option('blog_public') == '0') {
		$args = array(
			'id'    => 'search_engine_visibility_warning',
			'title' => 'SEO Disabled',
			'href'  => admin_url('options-reading.php'),
			'meta'  => array('class' => 'admin-bar-warning')
		);
		$wp_admin_bar->add_node($args);
	}
}

add_action('admin_head', function() {
	if (get_option('blog_public') == '0') {
		echo '<style>.admin-bar-warning { background-color: #f04449 !important; color: #fff !important; }</style>';
	}
});

// Disables Media Attachment Pages
function change_attachment_slug($post_ID) {
	if (get_post_type($post_ID) !== 'attachment') {
		return;
	}

	$unique_id = uniqid('attachment-');
	wp_update_post(array(
		'ID' => $post_ID,
		'post_name' => $unique_id
	));
}

function attachment_page_404() {
	if (is_attachment()) {
		global $wp_query;
		$wp_query->set_404();
		status_header(404);
		get_template_part('404');
		exit();
	}
}

// Enables Blur Images if no Alt Tag
function blur_images_without_alt_for_admins_editors() {
	if (!is_user_logged_in() || isset($_GET['bricks'])) {
		return;
	}

	$user = wp_get_current_user();
	$allowed_roles = ['administrator', 'editor'];

	if (array_intersect($allowed_roles, (array) $user->roles)) {
		wp_enqueue_style('blur-images', plugin_dir_url(__FILE__) . '../css/blur-images.css');
	}
}

function enqueue_custom_analytics_script() {
	if (is_admin() || is_user_logged_in()) {
		return;
	}

	// Get the site URL and parse it for the host component
	$domain = parse_url(get_bloginfo('url'), PHP_URL_HOST);

	// If the domain starts with 'www.', strip it
	if (substr($domain, 0, 4) === 'www.') {
		$domain = substr($domain, 4);
	}

	// Echo the script with the parsed domain
	echo '<script defer data-domain="' . esc_attr($domain) . '" src="https://analytics.cloudnineweb.app/js/script.js"></script>';
}


function plausible_dashboard_widgets() {
    global $wp_meta_boxes;

    add_meta_box(
        'cnw_plausible_dashboard_widget',              // Widget ID
        'Cloud Nine Web Analytics',                    // Title
		'display_plausible_dashboard_widget_content',  // Callback function
		'dashboard',                                   // Screen (dashboard)
		'normal',                                      // Context (normal for main column)
		'high'                                         // Priority
	);
}


function display_plausible_dashboard_widget_content() {
	$plausible_url = get_field('plausible_url', 'option');

	if ($plausible_url) {
		echo '<iframe plausible-embed src="' . esc_url($plausible_url) . '&embed=true&theme=light" scrolling="no" frameborder="0" loading="lazy" style="width: 1px; min-width: 100%; height: 1600px;"></iframe>
            <div style="font-size: 14px; padding-bottom: 14px;"></div>
            <script async src="https://analytics.cloudnineweb.app/js/embed.host.js"></script>';
	} else {
		echo 'No URL provided.';
	}
}