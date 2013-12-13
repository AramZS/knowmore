<?php
/**
 * Know More
 *
 * Read next module for articles
 *
 * @package   Know_More
 * @author    Yuri Victor <yurivictor@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.washingtonpost.com
 * @copyright 2013 Yuri Victor
 *
 * @wordpress-plugin
 * Plugin Name:       Know More
 * Plugin URI:        http://www.washingtonpost.com
 * Description:       Read next module for articles
 * Version:           0.0.1
 * Author:            Yuri Victor
 * Author URI:        http://wwww.yurivictor.com
 * Text Domain:       knowmore
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: yurivictor
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-knowmore.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Know_More', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Know_More', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Know_More', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-knowmore-admin.php' );
	add_action( 'plugins_loaded', array( 'Know_More_Admin', 'get_instance' ) );
}


/*----------------------------------------------------------------------------*
 * AJAX requests
 *----------------------------------------------------------------------------*/

/**
 * Handle ajax request
 * @since 1.0.0
 */
function handle_request() {
	$json = array();

	$url = $_GET['url'];

	if ( empty( $url ) ) {
		wp_send_json_error();
	}

	include_once 'lib/opengraph.php';

	$link = OpenGraph::fetch( esc_url( $url ), 10 );

	if ( empty( $link ) ) {
		wp_send_json_error();
	}

	$json['url']      = $url;
	$json['headline'] = $link->title;
	$json['site']     = $link->site_name;
	if ( ! empty( $link->image ) ) {
		$json['image']    = $link->image;
	}

	wp_send_json_success( $json );

}

add_action( 'wp_ajax_handle_request', 'handle_request' );