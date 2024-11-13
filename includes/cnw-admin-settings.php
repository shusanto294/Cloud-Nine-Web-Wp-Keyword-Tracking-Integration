<?php

add_action('acf/include_field_types', 'cnw_initialize_enable_admin_features');

function cnw_initialize_enable_admin_features() {
    $enable_admin_features = get_field('enable_admin_settings', 'option');

    if (!is_array($enable_admin_features)) {
        return;
    }

    // Enable Page Navigator
    if (in_array('cnw-pages', $enable_admin_features)) {
		add_action('init', 'cnw_conditionally_enable_page_navigator');
    }

    // Enable Ignore Plugin Updates Toggles
	if (in_array('cnw-ignoreupdates', $enable_admin_features)) {
		add_action('wp_ajax_save_ignore_updates_state', 'handle_ignore_updates_ajax');
		add_filter('manage_plugins_columns', 'add_ignore_updates_column');
		add_action('manage_plugins_custom_column', 'display_ignore_updates_column', 10, 2);
		add_filter('site_transient_update_plugins', 'disable_updates_for_ignored_plugins');
		add_action('admin_footer', 'add_checkbox_js');
    }
	
	// Disable Auto Updates
	if (in_array('cnw-disable-autoupdates', $enable_admin_features)) {
		add_filter( 'automatic_updater_disabled', '__return_true' );
	}

	// If both Gutenberg disable options are selected, display an admin notice
	if (in_array('cnw-disablegutenberg', $enable_admin_features) && in_array('cnw-disablegutenberg-everywhere', $enable_admin_features)) {
		add_action('admin_notices', 'cnw_gutenberg_conflict_notice');
	}

	// If both Gutenberg Everywhere and Disable Full Edit Mode are enabled, display an admin notice
	if (in_array('cnw-disable-gutenberg-fullscreen', $enable_admin_features) && in_array('cnw-disablegutenberg-everywhere', $enable_admin_features)) {
		add_action('admin_notices', 'cnw_gutenberg_fulledit_notice');
	}
	// Disable Gutenberg on all Post Types, except Blog Posts
	if (in_array('cnw-disablegutenberg', $enable_admin_features)) {
		add_filter( 'use_block_editor_for_post_type', 'disable_gutenberg_except_blog_posts', 10, 2 );
	}

	// Disable Gutenberg Everywhere
	if (in_array('cnw-disablegutenberg-everywhere', $enable_admin_features)) {
		add_filter('use_block_editor_for_post', '__return_false');
		// Disable Gutenberg for widgets.
		add_filter( 'use_widgets_block_editor', '__return_false' );
		add_action( 'wp_enqueue_scripts', 'remove_gutenberg_assets', 20 );
		// Remove CSS on the front end.
		wp_dequeue_style( 'wp-block-library' );

		// Remove Gutenberg theme.
		wp_dequeue_style( 'wp-block-library-theme' );

		// Remove inline global CSS on the front end.
		wp_dequeue_style( 'global-styles' );

		// Remove classic-themes CSS for backwards compatibility for button blocks.
		wp_dequeue_style( 'classic-theme-styles' );
	}
	
	// Disable Gutenberg Full Edit Mode
	if (in_array('cnw-disable-gutenberg-fullscreen', $enable_admin_features)) {
		add_action('enqueue_block_editor_assets', 'disable_gutenberg_fullscreen_mode');
	}

	// Add duplicate button to post table
	if (in_array('cnw-duplicate', $enable_admin_features)) {
		add_action("admin_action_rd_duplicate_post_as_draft", "rd_duplicate_post_as_draft");
		add_filter("post_row_actions", "rd_duplicate_post_link", 10, 2);
		add_filter("page_row_actions", "rd_duplicate_post_link", 10, 2);
	}
	
	// Hide ACF in Menu
	if (in_array('cnw-hideacfmenu', $enable_admin_features)) {
		add_filter('acf/settings/show_admin', '__return_false');
	}

	// Display System Stats Menu
	if (in_array('cnw-system-stats', $enable_admin_features)) {
		add_action('wp_dashboard_setup', 'add_system_info_dashboard_widget');
	}
	
	// Disable Site Health
	if (in_array('cnw-site-health', $enable_admin_features)) {
		add_action( 'wp_dashboard_setup', 'cnw_hide_site_health_widget');
		add_action( 'current_screen', 'cnw_hide_site_health_page');
		add_action( 'admin_menu', 'cnw_hide_site_health_menu' );
	}
}

