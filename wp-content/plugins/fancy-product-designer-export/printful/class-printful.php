<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if( !class_exists('FPD_Export_Printful') ) {

	class FPD_Export_Printful {

		private $current_variants = null;

		public function __construct() {

			//Global Settings
			add_filter( 'fpd_automated_export_settings', array( &$this,'register_settings' ) );
			add_filter( 'fpd_settings_blocks', array( &$this,'register_settings_block' ) );
			add_action( 'fpd_block_options_end', array(&$this, 'add_block_options') );

			add_filter( 'woocommerce_hidden_order_itemmeta', array( &$this, 'hide_order_item_meta' ) );

			$api_key = get_option( 'fpd_printful_api_key', '' );

			if( !empty($api_key) ) {

				require_once __DIR__ . '/class-printful-api.php';

				//--- BACKEND
				add_action( 'init', array(&$this, 'init') );
				add_action( 'fpd_post_meta_box_end', array(&$this, 'add_meta_box_handler') );
				add_action( 'woocommerce_before_order_itemmeta', array( &$this, 'add_printful_order_link' ), 20, 3 );

				//Ajax Handler
				add_action( 'wp_ajax_fpd_get_printful_products', array( &$this, 'ajax_get_printful_products' ) );
				add_action( 'wp_ajax_fpd_get_printful_product', array( &$this, 'ajax_get_printful_product' ) );
				add_action( 'wp_ajax_fpd_import_printful_product', array( &$this, 'ajax_import_printful_product' ) );

				//--- FRONTEND
				add_filter( 'fpd_designer_enabled', array(&$this, 'product_designer_enabled'), 20, 2 );
				add_filter( 'woocommerce_available_variation', array( &$this, 'set_variation_meta'), 20, 3 );
				add_action( 'fpd_after_product_designer', array( &$this, 'add_variation_handler') );

			}

		}

		public function init() {
            
            // $test = new FPD_Export_Printful_Api();
            // $res = $test->call('get_product', array('product_id' => 362));
            // var_dump($res);
            
		}

		//add options to automated export settings
		public function register_settings( $options ) {

			$options['printful'] = array(
				array(
					'title' 		=> __( 'API Key', 'radykal' ),
					'description' 		=> __( 'Enter the API key of your Printful store.', 'radykal' ),
					'id' 			=> 'fpd_printful_api_key',
					'default'		=> '',
					'type' 			=> 'password'
				),
				array(
					'title' 		=> __( 'Sales Profit', 'radykal' ),
					'description' 		=> __( 'Enter the sales profit. Either a fixed value that will be added to the net price (e.g. 5) or a percentage value (e.g. 15%) - net price * 0.15 = your profit.', 'radykal' ),
					'id' 			=> 'fpd_printful_profit',
					'default'		=> '',
					'type' 			=> 'text',
					'placeholder' 	=> __( 'For example: 5 or 15%', 'radykal' ),
				),
				array(
					'title' 		=> __( 'Region', 'radykal' ),
					'description' 		=> __( 'In which region are you going to sell the Printful products.', 'radykal' ),
					'id' 			=> 'fpd_printful_region',
					'default'		=> 'US',
					'type' 			=> 'select',
					'options'		=> array(
						'US'		=> __( 'USA', 'radykal' ),
						'EU'		=> __( 'Europe', 'radykal' ),
						'AU'		=> __( 'Australia/New Zealand', 'radykal' ),
						'CA'		=> __( 'Canada', 'radykal' ),
						'JP'		=> __( 'Japan', 'radykal' ),
						'MX'		=> __( 'Mexico', 'radykal' ),
						'worldwide'	=> __( 'Worldwide', 'radykal' ),
					)
				),
				array(
					'title' 		=> __( 'Enable Order Failure Mail', 'radykal' ),
					'description' 		=> __( 'If something goes wrong during the order process, the administrator will receive a mail with some details. Otherwise you can view any error in wp-content/fpd_php.log.', 'radykal' ),
					'id' 			=> 'fpd_printful_failure_admin_mail',
					'default'		=> 'yes',
					'type' 			=> 'checkbox'
				),
			);

			return $options;

		}

		//register settings block
		public function register_settings_block( $settings ) {

			$settings['automated-export']['printful'] = __('Printful', 'radykal');

			return $settings;

		}

		//display settings block
		public function add_block_options() {

			$options = FPD_Settings_Automated_Export::get_options();
			FPD_Settings::$radykal_settings->add_block_options( 'printful', $options['printful']);

		}


		public function add_meta_box_handler() {

			global $post;

			$api_key = get_option( 'fpd_printful_api_key', '' );

			if( $post->post_type == 'product' && !empty($api_key) ) :
			require_once __DIR__ . '/modal-products.php';

			?>
			<hr />
			<strong><?php _e('Printful', 'radykal'); ?></strong>
			<p>
				<button class="button button-secondary" id="fpd-printful-open-modal">
					<?php _e('Import Printful Product', 'radykal'); ?>
				</button>
			</p>
			<?php
			endif;

		}

		public function add_printful_order_link( $item_id, $item, $_product ) {

			$printful_order_dashboard_url = wc_get_order_item_meta( $item_id, '_fpd_printful_order_dashboard_url' );

			if( !empty($printful_order_dashboard_url) ):

				if( $printful_order_dashboard_url == 'process' || $printful_order_dashboard_url == 'failed' ) :

					$txt = $printful_order_dashboard_url == 'process' ? __( 'Printful order process queued...', 'radykal' ) : __( 'Printful order process failed.', 'radykal' );

	 				?><p><?php echo $txt; ?></p><?php

				else :
					?>
						<p><a href="<?php echo esc_attr( $printful_order_dashboard_url ); ?>" target="_blank" class="button button-secondary">
							<?php _e('View Printful Order', 'radykal'); ?>
						</a></p>
					<?php
				endif;

			endif;

		}

		public function hide_order_item_meta( $meta_keys ) {

			array_push($meta_keys, '_fpd_printful_order_dashboard_url');

			return $meta_keys;

		}

		public function ajax_get_printful_products() {

			$printful_api = new FPD_Export_Printful_Api();

			echo json_encode($printful_api->call('get_products'));

			die;

		}

		public function ajax_get_printful_product() {
			echo 'mzl ajax_get_printful_product';
			if ( !isset($_POST['product_id']) ) die;

			$printful_api = new FPD_Export_Printful_Api();

			echo json_encode( $printful_api->call('get_product', array(
				'product_id' => $_POST['product_id'],
			) ) );

			die;

		}

		public function ajax_import_printful_product() {

			if ( !isset($_POST['post_id']) || !isset($_POST['printful_product_id']) ) die;

			$include_colors = isset($_POST['include_colors']) ? $_POST['include_colors'] : null;
			$include_sizes = isset($_POST['include_sizes']) ? $_POST['include_sizes'] : null;

			$success = $this->import_printful_product(
				$_POST['post_id'],
				$_POST['printful_product_id'],
				$include_colors,
				$include_sizes
			);

			$response = array(
				'post_id' => $_POST['post_id']
			);

			if( $success ) {
				$response['post_url'] = admin_url('post.php?post='.$_POST['post_id'].'&action=edit');
			}
			else {
				$response['error'] = __( 'Something went wrong. Please try again!', 'radykal' );
			}

			echo json_encode( $response );

			die;

		}

		//show product designer when variable product has printful variants
		public function product_designer_enabled( $enabled, $post_id ) {

			if( !is_admin() && get_post_meta( $post_id, '_fpd_printful_product', true ) == '1') {
				return true;
			}

			return $enabled;

		}

		//add variation product id to variation attributes
		public function set_variation_meta( $attrs, $instance, $variation ) {

			$templates = $variation->get_meta('fpd_printful_templates');
			if(!empty($templates))
				$attrs['fpd_printful_templates'] = $templates;

			return $attrs;

		}

		public function add_variation_handler() {

			global $product;

			if( !$product || get_post_meta( $product->get_id(), '_fpd_printful_product', true ) != '1')
				return;

			?>
			<script type="text/javascript">

				jQuery(document).ready(function() {

					var currentTemplates = null,
						cartOrderLoad = <?php echo FPD_Frontend_Product::$form_views === null ? 0 : 1; ?>;

					jQuery('[name="variation_id"]:first').parents('form:first')
					.on('show_variation', function(evt, variation) {

						if(jQuery.isArray(variation.fpd_printful_templates)) {

							currentTemplates = variation.fpd_printful_templates;

							//if loading product from cart or order, ignore first variation load
							if(cartOrderLoad) {
								cartOrderLoad = false;
								return;
							}

							var fpdProduct = [];
							currentTemplates.forEach(function(template) {

								var scaling = FPDUtil.getScalingByDimesions(
									template.template_width,
									template.template_height,
									1000,
									1000
								);

								scaling = scaling > 1 ? 1 : scaling;

								var printingBox = {
									left: template.print_area_left * scaling,
									top: template.print_area_top * scaling,
									x: template.print_area_left * scaling, //for bb
									y: template.print_area_top * scaling, //for bb
									width: template.print_area_width * scaling,
									height: template.print_area_height * scaling
								};

								var fpdView = {
									title: template.name,
									thumbnail: template.image_url,
									options: {
										usePrintingBoxAsBounding: true,
										stageWidth: template.template_width * scaling,
										stageHeight: template.template_height * scaling,
										customImageParameters: {
											price: template.additional_price ? parseFloat(template.additional_price) : 0,
											boundingBox: printingBox
										},
										customTextParameters: {
											price: template.additional_price ? parseFloat(template.additional_price) : 0,
											boundingBoxMode: "clipping",
											boundingBox: printingBox
										},
										printingBox: printingBox,
										output: {
											width: template.printfile.width,
											height: template.printfile.height,
											dpi: template.printfile.dpi
										}
									},
									elements: [
										{
											title: template.name,
											source: template.image_url,
											type: 'image',
											parameters: {
												left: 0,
												top: 0,
												originX: 'left',
												originY: 'top',
												scaleX: 1,
												scaleY: 1,
												boundingBoxMode: 'none',
												excludeFromExport: true,
												topped: true
											}
										}
									]
								};

								if(template.additional_price) {
									fpdView.options.maxPrice = parseFloat(template.additional_price);
								}

								fpdProduct.push(fpdView);

							})

							fancyProductDesigner.loadProduct(fpdProduct, true);

						}

					})

					$selector.on('viewSelect', function(evt, viewIndex) {

						if(currentTemplates
							&& currentTemplates[viewIndex]
							&& currentTemplates[viewIndex].color_code) {

							jQuery('.fpd-main-wrapper')
							.css({
								'cssText': 'background-color: '+currentTemplates[viewIndex].color_code + '!important'
							});

						}

					})

				});

			</script>
			<?php

		}

		private function import_printful_product( $post_id, $printful_product_id, $include_colors=null, $include_sizes=null ) {

			$printful_api = new FPD_Export_Printful_Api();

			$printful_product = $printful_api->call('get_product', array(
				'product_id' 		=> $printful_product_id,
				'include_colors' 	=> $include_colors,
				'include_sizes' 	=> $include_sizes,
			) );

			if( $printful_product && isset($printful_product['variants']) ) {

				//upload featured image
				$attachment_id = fpd_admin_upload_image_media( $printful_product['product']['image'], true );

				$product_data = array(
					'name' 			=> $printful_product['product']['model'],
					'description' 	=> $printful_product['product']['description'],
					'attachment_id'	=> $attachment_id,
				);

				//delete existing variations
				$data_store = WC_Data_Store::load( 'product-variable' );
				$data_store->delete_variations( $post_id, true );

				$variable_product = $this->create_variable_product( $post_id, $product_data );

				$color_terms = array_unique( array_diff( array_column($printful_product['variants'], 'color'), [null] ) );
				$size_terms =  array_unique( array_diff( array_column($printful_product['variants'], 'size'), [null] ) );

				$atts = [];

				if( !empty($color_terms) )
					$atts[] = $this->create_attributes('Color', $color_terms);

				if( !empty($size_terms) )
					$atts[] = $this->create_attributes('Size', $size_terms);

				$variable_product->set_attributes( $atts );
				$variable_product->save();

				if( !empty($printful_product['variants']) ) {
					$this->current_variants = $printful_product['variants'];
					$this->create_variation_loop( $variable_product, 0);
				}

				$variable_product->save();

				return true;

			}

			return false;

		}

		private function create_variation_loop( $variable_product, $count ) {

			if( isset($this->current_variants[$count]) ) {

				$variant = $this->current_variants[$count];
				$values = array();

				if( isset($variant['color']) && !is_null($variant['color']) )
					$values['color'] = $variant['color'];

				if( isset($variant['size']) && !is_null($variant['size']) )
					$values['size'] = $variant['size'];

				$price = $variant['price'];
				$profit = get_option('fpd_printful_profit', '');

				if( !empty($profit) ) {

					if(strpos($profit, '%') !== false) {
						$price = $price * (1 + ( floatval(trim(str_replace('%', '', $profit))) / 100));
					}
					else {
						$price = $price + floatval($profit);
					}

				}

				$meta_data = array(
					'price' 		=> $price,
					'templates' 	=> $variant['templates'],
					'variant_id'	=> $variant['id']
				);

				$this->create_variations( $variable_product->get_id(), $values, $meta_data );

				$count++;
				$this->create_variation_loop( $variable_product, $count );

			}

		}

		private function create_variable_product( $post_id, $product_data) {

			if (!$post_id)
		    	return false;

		    $post = array( // Set up the basic post data to insert for our product
				'ID' => $post_id,
				'post_title'   => $product_data['name'],
		        'post_content' => $product_data['description'],
		        'post_name' => sanitize_title( $product_data['name'] ),
		        'post_type'    => 'product',
		        'meta_input'   => array(
			        '_fpd_printful_product' => true
		        )
		    );

		    wp_insert_post($post); // Insert the post returning the new post id
		    wp_set_object_terms($post_id, 'variable', 'product_type'); // Set it to a variable product type
		    set_post_thumbnail( $post_id, $product_data['attachment_id'] );
		    $product = new WC_Product_Variable();
			$product->set_id($post_id);
			return $product;

		}

		private function create_attributes( $name, $options ){

			$attribute = new WC_Product_Attribute();
			$attribute->set_id(0);
			$attribute->set_name($name);
			$attribute->set_options($options);
			$attribute->set_visible(true);
			$attribute->set_variation(true);

			return $attribute;

		}

		private function create_variations( $product_id, $values, $data ){

			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );
			$variation->set_attributes($values);
			$variation->set_status('publish');
			$variation->set_price($data['price']);
			$variation->set_regular_price($data['price']);
			$variation->set_stock_status();
			$variation->add_meta_data('fpd_printful_templates', $data['templates']);
			$variation->add_meta_data('fpd_printful_variant_id', $data['variant_id']);
			$variation->save();

		}

	}

}

new FPD_Export_Printful();