<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('FPD_Admin_Scripts_Styles') ) {

	class FPD_Admin_Scripts_Styles {

		const REACT_NO_CONFLICT_JS = 'window.lodash = _.noConflict(); window.underscore = _.noConflict();';

		public function __construct() {

			add_action( 'init', array( &$this, 'register'), 20 );
			add_action( 'admin_head', array( &$this, 'global_admin_head'), 100 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_styles_scripts' ), 1000 );
			add_action( 'admin_head-fancy-product-designer_page_fpd_ui_layout_composer', array( &$this, 'print_css_string' ), 100 );
			add_action( 'admin_footer', array( &$this, 'global_admin_footer' ) );

		}

		public function global_admin_head () {
			
			?>
			<script type="text/javascript">

				jQuery(document).ready(function() {

					/*----- NOTIFICATIONS ----------*/

					jQuery('body').on('click', '.fpd-dismiss-notification .notice-dismiss', function(evt) {

						evt.preventDefault();

						jQuery.ajax({
							url: '<?php echo admin_url('admin-ajax.php'); ?>',
							data: {
								action: 'fpd_dismiss_notification',
								_ajax_nonce: '<?php echo FPD_Admin::$ajax_nonce; ?>',
								name: this.value
							},
							type: 'post',
							dataType: 'json',
							success: function(data) {}
						});

						jQuery(this).parent('.fpd-dismiss-notification').remove();

					});

				});

			</script>
			<?php
		}

		public function register() {

			//register radykal css files
			wp_register_style( 'radykal-select2', plugins_url('/vendors/css/select2.min.css', __FILE__), false, '4.0.3' );
			wp_register_style( 'fpd-admin', plugins_url('/css/admin.css', __FILE__), array(
			), Fancy_Product_Designer::VERSION );

			//register radykal js files
			wp_register_script( 'radykal-select2', plugins_url('/vendors/js/select2.min.js', __FILE__), array( 'jquery' ), '4.0.3' );
			wp_register_script( 'radykal-select-sortable', plugins_url('/vendors/js/selectSortable.js', __FILE__), array( 'jquery' ), '1.0.0' );
			wp_register_script( 'fpd-admin', plugins_url('/js/admin.js', __FILE__), array(
				'jquery',
			), Fancy_Product_Designer::VERSION );

			wp_register_style( 'fpd-mdi', plugins_url('/vendors/mdi/css/materialdesignicons.min.css', __FILE__), '4.5.95' );
			wp_register_style( 'fpd-alertifyjs', plugins_url('/vendors/alertifyjs/css/alertify.min.css', __FILE__), '1.12.0' );
			wp_register_style( 'fpd-alertifyjs-default', plugins_url('/vendors/alertifyjs/css/themes/default.min.css', __FILE__), '1.12.0' );
			wp_register_style( 'fpd-semantic-ui', plugins_url('/vendors/semantic-ui/semantic.min.css', __FILE__), array(
				'fpd-mdi',
				'fpd-alertifyjs',
				'fpd-alertifyjs-default'
			) );

			wp_register_script( 'fpd-alertifyjs', plugins_url('/vendors/alertifyjs/alertify.min.js', __FILE__), '1.12.0' );
			wp_register_script( 'fpd-semantic-ui', plugins_url('/vendors/semantic-ui/semantic.js', __FILE__), array(
				'fpd-alertifyjs'
			) );

		}

		public function print_css_string() {

			//get css (colors)
			$selected_layout_id =  isset($_POST['fpd_selected_layout']) ? sanitize_key($_POST['fpd_selected_layout']) : 'default';
			$ui_layout = FPD_UI_Layout_Composer::get_layout($selected_layout_id);
			$css_str = FPD_UI_Layout_Composer::get_css_from_layout($ui_layout);

			if( !empty($css_str) )
				echo '<style type="text/css">'.$css_str.'</style>';

		}

		public function enqueue_styles_scripts( $hook ) {

			global $post, $pagenow, $typenow;

			$version = Fancy_Product_Designer::LOCAL ? time() : Fancy_Product_Designer::VERSION;

			//order viewer
			require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/order-viewer.php' );
			$order_viewer_opts = array(
				'labels' => json_encode( FPD_Labels_Order_Viewer::get_labels() ),
				'printReadyExportEnabled' => Fancy_Product_Designer::pro_export_enabled(),
				'options' => array(
					'enabled_fonts' => FPD_Fonts::to_data(FPD_Fonts::get_enabled_fonts())
				),
				'export_method' => get_option( 'fpd_pro_export_method', 'svg2pdf' )
			);

			wp_localize_script( 'fpd-admin', 'fpd_admin_opts', array(
					'adminAjaxUrl' => admin_url('admin-ajax.php'),
					'ajaxNonce' => FPD_Admin::$ajax_nonce,
					'adminUrl' => admin_url(),
					'localTest' => Fancy_Product_Designer::LOCAL
				)
			);

			//wc order details
			if(  $typenow === 'shop_order' || $hook === 'woocommerce_page_wc-orders' ) {

				wp_enqueue_style( 'fpd-react-order-viewer', plugins_url('/react-app/css/wc-order-viewer.css', __FILE__), array(
					'fpd-semantic-ui',
					'fpd-js'
				), $version );

				wp_enqueue_script( 'fpd-react-order-viewer', plugins_url('/react-app/js/wc-order-viewer.js', __FILE__), array(
					'fpd-semantic-ui',
					'fpd-admin',
					'fpd-js',
				), Fancy_Product_Designer::VERSION);

			}

			//edit post
		    if ( $hook == 'post-new.php' || $hook == 'post.php' ) {

		        //post/pages
		        wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'fpd-admin' );
				wp_enqueue_style( 'fpd-semantic-ui' );

				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'fpd-admin' );

				if( !wp_script_is('select2') && !wp_script_is('select2-avada-js') ) {
					wp_enqueue_style( 'radykal-select2' );
					wp_enqueue_script( 'radykal-select2' );
				}
				wp_enqueue_script( 'radykal-select-sortable' );

		    }

			//manage fancy products
		    if( $hook == 'toplevel_page_fancy_product_designer' ) {

			     require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/products.php' );

			    wp_enqueue_media();

				wp_enqueue_style( 'fpd-react-products', plugins_url('/react-app/css/products.css', __FILE__), array(
					'fpd-semantic-ui'
				), $version );

				wp_enqueue_script( 'fpd-react-products', plugins_url('/react-app/js/products.js', __FILE__), array(
					'jquery-ui-core',
					'jquery-ui-mouse',
					'jquery-ui-sortable',
					'jquery-ui-droppable',
					'fpd-semantic-ui',
					'fpd-admin'
				), $version );

				wp_add_inline_script( 'fpd-react-products', self::REACT_NO_CONFLICT_JS, 'after' );

				wp_localize_script( 'fpd-react-products', 'fpd_fancy_products_opts', array(
					'labels' => json_encode(FPD_Labels_Products::get_labels()),
					'productBuilderUri' => admin_url().'admin.php?page=fpd_product_builder',
					'currentUserId' => get_current_user_id(),
					'dokanUsers' => class_exists('WeDevs_Dokan') ? get_users( array('fields' => array('ID', 'user_nicename')) ) : null,
				));

			}

			//product builder
		    if( $hook == 'fancy-product-designer_page_fpd_product_builder' ) {

			    require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/product-builder.php' );

		    	wp_enqueue_media();

		    	wp_enqueue_style( 'fpd-react-product-builder', plugins_url('/react-app/css/product-builder.css', __FILE__), array(
			    	'radykal-select2',
			    	'fpd-js',
					'fpd-semantic-ui'
				), $version );

				wp_enqueue_script( 'fpd-react-product-builder', plugins_url('/react-app/js/product-builder.js', __FILE__), array(
					'jquery-ui-core',
					'jquery-ui-mouse',
					'jquery-ui-sortable',
					'jquery-ui-droppable',
					'fpd-semantic-ui',
					'radykal-select2',
					'fpd-admin',
					'fpd-js'
				), $version );

				wp_add_inline_script( 'fpd-react-product-builder', self::REACT_NO_CONFLICT_JS, 'after' );

				$script_options = FPD_Resource_Options::get_options(array(
					'fpd_common_parameter_originX',
					'fpd_common_parameter_originY',
					'fpd_uploadZonesTopped',
					'fpd_fabricjs_texture_size',
					'fpd_font',
					'enabled_fonts',
					'primary_layout_props',
					'design_categories',
					'fpd_custom_texts_parameter_maxFontSize',
					'fpd_custom_texts_parameter_patterns',
					'fpd_designs_parameter_patterns',
					'fpd_color_colorPickerPalette'
				));

				$script_options['color_lists'] = array(
					'none' => 'None'
				);

				$color_lists = json_decode( get_option( 'fpd_color_lists', '[]' ), true );

				if( is_array($color_lists) ) {

					foreach($color_lists as $key => $color_list) {
						$script_options['color_lists'][$key] = $color_list['name'];
					}

				}

				$script_options['templates_directory'] = plugins_url('/assets/templates/', FPD_PLUGIN_ROOT_PHP );
				$script_options['products'] = FPD_Resource_Products::get_products( array('limit' => -1) );
				$script_options['adminUrl'] = admin_url();
				$script_options['labels'] = FPD_Labels_Product_Builder::get_labels();

				wp_localize_script( 'fpd-react-product-builder', 'fpd_product_builder_opts', $script_options );

		    }

		    //ui composer
		    if( $hook == 'fancy-product-designer_page_fpd_ui_layout_composer' ) {

			    require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/ui-composer.php' );

				wp_enqueue_style( 'fpd-ui-layout-composer', plugins_url('/react-app/css/ui-composer.css', __FILE__), array(
					'fpd-semantic-ui',
					'fpd-admin',
					'fpd-js'
				), $version );

				wp_enqueue_script( 'fpd-ui-layout-composer', plugins_url('/react-app/js/ui-composer.js', __FILE__), array(
					'jquery-ui-core',
					'jquery-ui-mouse',
					'jquery-ui-sortable',
					'jquery-ui-droppable',
					'fpd-semantic-ui',
					'fpd-js',
					'fpd-admin'
				), $version );

				wp_add_inline_script( 'fpd-ui-layout-composer', self::REACT_NO_CONFLICT_JS, 'after' );

				$script_options = array(
					'labels' => json_encode(FPD_Labels_UI_Composer::get_labels()),
					'templates_directory' => plugins_url('/assets/templates/', FPD_PLUGIN_ROOT_PHP ),
					'languages' => FPD_Settings_Labels::get_active_lang_codes(),
					'dynamic_designs_modules' => fpd_get_option('fpd_dynamic_designs_modules'),
					'ui_theme' => fpd_get_option('fpd_ui_theme')
				);

				wp_localize_script( 'fpd-ui-layout-composer', 'fpd_ui_layout_composer_opts', $script_options );

			}

			//manage designs
		    if( $hook == 'fancy-product-designer_page_fpd_manage_designs') {

			    require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/designs.php' );

		    	wp_enqueue_media();

		    	wp_enqueue_style( 'fpd-react-designs', plugins_url('/react-app/css/designs.css', __FILE__), array(
					'fpd-semantic-ui'
				), $version );
                
                wp_enqueue_script( 'fpd-jquery-ui-sortable', plugins_url('/vendors/js/jquery-ui-sortable.min.js', __FILE__), array(
                    'jquery',
                ), '1.12.1' );

				wp_enqueue_script( 'fpd-nestedSortable', plugins_url('/vendors/js/jquery.mjs.nestedSortable.js', __FILE__), array(
					'fpd-admin',
				), $version );

				wp_enqueue_script( 'fpd-react-designs', plugins_url('/react-app/js/designs.js', __FILE__), array(
					'fpd-semantic-ui',
				), $version );

				wp_add_inline_script( 'fpd-react-designs', self::REACT_NO_CONFLICT_JS, 'after' );

				wp_localize_script( 'fpd-react-designs', 'fpd_fancy_designs_opts', array(
					'labels' => json_encode(FPD_Labels_Designs::get_labels()),
				));

		    }

		    //shortcode orders
		    if( $hook == 'fancy-product-designer_page_fpd_orders' ) {

				wp_enqueue_style( 'fpd-shortcode-orders', plugins_url('/react-app/css/shortcode-orders.css', __FILE__), array(
					'fpd-semantic-ui',
					'fpd-js'
				), $version );

				wp_enqueue_script( 'fpd-admin' );

				wp_enqueue_script( 'fpd-shortcode-orders', plugins_url('/react-app/js/shortcode-orders.js', __FILE__), array(
					'fpd-semantic-ui',
					'fpd-js'
				), $version );

				wp_add_inline_script( 'fpd-shortcode-orders', self::REACT_NO_CONFLICT_JS, 'after' );
				wp_localize_script( 'fpd-shortcode-orders', 'fpd_order_viewer_opts', $order_viewer_opts );

	        }

			//pricing rules
			if( $hook == 'fancy-product-designer_page_fpd_pricing_builder' ) {

				require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/pricing-rules.php' );

				wp_enqueue_style( 
					'fpd-react-pricing-rules', 
					plugins_url('/react-app/css/pricing-rules.css', __FILE__), 
					array(
						'fpd-semantic-ui'
					), 
					$version
				);

				wp_enqueue_script( 
					'fpd-react-pricing-rules', 
					plugins_url('/react-app/js/pricing-rules.js', __FILE__), 
					array(
						'fpd-semantic-ui',
						'fpd-admin',
						'jquery-ui-core',
						'jquery-ui-mouse',
						'jquery-ui-sortable',
						'jquery-ui-droppable',
					), 
					$version
				);

				wp_add_inline_script( 
					'fpd-react-pricing-rules', 
					FPD_Admin_Scripts_Styles::REACT_NO_CONFLICT_JS, 
					'after' 
				);

				wp_localize_script( 
					'fpd-react-pricing-rules', 
					'fpd_pricing_rules_opts', 
					array(
						'labels' 				=> json_encode(FPD_Labels_Pricing_Rules::get_labels()),
						'pricing_rules_groups' 	=> FPD_Resource_Pricing_Rules::get_pricing_rules(),
						'patterns'				=> array_merge(
							FPD_Settings_Default_Element_Options::get_pattern_urls(),
							FPD_Settings_Default_Element_Options::get_pattern_urls('svg')
						)
					)
				);

		    }

			//settings
			if( $hook == 'fancy-product-designer_page_fpd_settings') {

				require_once( FPD_PLUGIN_ADMIN_DIR . '/labels/settings.php' );

				wp_enqueue_media();

				wp_enqueue_style( 'fpd-react-settings', plugins_url('/react-app/css/settings.css', __FILE__), array(
					'fpd-semantic-ui'
				), $version );

				wp_enqueue_script( 'fpd-react-settings', plugins_url('/react-app/js/settings.js', __FILE__), array(
					'fpd-semantic-ui',
					'fpd-admin'
				), $version );

				wp_add_inline_script( 'fpd-react-settings', self::REACT_NO_CONFLICT_JS, 'after' );

				//deprecated: export addon
				$all_options = FPD_Settings::$radykal_settings->settings;
				if( isset($all_options['automated-export'])) {
					unset($all_options['automated-export']);
				}
				//--deprecated end

				wp_localize_script( 'fpd-react-settings', 'fpd_settings_opts', array(
					'labels' => FPD_Labels_Settings::get_labels(),
					'configs' => array(
						'all_options' 		=> $all_options,
						'option_tabs' 		=> array_keys($all_options),
						'option_blocks' 	=> FPD_Settings::$radykal_settings->block_titles,
						'enabled_fonts' 	=> FPD_Fonts::to_data(FPD_Fonts::get_enabled_fonts()),
						'custom_fonts' 		=> FPD_Settings_Fonts::get_custom_fonts(false),
						'custom_fonts_dir' 	=> content_url( 'uploads/fpd_fonts/'),
						'installed_3d_models' => FPD_3D_Preview::get_installed_models()
					),
				) );

			}

			//status
			if( $hook == 'fancy-product-designer_page_fpd_status') {

				wp_enqueue_style( 'fpd-semantic-ui' );
				wp_enqueue_style( 'fpd-admin' );

				wp_enqueue_script( 'fpd-semantic-ui' );
				wp_enqueue_script( 'fpd-admin' );
				wp_enqueue_script( 'fpd-js' );

				wp_enqueue_script( 'fpd-status-tools', plugins_url('/js/status-tools.js', __FILE__), false, $version );

				wp_localize_script( 'fpd-status-tools', 'fpd_status_tools_opts', array(
						'label_no_valid_url' => __('Entered string is not a valid URL!', 'radykal')
					)
				);

			}

			//status
			if( $hook == 'fancy-product-designer_page_fpd_genius') {

				wp_enqueue_style( 'fpd-semantic-ui' );
				wp_enqueue_style( 'fpd-admin' );

				wp_enqueue_script( 'fpd-semantic-ui' );
				wp_enqueue_script( 'fpd-admin' );

				wp_enqueue_script( 'fpd-genius', plugins_url('/js/genius.js', __FILE__), false, $version );

				wp_localize_script( 
					'fpd-genius', 
					'fpd_genius_opts', 
					array(
						
					)
				);

			}

			$order_id = 0;
			if( isset($_GET['post']) && $typenow == 'shop_order' ) //wc (HPOS disabled)
				$order_id = $_GET['post'];
			else if( isset($_GET['page']) && isset($_GET['id']) && $_GET['page'] == 'wc-orders') //wc (HPOS enabled)
				$order_id = $_GET['id'];
			else if( isset($_GET['lid']) ) //gravity form
				$order_id = $_GET['lid'];

			$order_id = intval( $order_id );

			$order_viewer_opts['order_id'] = $order_id;

			$order_type = 'wc';
			if( isset($_GET['page']) ) {

				if( $_GET['page'] == 'fpd_orders')
					$order_type = 'shortcode';
				else if( $_GET['page'] == 'gf_entries' )
					$order_type = 'gf';

			}

			$order_type = sanitize_key( $order_type );

			wp_add_inline_script( 'fpd-react-order-viewer', self::REACT_NO_CONFLICT_JS, 'after' );
			wp_localize_script( 'fpd-react-order-viewer', 'fpd_order_viewer_opts', $order_viewer_opts );

		}

		public function global_admin_footer () {
			// mzl fpd fpd_disable_usetiful
			// if( !fpd_get_option('fpd_disable_usetiful') ):
			// 	?>
			// 	<script>
			// 		(function (w, d, s) {
			// 			var a = d.getElementsByTagName('head')[0];
			// 			var r = d.createElement('script');
			// 			r.async = 1;
			// 			r.src = s;
			// 			r.setAttribute('id', 'usetifulScript');
			// 			r.dataset.token = "7147bc26ddfd1eb2397b9e43031503f5";
			// 								a.appendChild(r);
			// 		})(window, document, "https://www.usetiful.com/dist/usetiful.js");</script>
			// 	</script>
			// 	<?php
			// endif;

		}
	}
}

new FPD_Admin_Scripts_Styles();

?>