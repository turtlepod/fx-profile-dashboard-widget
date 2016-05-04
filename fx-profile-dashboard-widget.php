<?php
/**
 * Plugin Name: f(x) Profile Dashboard Widget
 * Plugin URI: http://genbumedia.com/plugins/fx-profile-dashboard-widget/
 * Description: Admin dashboard widget to edit profile.
 * Version: 1.1.0
 * Author: David Chandra Purnama
 * Author URI: http://shellcreeper.com/
 * License: GPLv2 or later
 * Text Domain: fx-profile-dashboard-widget
 * Domain Path: /languages/
 *
 * @author David Chandra Purnama <david@genbumedia.com>
 * @copyright Copyright (c) 2016, Genbu Media
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
**/

/* Do not access this file directly */
if ( ! defined( 'WPINC' ) ) { die; }

/* Constants
------------------------------------------ */

/* Set plugin version constant. */
define( 'FX_PDW_VERSION', '1.1.0' );

/* Set constant path to the plugin directory. */
define( 'FX_PDW_PATH', trailingslashit( plugin_dir_path(__FILE__) ) );

/* Set the constant path to the plugin directory URI. */
define( 'FX_PDW_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );


/* Plugins Loaded
------------------------------------------ */

/* Load plugins file */
add_action( 'plugins_loaded', 'fx_pdw_plugins_loaded' );

/**
 * Load plugins file
 * @since 0.1.0
 */
function fx_pdw_plugins_loaded(){

	/* Language */
	load_plugin_textdomain( 'fx-profile-dashboard-widget', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	/* Load Settings */
	if( is_admin() ){
		require_once( FX_PDW_PATH . 'includes/dashboard-widget.php' );
		$fx_profile_dashboard_widget = new fx_Profile_Dashboard_Widget();
	}

	/* Plugin Action Link */
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'fx_pdw_plugin_action_links' );


}

/**
 * Add Action Link For Support
 * @since 1.1.0
 */
function fx_pdw_plugin_action_links ( $links ){

	/* Get current user info */
	if( function_exists( 'wp_get_current_user' ) ){
		$current_user = wp_get_current_user();
	}
	else{
		global $current_user;
		get_currentuserinfo();
	}

	/* Build support url */
	$support_url = add_query_arg(
		array(
			'about'      => urlencode( 'f(x) Profile Dashboard Widget (v.' . FX_PDW_VERSION . ')' ),
			'sp_name'    => urlencode( $current_user->display_name ),
			'sp_email'   => urlencode( $current_user->user_email ),
			'sp_website' => urlencode( home_url() ),
		),
		'http://genbumedia.com/contact/'
	);

	/* Add support link */
	$links[] = '<a target="_blank" href="' . esc_url( $support_url ) . '">' . __( 'Get Support', 'fx-profile-dashboard-widget' ) . '</a>';
	return $links;
}


