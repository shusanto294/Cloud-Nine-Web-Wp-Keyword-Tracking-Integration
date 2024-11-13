<?php

// Ensure ACF is active and initialized
add_action('acf/include_field_types', 'cnw_initialize_admin_ui_settings');

function cnw_initialize_admin_ui_settings() {
    $admin_ui_settings_value = get_field('admin_ui_settings', 'option');

    if (is_array($admin_ui_settings_value)) {
        if (in_array('cnw-dashboard', $admin_ui_settings_value)) setup_dashboard();
        if (in_array('cnw-login', $admin_ui_settings_value)) setup_login_screen();
        if (in_array('cnw-support', $admin_ui_settings_value)) setup_support_widget();
        if (in_array('cnw-removedashwidget', $admin_ui_settings_value)) remove_dashboard_widgets();
        if (in_array('cnw-howdy', $admin_ui_settings_value)) replace_howdy_message();
        if (in_array('cnw-removewplogo', $admin_ui_settings_value)) remove_wp_logo_from_admin_bar();
        if (in_array('cnw-cleanup', $admin_ui_settings_value)) cleanup_dashboard();
		if (in_array('cnw-customlogo', $admin_ui_settings_value)) cnw_enable_custom_logo();
    }
}

// CNW Dashboard
function setup_dashboard() {
    add_action('admin_enqueue_scripts', 'cnw_dashboard_enqueue_styles');
    add_action('wp_enqueue_scripts', 'cnw_dashboard_enqueue_styles');
    add_filter('screen_layout_columns', 'cnw_dashboard_custom_columns');
    add_filter('get_user_option_screen_layout_dashboard', 'cnw_dashboard_custom_layout');
    add_action('admin_head', 'cnw_dashboard_custom_css');
    add_filter("admin_footer_text", "cnw_dashboard_custom_admin_footer_text");
    add_action('wp_head', 'cnw_dashboard_custom_css_for_logged_in_users');
    add_action('init', 'cnw_dashboard_force_light_admin_color_scheme');
    add_filter('user_contactmethods', 'cnw_dashboard_hide_admin_color_scheme_option');
}

function cnw_dashboard_enqueue_styles() {
    if (is_user_logged_in()) {
        // Enqueue stylesheets for both backend and frontend
        wp_enqueue_style('remix-icon', 'https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css');
        wp_enqueue_style('adminbar-styles', plugins_url('../css/adminbarstyles.css', __FILE__), array(), '6.5.5');
    }

    if (is_user_logged_in() && is_admin()) {
        // Enqueue stylesheets for the backend
        wp_enqueue_style('admin-styles', plugins_url('../css/adminstyles.css', __FILE__));
    }
}



function cnw_dashboard_custom_columns($columns) {
    $columns['dashboard'] = 2;
    return $columns;
}

function cnw_dashboard_custom_layout() {
    return 2;
}

function cnw_dashboard_custom_css() {
    echo '<style>
        #postbox-container-3,
        #postbox-container-4 {
            display: none;
        }
    </style>';
}

function cnw_dashboard_custom_admin_footer_text() {
    $site_title = get_bloginfo("name");
    $custom_css = '';
    $custom_text = sprintf(
        '<div class="custom-admin-footer"><p>%s <strong>%s</strong> %s <a href="%s" target="_blank">%s</a>.</p></div>',
        __("Your", "text-domain"),
        $site_title,
        __("website is supported by", "text-domain"),
        "https://cloudnineweb.co",
        __("Cloud Nine Web", "text-domain")
    );
    echo '<style type="text/css">' . $custom_css . "</style>";
    echo $custom_text;
}

function cnw_dashboard_custom_css_for_logged_in_users() {
    if (is_user_logged_in() && !is_admin()) {
        echo '<style>
            .wp-admin #wpadminbar #wp-admin-bar-site-name>.ab-item:before {
                font-family: remixicon;
                content: "\EE2B";
                font-size: 18px;
            }
            #wpadminbar #wp-admin-bar-comments .ab-icon:before {
                content: "\EB51";
                font-family: remixicon;
                font-size: 18px;
            }
            #wpadminbar #wp-admin-bar-new-content .ab-icon:before {
                content: "\EA11";
                font-family: remixicon;
                font-size: 18px;
            }
            #wpadminbar #wp-admin-bar-site-name>.ab-item:before {
                content: "\EC12";
                font-family: remixicon;
                font-size: 18px;
                top: 2px;
            }
            #wpadminbar #wp-admin-bar-customize>.ab-item:before {
                content: "\EFC3";
                font-family: remixicon;
                font-size: 18px;
            }
            #wpadminbar #wp-admin-bar-edit>.ab-item:before {
                content: "\EC86";
                font-family: remixicon;
                font-size: 18px;
                top: 2px;
            }
            #wp-admin-bar-pages-dropdown a.ab-item, .ab-icon {
                display: flex;
                justify-content: center;
            }
            .ri-file-edit-line:before {
                font-size: 18px;
            }
            i.ri-file-edit-line {
                display: flex;
                align-self: center;
                width: 24px !important;
            }
            #wpadminbar ul#wp-admin-bar-root-default>li {
                margin-right: 0;
            }
        </style>';
    }
}

