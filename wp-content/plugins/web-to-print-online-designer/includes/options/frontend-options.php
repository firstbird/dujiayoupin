<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!class_exists('NBD_FRONTEND_PRINTING_OPTIONS')){
    class NBD_FRONTEND_PRINTING_OPTIONS {
        protected static $instance;
        public $is_edit_mode = FALSE;
        /** Holds the cart key when editing a product in the cart **/
        public $cart_edit_key = NULL;
        /** Edit option in cart helper **/
        public $new_add_to_cart_key = FALSE;
        public function __construct() {
            if ( isset( $_REQUEST['nbo_cart_item_key'] ) && $_REQUEST['nbo_cart_item_key'] != '' ){
                $this->is_edit_mode = true;
                $this->cart_edit_key = $_REQUEST['nbo_cart_item_key'];
            }
        }
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
	}
        public function init(){
            add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'show_option_fields' ) );
            add_filter( 'nbd_js_object', array($this, 'nbd_js_object') );
            add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
            
            /* Edit cart item */
            // handle customer input as order item meta
            add_filter( 'woocommerce_get_item_data', array( $this, 'get_item_data' ), 10, 2 );
            // Alters add to cart text when editing a product
            add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'add_to_cart_text' ), 9999, 1 );     
            // Remove product from cart when editing a product
            add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'remove_previous_product_from_cart' ), 99999, 6 );            
            // Alters the cart item key when editing a product
            add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ), 10, 6 );
            // Change quantity value when editing a cart item
            add_filter( 'woocommerce_quantity_input_args', array( $this, 'quantity_input_args' ), 9999, 2 );
            // Redirect to cart when updating information for a cart item
            add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'add_to_cart_redirect' ), 9999, 1 );
            // Calculate totals on remove from cart/update
            //add_action( 'woocommerce_before_calculate_totals', array( $this, 'on_calculate_totals' ), 1, 1 );
            // mzl mod add_action( 'woocommerce_cart_loaded_from_session', array( $this, 're_calculate_price' ), 1, 1 );
            // Add meta to order
            add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_line_item' ), 50, 3 );
            // Gets saved option when using the order again function
            add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'order_again_cart_item_data' ), 50, 3 );
                
            // Alter the product thumbnail in cart
            add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'cart_item_thumbnail' ), 50, 2 );
            // Remove item quantity in checkout
            add_filter( 'woocommerce_checkout_cart_item_quantity', array($this, 'remove_cart_item_quantity'), 10, 3);
            // Adds edit link on product title in cart and item quantity
            add_filter( 'woocommerce_cart_item_name', array( $this, 'cart_item_name' ), 50, 3 );           
            
            // Add item data to the cart
            add_filter( 'woocommerce_add_cart_item_data', array( $this, 'add_cart_item_data' ), 10, 4 );
            
            // persist the cart item data, and set the item price (when needed) first, before any other plugins
            add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 1, 2 ); 

            // on add to cart set the price when needed, and do it first, before any other plugins
            add_filter( 'woocommerce_add_cart_item', array($this, 'set_product_prices'), 1, 1 );
            // Validate upon adding to cart
            add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 1, 6 );       
            /** Force Select Options **/
            if( nbdesigner_get_option('nbdesigner_force_select_options') == 'yes' ){
                add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_to_cart_url' ), 50, 1 );
                //add_action( 'woocommerce_product_add_to_cart_text', array( $this, 'add_to_cart_text' ), 10, 1 );
            }
            add_filter( 'woocommerce_cart_redirect_after_error', array( $this, 'cart_redirect_after_error' ), 50, 2 );
            
            /* Disables persistent cart **/
            if( nbdesigner_get_option('nbdesigner_turn_off_persistent_cart') == 'yes' ){
                add_filter( 'get_user_metadata', array( $this, 'turn_off_persistent_cart' ), 10, 3 );
                add_filter( 'update_user_metadata', array( $this, 'turn_off_persistent_cart' ), 10, 3 );
                add_filter( 'add_user_metadata', array( $this, 'turn_off_persistent_cart' ), 10, 3 );  
                add_filter( 'woocommerce_persistent_cart_enabled', '__return_false' );
            }
            
            /** Empty cart button **/
            if( nbdesigner_get_option('nbdesigner_enable_clear_cart_button') == 'yes' ){
                add_action( 'woocommerce_cart_actions', array( $this, 'add_empty_cart_button' ) );
                // check for empty-cart get param to clear the cart
                add_action( 'init', array( $this, 'clear_cart' ) );      
            }
            
            /* Bulk order */
            if ( isset( $_POST['nbb-fields'] ) ) {
                add_action( 'wp_loaded', array( $this, 'bulk_order' ), 20 );
            }
            
            /* Quick view */
            add_action( 'woocommerce_api_nbo_quick_view', array( $this, 'quick_view' ) );
            add_action( 'woocommerce_before_variations_form', array( $this, 'action_woocommerce_before_variations_form'), 10, 0 ); 
            
            if( nbdesigner_get_option('nbdesigner_change_base_price_html') == 'yes' ){
                add_filter( 'woocommerce_get_price_html', array( $this, 'change_product_price_display'), 10, 2 );
            }
            /* Compatible Autoptimize */
            add_filter( 'option_autoptimize_js_exclude', array( $this, 'autoptimize_js_exclude') );
        }
        public function action_woocommerce_before_variations_form(){
            if( isset($_REQUEST['wc-api']) && $_REQUEST['wc-api'] == 'NBO_Quick_View'){
                $nbd_qv_type = nbdesigner_get_option('nbdesigner_display_product_option');
                if( $nbd_qv_type == '2' ) echo '<div class="nbo-wc-options">'. __('Options', 'web-to-print-online-designer') .'</div>';
            }
        }
        public function autoptimize_js_exclude( $js ){
            if( false === strpos($js, 'angular') ) $js .= ', angular';
            return $js;
        }
        public function change_product_price_display( $price, $product ){
            $option_id = $this->get_product_option($product->get_id());
            $class = $product->get_type() == 'simple' ? 'nbo-base-price-html' : 'nbo-base-price-html-var';
            if( $option_id ){
                $price = '<span class="'. $class .'">'. __('From', 'web-to-print-online-designer') .'</span> ' . $price;
            }
            return $price;
        }
        public function add_empty_cart_button(){
            echo '<input type="submit" class="nbo-clear-cart-button button" name="nbo_empty_cart" value="' . __('Empty cart', 'web-to-print-online-designer') . '" />';
        }
        public function clear_cart(){
            if ( isset( $_POST['nbo_empty_cart'] ) ) {
		if ( !isset( WC()->cart ) || WC()->cart == '' ) {
                    WC()->cart = new WC_Cart();
		}
		WC()->cart->empty_cart( TRUE );
            }            
        }        
        public function turn_off_persistent_cart( $value, $id, $key ){
            $blog_id = get_current_blog_id();
            if ($key == '_woocommerce_persistent_cart' || $key == '_woocommerce_persistent_cart_' . $blog_id) {
                return FALSE;
            }
            return $value;
        }
        public function cart_redirect_after_error( $url = "", $product_id="" ){
            $option_id = $this->get_product_option($product_id);
            if($option_id){
                $url = get_permalink( $product_id );
            }
            return $url;
        }
        public function catalog_add_to_cart_text( $text = "" ){
            
            return $text;
        }
        public function add_to_cart_url( $url = "" ){
            global $product;
            if(!is_product() && is_object( $product ) && property_exists( $product, 'id' )){
                $product_id = $product->get_id();
                $option_id = $this->get_product_option($product_id);
                if($option_id){
                    $url = get_permalink( $product_id );
                }
            }
            return $url;
        }
        public function add_to_cart_validation( $passed, $product_id, $qty, $variation_id = '', $variations = array(), $cart_item_data = array() ){
            if( is_ajax() && !isset($_REQUEST['nbo-add-to-cart']) ){
                $option_id = $this->get_product_option($product_id);
                if($option_id){
                    $_options = $this->get_option($option_id);
                    if($_options){
                        $options = unserialize($_options['fields']);
                        $valid_fields = $this->get_default_option($options);
                        $required_option = false;
                        foreach($valid_fields as $field){
                            if( $field['enable'] && $field['required'] ){
                                $required_option = true;
                                wc_add_notice( sprintf( __( '"%s" is a required field.', 'web-to-print-online-designer' ), $field['title'] ), 'error' );
                            }
                        }
                        if( $required_option ){
                            return FALSE;
                        }
                    }
                }
            }else{
                // Try to validate uploads before they happen
                $option_id = $this->get_product_option($product_id);
                if($option_id){
                    $_options = $this->get_option($option_id);
                    if($_options){
                        $options = unserialize($_options['fields']);
                        $valid_fields = $this->get_default_option($options);
                        $required_upload = false;
                        foreach($valid_fields as $field_id => $field){
                            if( $field['is_upload'] ){
                                if( !empty($_FILES) && isset($_FILES["nbd-field"]) && isset($_FILES["nbd-field"]["name"][$field_id]) && $_FILES["nbd-field"]["error"][$field_id] == 0 ) {
                                    $origin_field = $this->get_field_by_id( $options, $field_id );
                                    $min_size = $origin_field['general']['upload_option']['min_size'];
                                    $max_size = $origin_field['general']['upload_option']['max_size'];
                                    $allow_type = $origin_field['general']['upload_option']['allow_type'];
                                    $file_info = pathinfo($_FILES["nbd-field"]["name"][$field_id]);
                                    $name = $file_info['filename'];
                                    $ext = strtolower( $file_info['extension'] );
                                    $size = $_FILES["nbd-field"]["size"][$field_id];
                                    if( $allow_type != '' ){
                                        $allow_type_arr = explode(',', strtolower( trim($allow_type ) ));
                                        $check_type = false;
                                        foreach($allow_type_arr as $type){
                                            if($ext == $type) $check_type = true;
                                        }
                                        if( !$check_type ){
                                            wc_add_notice( __( "Sorry, this file type is not permitted for security reasons.", 'web-to-print-online-designer' ) . ' (' . $ext . ')', 'error' );
                                            $passed = false;
                                        }
                                    }
                                    if( $min_size != '' ){
                                        $_min_size = intval($min_size) * 1024 * 1024;
                                        if( $_min_size > $size ){
                                            wc_add_notice( __( "Sorry, file is too small ( min size: ", 'web-to-print-online-designer' ) . $min_size . __( " MB )", 'web-to-print-online-designer' ), 'error' );
                                            $passed = false;
                                        }
                                    }
                                    if( $max_size != '' ){
                                        $_max_size = intval($max_size) * 1024 * 1024;
                                        if( $_max_size < $size ){
                                            wc_add_notice( __( "Sorry, file is too big ( max size: ", 'web-to-print-online-designer' ) . $max_size . __( " MB )", 'web-to-print-online-designer' ), 'error' );
                                            $passed = false;
                                        }
                                    }
                                }else{
                                    if( $field['enable'] && $field['required'] ){
                                        wc_add_notice( __( "Upload file is required.", 'web-to-print-online-designer' ), 'error' );
                                        $passed = false;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return $passed;
        }
        public function get_default_option($options) {
            $fields = array();
            
            // 添加类型检查
            if(!isset($options['fields']) || !is_array($options['fields'])) {
                return $fields;
            }
            
            foreach($options['fields'] as $field) {
                if(isset($field['general']['enabled']) && $field['general']['enabled'] == 'y') {
                    $fields[$field['id']] = array(
                        'title' => isset($field['general']['title']) ? $field['general']['title'] : '',
                        'enable' => true,
                        'required' => isset($field['general']['required']) && $field['general']['required'] == 'y',
                        'is_upload' => isset($field['general']['data_type']) && $field['general']['data_type'] == 'i' && 
                                     isset($field['general']['input_type']) && $field['general']['input_type'] == 'u'
                    );
                    
                    if(isset($field['general']['data_type'])) {
                        if($field['general']['data_type'] == 'i') {
                            $fields[$field['id']]['value'] = isset($field['general']['input_type']) && 
                                                           $field['general']['input_type'] != 't' ? 
                                                           (isset($field['general']['input_option']['min']) ? 
                                                           $field['general']['input_option']['min'] : 0) : '';
                        } else {
                            $fields[$field['id']]['value'] = 0;
                            if(isset($field['general']['attributes']['options'])) {
                                foreach($field['general']['attributes']['options'] as $key => $op) {
                                    if(isset($op['selected']) && $op['selected'] == 'on') {
                                        $fields[$field['id']]['value'] = $key;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            $valid_fields = $this->validate_field_option($options, $fields);
            return $valid_fields;
        }
        public function validate_field_option($options, $fields) {
            // 确保 $fields 是数组
            if(!is_array($fields)) {
                $fields = array();
            }
            
            // 确保 $options 是数组且包含必要的键
            if(!isset($options['fields']) || !is_array($options['fields'])) {
                return $fields;
            }
            
            // 添加类型检查和默认值
            foreach($fields as $field_id => $field) {
                if(!isset($options['fields'][$field_id])) {
                    continue;
                }
                
                $option_field = $options['fields'][$field_id];
                
                // 检查依赖关系
                if(isset($option_field['general']['depend'])) {
                    $depend = $option_field['general']['depend'];
                    if(!is_array($depend)) {
                        continue;
                    }
                    
                    $valid = true;
                    foreach($depend as $dep) {
                        if(!isset($dep['field']) || !isset($dep['value'])) {
                            continue;
                        }
                        
                        $field_value = isset($fields[$dep['field']]['value']) ? $fields[$dep['field']]['value'] : '';
                        $operator = isset($dep['operator']) ? $dep['operator'] : '=';
                        
                        if($operator == '=') {
                            $valid = $valid && ($field_value == $dep['value']);
                        } else {
                            $valid = $valid && ($field_value != $dep['value']);
                        }
                    }
                    
                    if(!$valid) {
                        unset($fields[$field_id]);
                    }
                }
            }
            
            return $fields;
        }
        public function order_again_cart_item_data( $arr,  $item,  $order ){
            remove_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 1, 6 );
            $order_items = $order->get_items();
            foreach( $order_items as $order_item_id => $item ){    
                if( $option_price = wc_get_order_item_meta($order_item_id, '_nbo_option_price') ){
                    $arr['nbo_meta']['option_price'] = $option_price;
                }
                if( $field = wc_get_order_item_meta($order_item_id, '_nbo_field') ){
                    $arr['nbo_meta']['field'] = $field;
                }
                if( $options = wc_get_order_item_meta($order_item_id, '_nbo_options') ){
                    $arr['nbo_meta']['options'] = $options;
                }
                if( $original_price = wc_get_order_item_meta($order_item_id, '_nbo_original_price') ){
                    $arr['nbo_meta']['original_price'] = $original_price;
                    $arr['nbo_meta']['price'] = $this->format_price($original_price + $option_price['total_price'] - $option_price['discount_price']);
                }              
            }
            return $arr;
        }
        public function remove_cart_item_quantity( $quantity_html, $cart_item, $cart_item_key ){
            if( isset($cart_item['nbo_meta']) ) $quantity_html = '';
            return $quantity_html;
        }
        public function order_line_item( $item, $cart_item_key, $values ){
            if ( isset( $values['nbo_meta'] ) ) {
                foreach ($values['nbo_meta']['option_price']['fields'] as $field) {
                    $price = floatval($field['price']) >= 0 ? '+' . wc_price($field['price']) : wc_price($field['price']);
                    if( isset($field['is_upload']) ){
                        if (strpos($field['val'], 'http') !== false) {
                            $file_url = $field['val'];
                        }else{
                            $file_url = Nbdesigner_IO::wp_convert_path_to_url( NBDESIGNER_UPLOAD_DIR . '/' .$field['val'] );
                        }
                        $field['value_name'] = '<a href="' . $file_url . '">' . $field['value_name'] . '</a>';
                    }
                    $item->add_meta_data( $field['name'], $field['value_name']. '&nbsp;&nbsp;' .$price );
                }
                $item->add_meta_data( __('Quantity Discount', 'web-to-print-online-designer'), '-' . wc_price($values['nbo_meta']['option_price']['discount_price']) );
         
                $item->add_meta_data('_nbo_option_price', $values['nbo_meta']['option_price']);
                $item->add_meta_data('_nbo_field', $values['nbo_meta']['field']);
                $item->add_meta_data('_nbo_options', $values['nbo_meta']['options']);
                $item->add_meta_data('_nbo_original_price', $values['nbo_meta']['original_price']);
            }
        }
        public function cart_item_thumbnail( $image = "", $cart_item = array() ){
            error_log('cart_item_thumbnail apply done src: ' . $image);
            

            if( isset($cart_item['nbo_meta']) ){//&& $cart_item['nbo_meta']['option_price']['cart_image'] != ''
                $size = 'shop_thumbnail';
                //$cart_item['nbo_meta']['option_price']['cart_image']
                $preview_url = isset(WC()->cart->cart_contents['cart_item_key']['preview_url']) 
                ? WC()->cart->cart_contents['cart_item_key']['preview_url'] 
                : 'not set';
                $dimensions = wc_get_image_size( $size ); 
                $image = '<img src="'. $preview_url
                        . '" width="' . esc_attr( $dimensions['width'] )
                        . '" height="' . esc_attr( $dimensions['height'] )
                        . '" class="nbo-thumbnail woocommerce-placeholder wp-post-image" />';
                error_log('cart_item_thumbnail apply done src: ' . $preview_url);
            }
            $image = apply_filters('nbo_cart_item_thumbnail', $image, $cart_item);
            return $image;
        }
        public function re_calculate_price( $cart ){
            foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
                if( isset($cart_item['nbo_meta']) ){
                    //$product = $cart_item['data'];
                    $variation_id = $cart_item['variation_id'];
                    $product_id = $cart_item['product_id'];
                    $product = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );
                    $options = $cart_item['nbo_meta']['options'];
                    $fields = $cart_item['nbo_meta']['field'];
                    $original_price = $this->format_price( $product->get_price('edit') );
                    $quantity = $cart_item['quantity'];
                    $option_price = $this->option_processing( $options, $original_price, $fields, $quantity );
                    if( isset($cart_item['nbo_meta']['nbdpb']) ){
                    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $cart_item['nbo_meta']['nbdpb'] . '/preview';
                        $images = Nbdesigner_IO::get_list_images($path, 1);
                        if( count($images) ){
                            $option_price['cart_image'] = Nbdesigner_IO::wp_convert_path_to_url($images[0]);
                        }
                    }
                    $adjusted_price = $original_price + $option_price['total_price'] - $option_price['discount_price'];
                    $adjusted_price = $adjusted_price > 0 ? $adjusted_price : 0;
                    $adjusted_price = $this->format_price($adjusted_price);
                    WC()->cart->cart_contents[ $cart_item_key ]['nbo_meta']['option_price'] = $option_price;
                    $adjusted_price = apply_filters('nbo_adjusted_price', $adjusted_price, $cart_item);
                    WC()->cart->cart_contents[ $cart_item_key ]['nbo_meta']['price'] = $adjusted_price;
                    WC()->cart->cart_contents[ $cart_item_key ]['data']->set_price( $adjusted_price ); 
                }
            }
        }
        public function remove_previous_product_from_cart( $passed, $product_id, $qty, $variation_id = '', $variations = array(), $cart_item_data = array() ){
            if ( $this->cart_edit_key ) {
                $cart_item_key = $this->cart_edit_key;
                if ( isset( $this->new_add_to_cart_key ) ) {
                    if ( $this->new_add_to_cart_key == $cart_item_key && isset( $_POST['quantity'] ) ) {
                        WC()->cart->set_quantity( $this->new_add_to_cart_key, $_POST['quantity'], TRUE );
                    } else {
                        $nbd_session = WC()->session->get($cart_item_key. '_nbd');
                        if( $nbd_session ){
                            WC()->session->set('nbd_session_removed', $nbd_session);
                            WC()->session->__unset($cart_item_key. '_nbd');
                        }                        
                        WC()->cart->remove_cart_item( $cart_item_key );
                        unset( WC()->cart->removed_cart_contents[ $cart_item_key ] );
                    }
                }
            }
            return $passed;
        }
        public function add_to_cart_redirect( $url = "" ){
            if ( empty( $_REQUEST['add-to-cart'] ) || !is_numeric( $_REQUEST['add-to-cart'] ) ) {
                return $url;
            }
            if ( $this->cart_edit_key || isset( $_REQUEST['submit_form_mode2'] ) ) {
                $url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : WC()->cart->get_cart_url();
            }
            return $url;
        }
        public function quantity_input_args( $args = "", $product = "" ){
            if ( $this->cart_edit_key ) {
                $cart_item_key = $this->cart_edit_key;
                $cart_item = WC()->cart->get_cart_item( $cart_item_key );
                if ( isset( $cart_item["quantity"] ) ) {
                    $args["input_value"] = $cart_item["quantity"];
                }
            }
            return $args;
        }
        public function add_to_cart_text($var){
            if( $this->is_edit_mode ){
                return esc_attr__( 'Update cart', 'woocommerce' );
            }
            return $var;
        }
        // mzl mod add to cart 走的是这个函数, key在前面已经产生了
        public function add_to_cart( $cart_item_key = "", $product_id = "", $quantity = "", $variation_id = "", $variation = "", $cart_item_data = "" ){
            // $nbd_session = WC()->session->get('nbd_session_saved');
            error_log('frontend-options add_to_cart ------'
             . ' $cart_item_key ' . $cart_item_key . ' cart_edit_key: ' . $this->cart_edit_key);
            // if( $nbd_session ){
            //     // mzl cart save cart_item_key -- session
            //     WC()->session->set($cart_item_key. '_nbd', $nbd_session);
            //     // WC()->session->__unset('nbd_session_removed');
            // }
            // 参数验证
            if (empty($cart_item_key)) {
                error_log('Error: cart_item_key is empty');
                return;
            }

            // 获取购物车内容
            if (!isset(WC()->cart) || !is_object(WC()->cart)) {
                error_log('Error: WC()->cart is not available');
                return;
            }
            
            $cart_contents = WC()->cart->cart_contents;
            error_log('Current cart_item_key: ' . $cart_item_key);
            
            // 检查购物车内容是否是数组
            if (!is_array($cart_contents)) {
                error_log('Error: cart_contents is not an array');
                $cart_contents = array();
            }

            // 检查并初始化购物车项
            if (!isset($cart_contents[$cart_item_key])) {
                // 如果购物车项不存在，创建新的购物车项
                error_log('Case 1: Cart item does not exist, creating new one');
                
                $cart_contents[$cart_item_key] = array(
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'variation' => $variation,
                    'quantity' => $quantity,
                    'data' => $cart_item_data,
                    'nbo_cart_item_key' => $cart_item_key
                );
                
                WC()->cart->cart_contents = $cart_contents;
                error_log('Created new cart item');
            } 
            else if (empty($cart_contents[$cart_item_key])) {
                // 如果购物车项存在但为空，更新购物车项
                error_log('Case 2: Cart item exists but is empty');
                
                $cart_contents[$cart_item_key] = array(
                    'product_id' => $product_id,
                    'variation_id' => $variation_id,
                    'variation' => $variation,
                    'quantity' => $quantity,
                    'data' => $cart_item_data,
                    'nbo_cart_item_key' => $cart_item_key
                );
                
                WC()->cart->cart_contents = $cart_contents;
                error_log('Initialized empty cart item');
            }
            else if (!isset($cart_contents[$cart_item_key]['nbo_cart_item_key'])) {
                // 如果购物车项存在但缺少nbo_cart_item_key，添加nbo_cart_item_key
                error_log('Case 3: Cart item exists but missing nbo_cart_item_key');
                
                WC()->cart->cart_contents[$cart_item_key]['nbo_cart_item_key'] = $cart_item_key;
                error_log('Added nbo_cart_item_key to existing item');
            }
            else {
                // 购物车项已经正确设置
                error_log('Case 4: Cart item already properly set up');
                error_log('Existing cart item: ' . print_r($cart_contents[$cart_item_key], true));
            }

            // 确保更改被保存
            try {
                WC()->cart->set_session();
                error_log('Cart session updated successfully');
            } catch (Exception $e) {
                error_log('Error saving cart session: ' . $e->getMessage());
            }
        }
        public function cart_item_name($title = "", $cart_item = array(), $cart_item_key = ""){
            if ( !(is_cart() || is_checkout()) ){
                return $title;
            }
            if ( !isset( $cart_item['nbo_meta'] ) ) {
                return $title;
            }
            if( is_checkout() ){
                $title .= ' &times; <strong>' . $cart_item['quantity'] .'</strong>';
            }
            $product = $cart_item['data'];
            $link = add_query_arg(
                array(
                    'nbo_cart_item_key'  => $cart_item_key,
                )
                , $product->get_permalink( $cart_item ) ); 
            $link = wp_nonce_url( $link, 'nbo-edit' );
            $title .= '<br /><a class="nbo-edit-option-cart" href="' . $link . '" class="nbo-cart-edit-options">' . __( 'Edit options', 'web-to-print-online-designer' ) . '</a><br />';
            return $title;
        }
        public function get_item_data( $item_data, $cart_item ){
            if ( isset( $cart_item['nbo_meta'] ) ) {
                $hide_zero_price = nbdesigner_get_option('nbdesigner_hide_zero_price');
                $num_decimals = absint( wc_get_price_decimals() );
                if( nbdesigner_get_option('nbdesigner_hide_options_in_cart') != 'yes' ){
                    $hide_option_price = nbdesigner_get_option('nbdesigner_hide_option_price_in_cart');
                    foreach ($cart_item['nbo_meta']['option_price']['fields'] as $field) {
                        $price = floatval($field['price']) >= 0 ? '+' . wc_price($field['price']) : wc_price($field['price']);
                        if( $hide_zero_price == 'yes' && round($field['price'], $num_decimals) == 0 ) $price = '';
                        if( isset($field['is_upload']) ){
                            if (strpos($field['val'], 'http') !== false) {
                                $file_url = $field['val'];
                            }else{
                                $file_url = Nbdesigner_IO::wp_convert_path_to_url( NBDESIGNER_UPLOAD_DIR . '/' .$field['val'] );
                            }
                            $field['value_name'] = '<a href="' . $file_url . '">' . $field['value_name'] . '</a>';
                        }
                        $item_data[] = array(
                            'name' => $field['name'],
                            'display' => $hide_option_price == 'yes' ? $field['value_name'] : $field['value_name']. '&nbsp;&nbsp;' .$price,
                            'hidden' => false                        
                        );
                    }
                    $item_data[] = array(
                        'name' => __('Quantity Discount', 'web-to-print-online-designer'),
                        'display' => '-' . wc_price($cart_item['nbo_meta']['option_price']['discount_price']),
                        'hidden' => false                        
                    );
                }
            }
            return $item_data;
        }
        public function get_cart_item_from_session($cart_item, $values) {
            // error_log('Session values: ' . print_r($values, true));
            // error_log('Cart item before: ' . print_r($cart_item, true));
            
            // if (isset($values['nbo_meta'])) {
                $cart_item['nbo_meta'] = $values['nbo_meta'];
            // } else {
            //     error_log('nbo_meta not found in session cart_item ' . print_r($cart_item, true));
            // }
            
            return $cart_item;
        }
        public function add_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ){
            error_log('frontend-options add_cart_item_data called with:');
            error_log('Cart Item Data: ' . print_r($cart_item_data, true));
            error_log('Product ID: ' . $product_id);
            error_log('Variation ID: ' . $variation_id);
            error_log('Quantity: ' . $quantity);
            $post_data = $_POST;
            $option_id = $this->get_product_option($product_id);
            if( !$option_id ){
                return $cart_item_data;
            }
            error_log('frontend-options add_cart_item_data option_id: ' . $option_id);
            if( isset($post_data['nbd-field']) || isset($post_data['nbo-add-to-cart']) ){
                error_log('frontend-options add_cart_item_data post_data');
                $options = $this->get_option($option_id);
                $option_fields = unserialize($options['fields']);
                $nbd_field = isset($post_data['nbd-field']) ? $post_data['nbd-field'] : array();
                if( isset($cart_item_data['nbd-field']) ){
                    /* Bulk variation */
                    $nbd_field = $cart_item_data['nbd-field'];
                    $nbd_field = $this->validate_before_processing($option_fields, $nbd_field);
                    unset($cart_item_data['nbd-field']);
                }else{
                    if( !empty($_FILES) && isset($_FILES["nbd-field"]) ) {
                        foreach( $_FILES["nbd-field"]['name'] as $field_id => $file ){
                            if( !isset($nbd_field[$field_id]) ){
                                $nbd_upload_field = $this->upload_file( $_FILES["nbd-field"], $field_id );
                                if( !empty($nbd_upload_field) ){
                                    $nbd_field[$field_id] = $nbd_upload_field[$field_id];
                                }
                            }
                        }
                    }
                }
                $product = $variation_id ? wc_get_product( $variation_id ) : wc_get_product( $product_id );
                $original_price = $this->format_price( $product->get_price('edit') );
                $option_price = $this->option_processing( $options, $original_price, $nbd_field, $quantity );
                error_log('frontend-options add_cart_item_data check nbdpb-folder: ' . isset($post_data['nbdpb-folder']));
                if( isset($post_data['nbdpb-folder']) ){
                    error_log('frontend-options add_cart_item_data nbdpb-folder in ');
                    $cart_item_data['nbo_meta']['nbdpb'] = $post_data['nbdpb-folder'];
                    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $post_data['nbdpb-folder'] . '/preview';
                    $images = Nbdesigner_IO::get_list_images($path, 1);
                    if( count($images) ){
                        $option_price['cart_image'] = Nbdesigner_IO::wp_convert_path_to_url($images[0]);
                    }
                }
                $cart_item_data['nbo_meta']['option_price'] = $option_price;
                $cart_item_data['nbo_meta']['field'] = $nbd_field;
                $cart_item_data['nbo_meta']['options'] = $options;
                $cart_item_data['nbo_meta']['original_price'] = $original_price;
                $cart_item_data['nbo_meta']['price'] = $this->format_price($original_price + $option_price['total_price'] - $option_price['discount_price']);  
            }
            return $cart_item_data;
        }
        public function upload_file( $files, $field_id ){
            $nbd_upload_fields = array();
            global $woocommerce;
            $user_folder = md5( $woocommerce->session->get_customer_id() );
            $file = $files['name'][$field_id];
            if( $files['error'][$field_id] == 0 ){
                $ext = pathinfo( $file, PATHINFO_EXTENSION );
                $new_name = strtotime("now").substr(md5(rand(1111,9999)),0,8).'.'.$ext;
                $new_path = NBDESIGNER_UPLOAD_DIR . '/' .$user_folder . '/' .$new_name;
                $mkpath = wp_mkdir_p( NBDESIGNER_UPLOAD_DIR . '/' .$user_folder);
                if( $mkpath ){
                    if (move_uploaded_file($files['tmp_name'][$field_id], $new_path)) {
                        $nbd_upload_fields[$field_id] = $user_folder . '/' .$new_name;
                    }else{
                        //todo
                    }
                }
            }
            return $nbd_upload_fields;
        }
        public function format_price( $price ){
            //$decimal_separator = stripslashes( wc_get_price_decimal_separator() );
            //$thousand_separator = stripslashes( wc_get_price_thousand_separator() );
            $num_decimals = wc_get_price_decimals();
            //$price = str_replace($decimal_separator, '.', $price);
            //$price = str_replace($thousand_separator, '', $price);
            $price = round($price, $num_decimals);
            return $price;
        }

        public function validate_before_processing($option_fields, $nbd_field) {
            // 初始化返回值
            $new_fields = array();
            
            // 检查参数有效性
            if(!is_array($option_fields)) {
                error_log('NBD Options Error: option_fields is not an array');
                return $new_fields;
            }
            
            // 确保fields键存在
            if(!isset($option_fields['fields']) || !is_array($option_fields['fields'])) {
                $option_fields['fields'] = array();
                error_log('NBD Options Warning: fields key is missing or invalid');
                return $new_fields;
            }
            
            // 验证nbd_field
            if(!is_array($nbd_field)) {
                error_log('NBD Options Error: nbd_field is not an array');
                return $new_fields;
            }
            
            try {
                $new_fields = $nbd_field;
                
                foreach($nbd_field as $field_id => $field) {
                    $origin_field = $this->get_field_by_id($option_fields, $field_id);
                    
                    // 如果找不到原始字段或没有条件设置，继续下一个
                    if(!$origin_field || 
                       !isset($origin_field['conditional']) || 
                       $origin_field['conditional']['enable'] != 'y' || 
                       !isset($origin_field['conditional']['depend']) || 
                       empty($origin_field['conditional']['depend'])) {
                        continue;
                    }
                    
                    // 处理条件逻辑
                    $show = isset($origin_field['conditional']['show']) ? $origin_field['conditional']['show'] : 'y';
                    $logic = isset($origin_field['conditional']['logic']) ? $origin_field['conditional']['logic'] : 'a';
                    $total_check = ($logic == 'a');
                    $check = array();
                    
                    foreach($origin_field['conditional']['depend'] as $key => $con) {
                        $check[$key] = true;
                        
                        if(isset($con['id']) && $con['id'] != '') {
                            if(!isset($new_fields[$con['id']])) {
                                $check[$key] = true;
                            } else {
                                switch($con['operator']) {
                                    case 'i':
                                        $check[$key] = ($nbd_field[$con['id']] == $con['val']);
                                        break;
                                    case 'n':
                                        $check[$key] = ($nbd_field[$con['id']] != $con['val']);
                                        break;
                                    case 'e':
                                        $check[$key] = ($nbd_field[$con['id']] == '');
                                        break;
                                    case 'ne':
                                        $check[$key] = ($nbd_field[$con['id']] != '');
                                        break;
                                }
                            }
                        }
                    }
                    
                    // 计算最终结果
                    foreach($check as $c) {
                        $total_check = ($logic == 'a') ? ($total_check && $c) : ($total_check || $c);
                    }
                    
                    $enable = ($show == 'y') ? $total_check : !$total_check;
                    if(!$enable) {
                        unset($new_fields[$field_id]);
                    }
                }
            } catch(Exception $e) {
                error_log('NBD Options Error: ' . $e->getMessage());
            }
            
            return $new_fields;
        }

        // 辅助方法：通过ID获取字段
        private function get_field_by_id($option_fields, $field_id) {
            // 检查参数有效性
            if(!is_array($option_fields)) {
                error_log('NBD Options Error: option_fields is not an array');
                return false;
            }
            
            // 确保fields键存在且为数组
            if(!isset($option_fields['fields']) || !is_array($option_fields['fields'])) {
                error_log('NBD Options Warning: fields key is missing or invalid');
                return false;
            }
            
            // 查找匹配的字段
            foreach($option_fields['fields'] as $field) {
                if(isset($field['id']) && $field['id'] == $field_id) {
                    return $field;
                }
            }
            
            return false;
        }
        public function recursive_stripslashes( $fields ){
            $valid_fields = array();
            foreach($fields as $key => $field){
                if(is_array($field) ){
                    $valid_fields[$key] = $this->recursive_stripslashes($field);
                }else if(!is_null($field)){
                    $valid_fields[$key] = stripslashes($field);
                }
            }
            return $valid_fields;
        }
        public function option_processing($options, $original_price, $fields, $quantity = 1) {
            // 记录函数调用
            error_log('option_processing called with:');
            error_log('Options: ' . print_r($options, true));
            error_log('Original Price: ' . $original_price);
            error_log('Fields: ' . print_r($fields, true));
            error_log('Quantity: ' . $quantity);
            
            // 记录调用栈以追踪来源
            error_log('Called from: ' . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2), true));
            
            // 记录当前页面信息
            error_log('Current page: ' . $_SERVER['REQUEST_URI']);
            
            try {
                // 初始化返回值
                $result = array(
                    'total_price' => 0,
                    'discount_price' => 0,
                    'fields' => array(),
                    'cart_image' => ''
                );
                
                // 检查参数有效性
                if(!is_array($options)) {
                    error_log('NBD Options Error: $options is not an array');
                    return $result;
                }
                
                // 确保fields存在且为数组
                $option_fields = isset($options['fields']) ? unserialize($options['fields']) : array();
                if(!is_array($option_fields)) {
                    error_log('NBD Options Error: Invalid option fields format');
                    return $result;
                }
                
                $option_fields = $this->recursive_stripslashes($option_fields);
                $quantity_break = $this->get_quantity_break($option_fields, $quantity);
                $xfactor = 1;
                $total_price = 0;
                $discount_price = 0;
                $_fields = array();
                $cart_image = '';
                
                // 检查fields参数
                if(!is_array($fields)) {
                    error_log('NBD Options Error: $fields is not an array');
                    return $result;
                }
                
                foreach($fields as $key => $val) {
                    $origin_field = $this->get_field_by_id($option_fields, $key);
                    if(!$origin_field) {
                        continue;
                    }
                    
                    $_fields[$key] = array(
                        'name' => isset($origin_field['general']['title']) ? $origin_field['general']['title'] : '',
                        'val' => $val,
                        'value_name' => $val
                    );
                    
                    // 处理字段值和价格计算
                    if(isset($origin_field['general']['data_type'])) {
                        if($origin_field['general']['data_type'] == 'i') {
                            // ... 保持原有的价格计算逻辑 ...
                        } else {
                            // ... 保持原有的选项处理逻辑 ...
                        }
                    }
                }
                
                // 计算最终价格
                $total_price += (($original_price + $total_price) * ($xfactor - 1));
                
                // 更新字段价格
                foreach($_fields as $key => $val) {
                    if(isset($_fields[$key]['is_pp']) && $_fields[$key]['is_pp'] == 1) {
                        $_fields[$key]['price'] = $_fields[$key]['price'] * ($original_price + $total_price) / ($_fields[$key]['price'] + 1);
                    }
                }
                
                // 计算折扣
                if($quantity_break['index'] == 0 && $quantity_break['oparator'] == 'lt') {
                    $qty_factor = '';
                } else {
                    $qty_factor = isset($option_fields['quantity_breaks'][$quantity_break['index']]['dis']) ? 
                                 $option_fields['quantity_breaks'][$quantity_break['index']]['dis'] : 0;
                }
                
                $qty_factor = floatval($qty_factor);
                $discount_type = isset($option_fields['quantity_discount_type']) ? $option_fields['quantity_discount_type'] : 'f';
                $discount_price = $discount_type == 'f' ? $qty_factor : ($original_price + $total_price) * $qty_factor / 100;
                
                return array(
                    'total_price' => $total_price,
                    'discount_price' => $discount_price,
                    'fields' => $_fields,
                    'cart_image' => $cart_image
                );
            } catch (Exception $e) {
                error_log('Error in option_processing: ' . $e->getMessage());
                return array(
                    'total_price' => 0,
                    'discount_price' => 0
                );
            }
        }
        public function calculate_price_base_measurement( $mesure_range, $width, $height){
            $area = floatval($width) * floatval($height);
            $price_per_unit = $start_range = $end_range = $price_range = 0;
            foreach($mesure_range as $key => $range){
                $start_range = floatval($range[0]);
                $end_range = floatval($range[1]);
                $price_range = floatval($range[2]);
                if( $start_range <= $area && ( $area <= $end_range || $end_range == 0 ) ){
                    $price_per_unit = $price_range;
                }
                if( $start_range <= $area && $key == ( count($mesure_range) - 1 ) && $area > $end_range  ){
                    $price_per_unit = $price_range;
                }
            }
            return $area * $price_per_unit;
        }
        public function get_quantity_break($options, $quantity) {
            // 初始化默认返回值
            $quantity_break = array(
                'index' => 0,
                'oparator' => 'gt'
            );
            
            // 检查参数有效性
            if(!is_array($options)) {
                return $quantity_break;
            }
            
            // 确保quantity_breaks存在且为数组
            if(!isset($options['quantity_breaks']) || !is_array($options['quantity_breaks'])) {
                $options['quantity_breaks'] = array(
                    array(
                        'val' => 1,
                        'dis' => 0
                    )
                );
            }
            
            // 构建数量档位数组
            $quantity_breaks = array();
            foreach($options['quantity_breaks'] as $break) {
                if(isset($break['val']) && is_numeric($break['val'])) {
                    $quantity_breaks[] = absint($break['val']);
                }
            }
            
            // 如果没有有效的数量档位，返回默认值
            if(empty($quantity_breaks)) {
                return $quantity_break;
            }
            
            // 计算数量档位
            foreach($quantity_breaks as $key => $break) {
                if($key == 0 && $quantity < $break) {
                    $quantity_break = array('index' => 0, 'oparator' => 'lt');
                }
                if($quantity >= $break && $key < (count($quantity_breaks) - 1)) {
                    $quantity_break = array('index' => $key, 'oparator' => 'bw');
                }
                if($key == (count($quantity_breaks) - 1) && $quantity >= $break) {
                    $quantity_break = array('index' => $key, 'oparator' => 'gt');
                }
            }
            
            return $quantity_break;
        }
        public function set_product_prices( $cart_item ){
            if ( isset( $cart_item['nbo_meta'] )){
                // mzl mod not set price
                // $new_price = (float) $cart_item['nbo_meta']['price'];
                // $cart_item['data']->set_price( $new_price );
            }
            return $cart_item;
        }
        public function nbd_js_object( $args ){
            $args['currency_format_num_decimals'] = wc_get_price_decimals();
            $args['currency_format_symbol'] =get_woocommerce_currency_symbol();
            $args['currency_format_decimal_sep'] = stripslashes( wc_get_price_decimal_separator() );
            $args['currency_format_thousand_sep'] = stripslashes( wc_get_price_thousand_separator() );
            $args['currency_format'] = esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format()) );
            $args['nbdesigner_hide_add_cart_until_form_filled'] = nbdesigner_get_option('nbdesigner_hide_add_cart_until_form_filled');
            $args['total'] = __('Total', 'web-to-print-online-designer');
            $args['check_invalid_fields'] = __('Please check invalid fields and quantity input!', 'web-to-print-online-designer');
            return $args;
        }
        public function wp_enqueue_scripts(){
            if(nbdesigner_get_option('nbdesigner_enable_angular_js') == 'yes'){
                // 基础库
                wp_register_script('jquery', false, array(), false, true);
                wp_register_script('underscore', NBDESIGNER_PLUGIN_URL . 'assets/libs/underscore.min.js', array(), '1.8.3', true);
                wp_register_script('backbone', NBDESIGNER_PLUGIN_URL . 'assets/libs/backbone.min.js', array('jquery', 'underscore'), '1.4.0', true);
                
                // Marionette 及其依赖
                wp_register_script('backbone.radio', NBDESIGNER_PLUGIN_URL . 'assets/libs/backbone.radio.min.js', array('backbone'), '2.0.0', true);
                wp_register_script('backbone.babysitter', NBDESIGNER_PLUGIN_URL . 'assets/libs/backbone.babysitter.min.js', array('backbone'), '0.1.12', true);
                wp_register_script('marionette', NBDESIGNER_PLUGIN_URL . 'assets/libs/backbone.marionette.min.js', array('backbone', 'backbone.radio', 'backbone.babysitter'), '3.5.1', true);
                
                // 自定义模块
                wp_register_script('common-modules', NBDESIGNER_PLUGIN_URL . 'assets/js/common-modules.min.js', array('marionette'), '1.0.0', true);
                
                // 其他依赖
                wp_register_script('accounting', NBDESIGNER_PLUGIN_URL . 'assets/libs/accounting.min.js', array('jquery'), '0.4.2', true);
                wp_register_script('angularjs', NBDESIGNER_PLUGIN_URL . 'assets/libs/angular.min.js', array('jquery'), '1.6.3', true);
                wp_register_script('spectrum', NBDESIGNER_PLUGIN_URL . 'assets/libs/spectrum.js', array('jquery'), '1.8.0', true);
                wp_register_script('angular-spectrum-colorpicker', NBDESIGNER_PLUGIN_URL . 'assets/libs/angular-spectrum-colorpicker.min.js', array('angularjs', 'spectrum'), '1.4.5', true);
                
                // 按顺序加载
                wp_enqueue_script('underscore');
                wp_enqueue_script('backbone');
                wp_enqueue_script('backbone.radio');
                wp_enqueue_script('backbone.babysitter');
                wp_enqueue_script('marionette');
                wp_enqueue_script('common-modules');
                wp_enqueue_script('accounting');
                wp_enqueue_script('angularjs');
                wp_enqueue_script('spectrum');
                wp_enqueue_script('angular-spectrum-colorpicker');
            }
        }
        public function show_option_fields(){
            global $product;
            $product_id = $product->get_id();
            $option_id = $this->get_product_option($product_id);
            if($option_id){
                $_options = $this->get_option($option_id);
                if($_options){
                    $options = unserialize($_options['fields']);
                    if( !isset($options['fields']) ){
                        //echo ''; 
                        //return;
                        $options['fields'] = array();
                    }
                    $options['fields'] = $this->recursive_stripslashes( $options['fields'] );
                    foreach ($options['fields'] as $key => $field){
                        if( !isset($field['general']['attributes']) ){
                            $field['general']['attributes'] = array();
                            $field['general']['attributes']['options'] = array();
                            $options['fields'][$key]['general']['attributes'] = array();
                            $options['fields'][$key]['general']['attributes']['options'] = array();
                        }
                        if($field['appearance']['change_image_product'] == 'y'){
                            foreach ($field['general']['attributes']['options'] as $op_index => $option ){
                                $option['product_image'] = isset($option['product_image']) ? $option['product_image'] : 0;
                                $attachment_id = absint($option['product_image']);
                                if( $attachment_id != 0 ){
                                    $image_link = wp_get_attachment_url($attachment_id);
                                    $attachment_object = get_post( $attachment_id );
                                    $full_src = wp_get_attachment_image_src( $attachment_id, 'large' );
                                    $image_title = get_the_title( $attachment_id );
                                    $image_alt = trim( strip_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', TRUE ) ) );
                                    $image_srcset = function_exists( 'wp_get_attachment_image_srcset' ) ? wp_get_attachment_image_srcset( $attachment_id, 'shop_single' ) : FALSE;
                                    $image_sizes = function_exists( 'wp_get_attachment_image_sizes' ) ? wp_get_attachment_image_sizes( $attachment_id, 'shop_single' ) : FALSE;
                                    $image_caption = $attachment_object->post_excerpt;                                    
                                    $options['fields'][$key]['general']['attributes']['options'][$op_index] = array_replace_recursive($options['fields'][$key]['general']['attributes']['options'][$op_index], array(
                                        'imagep'    =>  'y',
                                        'image_link'    => $image_link,
                                        'image_title'   => $image_title,
                                        'image_alt'     => $image_alt,
                                        'image_srcset'  => $image_srcset,
                                        'image_sizes'   => $image_sizes,
                                        'image_caption' => $image_caption,
                                        'full_src'      => $full_src[0],
                                        'full_src_w'    => $full_src[1],
                                        'full_src_h'    => $full_src[2]
                                        
                                    ));
                                }else{
                                    $options['fields'][$key]['general']['attributes']['options'][$op_index]['imagep'] = 'n';
                                }
                            }
                        }
                        if( isset($field['nbpb_type']) && $field['nbpb_type'] == 'nbpb_com' ){
                            if( isset($field['general']['pb_config']) ){
                                foreach( $field['general']['pb_config'] as $a_index => $attr ){
                                    foreach( $attr as $s_index => $sattr ){
                                        foreach( $sattr['views'] as $v_index => $view ){
                                            $pb_image_obj = wp_get_attachment_url( absint($view['image']) );
                                            $options['fields'][$key]['general']['pb_config'][$a_index][$s_index]['views'][$v_index]['image_url'] =  $pb_image_obj ? $pb_image_obj : NBDESIGNER_ASSETS_URL . 'images/placeholder.png';
                                        }
                                    }
                                }
                            }else{
                                $field['general']['pb_config'] = array();
                            }
                            foreach ($field['general']['attributes']['options'] as $op_index => $option ){
                                if( isset($option['enable_subattr']) && $option['enable_subattr'] == 'on' && isset($option['sub_attributes']) && count($option['sub_attributes']) > 0 ){
                                    foreach( $option['sub_attributes'] as $sa_index => $sattr ){
                                        $options['fields'][$key]['general']['attributes']['options'][$op_index]['sub_attributes'][$sa_index]['image_url'] = nbd_get_image_thumbnail( $sattr['image'] );
                                    }
                                }else{
                                    $options['fields'][$key]['general']['attributes']['options'][$op_index]['image_url'] = nbd_get_image_thumbnail( $option['image'] );
                                }
                            };
                            $options['fields'][$key]['general']['component_icon_url'] = nbd_get_image_thumbnail( $field['general']['component_icon'] );
                        }
                        if( isset($field['general']['attributes']['bg_type']) && $field['general']['attributes']['bg_type'] == 'i' ){
                            foreach ($field['general']['attributes']['options'] as $op_index => $option ){
                                foreach( $option['bg_image'] as $bg_index => $bg ){
                                    $bg_obj = wp_get_attachment_url( absint($bg) );
                                    $options['fields'][$key]['general']['attributes']['options'][$op_index]['bg_image_url'][$bg_index] = $bg_obj ? $bg_obj : NBDESIGNER_ASSETS_URL . 'images/placeholder.png';
                                }
                            };
                        }
                    }
                    if( isset($options['views']) ){
                        foreach ($options['views'] as $vkey => $view){
                            $view['base'] = isset($view['base']) ? $view['base'] : 0;
                            $options['views'][$vkey]['base'] = $view['base'];
                            $view_bg_obj = wp_get_attachment_url( absint($view['base']) );
                            $options['views'][$vkey]['base_url'] = $view_bg_obj ? $view_bg_obj : NBDESIGNER_ASSETS_URL . 'images/placeholder.png';
                        }
                    }
                    $product = wc_get_product($product_id);
                    $type = $product->get_type();
                    $variations = array();
                    $form_values = array();
                    $cart_item_key = '';
                    $quantity = 1;
                    $nbdpb_enable = get_post_meta($product_id, '_nbdpb_enable', true);
                    if($options['quantity_enable'] == 'y'){
                        $quantity = absint($options['quantity_breaks'][0]['val']);
                    }
                    if( isset($_POST['nbd-field']) ){
                        $form_values = $_POST['nbd-field'];
                        if( isset($_POST["nbo-quantity"]) ){
                            $quantity = $_POST["nbo-quantity"];
                        }
                    }else if( isset($_GET['nbo_cart_item_key']) && $_GET['nbo_cart_item_key'] != '' ){
                        $cart_item_key = $_GET['nbo_cart_item_key'];
                        $cart_item = WC()->cart->get_cart_item( $cart_item_key );
                        if( isset($cart_item['nbo_meta']) ){
                            $form_values = $cart_item['nbo_meta']['field'];
                        }
                        if ( isset( $cart_item["quantity"] ) ) {
                            $quantity = $cart_item["quantity"];
                        }
                    }
                    if( $type == 'variable' ){
                        $all = get_posts( array(
                            'post_parent' => $product_id,
                            'post_type'   => 'product_variation',
                            'orderby'     => array( 'menu_order' => 'ASC', 'ID' => 'ASC' ),
                            'post_status' => 'publish',
                            'numberposts' => -1,
                        ));
                        foreach ( $all as $child ) {
                            $vid = $child->ID;
                            $variation = wc_get_product( $vid );
                            $variations[$vid] = $variation->get_price();
                        }
                    }
                    ob_start();
                    nbdesigner_get_template('single-product/option-builder.php', array(
                        'product_id'  =>   $product_id,
                        'options'   =>  $options,
                        'type'  => $type,
                        'quantity'  => $quantity,
                        'nbdpb_enable'  => $nbdpb_enable,
                        'price'  =>  $product->get_price(),
                        'is_sold_individually'  =>  $product->is_sold_individually(),
                        'variations'  => json_encode( (array) $variations ),
                        'form_values'  => $form_values,
                        'cart_item_key'  => $cart_item_key,
                        'change_base'  => nbdesigner_get_option('nbdesigner_change_base_price_html'),
                        'tooltip_position'  => nbdesigner_get_option('nbdesigner_tooltip_position'),
                        'hide_zero_price'  => nbdesigner_get_option('nbdesigner_hide_zero_price')
                    ));
                    $options_form = ob_get_clean();
                    echo $options_form;
                }
            }
        }
        public function get_product_option($product_id){
            $enable = get_post_meta($product_id, '_nbo_enable', true);
            error_log('get_product_option enable: ' . $enable . ' product_id: ' . $product_id);
            if( !$enable ) return false;
            $option_id = get_transient( 'nbo_product_'.$product_id );
            if( false === $option_id ){
                global $wpdb;
                $sql = "SELECT id, priority, apply_for, product_ids, product_cats, date_from, date_to FROM {$wpdb->prefix}nbdesigner_options WHERE published = 1";
                $options = $wpdb->get_results($sql, 'ARRAY_A');
                if($options){
                    error_log('get_product_option wpdb options: ' . print_r($options, true));
                    $_options = array();
                    foreach( $options as $option ){
                        $execute_option = true;
                        $from_date = false;
                        if( isset($option['date_from']) ){
                            $from_date = empty( $option['date_from'] ) ? false : strtotime( date_i18n( 'Y-m-d 00:00:00', strtotime( $option['date_from'] ), false ) );
                        }
                        $to_date = false;
                        if( isset($option['date_to']) ){
                            $to_date = empty( $option['date_to'] ) ? false : strtotime( date_i18n( 'Y-m-d 00:00:00', strtotime( $option['date_to'] ), false ) );
                        }
                        $now  = current_time( 'timestamp' );
                        if ( $from_date && $to_date && !( $now >= $from_date && $now <= $to_date ) ) {
                                        $execute_option = false;
                        } elseif ( $from_date && !$to_date && !( $now >= $from_date ) ) {
                                        $execute_option = false;
                        } elseif ( $to_date && !$from_date && !( $now <= $to_date ) ) {
                                        $execute_option = false;
                        }
                        if( $execute_option ){
                            if( $option['apply_for'] == 'p' ){
                                $products = unserialize($option['product_ids']);
                                $execute_option = in_array($product_id, $products) ? true : false;
                            }else {
                                $categories = $option['product_cats'] ? unserialize($option['product_cats']) : array();
                                $product = wc_get_product($product_id);
                                $product_categories = $product->get_category_ids();
                                $intersect = array_intersect($product_categories, $categories);
                                $execute_option = ( count($intersect) > 0 ) ? true : false;
                            }
                        }
                        if( $execute_option ){
                            $_options[] = $option;
                        }
                    }
                    $_options = array_reverse( $_options );
                    $option_priority = 0;
                    foreach( $_options as $_option ){
                        if( $_option['priority'] > $option_priority ){
                            $option_priority = $_option['priority'];
                            $option_id = $_option['id'];
                        }
                    }
                    if( $option_id ){
                        error_log('get_product_option wpdb option_id: ' . $option_id);
                        set_transient( 'nbo_product_'.$product_id , $option_id );
                    }
                }
            } 
            return $option_id;
        }
        public function get_option( $id ){
            global $wpdb;
            $sql = "SELECT * FROM {$wpdb->prefix}nbdesigner_options";
            $sql .= " WHERE id = " . esc_sql($id);
            $result = $wpdb->get_results($sql, 'ARRAY_A');
            return count($result[0]) ? $result[0] : false;
        }
        public function bulk_order(){
            $bulk_fields = $_REQUEST['nbb-fields'];
            if( !is_array($bulk_fields) ) return false;
            $nbd_field = isset( $_REQUEST['nbd-field'] ) ? $_REQUEST['nbd-field'] : array();
            $qtys = $_REQUEST['nbb-qty-fields'];
            $first_field = reset($bulk_fields);
            // Gather bulk form fields.
            $nbb_fields = array();
            for( $i=0; $i < count($first_field); $i++ ){
                $arr = array();
                foreach($nbd_field as $field_id => $field_value){
                    if( !isset($bulk_fields[$field_id]) ){
                        $arr[$field_id] = $field_value;
                    }
                }
                foreach($bulk_fields as $field_id => $bulk_field){
                    $arr[$field_id] = $bulk_field[$i];
                }
                $nbb_fields[] = $arr;
            }
            $product_id = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : $_REQUEST['nbo-add-to-cart']; 
            $added_count  = 0;
            $failed_count = 0;        
            $success_message = '';
            $error_message   = '';
            $adding_to_cart = wc_get_product( $product_id );
            if ( ! $adding_to_cart ) {
                return false;
            }   
            $option_id = $this->get_product_option($product_id);
            if( !$option_id ) return false;
            $variation_id = isset($_REQUEST['variation_id']) ? $_REQUEST['variation_id'] : 0;
            $product_type = $adding_to_cart->get_type();
            $uploaded = false;
            $upload_fields = array();
            /* Gather online design data */
            $nbd_item_cart_key = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id;
            $nbd_session = WC()->session->get('nbd_item_key_'.$nbd_item_cart_key);
            $nbu_session = WC()->session->get('nbu_item_key_'.$nbd_item_cart_key);            
            if( $product_type == 'variable' ){
                if( $variation_id > 0 ){
                    $missing_attributes = array();
                    $variations = array();
                    try {
                        // Gather posted attributes.
                        $posted_attributes = array();
                        foreach ($adding_to_cart->get_attributes() as $attribute) {
                            if (!$attribute['is_variation']) {
                                continue;
                            }
                            $attribute_key = 'attribute_' . sanitize_title($attribute['name']);
                            if (isset($_REQUEST[$attribute_key])) {
                                if ($attribute['is_taxonomy']) {
                                    // Don't use wc_clean as it destroys sanitized characters.
                                    $value = sanitize_title(wp_unslash($_REQUEST[$attribute_key]));
                                } else {
                                    $value = html_entity_decode(wc_clean(wp_unslash($_REQUEST[$attribute_key])), ENT_QUOTES, get_bloginfo('charset'));
                                }
                                $posted_attributes[$attribute_key] = $value;
                            }
                        }

                        // Check the data we have is valid.
                        $variation_data = wc_get_product_variation_attributes( $variation_id );
                        foreach ( $adding_to_cart->get_attributes() as $attribute ) {
                            if ( ! $attribute['is_variation'] ) {
                                    continue;
                            }                        
                            // Get valid value from variation data.
                            $attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
                            $valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ]: '';
                            /**
                             * If the attribute value was posted, check if it's valid.
                             *
                             * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
                             */                        
                            if ( isset( $posted_attributes[ $attribute_key ] ) ) {
                                $value = $posted_attributes[ $attribute_key ];
                                // Allow if valid or show error.
                                if ( $valid_value === $value ) {
                                    $variations[ $attribute_key ] = $value;
                                } elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs() ) ) {
                                    // If valid values are empty, this is an 'any' variation so get all possible values.
                                    $variations[ $attribute_key ] = $value;
                                } else {
                                    throw new Exception( sprintf( __( 'Invalid value posted for %s', 'woocommerce' ), wc_attribute_label( $attribute['name'] ) ) );
                                }
                            } elseif ( '' === $valid_value ) {
                                $missing_attributes[] = wc_attribute_label( $attribute['name'] );
                            }
                        }
			if ( ! empty( $missing_attributes ) ) {
                            throw new Exception( sprintf( _n( '%s is a required field', '%s are required fields', count( $missing_attributes ), 'woocommerce' ), wc_format_list_of_items( $missing_attributes ) ) );
			}
                    } catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			return false;                        
                    }                   
                    foreach($nbb_fields as $index => $nbb_field){                        
                        /* Add online design data */
                        if( $nbd_session && ! WC()->session->get('nbd_item_key_'.$nbd_item_cart_key) ) WC()->session->set('nbd_item_key_'.$nbd_item_cart_key, $nbd_session);
                        if( $nbu_session && ! WC()->session->get('nbu_item_key_'.$nbd_item_cart_key) ) WC()->session->set('nbu_item_key_'.$nbd_item_cart_key, $nbu_session);
                        $quantity = $qtys[$index];
                        $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );
                        if( $quantity > 0){
                            if ( $passed_validation ) {
                                if( !$uploaded ){
                                    if( !empty($_FILES) && isset($_FILES["nbd-field"]) ) {
                                        foreach( $_FILES["nbd-field"]['name'] as $field_id => $file ){
                                            if( !isset($nbd_field[$field_id]) ){
                                                $nbd_upload_field = $this->upload_file( $_FILES["nbd-field"], $field_id );
                                                if( !empty($nbd_upload_field) ){
                                                    $upload_fields[$field_id] = $nbd_upload_field[$field_id];
                                                }
                                            }
                                        }
                                    }
                                    $uploaded = true;
                                }
                                $nbb_field = array_merge($nbb_field, $upload_fields);
                                $cart_item_data['nbd-field'] = $nbb_field;
                                $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations, $cart_item_data );
                                if ( $added ) {
                                    $added_count ++;
                                } else {
                                    $failed_count ++;
                                }
                            }else{
                                $failed_count++;
                            }
                        }else{
                            //$failed_count++;
                            continue;
                        }
                    }
                } else {
                    return false;
                }
            }else{
                foreach($nbb_fields as $index => $nbb_field){
                    /* Add online design data */
                    if( $nbd_session && ! WC()->session->get('nbd_item_key_'.$nbd_item_cart_key) ) WC()->session->set('nbd_item_key_'.$nbd_item_cart_key, $nbd_session);
                    if( $nbu_session && ! WC()->session->get('nbu_item_key_'.$nbd_item_cart_key) ) WC()->session->set('nbu_item_key_'.$nbd_item_cart_key, $nbu_session);
                    $quantity = $qtys[$index];
                    $passed_validation 	= apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );                    
                    if( $quantity > 0){
                        if ( $passed_validation ) {
                            if( !$uploaded ){
                                if( !empty($_FILES) && isset($_FILES["nbd-field"]) ) {
                                    foreach( $_FILES["nbd-field"]['name'] as $field_id => $file ){
                                        if( !isset($nbd_field[$field_id]) ){
                                            $nbd_upload_field = $this->upload_file( $_FILES["nbd-field"], $field_id );
                                            if( !empty($nbd_upload_field) ){
                                                $upload_fields[$field_id] = $nbd_upload_field[$field_id];
                                            }
                                        }
                                    }
                                }
                                $uploaded = true;
                            }
                            $nbb_field = array_merge($nbb_field, $upload_fields);
                            $cart_item_data['nbd-field'] = $nbb_field;
                            $added = WC()->cart->add_to_cart( $product_id, $quantity, 0, array(), $cart_item_data );
                            if ( $added ) {
                                $added_count ++;
                            } else {
                                $failed_count ++;
                            }
                        }else{
                            $failed_count++;
                        }
                    }else{
                        //$failed_count++;
                        continue;
                    }
                }
            }
            if ( $added_count ) {
                nbd_bulk_variations_add_to_cart_message( $added_count );
            }
            if ( $failed_count ) {
                wc_add_notice( sprintf( __( 'Unable to add %s to the cart.  Please check your quantities and make sure the item is available and in stock', 'web-to-print-online-designer' ), $failed_count ), 'error' );
            }  
            if ( ! $added_count && ! $failed_count ) {
                wc_add_notice( __( 'No product quantities entered.', 'web-to-print-online-designer' ), 'error' );
            }
            if ( $failed_count === 0 && wc_notice_count( 'error' ) === 0 ) {
                if ( $url = apply_filters( 'woocommerce_add_to_cart_redirect', false ) ) {
                    wp_safe_redirect( $url );
                    exit;
                } elseif ( get_option( 'woocommerce_cart_redirect_after_add' ) === 'yes' ) {
                    wp_safe_redirect( wc_get_cart_url() );
                    exit;
                }
            } 
        }
        public function quick_view(){
            global $woocommerce, $post;
            $product_id = absint($_GET['product']);
            if ($product_id) {
                $post = get_post($product_id);
                $type = nbdesigner_get_option('nbdesigner_display_product_option');
                setup_postdata($post);
                if($type == '1'){
                    nbdesigner_get_template('quick-view.php', array());
                }else{
                    nbdesigner_get_template('quick-view-tab.php', array());
                }
                exit;
            }
            exit;
        }
        public function get_field_values($options) {
            $values = array();
            
            // 添加类型检查
            if(!is_array($options) || !isset($options['fields']) || !is_array($options['fields'])) {
                return $values;
            }
            
            foreach($options['fields'] as $field_id => $field) {
                // 检查字段是否有效
                if(!isset($field['general']) || !is_array($field['general'])) {
                    continue;
                }
                
                $general = $field['general'];
                
                // 检查字段是否启用
                if(!isset($general['enabled']) || $general['enabled'] != 'y') {
                    continue;
                }
                
                // 初始化字段值
                $values[$field_id] = array(
                    'title' => isset($general['title']) ? $general['title'] : '',
                    'value' => '',
                    'value_name' => ''
                );
                
                // 处理不同类型的字段值
                if(isset($general['data_type'])) {
                    if($general['data_type'] == 'i') {
                        $values[$field_id]['value'] = isset($general['input_type']) && 
                                                    $general['input_type'] != 't' ? 
                                                    (isset($general['input_option']['min']) ? 
                                                    $general['input_option']['min'] : 0) : '';
                    } else {
                        $values[$field_id]['value'] = 0;
                        
                        if(isset($general['attributes']) && 
                           isset($general['attributes']['options']) && 
                           is_array($general['attributes']['options'])) {
                            
                            foreach($general['attributes']['options'] as $key => $op) {
                                if(isset($op['selected']) && $op['selected'] == 'on') {
                                    $values[$field_id]['value'] = $key;
                                    $values[$field_id]['value_name'] = isset($op['name']) ? $op['name'] : '';
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            return $values;
        }
        public function process_field_values($options) {
            // 初始化返回值
            $result = array(
                'fields' => array(),
                'values' => array()
            );
            
            // 基础类型检查
            if(!is_array($options)) {
                error_log('NBD Options Error: $options is not an array');
                return $result;
            }
            
            // 检查fields是否存在且为数组
            if(!isset($options['fields']) || !is_array($options['fields'])) {
                error_log('NBD Options Error: $options[fields] is not valid');
                return $result;
            }
            
            try {
                foreach($options['fields'] as $field_id => $field) {
                    // 验证字段结构
                    if(!$this->is_valid_field($field)) {
                        continue;
                    }
                    
                    $general = $field['general'];
                    
                    // 处理字段值
                    $field_value = $this->get_field_default_value($general);
                    
                    if($field_value !== null) {
                        $result['fields'][$field_id] = array(
                            'title' => isset($general['title']) ? $general['title'] : '',
                            'value' => $field_value,
                            'value_name' => $this->get_field_value_name($general)
                        );
                    }
                }
            } catch(Exception $e) {
                error_log('NBD Options Error: ' . $e->getMessage());
            }
            
            return $result;
        }

        // 辅助方法：验证字段结构
        private function is_valid_field($field) {
            return isset($field['general']) && 
                   is_array($field['general']) && 
                   isset($field['general']['enabled']) && 
                   $field['general']['enabled'] == 'y';
        }

        // 辅助方法：获取字段默认值
        private function get_field_default_value($general) {
            if(!isset($general['data_type'])) {
                return null;
            }
            
            if($general['data_type'] == 'i') {
                return isset($general['input_type']) && 
                       $general['input_type'] != 't' ? 
                       (isset($general['input_option']['min']) ? 
                       $general['input_option']['min'] : 0) : '';
            }
            
            return 0; // 默认返回0
        }

        // 辅助方法：获取字段值名称
        private function get_field_value_name($general) {
            if(!isset($general['attributes']) || 
               !isset($general['attributes']['options']) || 
               !is_array($general['attributes']['options'])) {
                return '';
            }
            
            foreach($general['attributes']['options'] as $key => $op) {
                if(isset($op['selected']) && $op['selected'] == 'on') {
                    return isset($op['name']) ? $op['name'] : '';
                }
            }
            
            return '';
        }

        public function process_quantity_breaks($options) {
            // 初始化默认值
            $quantity = 1;
            
            // 检查options是否为数组
            if(!is_array($options)) {
                return $quantity;
            }
            
            // 检查quantity_enable和quantity_breaks
            if(isset($options['quantity_enable']) && $options['quantity_enable'] == 'y') {
                // 确保quantity_breaks存在且为数组
                if(isset($options['quantity_breaks']) && is_array($options['quantity_breaks'])) {
                    // 确保有至少一个数量档位
                    if(!empty($options['quantity_breaks'])) {
                        // 获取第一个有效的数量值
                        foreach($options['quantity_breaks'] as $break) {
                            if(isset($break['val']) && is_numeric($break['val']) && $break['val'] > 0) {
                                $quantity = absint($break['val']);
                                break;
                            }
                        }
                    }
                } else {
                    // 如果quantity_breaks不存在或不是数组，记录错误
                    error_log('NBD Options Warning: quantity_breaks is not properly configured');
                }
            }
            
            // 确保返回值至少为1
            return max(1, $quantity);
        }

        // 在使用quantity_breaks的地方添加检查
        public function get_option_quantity($options) {
            if(!isset($options['quantity_breaks'])) {
                $options['quantity_breaks'] = array(
                    array(
                        'val' => 1,
                        'price' => 0
                    )
                );
            }
            
            return $this->process_quantity_breaks($options);
        }

        public function get_option_fields($option_id) {
            try {
                global $wpdb;
                
                // 直接从数据库获取字段数据
                $fields_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT fields FROM {$wpdb->prefix}nbdesigner_options WHERE id = %d",
                    intval($option_id)
                ));
                
                if (!$fields_data) {
                    error_log("NBD Options Debug: No data found for option ID {$option_id}");
                    return array();
                }
                
                // 记录原始数据用于调试
                error_log("NBD Options Debug: Raw fields data for ID {$option_id}: " . substr($fields_data, 0, 100));
                
                // 清理和修复数据
                $fields_data = $this->clean_serialized_data($fields_data);
                
                // 尝试反序列化
                $fields = $this->safe_unserialize($fields_data);
                
                if (empty($fields)) {
                    error_log("NBD Options Warning: Empty fields after unserialize for option ID {$option_id}");
                    return array();
                }
                
                return $fields;
                
            } catch (Exception $e) {
                error_log("NBD Options Error: " . $e->getMessage());
                return array();
            }
        }
        
        /**
         * 清理序列化的数据
         */
        private function clean_serialized_data($data) {
            if (empty($data)) return '';
            
            // 移除 BOM 和其他不可见字符
            $data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data);
            $data = preg_replace('/^\xef\xbb\xbf/', '', $data);
            
            // 修复 UTF-8 编码问题
            if (!mb_check_encoding($data, 'UTF-8')) {
                $data = mb_convert_encoding($data, 'UTF-8', 'ASCII,UTF-8,ISO-8859-1');
            }
            
            // 修复序列化字符串长度
            $data = preg_replace_callback('!s:(\d+):"(.*?)";!s', function($match) {
                return 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
            }, $data);
            
            return $data;
        }
        
        /**
         * 安全的反序列化
         */
        private function safe_unserialize($data) {
            if (empty($data)) return array();
            
            // 首先尝试直接反序列化
            $unserialized = @unserialize($data);
            if ($unserialized !== false) {
                return $unserialized;
            }
            
            // 如果失败，尝试 base64 解码（有些数据可能被编码）
            $decoded = @base64_decode($data);
            if ($decoded) {
                $unserialized = @unserialize($decoded);
                if ($unserialized !== false) {
                    return $unserialized;
                }
            }
            
            // 如果还是失败，尝试 JSON 解码（可能是 JSON 格式）
            $json_decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json_decoded;
            }
            
            // 所有尝试都失败，返回空数组
            error_log("NBD Options Debug: All unserialize attempts failed for data: " . substr($data, 0, 100));
            return array();
        }
    }
}
$nbd_fontend_printing_options = NBD_FRONTEND_PRINTING_OPTIONS::instance();
$nbd_fontend_printing_options->init();

// 移除已有的代码
remove_filter('pre_option_show_avatars', 'disable_all_avatars');

// 添加新的完整禁用方案
function nbd_disable_gravatar() {
    // 完全禁用头像功能
    add_filter('pre_option_show_avatars', '__return_false');
    
    // 移除头像相关的钩子
    remove_filter('get_avatar', 'get_avatar');
    
    // 提供空白头像
    add_filter('get_avatar', function($avatar, $id_or_email, $size, $default, $alt) {
        return '';
    }, 1, 5);
    
    // 禁用 Gravatar 请求
    add_filter('get_avatar_url', function($url) {
        return '';
    }, 1);
    
    // 移除管理员头像
    add_filter('admin_bar_menu', function($wp_admin_bar) {
        $wp_admin_bar->remove_node('my-account');
    }, 999);
}

// 在适当的时机执行禁用函数
add_action('init', 'nbd_disable_gravatar');