add_action('admin_head', function() {
	// Check if we are on the specific ACF options page
	$current_screen = get_current_screen();
	if ($current_screen->id === 'toplevel_page_cnw-settings') {
?>
<style>
	.cnw-custom-image-container {
		margin: 20px 60px;
		width: 20%;
	}
	.cnw-custom-image-container img {
		max-width: 320px; /* Adjust the size as needed */
		height: auto;
	}
	div#postbox-container-2 {
		display: flex;
	}
	#postbox-container-2 div#normal-sortables {
		width: 80%;
	}
</style>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		// Construct the full image URL using PHP for the plugin URL
		var imageUrl = '<?php echo plugins_url('img/Cloud-Nine-Admin-IMG.png', plugin_dir_path(__FILE__)); ?>';
		
		// Append the image HTML to the specific postbox
		var imageHtml = '<div class="cnw-custom-image-container"><img src="' + imageUrl + '"></div>';
		$('div.postbox-container:contains("Cloud Nine Web Tools")').append(imageHtml);
	});
</script>

<?php
	}
});


// Page Navigation Load
function cnw_conditionally_enable_page_navigator() {
    if ( is_user_logged_in() ) {
        include(plugin_dir_path(__FILE__) . 'quick-page-navigation.php');
	}
}

// Ignore Updates Toggles

function handle_ignore_updates_ajax() {
	// Security check
	if (!current_user_can('manage_options')) {
		wp_die('Unauthorized request');
	}

	// Check for necessary data
	if (isset($_POST['plugin_file']) && isset($_POST['is_ignored'])) {
		$plugin_file = sanitize_text_field($_POST['plugin_file']);
		$is_ignored = ($_POST['is_ignored'] === '1') ? true : false;

		update_option('ignore_updates_' . $plugin_file, $is_ignored);

		wp_send_json_success('State saved successfully');
	} else {
		wp_send_json_error('Invalid request data');
	}
}

// Add the "Ignore Updates" toggle column to the Plugins page
function add_ignore_updates_column($columns) {
	$columns['ignore_updates'] = 'Ignore Updates';
	return $columns;
}

function display_ignore_updates_column($column, $plugin_file) {
	if ($column === 'ignore_updates') {
		$is_ignored = get_option('ignore_updates_' . $plugin_file);
		$toggle_id = 'ignore_updates_toggle_' . $plugin_file;

		echo '<label class="toggle-switch">';
		echo '<input type="checkbox" id="' . esc_attr($toggle_id) . '" class="toggle-checkbox" ' . checked($is_ignored, true, false) . '>';
		echo '<span class="toggle-slider"></span>';
		echo '</label>';
	}
}

// Save the "Ignore Updates" toggle state
function save_ignore_updates_state($plugin_file) {
	if (isset($_POST['action']) && $_POST['action'] === 'update-selected') {
		$is_ignored = isset($_POST['ignore_updates_toggle_' . $plugin_file]) ? true : false;
		update_option('ignore_updates_' . $plugin_file, $is_ignored);
	}
}

add_action('admin_init', function () {
	if (isset($_REQUEST['plugin'])) {
		save_ignore_updates_state($_REQUEST['plugin']);
	}
});

function disable_updates_for_ignored_plugins($transient) {
	if (!is_object($transient) || !isset($transient->response)) {
		return $transient;  // Return original transient if it's not the expected format.
	}

	foreach ($transient->response as $plugin_path => $plugin_update_data) {
		if (get_option('ignore_updates_' . $plugin_path)) {
			// Remove this plugin from the update response if it's set to be ignored.
			unset($transient->response[$plugin_path]);
		}
	}

	return $transient;
}

