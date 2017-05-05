<?php
/**
 * DOWNLOAD NOW - WooCommerce - Functions
 * 
 * Various functions.
 * 
 * @version	2.3.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_enqueue_scripts', 'somdn_get_script_assets' );

function somdn_get_script_assets() {

	if ( ( isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'download_now_dashboard' ) ) {
		/**
		 * If the current admin page is this plugin's settings page
		 */
		wp_enqueue_script( 'somdn-settings-script', plugins_url('/assets/js/somdn-settings-script.js', dirname(__FILE__) ), 'jquery' , '1.0.0', true );
		 
		wp_register_style( 'somdn-settings-style', plugins_url('/assets/css/somdn-settings-style.css', dirname(__FILE__) ) );
		wp_enqueue_style( 'somdn-settings-style' );
	}

	/**
	 * CSS changes for admin area, including post columns
	 */
	wp_register_style( 'somdn-admin-style', plugins_url('/assets/css/somdn-admin-style.css', dirname(__FILE__) ) );
	wp_enqueue_style( 'somdn-admin-style' );

}

add_action( 'wp_enqueue_scripts', 'somdn_load_scripts' );

function somdn_load_scripts() {

	wp_enqueue_script( 'somdn-script', plugins_url('/assets/js/somdn_script.js', dirname(__FILE__) ), 'jquery' , '1.0.0', true );
	wp_register_style( 'somdn-style', plugins_url('/assets/css/somdn-style.css', dirname(__FILE__) ) );
	wp_enqueue_style( 'somdn-style' );

}

add_filter( 'woocommerce_is_purchasable', 'somdn_prevent_purchase', 10, 2 );

function somdn_prevent_purchase( $purchasable, $product ) {

	//return $purchasable = false;

	$productID = $product->get_id();
	$downloadable = $product->is_downloadable();

	if ( ! somdn_is_product_valid( $productID, $downloadable ) ) {
		return $purchasable;
	} else {
		$purchasable = false;
	}

	$purchasable = somdn_is_purchasable_compat( $purchasable );

	return $purchasable;

}

function somdn_is_product_valid( $productID, $downloadable ) {

	/**
	 * If global restrictions are in place
	 * 
	 */
	//if ( ! apply_filters( 'somdn_restrict', true, $productID ) ) return false;

	$product = wc_get_product( $productID );

	if ( ! $product->is_type( 'simple' ) ) {
		return false;
	}

	$genoptions = get_option( 'somdn_gen_settings' );

	// ignore and return if product is not downloadable;
	if ( $downloadable != 'yes' ) {
		return false;
	}
	
	$somdn_indy = isset( $genoptions['somdn_indy_items'] ) ? $genoptions['somdn_indy_items'] : false ;
	
	if ( $somdn_indy ) {
	
		$included = get_post_meta( $productID, 'somdn_included', true );
	
		if ( empty( $included  ) || ! $included ) {
			return false;
		}
	
	}

	$downloads = $product->get_downloads();
	$downloads_count = count( $downloads );

	if ( $downloads_count <= 0 )  {
		return false;
	}

	// ignore and return if product has variations
	if ( $product->get_attribute( 'variation' ) != '' ) {	
		return false;
	}

	$price = get_post_meta( $productID, '_regular_price', true);
	$sale = get_post_meta( $productID, '_sale_price', true);

	$onsaleticked = isset( $genoptions['somdn_include_sale_items'] ) ? $genoptions['somdn_include_sale_items'] : false ;
	if ( ! somdn_is_product_free( $price, $sale, $onsaleticked, $productID ) ) {
		return false;
	}

	if ( ! somdn_is_product_valid_compat( $productID ) ) {
		return false;
	}

	$requirelogin = isset( $genoptions['somdn_require_login'] ) ? true : false ;
	if ( ! is_user_logged_in() && $requirelogin ) {
		return false;
	}

	return true;

}