function cnw_dashboard_force_light_admin_color_scheme() {
    $users = get_users();
    $light_scheme = 'light';
    foreach ($users as $user) {
        update_user_option($user->ID, 'admin_color', $light_scheme);
    }
}

function cnw_dashboard_hide_admin_color_scheme_option($profile_fields) {
    unset($profile_fields['admin_color']);
    return $profile_fields;
}

// CNW Login Screen
function setup_login_screen() {
    add_action('login_enqueue_scripts', 'cnw_login_enqueue_custom_login_stylesheet');
    add_action("init", "cnw_login_checked_remember_me");
    add_filter("login_message", "cnw_login_custom_message");
    add_filter("login_errors", "cnw_login_error_override");
    add_filter("login_headerurl", "cnw_login_logo_url");
    add_filter("login_headertext", "cnw_login_logo_url_title");
}

function cnw_login_enqueue_custom_login_stylesheet() {
    wp_enqueue_style('custom-login-style', plugin_dir_url(__FILE__) . '../css/cnw-login.css');
}

function cnw_login_checked_remember_me() {
    add_filter("login_footer", "cnw_login_remember_me_checked");
}

function cnw_login_remember_me_checked() {
    echo "<script>document.getElementById('rememberme').checked = true;</script>";
}

function cnw_login_custom_message() {
    return '<p class="message text-align:center;"><span class="login-heading">Welcome Back</span><span class="login-message">Please enter your details to sign in.</span></p>';
}

function cnw_login_error_override() {
    return "Incorrect login details.";
}

function cnw_login_logo_url() {
    return home_url();
}

function cnw_login_logo_url_title() {
    return "Cloud Nine Web";
}


// CNW Support Widget
function setup_support_widget() {
    add_action('wp_head', 'cnw_support_load_helpspace_widget');
    add_action('admin_head', 'cnw_support_load_helpspace_widget');
    add_action('login_head', 'cnw_support_load_helpspace_widget');
    add_action('wp_dashboard_setup', 'cnw_support_cloudnineweb_info_box');
}

function cnw_support_load_helpspace_widget() {
    // Check if current user can edit others' posts (capability for editors and administrators)
    if (current_user_can('edit_others_posts') || $GLOBALS['pagenow'] === 'wp-login.php') {
        $current_user = wp_get_current_user();
        $user_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
        $user_email = $current_user->user_email;

        echo '<script>
            window.addEventListener("load", function() {
                HelpWidget("user", {
                    name: "' . esc_js($user_name) . '",
                    email: "' . esc_js($user_email) . '",
                    show: true
                });
            });
        </script>';
        
        echo '<script
            id="helpspace-widget-script"
            data-auto-init
            data-token="TWF9Pj35ipzlRpKL0eoxruFwEgtUFoCWCS6Q8cWB"
            data-client-id="4fac86b05484427e9084bc102b87cfc3"
            data-widget-id="a463ae74-e454-4905-b2f7-3f719b594908"
            src="https://cdn.helpspace.com/widget/widget-v1.js" 
            async
        ></script>';
    }
}

function cnw_support_cloudnineweb_info_box() {
    add_meta_box('dev_info_box_widget', 'Cloud Nine Web Support', 'cnw_support_dev_info_box', 'dashboard', 'side', 'high');
}

