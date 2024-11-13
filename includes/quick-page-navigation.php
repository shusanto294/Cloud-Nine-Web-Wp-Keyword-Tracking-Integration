<?php
/*
Plugin Name: Quick Page Navigation
Description: Quick Access to any page from admin bar at frontend / backend.
Author: sandesh055
Version: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


//if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//if( !class_exists( "sqpn_Quick_Page_Navigation" ) ) {
	class sqpn_Quick_Page_Navigation {

		/*
		* Constructor function that initializes required actions and hooks
		* @Since 1.0
		*/
		function __construct() {

			// Activation & Deactivation
//			register_deactivation_hook( __FILE__, array( $this, 'sqpn_plugin_deactivate' ) );
//			register_activation_hook( __FILE__, array( $this, 'sqpn_plugin_activate' ) );

			// Check User and Add Menu
//			add_action( 'init', array( $this, 'sqpn_check_user' ) );

			// Equeue Beaver Builder CSS & JS if Exist
			add_action('wp_enqueue_scripts', array( $this, 'sqpn_bb_css_js' ) );

			// Admin Bar Menu
			add_action( 'admin_bar_menu', array( $this, 'sqpn_add_admin_bar_wp_menu' ), 555 );
			add_action( 'admin_bar_menu', array( $this, 'sqpn_add_admin_bar_bb_menu' ), 556 );
			add_action( 'admin_bar_menu', array( $this, 'sqpn_add_admin_bar_bricks_menu' ), 557 );
			add_action('admin_enqueue_scripts', array( $this, 'sqpn_common_scripts' ), 555 );
			add_action('wp_enqueue_scripts', array( $this, 'sqpn_common_scripts' ), 555 );
		}



		function sqpn_bb_css_js() {
			if ( class_exists( 'FLBuilder' ) && FLBuilderModel::is_builder_active() ) {
				wp_enqueue_style('sqpn-bb', plugins_url('../css/sqpn-bb.css', __FILE__), array() );
				wp_enqueue_script('sqpn-bb', plugins_url('../js/sqpn-bb.js', __FILE__), array('jquery'), '', true);
			}
		}

		function sqpn_common_scripts() {
			wp_enqueue_style('sqpn-common', plugins_url('../css/sqpn-common.css', __FILE__), array() );
			wp_enqueue_script('sqpn-common', plugins_url('../js/sqpn-common.js', __FILE__), array('jquery'), '', true);
		}
		
		private function is_bricks_active() {
			$current_theme = wp_get_theme();
			return 'bricks' === $current_theme->get('TextDomain') || ($current_theme->parent() && 'bricks' === $current_theme->parent()->get('TextDomain'));
		}
				
    function sqpn_add_admin_bar_wp_menu( $wp_admin_bar ) {
        if ($this->is_bricks_active()) {
            return; // Exit if Bricks is active
		}
		$wp_admin_bar->add_node( 
			array(
				'id' => 'sqpn_wp_pages', // an unique id (required)
					'title' => '<i class="ri-file-edit-line"></i> Pages', // title/menu text to display
		    		'href' => admin_url( '/edit.php?post_type=page'), // target url of this menu item
		    		'meta' => array(
		    		    'class' => 'sqpn-wp-pages-menu',
		    		)
		    	)
		    );

		    $wp_admin_bar->add_node( 
				array(
		    		'parent'	=> 'sqpn_wp_pages',
		    		'id' 		=> 'search_sqpn_wp_sub_pages', // an unique id (required)
		    		'title' 	=> '<input type="text" class="sqpn-search-input sqpn-wp-search-page" placeholder="Search Page" data-type="wp"/>',
		    		/*'href' 		=> '#', // target url of this menu item*/
		    		'meta' 		=> array(
		    		    'class' => 'sqpn-search-input-group sqpn-wp-pages-search',
		    		)
		    	)
		    );

		    $options = array(
				'sort_column' => 'menu_order',
				'parent' => 0,
				'post_type' => 'page',
			); 
			$pages = get_pages($options); 

			//$edit_wrap = '<span class="sqpn-edit-pages-wrap"><i class="fa fa-wordpress"></i><i class="fa fa-behance-square"></i><i class="fa fa-eye"></i></span>';
			foreach ($pages as $page) {
				$id 	= $page->ID;
				$title 	= $page->post_title;
				$url  	= get_page_link($id);

				    // Modify the title to include the external link icon
    $modified_title = $title . ' <a href="' . $url . '" class="sqpn-external-link" target="_blank"><i class="ri-external-link-line"></i></a>';

    // Add node for the page with the modified title
    $wp_admin_bar->add_node( 
        array(
            'parent'    => 'sqpn_wp_pages',
            'id'        => $id.'_sqpn_wp_sub_pages',
            'title'     => $modified_title, // title/menu text to display with icon
            'href'      => admin_url('/post.php?post='.$id.'&action=edit'),
			'meta'      => array('class' => 'sqpn-wp-pages-sub-menu cnw-pages-item-wrapper')
		)
	);
				//var_dump( $page );
			}
	}

		function sqpn_add_admin_bar_bb_menu( $wp_admin_bar ) {
			if ($this->is_bricks_active()) {
				return; // Exit if Bricks is active
			}

			global $wp;

			// Check if beaver builder is active
			if ( !class_exists( 'FLBuilder' ) ) {
			return;
			}

			$current_url  = ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ) ? 'https://'.$_SERVER["SERVER_NAME"] :  'http://'.$_SERVER["SERVER_NAME"];
		  	$current_url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
		  	$current_url .= $_SERVER["REQUEST_URI"];

		  	//var_dump( $current_url );

			$pos = strpos($current_url, 'wp-admin');
			if ($pos === false){
				$current_url = $current_url . '?fl_builder';
			}else{
				$current_url = '';
			}
			
			//var_dump( $ )
			$wp_admin_bar->add_node( 
				array(
		    		'id' => 'sqpn_bb_pages', // an unique id (required)
					'title' => '<i class="ri-file-edit-line"></i> Pages', // title/menu text to display
		    		'href' => $current_url, // target url of this menu item
		    		'meta' => array(
		    		    'class' => 'sqpn-bb-pages-menu',
		    		)
		    	)
		    );

			$wp_admin_bar->add_node( 
				array(
		    		'parent'	=> 'sqpn_bb_pages',
		    		'id' 		=> 'search_sqpn_bb_sub_pages', // an unique id (required)
		    		'title' 	=> '<input type="text" class="sqpn-search-input sqpn-bb-search-page" placeholder="Search Page" data-type="bb"/>', // title/menu text to display
		    		/*'href' 		=> '#', // target url of this menu item*/
		    		'meta' 		=> array(
		    		    'class' => 'sqpn-search-input-group sqpn-bb-pages-search',
		    		)
		    	)
		    );

		    $options = array(
				'sort_column' => 'menu_order',
				'parent' => 0,
				'post_type' => 'page',
			); 
			$pages = get_pages($options); 

			foreach ($pages as $page) {
    $id    = $page->ID;
    $title = $page->post_title;
    $url   = get_page_link($id);

    // Modify the title to include the external link icon
    $modified_title = $title . ' <a href="' . $url . '" class="sqpn-external-link" target="_blank"><i class="ri-external-link-line"></i></a>';

    // Add node for the page with the modified title
    $wp_admin_bar->add_node( 
        array(
            'parent'    => 'sqpn_bb_pages',
            'id'        => $id.'_sqpn_bb_sub_pages',
            'title'     => $modified_title,
            'href'      => $url . '?fl_builder',
			'meta'      => array('class' => 'sqpn-bb-pages-sub-menu cnw-pages-item-wrapper')
        )
    );
				//var_dump( $page );
			}
		}

		function sqpn_add_admin_bar_bricks_menu( $wp_admin_bar ) {
			if (!$this->is_bricks_active()) {
				return; // Exit if Bricks is not active
			}

			global $wp;

			// Check if Bricks is active
			$current_theme = wp_get_theme();
			if ('bricks' != $current_theme->get('TextDomain') && !($current_theme->parent() && 'bricks' == $current_theme->parent()->get('TextDomain'))) {
				// If Bricks is not the active theme or a parent theme, return early.
				return;
			}

			$current_url  = ( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ) ? 'https://'.$_SERVER["SERVER_NAME"] :  'http://'.$_SERVER["SERVER_NAME"];
			$current_url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
	$current_url .= $_SERVER["REQUEST_URI"];

	//var_dump( $current_url );

	$pos = strpos($current_url, 'wp-admin');
	if ($pos === false){
		$current_url = $current_url . '?bricks=run';
			}else{
				$current_url = '';
			}

			//var_dump( $ )
			$wp_admin_bar->add_node( 
				array(
					'id' => 'sqpn_bricks_pages', // an unique id (required)
					'title' => '<i class="ri-file-edit-line"></i> Pages', // title/menu text to display
					'href' => $current_url, // target url of this menu item
					'meta' => array(
						'class' => 'sqpn-bricks-pages-sub-menu',
					)
				)
			);

			$wp_admin_bar->add_node( 
				array(
					'parent'	=> 'sqpn_bricks_pages',
					'id' 		=> 'search_sqpn_bricks_sub_pages', // an unique id (required)
					'title' 	=> '<input type="text" class="sqpn-search-input sqpn-bricks-search-page" placeholder="Search Page" data-type="bricks"/>', // title/menu text to display
					/*'href' 		=> '#', // target url of this menu item*/
					'meta' 		=> array(
						'class' => 'sqpn-search-input-group sqpn-bricks-pages-search',
					)
				)
			);

			$options = array(
				'sort_column' => 'menu_order',
				'parent' => 0,
				'post_type' => 'page',
			); 
			$pages = get_pages($options); 

			foreach ($pages as $page) {
foreach ($pages as $page) {
    $id    = $page->ID;
    $title = $page->post_title;
    $url   = get_page_link($id);

    // Modify the title to include the external link icon
    $modified_title = $title . ' <a href="' . $url . '" class="sqpn-external-link" target="_blank"><i class="ri-external-link-line"></i></a>';

    // Add node for the page with the modified title
    $wp_admin_bar->add_node( 
        array(
            'parent'    => 'sqpn_bricks_pages',
            'id'        => $id.'_sqpn_bricks_sub_pages',
            'title'     => $modified_title,
            'href'      => $url . '?bricks=run',
            'meta'      => array('class' => 'sqpn-bricks-pages-sub-menu cnw-pages-item-wrapper')
        )
    );
}

				
				
// Add the 'Templates' node after the foreach loop
$wp_admin_bar->add_node( 
    array(
        'parent'    => 'sqpn_bricks_pages',
        'id'        => 'sqpn_bricks_templates',
        'title'     => 'Templates',
		'href'      => '/wp-admin/edit.php?post_type=bricks_template',
		'meta'      => array('class' => 'sqpn-bricks-pages-sub-menu cnw-pages-item-wrapper')
	)
);

				//var_dump( $page );
			}
		}
	}
new sqpn_Quick_Page_Navigation();
//}