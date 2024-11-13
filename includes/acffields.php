<?php

add_action('acf/init', function() {
    // Main Options Page
    acf_add_options_page(array(
        'page_title' => 'Cloud Nine Web',
        'menu_slug' => 'cnw-settings',
        'icon_url' => 'https://gocloudnine.b-cdn.net/images/Menu-Icon.svg',
        'position' => '',
        'redirect' => false,
    ));

	// Check 'enable_user_features' to see if 'cnw-email-users' is checked
	$user_settings = get_field('enable_user_features', 'option');
    if (is_array($user_settings) && in_array('cnw-email-users', $user_settings)) {
        // Add Sub-page under 'cnw-settings'
        acf_add_options_page(array(
            'page_title' => 'Email Users',
            'menu_slug' => 'cnw-email-users-settings',
			'icon_url' => 'https://gocloudnine.b-cdn.net/images/CNW-Mail.svg',
			'position' => '',
			'redirect' => false,
        ));


    }
});

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$today = date('mdY') . 'cnw'; // Generates today's date in mdY format and appends 'cnw'
	acf_add_local_field_group( array(
	'key' => 'group_64e91ec47fa1c',
	'title' => 'Cloud Nine Web Tools',
	'fields' => array(
		array(
			'key' => 'field_652a16e21ddbf',
			'label' => 'General',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_652740991f360',
			'label' => 'Enable Admin Settings',
			'name' => 'enable_admin_settings',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),
			'choices' => array(
				'cnw-pages' => 'Add Pages Link to Admin Bar',
				'cnw-ignoreupdates' => 'Enable Ignore Plugin Updates Toggles',
				'cnw-disable-autoupdates' => 'Disable Auto Updates',
				'cnw-disablegutenberg' => 'Disable Gutenberg on all post types, except blog posts',
				'cnw-disablegutenberg-everywhere' => 'Disable Gutenberg Everywhere',
				'cnw-disable-gutenberg-fullscreen' => 'Disable Gutenberg Fullscreen Editor',
				'cnw-duplicate' => 'Add duplicate button to post table',
				'cnw-system-stats' => 'Display System Stats Widget',
				'cnw-site-health' => 'Disable WP Site Health Widget'
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),

		array(
			'key' => 'field_652a172f1ddc0',
			'label' => 'Security',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_6527420c1f361',
			'label' => 'Security Settings',
			'name' => 'security_settings',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),
			'choices' => array(
				'cnw-hidewp' => 'Hide WordPress Version',
				'cnw-comments' => 'Disable Comments',
				'cnw-xmlrpc' => 'Disable XML-RPC',
				'cnw-tpeditors' => 'Disable Theme and Plugin file editors',
				'cnw-blacklist' => 'Auto Update WordPress Comment Blacklist',
				'cnw-userenumeration' => 'Block User Enumeration',
				'cnw-limit-login' => 'Limit Login Attempts'
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
		array(
			'key' => 'field_652a173b1ddc1',
			'label' => 'Admin UI',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_6527f4462d508',
			'label' => 'Admin UI Settings',
			'name' => 'admin_ui_settings',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),
			'choices' => array(
				'cnw-dashboard' => 'CNW Dashboard',
				'cnw-login' => 'CNW Login Screen',
				'cnw-support' => 'Enable CNW Support Widget',
				'cnw-removedashwidget' => 'Remove WordPress Dashboard Widgets',
				'cnw-howdy' => 'Rename WordPress Howdy',
				'cnw-removewplogo' => 'Remove WordPress logo/link from Admin Bar',
				'cnw-cleanup' => 'CNW Dashboard Clean-Up',
				'cnw-customlogo' => 'Customize the Admin Menu logo'
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
		array(
			'key' => 'field_customlogo',
			'label' => 'Custom Logo',
			'name' => 'custom_logo',
			'type' => 'url',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_6527f4462d508',
						'operator' => '==',
						'value' => 'cnw-customlogo',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
		),
		array(
			'key' => 'field_652a17531ddc2',
			'label' => 'Performance',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_652967c84562d',
			'label' => 'Enable Performance Features',
			'name' => 'enable_performance_features',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),

'choices' => array(
	'cnw-quickpurge' => 'Enable LiteSpeed Quick Purge Link <span class="cnw-notes">?clear=' . $today . '</span>',
	'cnw-disableauthorarchives' => 'Disable Author Archives',
	'cnw-disablecompression' => 'Disable Image Compression in WP',
),

			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),

		// SEO tab
		array(
			'key' => 'field_01b1caf90',
			'label' => 'SEO',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
array(
    'key' => 'field_00e2fcb92',
    'label' => 'SEO Settings',
    'name' => 'enable_seo_features',
    'type' => 'checkbox',
	'show_in_rest' => false,
    'instructions' => '',
    'required' => 0,
    'conditional_logic' => 0,
    'wrapper' => array(
        'width' => '',
        'class' => 'checkbox-wrapper',
        'id' => '',
    ),
    'choices' => array(
        'cnw-disable-attachments' => 'Disable Media Attachment Pages',
        'cnw-blur-alt-images' => 'Blur Images Without an Alt Tag',
        'cnw-plausible' => 'Enable CNW Plausible Analytics',
		'cnw-keyword-tracking' => 'Enable CNW Keyword Tracking'
    ),
    'default_value' => array(),
    'return_format' => 'value',
    'allow_custom' => 0,
    'layout' => 'vertical',
    'toggle' => 0,
    'save_custom' => 0,
    'custom_choice_button_text' => 'Add new choice',
),
array(
    'key' => 'field_65246fea3f535',
    'label' => 'Plausible URL',
    'name' => 'plausible_url',
    'type' => 'url',
    'instructions' => '',
    'required' => 0,
    'conditional_logic' => array(
        array(
            array(
                'field' => 'field_00e2fcb92',
                'operator' => '==',
                'value' => 'cnw-plausible',
            ),
        ),
    ),
    'wrapper' => array(
        'width' => '',
        'class' => '',
        'id' => '',
    ),
    'default_value' => '',
    'placeholder' => '',
),

		// Users tab
		array(
			'key' => 'field_587392748a3b9',
			'label' => 'Users',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_763428194b7d2',
			'label' => 'User Settings',
			'name' => 'enable_user_features',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),
			'choices' => array(
				'cnw-removeab' => 'Remove Admin Bar from all users, except Admins',
				'cnw-email-users' => 'Enable Email Users by Role',
				'cnw-role-management' => 'Hide Menu Items by Role',
				'cnw-member-role' => 'Add Member User Role',
				'cnw-hide-roles' => 'Hide Unnecessary User Roles <span class="cnw-notes">Adds a page under Users</span>'
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
		
		// Forms tab
		array(
			'key' => 'field_cnw_form_tab',
			'label' => 'Forms',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_cnw_form_settings',
			'label' => 'Form Settings',
			'name' => 'enable_form_features',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),
			'choices' => array(
				'cnw-gf-blacklist' => 'Gravity Forms Validate Against Comment Blacklist',
				'cnw-force-gf-honeypot' => 'Force Gravity Forms Honeypot Enabled',
				'cnw-cache-buster' => 'Enable GravityWiz Cache Buster for Gravity Forms'
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
/*		array(
			'key' => 'field_gf_cache_buster', // Change this key to a unique value
			'label' => 'Gravity Form IDs to exclude from Cache Buster',
			'name' => 'gravity_form_ids_exclude',
			'type' => 'text',
			'instructions' => 'Enter the form IDs separated by commas (e.g., 1, 2, 3).  Leave blank for no exclusions.',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_cnw_form_settings',
						'operator' => '==',
						'value' => 'cnw-cache-buster',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
		), 
*/
		// Notifications tab
		array(
			'key' => 'field_cnw_notifications_tab',
			'label' => 'Notifications',
			'name' => '',
			'aria-label' => '',
			'type' => 'tab',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placement' => 'top',
			'endpoint' => 0,
		),
		array(
			'key' => 'field_cnw_notifications_settings',
			'label' => 'Notification Settings',
			'name' => 'enable_notification_features',
			'aria-label' => '',
			'type' => 'checkbox',
			'show_in_rest' => false,
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'checkbox-wrapper',
				'id' => '',
			),
			'choices' => array(
				'cnw-admin-email-check' => 'Disable Admin Email Confirmations',
				'cnw-update-emails' => 'Disable Update Emails',
				'cnw-newuser-emails' => 'Disable New User Emails',
				'cnw-passwordreset-emails' => 'Disable Password Reset Emails'
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
		// Email tab
		array(
			'key' => 'field_cnw_email_tab',
			'label' => 'Email',
			'name' => '',
			'type' => 'tab',
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_763428194b7d2',
						'operator' => '==',
						'value' => 'cnw-email-users',
					),
				),
			),
		),

		// Email Signature field under Email tab
		array(
			'key' => 'field_cnw_email_signature',
			'label' => 'Email Signature',
			'name' => 'cnw_email_signature',
			'type' => 'wysiwyg',
			'show_in_rest' => false,
			'instructions' => 'Create an email signature.',
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_763428194b7d2',
						'operator' => '==',
						'value' => 'cnw-email-users',
					),
				),
			),
		),

	),
		'location' => array(
			array(
				array(
					'param' => 'options_page',
					'operator' => '==',
					'value' => 'cnw-settings',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'top',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => true,
		'description' => '',
		'show_in_rest' => 0,
	));


});