function cnw_support_dev_info_box() {
    echo '
    <style>
        /* Define the common button styles */
        .custom-button {
            width: 200px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            color: #fff;
            font-family: \'Plus Jarka Sans\';
            text-decoration: none;
            display: flex;
            transition: box-shadow 0.3s ease-in-out, opacity 0.3s ease-in-out;
            background-color: #ed1b4e;
            align-items: center;
            justify-content: center;
            height: 24px;
        }

        /* Add the fade-in-out box shadow and opacity on hover */
        .custom-button:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            opacity: 0.85;
            color: #fff;
        }

        /* Add padding to the entire container */
        .container {
            padding: 15px;
            display: flex;
            flex-direction: row;
            column-gap: 30px;
        }

        /* Flex container for left side with row gap */
        .left-side {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 50%;
        }

        /* Style the UL element */
        ul {
            list-style: none;
            padding: 0;
        }

        /* Add column gap between left and right sides */
        .right-side {
            flex: 1;
            text-align: center;
            max-width: 50%;
        }

        /* Add row gap between containers */
        .container:not(:last-child) {
            margin-bottom: 20px;
        }
        .btn-container {
            display: flex;
            flex-direction: column;
            row-gap: 10px;
        }
        .status-container {
            display: flex;
            flex-direction: column;
            margin-top: auto;
        }
    </style>

    <div class="container">
        <!-- Logo container with SVG -->
        <div class="left-side">
            <div class="logo-container">
                <img src="https://gocloudnine.b-cdn.net/images/Cloud-Nine-Final-Logo-01.svg" width="256px">
            </div>
            
            <!-- List container with Website, Email, Phone -->
            <div class="list-container">
                <ul>
                    <li><strong>Website:</strong> <a target="_blank" href="https://cloudnineweb.co">cloudnineweb.co</a></li>
                    <li><strong>Email:</strong> <a target="_blank" href="mailto:support@cloudnineweb.co">support@cloudnineweb.co</a></li>
                    <li><strong>Phone:</strong> <a target="_blank" href="tel:+18155854800">(815) 585-4800</a></li>
                </ul>
            </div>
            
            <!-- Buttons container -->
            <div class="btn-container">
                <a href="javascript:HelpWidget(\'open\', { contact: true });" class="custom-button">Open Support Ticket</a>
                <a href="javascript:HelpWidget(\'open\', { docs: true } );" class="custom-button">Knowledge Base</a>
            </div>
            <div class="status-container">
                <h3 class="status-title"><strong>Cloud Nine Web</strong> | Network Status</h3>
                <iframe src="https://status.gocloudnine.net/badge?theme=light" width="250" height="30" frameborder="0" scrolling="no"></iframe>
            </div>
        </div>
        
        <!-- Right side with the image -->
        <div class="right-side">
            <img src="https://gocloudnine.b-cdn.net/images/Support-Side-IMG.jpg" alt="Support Image" style="max-width: 100%; height: auto;">
        </div>
    </div>';
}


// Remove WordPress Dashboard Widgets
function remove_dashboard_widgets() {
    add_action('wp_dashboard_setup', 'cnw_remove_custom_dashboard_widgets');
    add_action('wp_dashboard_setup', 'cnw_remove_remove_dashboard_widgets');
}

function cnw_remove_custom_dashboard_widgets() {
    global $wp_meta_boxes;
    
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

function cnw_remove_remove_dashboard_widgets() {
    // Remove Site Health Status
    remove_meta_box('dashboard_site_health', 'dashboard', 'normal');

    // Remove At a Glance
    remove_meta_box('dashboard_right_now', 'dashboard', 'normal');

    // Remove Activity
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    
    // Remove Quick Draft
    remove_meta_box('dashboard_quick_press', 'dashboard', 'side');

    // Remove WordPress Events and News
    remove_meta_box('dashboard_primary', 'dashboard', 'side');

    // Remove Welcome Panel
    remove_action('welcome_panel', 'wp_welcome_panel');
}


// Rename WordPress Howdy
function replace_howdy_message() {
    add_filter("admin_bar_menu", "cnw_howdy_replace_howdy", 9999);
}

function cnw_howdy_replace_howdy($wp_admin_bar) {
	
	// Edit the line below to set what you want the admin bar to display intead of "Howdy,".
	$new_howdy = '👋 Hi there,';

	$my_account = $wp_admin_bar->get_node( 'my-account' );
	if ( ! isset( $my_account->title ) ) {
		return;
	}
	$wp_admin_bar->add_node(
		array(
			'id'    => 'my-account',
			'title' => str_replace( 'Howdy,', $new_howdy, $my_account->title ),
		)
	);
}


// Remove WordPress logo/link from Admin Bar
function remove_wp_logo_from_admin_bar() {
    add_action('wp_before_admin_bar_render', 'cnw_logo_admin_bar_remove_logo', 0);
}

function cnw_logo_admin_bar_remove_logo() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}


// CNW Dashboard Clean-Up
function cleanup_dashboard() {
    add_action('admin_enqueue_scripts', 'cnw_cleanup_enqueue_cleanup_stylesheet');
}

function cnw_cleanup_enqueue_cleanup_stylesheet() {
    wp_enqueue_style('cleanup-style', plugin_dir_url(__FILE__) . '../css/cleanup.css');
}

// CNW Custom Logo

function cnw_enable_custom_logo() {
	add_action('admin_head', 'cnw_custom_logo');
}

function cnw_custom_logo() {
	// Get the custom logo URL from ACF field
	$custom_logo_url = get_field('custom_logo', 'option');

	// Check if the custom logo URL is available
	if ($custom_logo_url) {
		// Output the CSS with the custom logo URL
		echo '<style>
            ul#adminmenu::before {
                content: url(' . esc_url($custom_logo_url) . ') !important;
            }
        </style>';
	}
}
