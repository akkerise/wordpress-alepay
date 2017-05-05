<?php
/**
 * Plugin Name: Download Now - WooCommerce
 * Plugin URI: https://www.squareonemedia.co.uk
 * Description: Allow users to instantly download your free digital products without going through the checkout.
 * Author: Square One Media
 * Author URI: https://www.squareonemedia.co.uk
 * Text Domain: download-now-for-woocommerce
 * Version: 2.4.4
 * Requires at least: 4.4
 * Tested up to: 4.7.3
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VAYF6G99MCMHU
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SOMDN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOMDN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once( SOMDN_PATH . 'includes/somdn-functions.php' );

require_once( SOMDN_PATH . 'includes/somdn-downloader.php' );
require_once( SOMDN_PATH . 'includes/somdn-download-page.php' );
require_once( SOMDN_PATH . 'includes/somdn-plugin-settings.php' );
require_once( SOMDN_PATH . 'includes/somdn-settings-tab-support.php' );
require_once( SOMDN_PATH . 'includes/somdn-compatibility.php' );
require_once( SOMDN_PATH . 'includes/somdn-meta.php' );
require_once( SOMDN_PATH . 'includes/somdn-doc-viewer-functions.php' );
require_once( SOMDN_PATH . 'somdn-custom-functions.php' );

$pro_loader = SOMDN_PATH . 'pro/somdn-pro-loader.php';
if ( file_exists( $pro_loader ) ) require_once( $pro_loader );

register_activation_hook( __FILE__, 'somdn_activated' );
register_deactivation_hook( __FILE__, 'somdn_deactivated' );

function somdn_activated() {

	if ( ! wp_next_scheduled ( 'somdn_delete_download_files_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'somdn_delete_download_files_event' );
	}
	
	$upload_dir = wp_upload_dir();

	$zip_path = $upload_dir['basedir'] . '/download-now-uploads';

	if ( ! file_exists( $zip_path ) ) {
		mkdir( $zip_path, 0777, true );
	}

	do_action( 'somdn_on_activate' );// extra hook for plugin activation

}

add_action('somdn_delete_download_files_event', 'somdn_delete_download_files');

function somdn_delete_download_files() {

	$upload_dir = wp_upload_dir();
	array_map( 'unlink', glob( $upload_dir['basedir'] . '/download-now-uploads/*' ) );

}

function somdn_deactivated() {

	wp_clear_scheduled_hook( 'somdn_delete_download_files_event' );
	
	$upload_dir = wp_upload_dir();
	
	array_map( 'unlink', glob( $upload_dir['basedir'] . '/download-now-uploads/*' ) );

	$zip_path = $upload_dir['basedir'] . '/download-now-uploads/';
	
	rmdir( $zip_path );
	
	do_action( 'somdn_on_deactivate' );

}

function somdn_get_forum_link() {
	return 'If you need further support please visit the support forum for this plugin over at <a href="https://wordpress.org/support/plugin/download-now-for-woocommerce/" target="_blank">WordPress.org</a>.';
}