function somdn_is_product_free( $price, $sale, $onsaleticked, $productID ) {

	$free = false;

	if ( ( $price <= 0.0 ) || ( $onsaleticked == true && ( $sale != NULL && $sale <= 0.0 ) ) ) {
		return $free = true;
	}

	return apply_filters( 'somdn_is_free', $free, $productID );

}

add_filter( 'woocommerce_product_add_to_cart_text' , 'somdn_change_read_more' );

function somdn_change_read_more( $text ) {

	global $product;

	$productID = $product->get_id();
	$downloadable = $product->is_downloadable();

	if ( ! somdn_is_product_valid( $productID, $downloadable ) ) {
		return $text;
	}
	
	$options = get_option( 'somdn_gen_settings' );
	$newtext = ( isset( $options['somdn_read_more_text'] ) && $options['somdn_read_more_text'] ) ? $options['somdn_read_more_text']: false ;
	
	if ( $newtext ) {
		return $newtext;
	} else {
		return $text;
	}
	
}

function somdn_get_available_downloads_text() {

	$multioptions = get_option( 'somdn_multi_settings' );

	if ( ! isset( $multioptions['somdn_available_downloads_text'] ) || ! $multioptions['somdn_available_downloads_text'] ) {
		$available_downloads_text = __( 'Available Downloads:', 'download-now-for-woocommerce' );
	} else {
		$available_downloads_text = isset( $multioptions['somdn_available_downloads_text'] ) ? $multioptions['somdn_available_downloads_text'] : __( 'Available Downloads:', 'download-now-for-woocommerce' ) ;
	} ?>
	
	<div class="somdn-available-downloads">
		<span><?php echo $available_downloads_text; ?></span>
	</div>
	
<?php

}

function somdn_get_plugin_link_full() {
	return '?page=download_now_dashboard';
}

if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}

add_shortcode( 'download_now', 'somdn_single_shortcode' );
function somdn_single_shortcode( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
			'id' => '',
			'align' => 'left',
			'text' => ''
		),
		$atts,
		'download_now'
	);
	
	$productID = $atts['id'];
	$align = $atts['align'];
	$text = $atts['text'];
	
	$product = wc_get_product( $productID );
	
	$link = somdn_product_page( true, $product, false, true, true, $text );
	// function args = ( $archive = false, $product = '', $echo = true, $archive_enabled = false, $shortcode = false, $shortcode_text = '', $product_page_short = false )
	
	if ( ! $link ) {
		return;
	}
	
	$content = '<div class="somdn-shortcode-wrap ' . $align . '">' . $link . '</div>';
	
	return $content;

}

add_shortcode( 'download_now_page', 'somdn_single_shortcode_page' );
function somdn_single_shortcode_page( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
			'id' => '',
			'text' => ''
		),
		$atts,
		'download_now_page'
	);
	
	$productID = $atts['id'];
	$text = $atts['text'];

	if ( ! $productID ) {

		$product = wc_get_product();

		if ( ! $product ) {
			global $product;
		}

		if ( ! $product ) {
			return;
		}

	} else {

		$product = wc_get_product( $productID );

	}

	$content = somdn_product_page( false, $product, false, false, false, $text, true ); // False $shortcode to replicate product page
	// function args = ( $archive = false, $product = '', $echo = true, $archive_enabled = false, $shortcode = false, $shortcode_text = '', $product_page_short = false )
	
	if ( ! $content ) {
		return;
	}
	
	return $content;

}

function somdn_is_pro() {
	if ( defined( 'SOMDN_PRO' ) && file_exists( SOMDN_PRO ) ) {
		return true;
	}
}

/**
 * Get filesize function. In testing.
 * 
 */
//foreach( $downloads as $key => $each_download )  {
//		$path = parse_url( $each_download['file'], PHP_URL_PATH );
//		$abs_filepath = $_SERVER['DOCUMENT_ROOT'] . $path;
//		$size = filesize($abs_filepath);
//		echo formatBytes($size, 1);
//}
//function formatBytes($size, $precision = 2) {
//	$base = log($size, 1024);
//	$suffixes = array('', ' KB', ' MB', ' G', ' T');   
//	return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
//}