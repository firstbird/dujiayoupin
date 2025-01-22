<?php


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!class_exists('FPD_WC_Cart')) {

	class FPD_WC_Cart {

		public function __construct() {

			$cross_sell_display = get_option('fpd_cross_sells_display', 'none');
			// mzl add
			// add_action( 'woocommerce_before_single_product', array( &$this, 'before_product_container'), 1 );
			// add_filter( 'fpd_frontend_setup_configs', array( &$this, 'add_app_options') );
			// add_action( 'fpd_before_product_designer', array( &$this, 'before_product_designer'), 1 );
			// mzl 在after designer中附加了定制图片
			// add_action( 'fpd_after_product_designer', array( &$this, 'after_product_designer'), 1 );
		
			//ADD_TO_CART process
			//add additional [fpd_data]([fpd_product],[fpd_price]) to cart item
			// mzl add custom product info
			add_filter( 'woocommerce_add_cart_item_data', array(&$this, 'add_cart_item_data'), 10, 2 );
			add_filter( 'woocommerce_add_cart_item', array(&$this, 'add_cart_item'), 10 );
			//handler when a product is added to the cart
			add_action( 'woocommerce_add_to_cart', array( &$this, 'add_product_to_cart'), 10, 6 );

			//IN CART
			//get cart item from session
			add_filter( 'woocommerce_get_cart_item_from_session', array(&$this, 'get_cart_item_from_session'), 10, 2 );
			//add some extra meta data
			add_filter( 'woocommerce_get_item_data', array(&$this, 'get_item_data'), 10, 2 );
			//reset cart item link so the customized product is loaded from the cart
			add_filter( 'woocommerce_cart_item_permalink', array(&$this, 'set_cart_item_permalink'), 100, 3 );
			add_filter( 'woocommerce_cart_item_name', array(&$this, 'reset_cart_item_link'), 100, 3 );

			//change cart item thumbnail
			add_filter( 'woocommerce_cart_item_thumbnail', array(&$this, 'change_cart_item_thumbnail'), 100, 3 );
		
			add_action( 'woocommerce_after_cart', array(&$this, 'after_cart') );

			//no price when get quote is enabled
			add_filter( 'woocommerce_cart_item_price', array(&$this, 'cart_item_price'), 10, 3 );
			add_filter( 'woocommerce_cart_item_subtotal', array(&$this, 'cart_item_price'), 10, 3 );

			add_filter( 'woocommerce_cart_item_quantity', array(&$this, 'set_quantity_input_field'), 10, 3 );
			
			if( $cross_sell_display !== 'none' )
				add_action( 'woocommerce_cart_collaterals', array(&$this, 'cross_sells_display'), 20 );

			//names & numbers
			add_action( 'woocommerce_after_cart_item_name', array(&$this, 'after_cart_item_name'), 10, 2 );

		}

		public function before_product_container() {
			// echo esc_html( 'mzl cart before_product_container in ......' );
	
			global $post;
	
			if( is_fancy_product( $post->ID ) ) {
	
				//add product designer
				$product_settings = new FPD_Product_Settings( $post->ID );
				$position = $product_settings->get_option('placement');
				// echo 'mzl $position ' . $position;
	
				if( $position  == 'fpd-replace-image') {
					add_action( 'woocommerce_before_single_product_summary', 'FPD_Frontend_Product::add_product_designer', 15 );
				}
				else if( $position  == 'fpd-under-title') {
					add_action( 'woocommerce_single_product_summary', 'FPD_Frontend_Product::add_product_designer', 6 );
				}
				else if( $position  == 'fpd-after-summary') {
					add_action( 'woocommerce_after_single_product_summary', 'FPD_Frontend_Product::add_product_designer', 1 );
				}
				else {
					add_action( 'fpd_product_designer', 'FPD_Frontend_Product::add_product_designer' );
				}
	
				//remove product image, there you gonna see the product designer
				// mzl 条件不能去掉，不然product界面看不到预览
				if( $product_settings->get_option('hide_product_image') || ($position == 'fpd-replace-image' && (!$product_settings->customize_button_enabled)) ) {
					remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
				}
	
			}
		}
	
		public function before_product_designer( $post ) {

			if( get_post_type( $post ) !== 'product' )
				return;

			global $product, $woocommerce;

			//added to cart, recall added product
			if( isset($_POST['fpd_product']) ) {

				if( fpd_get_option('fpd_wc_add_to_cart_product_load') == 'customized-product' ) {

					$views = strip_tags( $_POST['fpd_product'] );
					FPD_Frontend_Product::$initial_product = stripslashes($views);

				}
				

			}
			else if( isset($_GET['cart_item_key']) ) {

				//load from cart item
				$cart = $woocommerce->cart->get_cart();				

				$cart_item = $woocommerce->cart->get_cart_item( sanitize_key( $_GET['cart_item_key'] ) );
				if( !empty($cart_item) ) {

					if( isset($cart_item['fpd_data']) ) {

						if( isset( $cart_item['quantity'] ) )
							$_POST['quantity'] = $cart_item['quantity'];

						$views = $cart_item['fpd_data']['fpd_product'];
						FPD_Frontend_Product::$initial_product = stripslashes($views);
						// echo 'mzl before_product_designer, views: ' . json_encode(stripslashes($views));//htmlspecialchars($cart_item);

						// echo $cart[$_GET['cart_item_key']]['fpd_data']['fpd_product_thumbnail'];
						// add_filter( 'woocommerce_product_single_add_to_cart_text', array( &$this, 'change_add_to_cart_btn_text') );
					}

				}
				else {

					//cart item could not be found
					echo '<p><strong>';
					echo FPD_Settings_Labels::get_translation( 'woocommerce', 'cart_item_not_found' );
					echo '</strong></p>';
					return;

				}

			}
			else if( isset($_GET['order']) && isset($_GET['item_id']) ) {

				$order = wc_get_order( intval( $_GET['order'] ) );

				//check if order belongs to customer
				if(!fpd_get_option('fpd_order_login_required')
					|| current_user_can(Fancy_Product_Designer::CAPABILITY)
					|| $order->get_user_id() === get_current_user_id()
				) {

					$item_meta = fpd_wc_get_order_item_meta( intval( $_GET['item_id'] ) );

					//V3.4.9: only order is stored in fpd_data
					FPD_Frontend_Product::$initial_product = is_array($item_meta) ? $item_meta['fpd_product'] : $item_meta;

					if( $order && $order->is_paid() ) {
						FPD_Frontend_Product::$remove_watermark = true;

						if( $product->is_downloadable() ) :
						?>
						<a href="#" id="fpd-extern-download-pdf" class="<?php echo trim(fpd_get_option('fpd_start_customizing_css_class')); ?>" style="display: inline-block; margin: 10px 10px 10px 0;">
							<?php echo FPD_Settings_Labels::get_translation( 'actions', 'download' ); ?>
						</a>
						<?php
						endif;

					}
					else {
						FPD_Frontend_Product::$remove_watermark = false;
					}

					$allowed_edit_status = array(
						'pending',
						'processing',
						'on-hold'
					);

					if( fpd_get_option('fpd_order_save_order') && in_array($order->get_status(), $allowed_edit_status) ) : ?>
						<a href="#" id="fpd-save-order" class="<?php echo trim(fpd_get_option('fpd_start_customizing_css_class')); ?>"  style="display: inline-block; margin: 10px 10px 10px 0;">
							<?php echo FPD_Settings_Labels::get_translation( 'woocommerce', 'save_order' ); ?>
						</a>
					<?php endif;

				}

			}
			else if( isset($_GET['start_customizing']) && isset($_GET['fpd_product']) ) {

				$get_fpd_product_id = intval($_GET['fpd_product']);
				
				if( FPD_Product::exists($get_fpd_product_id) ) {
					$fancy_product = new FPD_Product( $get_fpd_product_id );
					FPD_Frontend_Product::$initial_product = $fancy_product->to_JSON();
					
				}

			}

		}

		public function after_product_designer( $post ) {
			// echo 'mzl after_product_designer post: ' . get_post_type( $post );

			if( is_admin() || get_post_type( $post ) !== 'page' )
				return;

			global $product;
            
            if( !method_exists($product, 'get_id') ) return;
			echo 'mzl after_product_designer method_exists id: ' . $product->get_id();

			$product_settings = new FPD_Product_Settings( $product->get_id() );

			$product_price = wc_get_price_to_display( $product );
			$product_price = $product_price && is_numeric($product_price) ? $product_price : 0;

			wp_enqueue_script( 
				'fpd-frontend-woo', 
				plugins_url('/assets/js/frontend-woo.js', FPD_PLUGIN_ROOT_PHP), 
				array('fpd-js-utils'), 
				Fancy_Product_Designer::VERSION 
			);
			// echo 'mzl fpd_lightbox_update_product_image: ' . fpd_get_option('fpd_lightbox_update_product_image');
			$woo_configs = array(
				'options' => array(
					'lightbox_update_product_image' => fpd_get_option('fpd_lightbox_update_product_image'),
					'replace_initial_elements' 		=> 1,//$product_settings->get_option('replace_initial_elements'),
					'product_image_css_selector' 	=> fpd_get_option('fpd_wc_product_image_css_selector'),
				),
				'labels' => array(
					'loading_product' 		=> FPD_Settings_Labels::get_translation( 'woocommerce', 'loading_product' ),
					'product_loading_fail'	=> FPD_Settings_Labels::get_translation( 'woocommerce', 'product_loading_fail' )
				)
			);
			wp_localize_script( 'fpd-frontend-woo', 'fpd_woo_configs', $woo_configs);

		}

		public function cross_sells_display() {
			echo 'mzl cross_sells_display'
			?>
			<style type="text/css">

				.fpd-cross-sell-item {
					position: relative;
				}

				.fpd-cross-sell-overlay {
					position: absolute;
					top: 50%;
					left: 50%;
					-webkit-transform:translate(-50%,-50%);
					transform:translate(-50%,-50%)
				}

			</style>
			<script type="text/javascript">
				jQuery(document).ready(function() {

					var $cartItems = jQuery('.cart_item .product-thumbnail img');

					if($cartItems.length) {


					}

				});
			</script>
			<?php

		}

		//1 - store values from additional form fields
		public function add_cart_item_data( $cart_item_meta, $product_id ) {
			// echo '<div class="error"><p>'. 'add_cart_item_data' .'</p></div>';

			if( isset($_POST['fpd_product']) ) {

				$cart_item_meta['fpd_data'] = array();
				$cart_item_meta['fpd_data']['fpd_product'] = strip_tags( $_POST['fpd_product'] );
				$cart_item_meta['fpd_data']['fpd_remove_cart_item'] = strip_tags( $_POST['fpd_remove_cart_item'] );

				if( isset($_POST['fpd_product_price']) )
					$cart_item_meta['fpd_data']['fpd_product_price'] = strip_tags( $_POST['fpd_product_price'] );

				if( isset($_POST['fpd_quantity']) && !empty($_POST['fpd_quantity']) )
					$cart_item_meta['fpd_data']['fpd_quantity'] = strip_tags( $_POST['fpd_quantity'] );

				if( isset($_POST['fpd_product_thumbnail']) ) {
					// echo 'mzl post fpd_product_thumbnail: ' . strip_tags( $_POST['fpd_product_thumbnail'] );
					$cart_item_meta['fpd_data']['fpd_product_thumbnail'] = strip_tags( $_POST['fpd_product_thumbnail'] );
					// $cart_item_meta['fpd_data']['fpd_product_thumbnail'] = 'https://www.google.com/url?sa=i&url=https%3A%2F%2Fwww.bbc.co.uk%2Fnews%2Farticles%2Fcy4g193qezno&psig=AOvVaw10h4_g8Oy_XEFOiwQIn7Pn&ust=1735751780891000&source=images&cd=vfe&opi=89978449&ved=0CBQQjRxqFwoTCKDe-ITC0ooDFQAAAAAdAAAAABAJ';
				}
				if( isset($_POST['fpd_print_order']) )
					$cart_item_meta['fpd_data']['fpd_print_order'] = $_POST['fpd_print_order'];

			}

		    return $cart_item_meta;
		}

		//2- set cart item price
		public function add_cart_item( $cart_item ) {
			// echo '<div class="error"><p>'. 'add_cart_item' .'</p></div>';

			global $woocommerce;

			//check if data contains a product
	        if ( isset($cart_item['fpd_data']) && $cart_item['fpd_data'] ) {

		        $fpd_data = $cart_item['fpd_data'];
	            if (isset($fpd_data['fpd_product_price'])) {

		            $product = $cart_item['data'];
					$final_price = floatval($fpd_data['fpd_product_price']);

					//prices exclusive of tax
					$tax_rates 		= WC_Tax::get_rates( $product->get_tax_class() );
		           	if( !wc_prices_include_tax() ) {
/*
						$taxes      	= WC_Tax::calc_inclusive_tax( $fpd_data['fpd_product_price'], $tax_rates);
						$tax_amount 	= WC_Tax::get_tax_total( $taxes );
						$final_price 	= $final_price - $tax_amount;
*/
		            }
		            else {
/*
						$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
						$base_taxes   = WC_Tax::calc_tax(  $final_price, $base_tax_rates, true );
						$modded_taxes = WC_Tax::calc_tax(  $final_price - array_sum( $base_taxes ), $tax_rates, false );
						$final_price = round( $final_price + array_sum( $base_taxes )  );
*/
		            }

					//security: check that price can not be lower than wc price
					$final_price = $final_price < $product->get_price() ? $product->get_price() : $final_price;
		            $final_price = apply_filters( 'fpd_wc_cart_item_price', $final_price, $cart_item, $fpd_data );
					$product->set_price($final_price);

					$product_settings = new FPD_Product_Settings( is_a($product, 'WC_Product_Variation') ? $product->get_parent_id() : $product->get_id() );
					if( $product_settings->get_option('get_quote') ) {
						$product->set_price(0);
					}

	            }
	        }

		    return $cart_item;

		}

		//3
		public function add_product_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			//echo '<div class="error"><p>'. 'add_product_to_cart' .'</p></div>';

			if( isset($cart_item_data['fpd_data']) ) {

				global $woocommerce;

				//check if an old cart item exist
				if( !empty($cart_item_data['fpd_data']['fpd_remove_cart_item']) )
					$woocommerce->cart->set_quantity($cart_item_data['fpd_data']['fpd_remove_cart_item'], 0);

				//set quantity via fpd-plus
				if( isset($cart_item_data['fpd_data']['fpd_quantity']) )
					$woocommerce->cart->set_quantity($cart_item_key, intval($cart_item_data['fpd_data']['fpd_quantity']));

				do_action( 'fpd_wc_add_to_cart', $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			}

		}

		public function get_cart_item_from_session( $cart_item, $values ) {

	        //check for fpd data in session
	        if (isset($values['fpd_data'])) {
	            $cart_item['fpd_data'] = $values['fpd_data'];
	        }

			//check if cart item is fancy product
	        if (isset($cart_item['fpd_data'])) {
	        	//add fpd data to cart item
	            $this->add_cart_item($cart_item);
	        }

	        return $cart_item;
	    }

		//meta data displayed after the title, key: value
	    public function get_item_data( $other_data, $cart_item ) {

		    if ( isset($cart_item['fpd_data']) && fpd_get_option('fpd_cart_show_element_props') == 'props' ) {

				//get fpd data
				$fpd_data = $cart_item['fpd_data'];
				

				if( isset($fpd_data['fpd_product']) && $fpd_data['fpd_product'] ) {

					$order = json_decode(stripslashes($fpd_data['fpd_product']), true);

					if(isset($order['product'])) {

						$views = $order['product'];

						//display relevant elements with props
						$display_elements = self::get_display_elements( $views );
						foreach($display_elements as $display_element) {

							array_push($other_data, array(
								'name' => '<span class="fpd-cart-item-meta-title">'.$display_element['title'].'</span>',
								'value' => $display_element['values']
							));

						}

						if( isset($order['bulkVariations']) ) {

							require_once(FPD_PLUGIN_DIR.'/inc/addons/class-bulk-variations.php');

							$bulk_variations = $order['bulkVariations'];
							
							if( !empty($bulk_variations) ) {
		
								foreach($bulk_variations as $variation) {
		
									array_push($other_data, array(
										'name' => FPD_Bulk_Variations::create_variation_string($variation['variation']),
										'value' => $variation['quantity']
									));
		
								}
		
							}
						}

					}

				}

			}

		    return $other_data;

	    }

	    public function set_cart_item_permalink( $permalink, $cart_item=null, $cart_item_key=null ) {

		    if ( !empty($permalink) && $cart_item && isset($cart_item['fpd_data']) && $cart_item['fpd_data'] ) {

				 $permalink = add_query_arg( array('cart_item_key' => $cart_item_key), $permalink );

			}
			// echo 'mzl set_cart_item_permalink permalink: ' . $permalink;

			return $permalink;

	    }

		public function reset_cart_item_link( $link, $cart_item, $cart_item_key ) {

			if ( !empty($link) && isset($cart_item['fpd_data']) && $cart_item['fpd_data'] ) {

				$url = '';
				//get href from a tag
				$dom = new DOMDocument;
				libxml_use_internal_errors(true);
				$dom->loadHTML( $link );
				$xpath = new DOMXPath( $dom );
				libxml_clear_errors();
				$doc = $dom->getElementsByTagName("a")->item(0);
				$href = $xpath->query(".//@href");
				//echo 'mzl reset_cart_item_link href: ' . $href;

				foreach ( $href as $s ) {
					$url = $s->nodeValue;

				}

				if( !empty($url) ) {

					//set again for WPML
					$url = add_query_arg( array('cart_item_key' => $cart_item_key), $url );
					echo 'mzl reset_cart_item_link url: ' . $url;

					$link = sprintf( '<a href="%s">%s<br /><i style="opacity: 1; font-size: 0.9em;">%s</i></a>', $url, $cart_item['data']->get_name(), FPD_Settings_Labels::get_translation( 'woocommerce', 'cart:_re-edit product' ) );

				}

				if( fpd_get_option('fpd_cart_show_element_props') === 'used_colors' ) {

					$fpd_data = $cart_item['fpd_data'];					
					$order = json_decode(stripslashes($fpd_data['fpd_product']), true);
					
					if(isset($order['product'])) {

						$views = $order['product'];
						$link .= '<div style="margin-top:10px;" class="fpd-wc-cart-element-colors fpd-clearfix">'.implode('',self::get_display_elements( $views, 'used_colors' )).'</div>';

					}
					

				}

			}

			return $link;

		}

		public function change_cart_item_thumbnail( $thumbnail, $cart_item = null ) {
			//echo '<div class="error"><p>'. 'change_cart_item_thumbnail' .'</p></div>';
			global $product;
			$product = $cart_item['data'];

			if( fpd_get_option('fpd_cart_custom_product_thumbnail') && !empty($thumbnail) && !is_null($cart_item) && isset($cart_item['fpd_data']) ) {
			//	echo esc_html( 'change_cart_item_thumbnail switch on ......' );
				$fpd_data = $cart_item['fpd_data'];

				//check if data contains the fancy product thumbnail				
		        if ( isset($fpd_data['fpd_product_thumbnail']) && !empty($fpd_data['fpd_product_thumbnail']) ) {
			// //		echo esc_html( 'change_cart_item_thumbnail isset fpd_data ......' );

		        	$dom = new DOMDocument;
					libxml_use_internal_errors(true);
					$dom->loadHTML( $thumbnail );
					$xpath = new DOMXPath( $dom );
					libxml_clear_errors();
					$doc = $dom->getElementsByTagName("img")->item(0);
					$src = $xpath->query(".//@src");
					$srcset = $xpath->query(".//@srcset");

					// todo mzl mod cart image
					foreach ( $src as $s ) {
						$s->nodeValue = $fpd_data['fpd_product_thumbnail'];
					}
					
					foreach ( $srcset as $s ) {						
						$s->nodeValue = $fpd_data['fpd_product_thumbnail'];
					}
					$dom->saveXML( $doc );
					//return $dom->saveXML( $doc );
					return $s->nodeValue;
					
					
					// $classstr = 'class="' . esc_attr( implode( ' ', wc_get_product_class( '', $product ) ) ) . '"';
					// $_div = '<div id="product-' . $cart_item['product_id'] . '" ' .
					// $classstr;
					// $_div .= '>';
					
					// do_action( 'woocommerce_before_single_product_summary' );
			
				
					// do_action( 'woocommerce_after_single_product' );
					// $_div .= '</div>';
					// return $_div;
		        }

			}

			return null;
		}

		public function after_cart() {

			//fix for WC 3.4, because wp_kses_post method is running over thumbnail that removes the data:
			?>
			<script type="text/javascript">
				jQuery(document).ready(function() {

					function setImageDataURI() {
						jQuery('.product-thumbnail img').each(function() {

							var $img = jQuery(this);

							if($img.attr('src') && $img.attr('src').substring(0, 5) !== 'data:' && $img.attr('src').substring(0, 4) !== 'http') {
								$img.attr('src', 'data:'+$img.attr('src'))
								.removeAttr('srcset');
							}

						});
					};

					jQuery( document.body ).on( 'updated_wc_div' , setImageDataURI);
					setImageDataURI();

				});
			</script>
			<?php

		}

		public function cart_item_price( $price, $cart_item, $cart_item_key ) {

			if ( isset($cart_item['fpd_data']) && $cart_item['fpd_data'] ) {

				$product = $cart_item['data'];

				$product_settings = new FPD_Product_Settings( is_a($product, 'WC_Product_Variation') ? $product->get_parent_id() : $product->get_id() );
				if( $product_settings->get_option('get_quote') ) {
					return '-';
				}

			}

			return $price;

		}

		//add custom quantity display to cart for fpd quantity
		public function set_quantity_input_field( $product_quantity, $cart_item_key, $cart_item=null ) {

		    if( $cart_item && isset($cart_item['fpd_data']) && isset($cart_item['fpd_data']['fpd_quantity']) ) {

				$product_quantity = '<div class="quantity"><span class="fpd-quantity">'.$cart_item['fpd_data']['fpd_quantity'].'</span></div>';

		    }

		    return $product_quantity;

		}

		public static function get_display_elements( $views, $format=1 ) {

			if( !is_array($views) )
				return array();

			$display_elements = array();

			foreach($views as $view) {

				$viewElements = $view['elements'];
				foreach($viewElements as $viewElement) {

					$elementParams = $viewElement['parameters'];
					if( isset($elementParams['isEditable']) && @$elementParams['isEditable'] ) {

						$values = array();

						//check if fill is set and if yes, look for a hex name
						if( isset($elementParams['fill']) && @$elementParams['fill'] && is_string($elementParams['fill']) ) {

							$element_colors = isset($elementParams['svgFill']) ? $elementParams['svgFill'] : array($elementParams['fill']);
							$color_display = self::color_display( $element_colors );

							if( !empty($color_display) ) {

								//show only used colors
								if( $format === 'used_colors' ) {
									$display_elements[] = $color_display;
									continue;
								}

								array_push($values, $color_display);

							}

						}

						//get font family and text size
						if( $format !== 'used_colors' && isset($elementParams['fontFamily']) && @$elementParams['fontFamily'] )
							array_push($values, $elementParams['fontFamily'].', '.intval($elementParams['fontSize']).'px' );

						//sku
						if( $format !== 'used_colors' && isset($elementParams['sku']) && !empty($elementParams['sku']) )
							array_push($values, FPD_Settings_Labels::get_translation( 'woocommerce', 'sku' ) . $elementParams['sku'] );

						if( sizeof($values) > 0 ) {

							$title = isset($elementParams['text']) ? $elementParams['text'] : $viewElement['title'];
							$display_elements[] = array(
								'title' => $title,
								'values' => implode(' ', $values)
							);

						}

					}

				}

			}

			return $display_elements;

		}

		public static function color_display( $color_arr ) {

			if( !is_array($color_arr) ) return '';

			$color_html = '';
			$color_arr = array_unique($color_arr);
			foreach($color_arr as $color_value) {

				$hex =  strtolower(str_replace('#', '', $color_value));
				$hex_name = fpd_get_hex_name($hex);
				$hex_title = empty($hex_name) ? '#'.$hex : $hex_name. ' - #'.$hex;

				$hex_title = apply_filters( 'fpd_wc_cart_item_color_name', $hex_title, $hex, empty($hex_name) ? null : $hex_name);

				if( !empty($hex_title) ) {

					$color_contrast = fpd_get_contrast_color($color_value);
					$color_html .= '<span class="fpd-cart-element-color" style="border-radius:4px;float: left;margin: 0 4px 6px 0; padding: 1px 3px; border: 1px solid #f2f2f2; white-space: nowrap; font-size: 11px;background: '.$color_value.'; color:'.$color_contrast.'">'.strtoupper($hex_title).'</span>';

				}


			}

			return $color_html;

		}

		public function after_cart_item_name( $cart_item, $cart_item_key ) {

			if ( isset($cart_item['fpd_data']) ) {

				$fpd_data = $cart_item['fpd_data'];

				if( isset($fpd_data['fpd_product']) ) {

					require_once(FPD_PLUGIN_DIR.'/inc/addons/class-names-numbers.php');

					$fpd_product = json_decode(stripslashes($fpd_data['fpd_product']), true);

					if(isset($fpd_product['product'])) {

						$views = $fpd_product['product'];

						FPD_Names_Numbers::display_names_numbers_items($views);

					}
					

				}

			}

	    }

	}
}

new FPD_WC_Cart();

?>