// JavaScript and CSS
function add_checkbox_js() {
?>
<script>
	jQuery(document).ready(function ($) {
		$('.toggle-checkbox').on('change', function () {
			var checkbox = $(this);
			var pluginFile = checkbox.attr('id').replace('ignore_updates_toggle_', '');

			// Send an AJAX request to save the checkbox state
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'save_ignore_updates_state',
					plugin_file: pluginFile,
					is_ignored: checkbox.is(':checked') ? '1' : '0'
				},
				success: function (response) {
					console.log('Checkbox state saved.');
				}
			});
		});
	});
</script>
<style>
	.toggle-checkbox {
		display: none !important;
	}

	.toggle-switch {
		position: relative;
		display: inline-block;
		width: 40px;  /* Adjusted size */
		height: 20px; /* Adjusted size */
		cursor: pointer;
	}

	.toggle-slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		transition: .4s;
		border-radius: 20px; /* Adjusted size */
	}

	.toggle-slider:before {
		position: absolute;
		content: "";
		height: 16px;  /* Adjusted size */
		width: 16px;   /* Adjusted size */
		left: 2px;     /* Adjusted size */
		bottom: 2px;   /* Adjusted size */
		background-color: white;
		transition: .4s;
		border-radius: 50%;
	}

	.toggle-checkbox:checked + .toggle-slider:before {
		transform: translateX(20px); /* Adjusted size */
	}

	.toggle-checkbox:checked + .toggle-slider {
		background-color: #F04449; /* Changed to the requested color */
	}
</style>
<?php
}

// Gutenberg Conflict Notice
function cnw_gutenberg_conflict_notice() {
	echo '<div class="notice notice-warning"><p>Both Gutenberg disable options are active. Please deactivate one of them in the options page.</p></div>';
}

// Gutenberg Full Editor Notice
function cnw_gutenberg_fulledit_notice() {
	echo '<div class="notice notice-warning"><p>Both Gutenberg Everywhere and Full Editor Mode are disabled.  This is unnecessary.</p></div>';
}

// Disable Gutenberg on all Post Types, except Blog Posts
function disable_gutenberg_except_blog_posts( $use_block_editor, $post_type ) {
    if ( $post_type !== 'post' ) {  // Gutenberg is disabled for all post types except 'post'
        return false;
    }
    return $use_block_editor;  // Returns true for 'post' post type
}

// Disable Gutenberg Everywhere
function remove_gutenberg_assets() {
	// Remove CSS on the front end.
	wp_dequeue_style( 'wp-block-library' );

	// Remove Gutenberg theme.
	wp_dequeue_style( 'wp-block-library-theme' );

	// Remove inline global CSS on the front end.
	wp_dequeue_style( 'global-styles' );

	// Remove classic-themes CSS for backwards compatibility for button blocks.
	wp_dequeue_style( 'classic-theme-styles' );
}

// Disable Fullscreen Editor for Gutenberg
function disable_gutenberg_fullscreen_mode() {
	$script = "
        window.onload = function() {
            const isFullscreenMode = wp.data.select('core/edit-post').isFeatureActive('fullscreenMode');
            if(isFullscreenMode) {
                wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');
            }
        }
    ";

	wp_add_inline_script('wp-blocks', $script);
}

// Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
function rd_duplicate_post_as_draft() {
	if (!current_user_can("edit_posts")) {
		return;
	}

	/*
         * Nonce verification
         */
	if (
		!isset($_GET["duplicate_nonce"]) ||
		!wp_verify_nonce($_GET["duplicate_nonce"], basename(__FILE__))
	) {
		return;
	}

	global $wpdb;

	if (
		!(
			isset($_GET["post"]) ||
			isset($_POST["post"]) ||
			(isset($_REQUEST["action"]) &&
			 "rd_duplicate_post_as_draft" == $_REQUEST["action"])
		)
	) {
		wp_die("No post to duplicate has been supplied!");
	}

	/*
         * get the original post id
         */
	$post_id = isset($_GET["post"])
		? absint($_GET["post"])
		: absint($_POST["post"]);
	/*
         * and all the original post data then
         */
	$post = get_post($post_id);

	/*
         * if you don't want current user to be the new post author,
         * then change next couple of lines to this: $new_post_author = $post->post_author;
         */
	$current_user = wp_get_current_user();
	$new_post_author = $current_user->ID;

	/*
         * if post data exists, create the post duplicate
         */
	if (isset($post) && $post != null) {
		/*
             * new post data array
             */
		$args = [
			"comment_status" => $post->comment_status,
			"ping_status" => $post->ping_status,
			"post_author" => $new_post_author,
			"post_content" => $post->post_content,
			"post_excerpt" => $post->post_excerpt,
			"post_name" => $post->post_name,
			"post_parent" => $post->post_parent,
			"post_password" => $post->post_password,
			"post_status" => "draft",
			"post_title" => $post->post_title,
			"post_type" => $post->post_type,
			"to_ping" => $post->to_ping,
			"menu_order" => $post->menu_order,
		];

		/*
             * insert the post by wp_insert_post() function
             */
		$new_post_id = wp_insert_post($args);

		/*
             * get all current post terms ad set them to the new post draft
             */
		$taxonomies = get_object_taxonomies($post->post_type); // returns an array of taxonomy names for the post type
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, [
				"fields" => "slugs",
			]);
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}

		/*
             * duplicate all post meta just in two SQL queries
             */
		$post_meta_infos = $wpdb->get_results(
			"SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id"
		);
		if (count($post_meta_infos) != 0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				if ($meta_key == "_wp_old_slug") {
					continue;
				}
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query .= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}

		/*
             * finally, redirect to the edit post screen for the new draft
             */
		wp_safe_redirect(
			admin_url("post.php?action=edit&post=" . $new_post_id)
		);
		exit();
	} else {
		wp_die(
			"Post creation failed, could not find the original post: " . $post_id
		);
	}
}

// Add the duplicate link to action list for post_row_actions
function rd_duplicate_post_link($actions, $post) {
	if (current_user_can("edit_posts")) {
		$actions["duplicate"] =
			'<a href="' .
			wp_nonce_url(
			"admin.php?action=rd_duplicate_post_as_draft&post=" . $post->ID,
			basename(__FILE__),
			"duplicate_nonce"
		) .
			'" title="Duplicate this item" rel="permalink">Duplicate</a>';
	}
	return $actions;
}

function add_system_info_dashboard_widget() {
	add_meta_box(
		'system_info_dashboard_widget', // Widget slug.
		'System Information', // Title.
		'system_info_dashboard_widget_display', // Display function.
		'dashboard',
		'side',
		'low'
	);
}

function system_info_dashboard_widget_display() {
	global $wpdb;

	// Get system information
	$wordpress_version = get_bloginfo('version');
	$php_version = phpversion();
	$db_version = $wpdb->db_version();
	$webserver_info = $_SERVER['SERVER_SOFTWARE'];

	// Determine if the database is MySQL or MariaDB
	$database_type = strpos($db_version, 'MariaDB') !== false ? 'MariaDB' : 'MySQL';
	$db_version_string = "<strong>{$database_type} Version:</strong> {$db_version}";

	// Get PHP memory limit
	$php_memory_limit = ini_get('memory_limit');

	// Display the information
	echo "<p><strong>WordPress Version:</strong> {$wordpress_version}</p>";
	echo "<p><strong>PHP Version:</strong> {$php_version}</p>";
	echo "<p>{$db_version_string}</p>";
	echo "<p><strong>Webserver:</strong> {$webserver_info}</p>";
	echo "<p><strong>PHP Memory Limit:</strong> {$php_memory_limit}</p>";
}

// Remove Tools Submenu Item for Site Health.
function cnw_hide_site_health_menu() {
	remove_submenu_page( 'tools.php', 'site-health.php' );
}
// Prevent direct access to the Site Health page.
function cnw_hide_site_health_page() {
	$screen = get_current_screen();
	if ( 'site-health' === $screen->id ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
// Disable the Site Health Dashboard Widget.
function cnw_hide_site_health_widget() {
	global $wp_meta_boxes;
	if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] ) ) {
		unset( $wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health'] );
	}
}