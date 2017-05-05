<?php
/**
 * DOWNLOAD NOW - WooCommerce - Download Page
 * 
 * The function to display download links.
 * 
 * @version	2.3.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'somdn_download_button', 'somdn_get_download_button', 10, 4 );
add_action( 'somdn_single_download_link', 'somdn_get_single_download_link', 10, 4 );
add_action( 'somdn_multi_download_link', 'somdn_get_multi_download_link', 10, 4 );

add_action( 'woocommerce_single_product_summary', 'somdn_product_page', 31 );

if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {

	$genoptions = get_option( 'somdn_gen_settings' );
	$archive_enabled = ( isset( $genoptions['somdn_include_archive_items'] ) && $genoptions['somdn_include_archive_items'] ) ? true : false ;

	if ( ! $archive_enabled ) {
		return;
	}

	function woocommerce_template_loop_add_to_cart( $args = array() ) {

		global $product;

		$productID = $product->get_id();
		$downloadable = $product->is_downloadable();

		if ( somdn_is_product_valid( $productID, $downloadable ) ) {
			echo '<div style="text-align: center;">';
			somdn_product_page( true, $product, true );
			echo '</div>';
			return;
		} else {

			if ( $product ) {
				$defaults = array(
					'quantity' => 1,
					'class'    => implode( ' ', array_filter( array(
						'button',
						'product_type_' . $product->get_type(),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
					) ) ),
				);
				$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );
				wc_get_template( 'loop/add-to-cart.php', $args );
	
			}
		
		}

	}
}

function somdn_product_page( $archive = false, $product = '', $echo = true, $archive_enabled = false, $shortcode = false, $shortcode_text = '', $product_page_short = false ) {


	if ( ! $product ) {
		$product = wc_get_product();
	}
 
	if ( ! $product ) {
		global $product;
	}

	if ( ! $product ) {
		return;
	}

	if ( ! $archive && ! is_product() && ! is_page() && ! $archive_enabled ) {
		return;
	}

	$productID = $product->get_id();
	$downloadable = $product->is_downloadable();

	//if ( $product_page_short ) {
	//	if ( function_exists( 'wc_memberships' ) && function_exists( 'get_product_purchasing_restricted_message' ) ) {
	//		// if the user can't purchase restricted product
	//		if ( ! somdn_is_user_member_purchase( $productID ) ) {
	//			echo '<div class="wc-memberships-content-restricted-message">' . get_product_purchasing_restricted_message( $productID ) . '</div>';
	//			return;
	//		}
	//	}
	//}

	if ( ! somdn_is_product_valid( $productID, $downloadable ) ) {
		return;
	}

	$downloads = $product->get_downloads();

	$downloads_count = count( $downloads );
	$is_single_download = ( 1 == $downloads_count ) ? true : false ;

	$genoptions = get_option( 'somdn_gen_settings' );
	$singleoptions = get_option( 'somdn_single_settings' );
	$multioptions = get_option( 'somdn_multi_settings' );
	$docoptions = get_option( 'somdn_docviewer_settings' );
	 
	$shownumber = ( isset( $multioptions['somdn_show_numbers'] ) && $multioptions['somdn_show_numbers'] ) ? true : false ;
	 
	$buttoncss = ( isset( $genoptions['somdn_button_css'] ) && $genoptions['somdn_button_css'] ) ? $genoptions['somdn_button_css'] : '' ;
	
	$linkcss = ( isset( $genoptions['somdn_link_css'] ) && $genoptions['somdn_link_css'] ) ? $genoptions['somdn_link_css'] : '' ;

	$pdfenabled = ( isset( $docoptions['somdn_docviewer_enable'] ) && $docoptions['somdn_docviewer_enable'] ) ? true : false ;

	if ( $is_single_download ) {
		$buttontext = ( isset( $singleoptions['somdn_single_button_text'] ) && $singleoptions['somdn_single_button_text'] ) ? $singleoptions['somdn_single_button_text'] : __( 'Download Now', 'download-now-for-woocommerce' ) ;
	} else {
		$buttontext = ( isset( $multioptions['somdn_multi_button_text'] ) && $multioptions['somdn_multi_button_text'] ) ? $multioptions['somdn_multi_button_text'] : __( 'Download All (.zip)', 'download-now-for-woocommerce' );
	}
	
	$single_type = ( isset( $singleoptions['somdn_single_type'] ) && 2 == $singleoptions['somdn_single_type'] ) ? 2 : 1 ;
	
	$pdf_default = __( 'Download PDF', 'download-now-for-woocommerce' );
	$pdf_output = false;

	if ( ! $archive_enabled ) {
		$archive_enabled = ( isset( $genoptions['somdn_include_archive_items'] ) && $genoptions['somdn_include_archive_items'] ) ? true : false ;
	}

	if ( $archive_enabled && $archive && ! $shortcode ) {
		$buttontext = ( isset( $options['somdn_read_more_text'] ) && $options['somdn_read_more_text'] ) ? $options['somdn_read_more_text']: __( 'Download', 'download-now-for-woocommerce' ) ;
		$single_type = 1;
	}
	
	if ( $shortcode ) {
		$single_type = 1;
	}
	
	if ( $shortcode_text ) {
		$buttontext = $shortcode_text;
	}
	
	ob_start();
	
	if ( ( is_page() || is_product() ) && ! $archive ) {
		echo somdn_hide_cart_style();
	}

	if ( $is_single_download ) { ?>
	
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_form' ); ?>

		<div class="somdn-download-wrap">
	
			<?php

				if ( $pdfenabled ) {

					foreach( $downloads as $key => $each_download )  {	
						$ext = somdn_get_ext_from_path( $each_download['file'] );
						if ( $ext == 'pdf' ) {
							$pdf_output = true;
							$buttontext = ( isset( $docoptions['somdn_docviewer_single_link_text'] ) && $docoptions['somdn_docviewer_single_link_text'] ) ? $docoptions['somdn_docviewer_single_link_text'] : $pdf_default ;
						} else {
							$pdf_output = false;
						}
					}
				}

			?>

			<form <?php if ( $archive_enabled && $archive ) echo ' class="somdn-archive-download-form" '; ?>action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="somdn-download-single-form">
				
					<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_button' ); ?>
							 
					<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
					<input type="hidden" name="action" value="somdn_download_single">
					<input type="hidden" name="product" value="<?php echo $productID; ?>">
					
					<?php if ( $pdf_output ) { ?>
					<input type="hidden" name="pdf" value="true">
					<?php } ?>
					
					<?php

					if ( $pdf_output ) { ?>

						<?php if ( isset( $docoptions['somdn_docviewer_single_display'] ) && 2 == $docoptions['somdn_docviewer_single_display'] ) { ?>
						
							<?php if ( $archive_enabled && $archive ) { ?>
							
								<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>
							
							<?php } else { ?>
							
								<?php do_action( 'somdn_single_download_link', $buttontext, $linkcss, $archive, $productID ); ?>
								
							<?php } ?>
							
						<?php } else { ?>

							<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>

						<?php } ?>

					<?php } else { ?>

						<?php if ( $single_type == 2 ) { ?>
						
							<?php do_action( 'somdn_single_download_link', $buttontext, $linkcss, $archive, $productID ); ?>
							
						<?php } else { ?>
						
							<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>
							
						<?php } ?>

					<?php } ?>
					
					<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_button' ); ?>


			</form>
			
		</div>
		
		<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_form' ); ?>

		<?php $content = ob_get_clean();
		
		if ( $echo ) {
			echo $content;
			return;
		} else {
			return $content;
		}
	 
	}
	 
	$multi_type = ( isset( $multioptions['somdn_display_type'] ) && $multioptions['somdn_display_type'] ) ? $multioptions['somdn_display_type'] : '1' ;

	if ( $archive_enabled && $archive ) {
		$multi_type = 2;
	}

	if ( $shortcode ) {
		$multi_type = 2;
	}

	/**
	 * 1. Links Only
	 */
	if ( 1 == $multi_type ) { ?>
	
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_form' ); ?>
		
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_button' ); ?>
	
		<?php somdn_get_available_downloads_text(); ?>

		<div class="somdn-download-wrap">
		 
			<?php
							 
			$count = 0;
	 
			foreach( $downloads as $key => $each_download )  {
							 
				$count++;

				if ( $shownumber ) {
					$shownumber = $count . '. ';
				} else {
					$shownumber = '';
				}
				
				$ext = somdn_get_ext_from_path( $each_download['file'] );
				if ( $ext == 'pdf' && $pdfenabled ) {
					$pdf_output = true;
				} else {
					$pdf_output = false;
				}
				
				?>

				<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="somdn-md-form-<?php echo $count; ?>">
										 
					<div class="somdn-form-table-bottom">
																 
						<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
						<input type="hidden" name="action" value="somdn_download_multi_single">
						<input type="hidden" name="product" value="<?php echo $productID; ?>">
						<input type="hidden" name="productfile" value="<?php echo $productID; ?>">
						
						<?php if ( $pdf_output ) { ?>
						<input type="hidden" name="pdf" value="true">
						<?php } ?>

						<?php do_action( 'somdn_multi_download_link', $count, $linkcss, $shownumber, $each_download['name'] ); ?>
												 
					</div>
								 
				</form>               
					 
			<?php } ?>
			
			<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_button' ); ?>
							 
		</div>
		
		<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_form' ); ?>
			 
		<?php $content = ob_get_clean();
		
		if ( $echo ) {
			echo $content;
			return;
		} else {
			return $content;
		}
	 
	}
	 
	/**
	 * 2. Button Only
	 */
	if ( 2 == $multi_type ) { ?>
	
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_form' ); ?>

		<div class="somdn-download-wrap">
	 
			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post"<?php if ( $archive_enabled && $archive ) echo ' class="somdn-archive-download-form"'; ?>>
							 
				<div class="somdn-form-table-bottom"<?php if ( $archive_enabled && $archive ) echo ' style="text-align: center!important;"'; ?>>
				
					<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_button' ); ?>
													 
					<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
					<input type="hidden" name="action" value="somdn_download_all_files">
					<input type="hidden" name="product" value="<?php echo $productID; ?>">
					<input type="hidden" name="totalfiles" value="<?php echo count( $downloads ); ?>">
									
					<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>
					
					<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_button' ); ?>
									
				</div>
					 
			</form>
			 
		</div>
		
		<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_form' ); ?>
			 
		<?php $content = ob_get_clean();
		
		if ( $echo ) {
			echo $content;
			return;
		} else {
			return $content;
		}
	 
	}

	/**
	 * 3. Button + Checkboxes
	 */
	if ( 3 == $multi_type ) { ?>
	
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_form' ); ?>
		
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_button' ); ?>
	
		<div class="somdn-download-wrap">
	 
			<?php somdn_get_available_downloads_text(); ?>
			 
			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="somdn-checkbox-form">
							 
				<div class="somdn-form-table-bottom somdn-form-validate" style="display: none;">
					<p style="color: red;"><strong>Please select at least 1 checkbox</strong></p>
				</div>
													 
				<?php
								 
				$count = 0;
		 
						 
				foreach( $downloads as $key => $each_download )  {
						 
					$count++; ?>
								 
					<div class="somdn-form-table-bottom somdn-checkboxes-wrap">
						 
						<input style="display: inline-block;" type="checkbox" id="somdn-download-file-<?php echo $count; ?>" name="somdn-download-file-<?php echo $count; ?>" value="1">
						<label style="display: inline-block;" for="somdn-download-file-<?php echo $count; ?>"><?php echo $each_download['name']; ?></label>
								 
					</div>
				 
				<?php } ?>
						 
				<?php if ( isset( $multioptions['somdn_select_all'] ) && $multioptions['somdn_select_all'] ) { ?>
				 
					<div class="somdn-form-table-bottom somdn-checkboxes-wrap somdn-select-all-wrap">
							 
						<input style="display: inline-block;" type="checkbox" id="somdn-download-files-all" name="somdn-download-files-all">
						<label style="display: inline-block;" for="somdn-download-files-all">Select All</label>
									 
					</div>
						 
				<?php } ?>        
	
										 
				<div class="somdn-form-table-bottom somdn-checkboxes-button-wrap">
																 
					<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
					<input type="hidden" name="action" value="somdn_download_multi_checked">
					<input type="hidden" name="product" value="<?php echo $productID; ?>">
					<input type="hidden" name="totalfiles" value="<?php echo count( $downloads ); ?>">
												
					<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>
					
					<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_button' ); ?>
												
				</div>
							 
			</form>

		</div>
		
		<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_form' ); ?>
			 
		<?php $content = ob_get_clean();
		
		if ( $echo ) {
			echo $content;
			return;
		} else {
			return $content;
		}
	 
	}
	 
	/**
	 * 4. Button + Links
	 */
	if ( 4 == $multi_type ) { ?>
	
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_form' ); ?>
		
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_button' ); ?>
	 
		<?php somdn_get_available_downloads_text(); ?>

		<div class="somdn-download-wrap">

			<?php
							 
			$count = 0;
	 
			foreach( $downloads as $key => $each_download )  {
							 
				$count++;

				if ( $shownumber ) {
					$shownumber = $count . '. ';
				} else {
					$shownumber = '';
				}

				$ext = somdn_get_ext_from_path( $each_download['file'] );
				if ( $ext == 'pdf' && $pdfenabled ) {
					$pdf_output = true;
				} else {
					$pdf_output = false;
				}

				?>

				<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" id="somdn-md-form-<?php echo $count; ?>">
									 
					<div class="somdn-form-table-bottom">
															 
						<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
						<input type="hidden" name="action" value="somdn_download_multi_single">
						<input type="hidden" name="product" value="<?php echo $productID; ?>">
						<input type="hidden" name="productfile" value="<?php echo $count; ?>">

						<?php if ( $pdf_output ) { ?>
						<input type="hidden" name="pdf" value="true">
						<?php } ?>
											
						<?php do_action( 'somdn_multi_download_link', $count, $linkcss, $shownumber, $each_download['name'] ); ?>
											 
					</div>
							 
				</form>               
					 
			<?php } ?>

			<div style="padding-top: 15px">
			 
				<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="somdn-button-form">
									 
					<div class="somdn-form-table-bottom">
															 
						<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
						<input type="hidden" name="action" value="somdn_download_all_files">
						<input type="hidden" name="product" value="<?php echo $productID; ?>">
						<input type="hidden" name="totalfiles" value="<?php echo count( $downloads ); ?>">
											
						<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>
						
						<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_button' ); ?>
											
					</div>
							 
				</form>
					 
			</div>
							 
		</div>
		
		<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_form' ); ?>
			 
		<?php $content = ob_get_clean();
		
		if ( $echo ) {
			echo $content;
			return;
		} else {
			return $content;
		}
	 
	}

	/**
	 * 5. Button & Filenames
	 */
	if ( 5 == $multi_type ) { ?>
	
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_form' ); ?>
		
		<?php if ( is_single() ) do_action( 'woocommerce_before_add_to_cart_button' ); ?>
	
		<div class="somdn-download-wrap">
	 
			<?php somdn_get_available_downloads_text(); ?>
	 
			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="somdn-button-form">
					 
				<p>
					 
					<?php
							 
					$count = 0;
								 
					foreach( $downloads as $key => $each_download )  {
								 
						$count++;
										 
						if ( $shownumber ) {
							$shownumber = $count . '. ';
						} else {
							$shownumber = '';
					} ?>
	
					<span style="display: inline-block;" class="somdn-download-filename"><?php echo $shownumber . $each_download['name']; ?></span><br>
						 
					<?php } ?>
							 
				</p>
							 
				<div class="somdn-form-table-bottom">
							 
					<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
					<input type="hidden" name="action" value="somdn_download_all_files">
					<input type="hidden" name="product" value="<?php echo $productID; ?>">
					<input type="hidden" name="totalfiles" value="<?php echo count( $downloads ); ?>">

					<?php do_action( 'somdn_download_button', $buttontext, $buttoncss, $archive, $productID ); ?>
					
					<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_button' ); ?>
								
				</div>
					 
			</form>
			 
		</div>
		
		<?php if ( is_single() ) do_action( 'woocommerce_after_add_to_cart_form' ); ?>

		<?php $content = ob_get_clean();
		
		if ( $echo ) {
			echo $content;
			return;
		} else {
			return $content;
		}
	 
	}

	$content = ob_get_clean();
		
	if ( $echo ) {
		echo $content;
		return;
	} else {
		return $content;
	}
 
}

function somdn_get_download_button( $text, $css, $archive = false, $productID = '' ) {

	if ( $archive ) { ?>
	
		<a rel="nofollow" href="<?php echo get_the_permalink( $productID ); ?>" class="somdn-download-archive button product_type_simple add_to_cart_button"><?php echo $text; ?></a>
			
	<?php } else { ?>

		<button style="<?php echo esc_attr( $css ); ?>" type="submit" id="somdn-form-submit-button" class="somdn-download-button single_add_to_cart_button button"><?php echo $text; ?></button>
	
	<?php }

}

function somdn_get_single_download_link( $text, $css, $archive = false, $productID = '' ) { ?>

	<a id="somdn-sdbutton" href="#" class="somdn-download-link" style="<?php echo esc_attr( $css ); ?>"><?php echo esc_html( $text ); ?></a>

<?php }

function somdn_get_multi_download_link( $count, $css, $shownumber, $name ) { ?>

	<a id="somdn-md-link-<?php echo $count; ?>" href="#" class="somdn-download-link" style="<?php echo esc_attr( $css ); ?>"><?php echo $shownumber . $name; ?></a>

<?php }
