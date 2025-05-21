<?php
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
use setasign\Fpdi;
class Nbdesigner_Plugin {
    public $textdomain;
    public $plugin_id;
    public $activedomain;
    public $removedomain;
    public function __construct() {
        $this->plugin_id    = 'nbdesigner';
        $this->activedomain = 'activedomain/netbase/';
        $this->removedomain = 'removedomain/netbase/';
        $this->init_settings();
    }
    private function init_settings() {
        // 初始化颜色设置
        $color_settings = get_option('nbdesigner_color_settings', array());
        // error_log('nbdesigner_color_settings init: ' . print_r($color_settings, true));
        if (empty($color_settings)) {
            $color_settings = array(
                'combination_colors' => array(
                    'bg_codes' => array('#FF5733', '#33FF57', '#3357FF', '#FF33F6', '#33FFF6', '#F6FF33'),
                    'bg_names' => array('Red', 'Green', 'Blue', 'Pink', 'Cyan', 'Yellow'),
                    'fg_codes' => array('#FFFFFF', '#000000', '#FFFFFF', '#000000', '#000000', '#000000'),
                    'fg_names' => array('White', 'Black', 'White', 'Black', 'Black', 'Black')
                ),
                'background_colors' => array(
                    'codes' => array('#FFFFFF', '#000000', '#FF5733', '#33FF57', '#3357FF'),
                    'names' => array('White', 'Black', 'Red', 'Green', 'Blue')
                )
            );
            error_log('nbdesigner_color_settings: ' . print_r($color_settings, true));
        }
        update_option('nbdesigner_color_settings', $color_settings);

        // 将颜色设置添加到前端配置
        add_filter('nbdesigner_frontend_settings', function($settings) use ($color_settings) {
            $settings['nbes_settings'] = $color_settings;
            return $settings;
        });
    }
    public function init(){
        $this->hook();
        $this->schehule();
        $this->nbdesigner_lincense_notices();
        add_action( 'nbdesigner_lincense_event', array( $this, 'nbdesigner_lincense_event_action' ) ); 
        if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) || is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) ) {
            $this->woocommerce_hook();
        }
        if (is_admin()) {
            $this->admin_hook();
            $this->ajax();
        } else {
            $this->frontend_hook();
        }
        add_action( 'wpmu_new_blog', array( $this, 'install_in_new_blog' ), 10, 6 );
    }
    public function ajax(){
        // Nbdesigner_EVENT => nopriv
        $ajax_events = array(
            'nbdesigner_add_font_cat'                   => false,
            'nbdesigner_add_art_cat'                    => false,
            'nbdesigner_add_google_font'                => false,
            'nbdesigner_delete_font_cat'                => false,
            'nbdesigner_delete_art_cat'                 => false,
            'nbdesigner_delete_font'                    => false,
            'nbdesigner_delete_art'                     => false,
            'nbdesigner_get_product_info'               => true,
            'nbdesigner_get_qrcode'                     => true,
            'nbdesigner_get_facebook_photo'             => true,
            'nbdesigner_get_art'                        => true,
            'nbdesigner_design_approve'                 => false,
            'nbdesigner_design_order_email'             => false,
            'nbdesigner_customer_upload'                => true,
            'nbdesigner_get_customer_files'             => true,
            'nbdesigner_delete_customer_files'          => true,
            'nbd_upload_design_file'                    => true,
            'nbdesigner_get_font'                       => true,
            'nbdesigner_get_pattern'                    => true,
            'nbdesigner_get_info_license'               => false,
            'nbdesigner_remove_license'                 => false,
            'nbdesigner_get_license_key'                => false,
            'nbdesigner_get_language'                   => true,
            'nbdesigner_save_language'                  => false,
            'nbdesigner_create_language'                => false,
            'nbdesigner_make_primary_design'            => false,
            'nbdesigner_load_admin_design'              => true,
            'nbdesigner_save_webcam_image'              => true,
            'nbdesigner_migrate_domain'                 => false,
            'nbdesigner_restore_data_migrate_domain'    => false,
            'nbdesigner_theme_check'                    => false,
            'nbdesigner_custom_css'                     => false,
            'nbdesigner_custom_js'                      => false,
            'nbdesigner_copy_image_from_url'            => true,
            'nbdesigner_get_suggest_design'             => true,
            'nbdesigner_save_design_to_pdf'             => false,
            'nbdesigner_save_design_to_pdf_with_layout' => false,
            'nbdesigner_delete_language'                => false,
            'nbdesigner_update_all_product'             => false,
            'nbd_save_customer_design'                  => true,
            'nbd_remove_design_and_file'                => true,
            'nbd_save_draft_design'                     => true,
            'nbd_update_customer_upload'                => true,
            'nbd_save_cart_design'                      => true,
            'nbd_get_nbd_products'                      => true,
            'nbd_remove_cart_design'                    => true,
            'nbd_get_product_description'               => true,
            'nbd_convert_files'                         => true,
            'nbd_frontend_download_pdf'                 => true,
            'nbd_frontend_download_jpeg'                => true,
            'nbd_download_final_pdf'                    => true,
            'nbdesigner_update_variation_v180'          => false,
            'nbdesigner_update_all_template'            => false,
            'nbd_clear_transients'                      => false,
            'nbd_create_pages'                          => false,
            'nbd_get_product_config'                    => true,
            'nbd_delete_order_design'                   => false,
            'nbd_check_use_logged_in'                   => true,
            'nbd_crop_image'                            => true,
            'nbd_fix_pdf_font'                          => true,
            'nbd_upload_pdf_as_bg_image'                => true,
            'nbd_import_images'                         => true,
            'check_flysystem_connected'                 => true,
            'nbd_get_instagram_token'                   => true,
            'nbdesigner_get_customer_files'              => true,
            'nbdesigner_clear_customer_album'            => true
        );
        foreach( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_' . $ajax_event, array( $this, $ajax_event ) );
            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $ajax_event ) );
            }
        }
    }
    public function nbd_get_user_designs(){
        // error_log('nbd_get_user_designs begin');
        $user_id = wp_get_current_user()->ID;
        $result = array(
            'flag'   =>  1
        );
        if( $user_id ){
            if( !isset($_POST['did']) ){
                // error_log('nbd_get_user_designs $_POST not did');
                global $wpdb;
                $table_name =  $wpdb->prefix . 'nbdesigner_mydesigns';
                if( isset($_POST['product_id']) ){
                    $product_id     = absint( $_POST['product_id'] );
                    $variation_id   = absint( $_POST['variation_id'] );
                    // error_log('nbd_get_user_designs product_id: ' . $product_id . ' variation_id: ' . $variation_id);
                    $designs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nbdesigner_mydesigns WHERE user_id = {$user_id} AND product_id = {$product_id} AND variation_id = {$variation_id} ORDER BY created_date DESC" );
                }else{
                    // error_log('nbd_get_user_designs no product_id: . variation_id: ' . $variation_id);
                    $designs = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nbdesigner_mydesigns WHERE user_id = {$user_id} ORDER BY created_date DESC" );
                }
                $cart_design_ids = array();
                foreach(WC()->cart->cart_contents as $cart_item_key => $cart_item) {
                    if(isset($cart_item['nbd_design_id'])) {
                        // error_log('nbd_get_user_designs cart_design_id: ' . $cart_item['nbd_design_id']);
                        $cart_design_ids[] = $cart_item['nbd_design_id'];
                    }
                }
                // error_log('nbd_get_user_designs cart_design_ids: ' . print_r($cart_design_ids, true));
                foreach( $designs as $design ){
                    $path_preview   = NBDESIGNER_CUSTOMER_DIR .'/'.$design->folder. '/preview';
                    $listThumb      = Nbdesigner_IO::get_list_images( $path_preview );
                    $image          = '';
                    // error_log('nbd_get_user_designs $design->folder: ' . $design->folder);
                    if( !in_array($design->folder, $cart_design_ids) ){
                        if( count( $listThumb ) ){
                            asort($listThumb);
                            $image = Nbdesigner_IO::wp_convert_path_to_url( reset( $listThumb ) );
                            $result['designs'][] = array(
                                'id'    => $design->folder,
                                'src'   => $image,
                                'created_date' => $design->created_date
                            );
                        }
                    }
                }
            }else {
                $folder         = wc_clean( $_POST['did'] );
                $result         = nbd_get_template_by_folder( $folder );
                $result['flag'] = 1;
            }  
        } else {
            $result['flag'] = 0;
            // error_log('nbd_get_user_designs error');
        }
        // error_log('nbd_get_user_designs end');
        wp_send_json_success($result);
        wp_die();
    }

    // public function nbd_get_user_designs() {
    //     // 获取参数
    //     $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    //     $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    //     $user_id = get_current_user_id();
        
    //     error_log('nbd_get_user_designs - product_id: ' . $product_id . ', variation_id: ' . $variation_id . ', user_id: ' . $user_id);
    
    //     // 调用 NBD_My_Design 类的方法获取设计
    //     $designs = My_Design_Endpoint::nbdesigner_get_my_designs($user_id, $product_id, $variation_id);
        
    //     if ($designs !== false) {
    //         wp_send_json_success(array(
    //             'designs' => $designs
    //         ));
    //     } else {
    //         wp_send_json_error(array(
    //             'message' => 'Error fetching designs'
    //         ));
    //     }
    // }
    function nbd_delete_design() {
        // 验证nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'save-design')) {
            wp_send_json_error(array('message' => '安全验证失败'));
        }
        // 移除 WooCommerce 添加到购物车消息
        remove_action('wc_add_to_cart_message_html', 'wc_add_to_cart_message', 10);
    
        // 临时移除所有 WooCommerce 通知
        remove_all_actions('woocommerce_add_to_cart_message');
        remove_all_actions('woocommerce_add_to_cart_message_html');

        // 获取设计ID
        $design_id = isset($_POST['design_id']) ? sanitize_text_field($_POST['design_id']) : '';
        if (empty($design_id)) {
            wp_send_json_error(array('message' => '设计ID不能为空'));
        }
    
        // 验证用户权限
        $user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'nbdesigner_mydesigns';
        
        // 检查设计是否属于当前用户
        $design = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE folder = %s AND user_id = %d",
            $design_id,
            $user_id
        ));
    
        if (!$design) {
            wp_send_json_error(array('message' => '没有权限删除此设计'));
        }
    
        // 删除文件系统中的设计文件
        $design_path = NBDESIGNER_CUSTOMER_DIR . '/' . $design_id;
        if (file_exists($design_path)) {
            // require_once(NBDESIGNER_PLUGIN_DIR . 'includes/class-io.php');
            Nbdesigner_IO::delete_folder($design_path);
        }
    
        // 从数据库中删除记录
        $wpdb->delete(
            $table_name,
            array('folder' => $design_id),
            array('%s')
        );
        // 确保不触发 WooCommerce 的购物车更新
        if (isset($GLOBALS['woocommerce'])) {
            remove_action('wp_loaded', array($GLOBALS['woocommerce'], 'init'), 10);
        }

        wp_send_json_success(array('message' => '设计已成功删除'));
    }
    public function hook(){
        add_action( 'plugins_loaded', array( $this, 'translation_load_textdomain' ) );
        add_filter( 'cron_schedules', array( $this, 'set_schedule' ) );
        add_filter( 'query_vars', array( $this, 'nbdesigner_add_query_vars_filter' ) );
        $position = nbdesigner_get_option( 'nbdesigner_position_button_product_detail', 1 );
        if( $position == 4 ) add_shortcode( 'nbdesigner_button', array( $this,'nbdesigner_button' ) );
        add_shortcode( 'nbd_loggin_redirect', array( $this,'nbd_loggin_redirect_func' ) );
        add_action( 'template_redirect', array( $this, 'nbdesigner_editor_html' ) );
        // add_action( 'admin_head', array( $this, 'nbdesigner_add_tinymce_editor' ) );
        add_action( 'woocommerce_init', array( $this, 'init_session' ) );
        add_action('wp_ajax_nbd_get_user_designs', array($this, 'nbd_get_user_designs'));
        add_action('wp_ajax_nopriv_nbd_get_user_designs', array($this, 'nbd_get_user_designs'));
        add_filter( 'body_class', array( $this, 'add_body_class' ), 10, 1 );
        add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
        add_action('wp_ajax_nbd_delete_design', array($this, 'nbd_delete_design'));
        add_action('wp_ajax_nopriv_nbd_delete_design', array($this, 'nbd_delete_design'));
        
        add_action('wp_ajax_nbdesigner_editor_html', array($this, 'nbdesigner_editor_html'));
        add_action('wp_ajax_nopriv_nbdesigner_editor_html', array($this, 'nbdesigner_editor_html'));
        /* Force login */
        if( 'yes' == nbdesigner_get_option( 'nbdesigner_site_force_login', 'no' ) ){
            add_action( 'template_redirect', array( $this, 'force_login' ), 1 );
            add_filter( 'rest_authentication_errors', 'forcelogin_rest_access', 99 );
        }
        
        /* Cron job flush W3 Total cache */
        if( 'yes' == nbdesigner_get_option( 'nbdesigner_cron_job_clear_w3_cache', 'no' ) ){
            add_action( 'wp', array( $this, 'w3tc_cache_flush' ) );
            add_action( 'nbd_w3_flush_cache', array( $this, 'w3_flush_cache' ) ); 
        }
    }
    public function w3_flush_cache(){
        if ( function_exists( 'w3tc_flush_all' ) ){
            w3tc_flush_all();
        }
    }
    public function w3tc_cache_flush(){
        if ( ! wp_next_scheduled( 'nbd_w3_flush_cache' ) ) {
            wp_schedule_event( current_time( 'timestamp' ), 'every6hours', 'nbd_w3_flush_cache' );
        }
    }
    public function forcelogin_rest_access( $result ){
        if ( null === $result && ! is_user_logged_in() ) {
            return new WP_Error( 'rest_unauthorized', __( "Only authenticated users can access the REST API.", 'web-to-print-online-designer' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return $result;
    }
    public function force_login( ){
        // Exceptions for AJAX, Cron, or WP-CLI requests
        if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
            return;
        }
        // Redirect unauthorized visitors
        if ( ! is_user_logged_in() ) {
            $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
            $url .= '://' . $_SERVER['HTTP_HOST'];
            if ( strpos( $_SERVER['HTTP_HOST'], ':' ) === FALSE ) {
                $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
            }
            $url .= $_SERVER['REQUEST_URI'];
            $bypass     = apply_filters( 'nbd_forcelogin_bypass', false, $url );
            $whitelist  = apply_filters( 'nbd_forcelogin_whitelist', array(), $url );  
            if( preg_replace( '/\?.*/', '', $url ) != preg_replace( '/\?.*/', '', wp_login_url() ) && !in_array( $url, $whitelist ) && !$bypass ) {
                $redirect_url = apply_filters( 'nbd_forcelogin_redirect', $url );
                wp_safe_redirect( wp_login_url( $redirect_url ), 302 );
                exit;
            }
        }else{
            // Only allow Multisite users access to their assigned sites
            if ( ! is_user_member_of_blog() && ! current_user_can('setup_network') ) {
                wp_die( esc_html__( "You're not authorized to access this site.", 'web-to-print-online-designer' ), get_option( 'blogname' ) . ' &rsaquo; ' . __( "Error", 'web-to-print-online-designer' ) );
            }
        }
    }
    private function is_request($type) {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON');
        }
    }
    public function nbd_check_use_logged_in(){
        wp_send_json(
            array(
                'is_login'  => nbd_user_logged_in(),
                'nonce'     => wp_create_nonce( 'save-design' ),
                'nonce_get' => wp_create_nonce( 'nbdesigner-get-data' )
            )
        );
    }

    /**
     * Add custom post class for products
     * 
     * @param array $classes Post classes
     * @param array $class Additional classes
     * @param int $post_id Post ID
     * @return array Modified post classes
     */
    public function add_post_class($classes, $class = '', $post_id = '') {
        if (!$post_id || get_post_type($post_id) !== 'product') {
            return $classes;
        }
        
        // 检查产品是否启用了设计功能
        $enable_design = get_post_meta($post_id, '_nbdesigner_enable', true);
        if ($enable_design) {
            $classes[] = 'nbd-product';
        }
        
        // 检查产品是否有模板
        $has_template = $this->nbd_has_template($post_id);
        if ($has_template) {
            $classes[] = 'nbd-has-template';
        }
        
        // 检查变体产品
        if (get_post_meta($post_id, '_nbdesigner_variation_enable', true)) {
            $classes[] = 'nbd-variation-product';
        }
        
        return array_unique($classes);
    }
    
    /**
     * Check if product has templates
     * 
     * @param int $product_id Product ID
     * @return boolean
     */
    private function nbd_has_template($product_id) {
        global $wpdb;
        
        $sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}nbdesigner_templates 
            WHERE product_id = %d AND publish = 1",
            $product_id
        );
        
        return (int)$wpdb->get_var($sql) > 0;
    }
    /**
     * Get cart item from session
     * 
     * @param array $cart_item Cart item data
     * @param array $values Cart item values from session
     * @return array
     */
    public function get_cart_item_from_session($cart_item, $values) {
        if (isset($values['nbd_item_meta_ds'])) {
            $cart_item['nbd_item_meta_ds'] = $values['nbd_item_meta_ds'];
        }
        
        if (isset($values['nbd_design_id'])) {
            $cart_item['nbd_design_id'] = $values['nbd_design_id'];
            // error_log('nbdesigner get_cart_item_from_session nbd_design_id: ' . $cart_item['nbd_design_id']);
        }

        if (isset($values['nbd_design_key'])) {
            $cart_item['nbd_design_key'] = $values['nbd_design_key'];
        }

        if (isset($values['nbd_design_price'])) {
            $cart_item['nbd_design_price'] = $values['nbd_design_price'];
        }
        // 如果存在 nbo_meta
        if (isset($values['nbo_meta'])) {
            $cart_item['nbo_meta'] = $values['nbo_meta'];
            // error_log('nbdesigner get_cart_item_from_session nbo_meta: ' . print_r($cart_item['nbo_meta'], true));
        }
        // $variation_id = absint($_POST['variation_id']);
        // error_log('nbdesigner get_cart_item_from_session variation_id: ' . $variation_id);//print_r($cart_item, true)
        return $cart_item;
    }
    /**
     * Add hooks for cart session handling
     */
    public function init_cart_session() {
        // 添加购物车会话处理钩子
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 2);
        // add_filter('woocommerce_cart_item_thumbnail', array($this, 'modify_cart_item_thumbnail'), 10, 3);        
    }
    /**
     * 处理管理员重定向
     * 
     * @since 1.0.0
     * @return void
     */
    public function admin_redirects() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // 处理重定向逻辑
        global $pagenow;
        
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'nbdesigner') {
            // 添加必要的重定向处理
            try {
                // 检查nonce
                if (isset($_REQUEST['_wpnonce'])) {
                    check_admin_referer('nbdesigner_admin_action', '_wpnonce');
                }
                
                // 处理重定向
                if (isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case 'process_design':
                            // 处理设计
                            do_action('nbdesigner_process_design');
                            break;
                            
                        default:
                            // 默认行为
                            break;
                    }
                }
                
            } catch (Exception $e) {
                // 记录错误
                error_log('NBDesigner Admin Redirect Error: ' . $e->getMessage());
            }
        }
    }
    /**
     * Initiate Woocommerce User session when it's not logged in
     *
     * @return void
     */
    public function init_session(){
        global $woocommerce;
        if ( $this->is_request( 'frontend' ) && $woocommerce->session != null ) {
            if( is_user_logged_in() || is_admin() ) return;
            if ( ! WC()->session->has_session() ) $woocommerce->session->set_customer_session_cookie( true );
        }
    }
    public function admin_hook(){
        add_action( 'plugins_loaded', array( $this, 'nbdesigner_user_role' ) );
        add_action( 'admin_menu', array( $this, 'nbdesigner_menu' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_design_box' ), 30 );
        add_action( 'save_post', array( $this, 'save_settings' ) );
        add_action( 'save_post', array( $this, 'delete_nbd_product_transient' ) );
        add_action( 'delete_post', array( $this, 'delete_nbd_product_transient' ) );
        add_filter( 'upload_mimes', array( $this, 'upload_mimes' ) );
        add_filter( 'manage_product_posts_columns', array( $this, 'nbdesigner_add_design_column' ) );
        add_action( 'manage_product_posts_custom_column', array($this, 'nbdesigner_display_posts_design' ), 1, 2 );
        add_filter( 'nbdesigner_notices', array( $this, 'nbdesigner_notices' ) );
        add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );
        add_filter( 'parse_query', array( $this, 'nbdesigner_admin_posts_filter' ) );
        add_filter( 'views_edit-product', array( $this, 'nbdesigner_product_sorting_nbd' ),30 );
        add_action( 'admin_enqueue_scripts', array( $this, 'nbdesigner_admin_enqueue_scripts' ), 30, 1 );
        add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );
        add_filter( 'admin_footer', array( $this, 'footer_notice' ), 1 );
        add_action( 'admin_init', array( $this, 'admin_redirects' ) );
    }
    public function frontend_hook(){
        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ) );
        if ( isset( $_GET['download_nbd_design_file'] ) && isset( $_GET['order_id'] ) && isset( $_GET['order_item_id'] ) ) {
            add_action( 'init', array( $this, 'download_order_item_design_files' ) );
        }
    }

    private function get_cart_item_key() {
        // 添加调试日志
        error_log('Getting cart item key');
        // error_log('POST data: ' . print_r($_POST, true));
        
        $nbd_item_cart_key = '';
        
        // 从 POST 数据中获取
        if (isset($_POST['nbd-add-to-cart'])) {
            $nbd_item_cart_key = $_POST['nbd-add-to-cart'];
            error_log('Found nbd-add-to-cart: ' . $nbd_item_cart_key);
        }
        
        // 从 SESSION 中获取
        if (isset($_SESSION['nbd_item_cart_key'])) {
            $nbd_item_cart_key = $_SESSION['nbd_item_cart_key'];
            error_log('Found session cart key: ' . $nbd_item_cart_key);
        }
        
        // 生成新的 key
        if (empty($nbd_item_cart_key)) {
            $nbd_item_cart_key = substr(md5(uniqid()), 0, 10);
            error_log('Generated new cart key: ' . $nbd_item_cart_key);
            $_SESSION['nbd_item_cart_key'] = $nbd_item_cart_key;
        }
        
        return $nbd_item_cart_key;
    }

    /**
     * Add custom data to cart item
     */
    public function add_cart_item_data($cart_item_data, $product_id, $variation_id) {
        // 添加调试日志
        error_log('Adding cart item data:');
        error_log('Product ID: ' . $product_id);
        error_log('Variation ID: ' . $variation_id);
        error_log('nbd_design_id: ' . isset($_POST['nbd_design_id']));
        error_log('Original cart item data: ' . print_r($cart_item_data, true));
        // 检查设计数据是否正确添加
        if (isset($_POST['nbd-add-to-cart'])) {
            error_log('NBD add to cart detected');
            $nbd_item_cart_key = ($variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id;//$this->get_cart_item_key();
            error_log('NBD cart item key: ' . $nbd_item_cart_key);
        }
        // echo 'cant in here add_cart_item_data';
        // 添加设计相关数据到购物车项
        if (isset($_POST['nbd_design_id'])) {
            error_log('add_cart_item_data has nbd_design_id: ' . $_POST['nbd_design_id']);
            $cart_item_data['nbd_design_id'] = $_POST['nbd_design_id'];
        } else {
            // mzl 这里设置 nbd_design_id 后购物车中显示设计ID
            $cart_item_data['nbd_design_id'] = WC()->session->get( 'nbd_design_id_' . $nbd_item_cart_key);
            error_log('add_cart_item_data $nbd_item_cart_key: ' . $nbd_item_cart_key . ' nbd_design_id: ' . $cart_item_data['nbd_design_id']);
        }
        return $cart_item_data;
    }

    /**
     * Get item data for display in cart
     */
    public function get_item_data($item_data, $cart_item) {
        // 显示设计相关信息在购物车中
        if (isset($cart_item['nbd_design_id'])) {
            $item_data[] = array(
                'key' => __('Design ID', 'web-to-print-online-designer'),
                'value' => $cart_item['nbd_design_id']
            );
        }
        
        return $item_data;
    }

    public function enqueue_cart_scripts() {
        if (is_cart()) {
            error_log('Enqueueing NBD cart scripts');
            
            // 确保所有依赖都已加载
            wp_enqueue_script(
                'nbdesigner-cart',
                NBDESIGNER_PLUGIN_URL . 'assets/js/cart.js',
                array(
                    'wp-hooks',
                    'wp-blocks',
                    'wp-element',
                    'wc-blocks-registry',
                    'wc-blocks'
                ),
                NBDESIGNER_VERSION,
                true
            );
            
            // 添加调试信息
            wp_add_inline_script('nbdesigner-cart', 'console.log("NBD cart script dependencies loaded");');
        }
    }
    public function get_nbd_cart_data() {
        $cart_data_array = array();
        foreach (WC()->cart->cart_contents as $cart_item_key => $cart_item) {
            error_log('get_nbd_cart_data: Cart item key: ' . $cart_item_key);
            // 特别打印一些重要信息
            error_log('get_nbd_cart_data: Product ID: ' . (isset($cart_item['product_id']) ? $cart_item['product_id'] : 'not set'));
            error_log('get_nbd_cart_data: Variation ID: ' . (isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 'not set'));
            error_log('get_nbd_cart_data: NBD Design ID: ' . (isset($cart_item['nbd_design_id']) ? $cart_item['nbd_design_id'] : 'not set'));
            // error_log('Cart item details: ' . print_r($cart_item, true));
            
            //isset($cart_item['nbo_meta']) && 
            if (isset($cart_item['nbd_design_id'])) {
                // 获取 option_price 中的 cart_image
                $path = NBDESIGNER_CUSTOMER_DIR . '/' . $cart_item['nbd_design_id'] . '/preview';
                $images = Nbdesigner_IO::get_list_images($path, 1);
                $cart_image = '';
                if (count($images)) {
                    $cart_image = Nbdesigner_IO::wp_convert_path_to_url($images[0]);
                }
                error_log('get_nbd_cart_data cart_image: ' . $cart_image);
                $cart_data = array(
                    'cart_item_key' => $cart_item_key,
                    "nbd_item_key" => $cart_item['nbd_design_id'],
                    'product_id' => $cart_item['product_id'],
                    'variation_id' => $cart_item['variation_id'], 
                    'quantity' => $cart_item['quantity'],
                    'design_id' => $cart_item['nbd_design_id'],
                    // 'nbo_meta' => isset($cart_item['nbo_meta']) ? $cart_item['nbo_meta'] : null,
                    // 'line_total' => $cart_item['line_total'],
                    // 'data' => $cart_item['data'],
                    'cart_image' => $cart_image
                );
                //error_log('get_nbd_cart_data Cart item data for ' . $cart_item_key . ': ' . print_r($cart_data[$cart_item_key] ?? 'not set', true));
                error_log('get_nbd_cart_data Cart item data for cart_item_key: ' . $cart_item_key . ' cartImage: ' . $cart_data[$cart_item_key]['cart_image']);
                $cart_data_array[] = $cart_data;
                
                // // 如果没有 cart_image，尝试从 nbdpb 文件夹获取
                // else if (isset($cart_item['nbo_meta']['nbdpb'])) {
                //     $path = NBDESIGNER_CUSTOMER_DIR . '/' . $cart_item['nbo_meta']['nbdpb'] . '/preview';
                //     $images = Nbdesigner_IO::get_list_images($path, 1);
                //     if (count($images)) {
                //         $cart_data[$cart_item_key] = array(
                //             'cart_image' => Nbdesigner_IO::wp_convert_path_to_url($images[0])
                //         );
                //     }
                // }
            }
        }
        wp_send_json_success($cart_data_array);

    }

    public function check_session() {
        if (!WC()->session) {
            wp_send_json_error('Session not initialized');
            return;
        }
    
        $session_data = array(
            'customer_id' => WC()->session->get('customer_id'),
            'logged_in' => is_user_logged_in(),//is_user_logged_in(),
            'user_data' => WC()->session->get('nbd_user_data')
        );
    
        wp_send_json_success($session_data);
    }
    public function remove_design_when_cart_item_removed($cart_item_key, $cart) {
        error_log('Removing design for cart item: ' . $cart_item_key);
        
        // 获取购物车项目数据
        $cart_item = $cart->cart_contents[$cart_item_key];
        if (!isset($cart_item['nbd_design_id']) || !isset($cart_item['product_id']) || !isset($cart_item['variation_id'])) {
            error_log('No design data found for cart item');
            return;
        }
        
        $product_id = $cart_item['product_id'];
        $variation_id = $cart_item['variation_id'];
        $design_id = $cart_item['nbd_design_id'];
        
        error_log("Deleting design - Product: $product_id, Variation: $variation_id, Design: $design_id");
        
        // 查找并删除设计记录
        global $wpdb;
        $design = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nbdesigner_mydesigns 
            WHERE product_id = %d AND variation_id = %d AND folder = %s",
            $product_id,
            $variation_id,
            $design_id
        ));
        
        if ($design) {
            // 删除设计文件夹
            if (!empty($design->folder)) {
                $design_path = NBDESIGNER_CUSTOMER_DIR . '/' . $design->folder;
                if (file_exists($design_path)) {
                    try {
                        Nbdesigner_IO::delete_folder($design_path);
                        error_log("Deleted design folder: $design_path");
                    } catch(Exception $e) {
                        error_log("Error deleting design folder: " . $e->getMessage());
                    }
                }
            }
            
            // 删除数据库记录
            $deleted = My_Design_Endpoint::nbd_delete_my_design_inner(
                $design->product_id,
                $design->variation_id,
                $design->folder
            );
            
            if ($deleted) {
                error_log("Successfully deleted design record from database");
            } else {
                error_log("Failed to delete design record from database");
            }
        } else {
            error_log("No design found in database");
        }
    }

    public function woocommerce_hook(){
        /* ADMIN */
        add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_nbdesinger_order_actions_button' ), 30, 2 );
        
        /* FRONTEND */
        add_filter( 'woocommerce_cart_item_name', array( $this, 'render_cart' ), 1, 3 );
        // add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ), 10, 6 );
        // 添加 AJAX 接口
        add_action('wp_ajax_get_nbd_cart_data', array($this, 'get_nbd_cart_data'));
        add_action('wp_ajax_nopriv_get_nbd_cart_data', array($this, 'get_nbd_cart_data'));
        
        add_action('wp_ajax_nbd_check_session', array($this, 'check_session'));
        add_action('wp_ajax_nopriv_nbd_check_session', array($this, 'check_session'));
        // 添加调试日志
        add_action('woocommerce_add_to_cart', function($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
            error_log('woocommerce_add_to_cart Product added to cart:');
            error_log('woocommerce_add_to_cart Cart item key: ' . $cart_item_key);
            error_log('woocommerce_add_to_cart Product ID: ' . $product_id);
            error_log('woocommerce_add_to_cart Variation ID: ' . $variation_id);
            // error_log('Cart item data: ' . print_r($cart_item_data, true));
        }, 10, 6);
        if( ! is_woo_v3() ){
            add_action( 'woocommerce_add_order_item_meta', array( $this, 'nbdesigner_add_order_design_data' ), 1, 3 );
        }else{
            add_action( 'woocommerce_new_order_item', array( $this, 'nbdesigner_add_new_order_item' ), 1, 3 );
        }
        add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'order_line_item' ), 50, 3 );
        add_action( 'woocommerce_checkout_order_processed', array( $this, 'set_nbd_order_data' ), 1, 1 );
        add_action( 'nbd_checkout_order_processed', array( $this, 'set_nbd_order_data' ), 1, 1 );
        add_filter( 'woocommerce_locate_template', array( $this, 'nbdesigner_locate_plugin_template' ), 20, 3 );
        //add_filter( 'woocommerce_order_item_name', array($this, 'order_item_name'), 1, 2);
        //add_filter( 'woocommerce_order_item_quantity_html', array($this, 'nbdesigner_order_item_quantity_html'), 1, 2);
        add_filter( 'woocommerce_checkout_cart_item_quantity', array( $this, 'nbd_checkout_cart_item_quantity' ), 10, 3 );
        add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'nbdesigner_hidden_custom_order_item_metada' ) );
        add_action( 'nbdesigner_admin_notifications_event', array( $this, 'nbdesigner_admin_notifications_event_action' ) );
        add_action( 'woocommerce_cart_item_removed', array( $this, 'nbdesigner_remove_cart_item_design'), 10, 2 );
        add_action( 'woocommerce_product_after_variable_attributes', array( $this,'nbdesigner_variation_settings_fields' ), 10, 3 );
        add_action( 'woocommerce_save_product_variation', array( $this,'nbdesigner_save_variation_settings_fields' ), 10, 2 );
        // mzl mod add_filter( 'woocommerce_add_cart_item_data', array( $this, 'nbd_add_cart_item_data'), 10, 3 );   
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);
        add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 10, 2);

        // mzl mod add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 1, 2 );
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 10, 2);
        add_action('woocommerce_remove_cart_item', array($this, 'remove_design_when_cart_item_removed'), 10, 2);

        add_filter( 'woocommerce_add_cart_item', array( $this, 'nbd_add_cart_item' ), 99999, 1 );
        add_filter( 'woocommerce_cart_item_price', array( &$this, 'change_cart_item_prices_text' ), 10, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', array( &$this, 'change_cart_item_prices_text' ), 10, 3 );
        add_action( 'woocommerce_before_calculate_totals', array( $this, 'nbd_before_calculate_totals' ), 10, 1 );
        //add_action('woocommerce_cart_loaded_from_session', array($this, 'cart_loaded_from_session'), 100);
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'nbd_before_add_to_cart_button' ) );
        //add_action( 'woocommerce_ordered_again', array( $this, 'nbd_ordered_again' ), 10, 1 );
        add_filter( 'woocommerce_order_again_cart_item_data', array( $this, 'nbd_order_again_cart_item_data' ), 10, 3 );
        $page_design = nbdesigner_get_option( 'nbdesigner_page_design_tool', 1 );
        if( $page_design == 2 ){
            add_action( 'woocommerce_before_single_product', array( $this, 'check_has_design' ) );
            add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'nbd_add_to_cart_redirect' ) );
        }else{
            add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'add_to_cart_text' ), 20, 2 );
            $position = nbdesigner_get_option( 'nbdesigner_position_button_product_detail', 1 );
            if( $position == 1 ){
                add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'nbdesigner_button' ), 30 );
            }else if( $position == 2 ){
                add_action( 'woocommerce_before_add_to_cart_form', array( $this, 'nbdesigner_button2' ), 30 );
            }else if( $position == 3 ){
                add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'nbdesigner_button' ), 30 );
            }
        }
        $catalog_button_pos = nbdesigner_get_option( 'nbdesigner_position_button_in_catalog', 1 );
        if( $catalog_button_pos == 2 ){
           add_action( 'woocommerce_after_shop_loop_item', array( &$this, 'add_catalog_nbdesign_button' ), 20 );
        }elseif( $catalog_button_pos == 1 ) {
            add_filter( 'woocommerce_loop_add_to_cart_link', array( &$this, 'nbdesigner_add_to_cart_shop_link' ), 30, 2 );
        }
        if( nbdesigner_get_option( 'nbdesigner_attachment_admin_email', 'no' ) == 'yes' ){
            add_filter( 'woocommerce_email_attachments', array( &$this, 'attach_design_to_admin_email' ), 10, 3 );
        }
        //add_filter('woocommerce_email_attachments', array(&$this, 'attach_design_to_admin_email2'), 10, 3);
        /** bulk action **/
        // add_action( 'woocommerce_before_add_to_cart_button', array( &$this, 'bulk_variation_field' ), 9999 );
        if ( isset( $_POST['nbd-variation-value'] ) && $_POST['nbd-variation-value'] ) {
            add_action( 'wp_loaded', array( $this, 'process_add_bulk_variation' ), 99 );
        }
        add_filter( 'post_class', array( $this, 'add_post_class' ), 10, 3 );
        /** end. bulk action **/
        add_action( 'woocommerce_order_item_meta_end', array( $this, 'woocommerce_order_item_meta_end' ), 30, 3 );
        add_action( 'woocommerce_order_details_after_order_table', array( $this, 'woocommerce_order_details_after_order_table' ), 30, 1 );
        
        //add_action( 'woocommerce_order_status_completed', array($this, 'update_order_item_meta_data') );
        
        add_filter( 'woocommerce_login_redirect', array( $this, 'login_redirect' ), 10, 1 );
        add_action( 'woocommerce_registration_redirect', array( $this, 'login_redirect' ), 10, 1 );
        add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'nbd_auto_add_to_cart_redirect' ) );

        if( nbdesigner_get_option( 'nbdesigner_auto_export_pdf', 'no' ) == 'yes' ){
            $status = nbdesigner_get_option( 'nbdesigner_order_status_auto_export', 'completed' );
            add_action( 'woocommerce_order_status_' . $status, array($this, 'auto_export_pdf') );
        }
        add_action( 'nbd_synchronize_output', array( $this, 'synchronize_output' ), 10, 3 );
        add_action('wp_enqueue_scripts', array($this, 'enqueue_cart_scripts'));

        // 初始化购物车会话处理
        $this->init_cart_session();
    }
    public function nbd_auto_add_to_cart_redirect( $url ){
        if( isset( $_REQUEST['nbd-auto-add-to-cart-in-detail-page'] ) ){
            $url = esc_url( wc_get_cart_url() );
        }
        return $url;
    }
    public function footer_notice(){
        $valid_license = nbd_check_license();
        if( !$valid_license ){
            include( NBDESIGNER_PLUGIN_DIR .'views/license-notice.php' );
        }
    }
    public function login_redirect( $redirect ){
        if( isset( $_GET['nbd_redirect'] ) ) $redirect = esc_url( getUrlPageNBD( 'redirect' ) );
        if( isset( $_GET['nbu_redirect'] ) ) $redirect = esc_url( $_GET['nbu_redirect'] );
        return $redirect;
    }
    public function nbd_order_again_cart_item_data( $arr, $item, $order ){
        $order_items = $order->get_items();
        foreach( $order_items AS $order_item_id => $_item ){
            $nbd_item_key       = wc_get_order_item_meta( $order_item_id, '_nbd' );
            $nbu_item_key       = wc_get_order_item_meta( $order_item_id, '_nbu' ); 
            $nbd_design_id      = wc_get_order_item_meta( $order_item_id, '_nbd_design_id' ); 
            if( $item->get_id() == $order_item_id ){
                if( $nbd_item_key ){
                    $new_nbd_item_key   = substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
                    $design_path        = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
                    $new_design_path    = NBDESIGNER_CUSTOMER_DIR . '/' . $new_nbd_item_key;
                    Nbdesigner_IO::copy_dir( $design_path, $new_design_path );
                    $arr['nbd_item_meta_ds']['nbd'] = $new_nbd_item_key;
                }
                if( $nbu_item_key ){
                    $new_nbu_item_key   = substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
                    $upload_path        = NBDESIGNER_UPLOAD_DIR . '/' . $nbu_item_key;
                    $new_upload_path    = NBDESIGNER_UPLOAD_DIR . '/' . $new_nbu_item_key;
                    Nbdesigner_IO::copy_dir( $upload_path, $new_upload_path );
                    $arr['nbd_item_meta_ds']['nbu'] = $new_nbu_item_key;
                }
                if( $nbd_design_id ){
                    $arr['nbd_design_id'] = $nbd_design_id;
                }
            }
        }
        return $arr;
    }
    public function add_to_cart_text( $text, $product ){
        $option = unserialize( get_post_meta( $product->get_id(), '_nbdesigner_option', true ) );  
        if( $option['request_quote'] ){
            return esc_html__( 'Get a Quote', 'web-to-print-online-designer' );
        }else {
            return $text;
        }
    }
    public function nbdesigner_add_to_cart_shop_link( $handler, $product ){
        // error_log('nbdesigner_add_to_cart_shop_link check');
        if( is_nbdesigner_product( $product->get_id() ) ){
            $label = esc_html__( 'Start Design', 'web-to-print-online-designer' );
            ob_start();
            nbdesigner_get_template( 'loop/start-design.php', array( 'product' => $product, 'label' => $label, 'type' =>  $product->get_type() ) );
            $button = ob_get_clean();
            return $button;
        }
        return $handler;
    }
    public function add_catalog_nbdesign_button(){
        global $product;
        if( is_nbdesigner_product( $product->get_id() ) ){
            $label = esc_html__( 'Start Design', 'web-to-print-online-designer' );
            ob_start();
            nbdesigner_get_template( 'loop/start-design.php', array( 'product' => $product, 'label' => $label, 'type' =>  $product->get_type() ) );
            $button = ob_get_clean();
            echo $button;
        }
    }
    public function check_has_design(){
        global $product;
        $product_id = $product->get_id();
        if( is_nbdesigner_product( $product_id ) ){
            $layout = nbd_get_product_layout( $product_id );
            if( $layout != 'v' ){
                add_filter( 'woocommerce_product_single_add_to_cart_text', function(){
                    return esc_html__( 'Add to cart & start design', 'web-to-print-online-designer' );               
                });
            }
        }
    }
    public function add_body_class( $classes ){
        if( is_product() ){
            $product_id = get_the_ID();
            if( is_nbdesigner_product( $product_id ) ){
                $classes[]  = 'nbd-single-product-page';
                $option     = unserialize( get_post_meta( $product_id, '_nbdesigner_option', true ) );
                if( $option ){
                    $_layout    = nbdesigner_get_option( 'nbdesigner_design_layout', 'm' );
                    $layout     = isset( $option['layout'] ) ? $option['layout'] : $_layout;
                    if( $layout == 'm' ){
                        $classes[] = 'nbd-modern-layout';
                    }else if( $layout == 'v' ){
                        $classes[] = 'nbd-visual-layout';
                    }
                }
            }
        }
        return $classes;
    }
    public function nbd_add_to_cart_redirect( $url ){
        if( isset( $_REQUEST['nbd-force-ignore-design'] ) ) return $url;
        if( !isset( $_REQUEST['add-to-cart'] ) && !isset( $_REQUEST['nbo-add-to-cart'] ) ) return $url;
        error_log('nbd_add_to_cart_redirect ----');
        $product_id         = isset( $_REQUEST['add-to-cart'] ) ? absint( $_REQUEST['add-to-cart'] ) : absint( $_REQUEST['nbo-add-to-cart'] );
        $variation_id       = isset( $_REQUEST['variation_id'] ) ? absint( $_REQUEST['variation_id'] ) : 0;
        $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id; 
        $layout             = nbd_get_product_layout( $product_id );
        if($layout == 'v'){
            return $url;
        }
        if( is_nbdesigner_product( $product_id ) ){
            $cart_item_key  = WC()->session->get( 'nbd_last_item_cart' );
            $without_design = get_post_meta( $product_id, '_nbdesigner_enable_upload_without_design', true );
            $url            = add_query_arg( array(
                'task2'         => 'update',
                'product_id'    => $product_id,
                'view'          => $layout,
                'cik'           => $cart_item_key
            ), getUrlPageNBD( 'create' ) );
            if( $without_design ){
                $url =  add_query_arg( array(
                    'task2'         => 'add_file',
                    'task'          => 'new',
                    'product_id'    => $product_id,
                    'view'          => 'c',
                    'cik'           => $cart_item_key
                ), getUrlPageNBD( 'create' ) );
            }
            if( isset( $_REQUEST['nbds-ref'] ) && $_REQUEST['nbds-ref'] != '' ){
                $url = add_query_arg( array(
                   'reference'  => $_REQUEST['nbds-ref']
                ), $url);
            }
            if( $cart_item_key ){
                $cart_item = WC()->cart->cart_contents[ $cart_item_key ];
                if( $cart_item ){
                    $cart_product       = $cart_item['data'];
                    $product_permalink  = $cart_product->get_permalink( $cart_item );
                    $att_query          = parse_url( $product_permalink, PHP_URL_QUERY );
                    $url               .= '&'.$att_query;
                }
            }
            if( $variation_id > 0 ) $url .= '&variation_id=' . $variation_id;
            WC()->session->__unset( 'nbd_last_item_cart' );
            $nbd_item_key = substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
            if( !$without_design )  WC()->session->set( 'nbd_item_key_'.$nbd_item_cart_key, $nbd_item_key );
            if( $without_design )   WC()->session->set( 'nbu_item_key_'.$nbd_item_cart_key, $nbd_item_key );
        }
        return $url;
    }
    public function change_cart_item_prices_text( $price, $cart_item, $cart_item_key ){
        $nbd_session = WC()->session->get( $cart_item_key . '_nbd' );
        $nbu_session = WC()->session->get( $cart_item_key . '_nbu' );
        if( isset( $nbd_session ) || isset( $nbu_session ) ){
            $option = unserialize( get_post_meta( $cart_item['product_id'], '_nbdesigner_option', true ) );
            if( $option['request_quote'] ){
                return '-';
            }
        }
        return $price;
    }
    public function nbd_before_add_to_cart_button(){
        $product_id     = get_the_ID();
        $is_nbupload    = get_post_meta( $product_id, '_nbdesigner_enable_upload', true );
        if( $is_nbupload ){
            echo '<input type="hidden" class="nbd-upload" id="nbd-upload-files" name="nbd-upload-files" value="" />';
        }
        if( isset( $_GET['nbo_cart_item_key'] ) && $_GET['nbo_cart_item_key'] != '' && isset( $_GET['task'] ) && $_GET['task'] == 'edit' ){
            echo '<input type="hidden" name="nbu-ignore-update" value="1" />';
            $cart_item = WC()->cart->get_cart_item( $_GET['nbo_cart_item_key'] );
            if( isset( $cart_item['nbd_item_meta_ds'] ) ){
                if( isset( $cart_item['nbd_item_meta_ds']['nbd'] ) ){
                    echo '<input type="hidden" class="nbd-reserve" name="nbd-reserve" value="' . $cart_item['nbd_item_meta_ds']['nbd'] . '" />';
                }
                if( isset( $cart_item['nbd_item_meta_ds']['nbu'] ) ){
                    echo '<input type="hidden" class="nbu-reserve" name="nbu-reserve" value="' . $cart_item['nbd_item_meta_ds']['nbu'] . '" />';
                }
            }
        }
        if( isset( $_GET['nbds-ref'] ) && $_GET['nbds-ref'] != '' ){ ?>
            <input type="hidden" name="nbds-ref" value="<?php echo sanitize_text_field( $_GET['nbds-ref'] ); ?>"/>
        <?php }
    }
    public function nbd_remove_cart_design(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die('Security error');
        }
        $type           = sanitize_text_field( $_POST['type'] );
        $cart_item_key  = sanitize_text_field( $_POST['cart_item_key'] );
        if( $type == 'custom' ){
            WC()->session->__unset( $cart_item_key . '_nbd' );
            unset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbd'] );
            unset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_design_id'] );
        }else {
            WC()->session->__unset( $cart_item_key . '_nbu' );
            unset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbu'] );
        }
        if( count( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] ) == 0 ){
            unset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] );
        }
        WC()->cart->set_session();
        echo 'success';
        wp_die();
    }
    public function nbd_before_calculate_totals( $cart_obj ){
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        foreach ( $cart_obj->get_cart() as $key => $cart_item_data ) {
            $product_id         = $cart_item_data['product_id'];
            $variation_id       = $cart_item_data['variation_id'];
            $product_id         = get_wpml_original_id( $product_id );
            $variation_id       = get_wpml_original_id( $variation_id );
            $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id;
            $nbd_session        = WC()->session->get( $key . '_nbd' );
            $nbu_session        = WC()->session->get( $key . '_nbu' );
            
            if( isset( $cart_item_data["nbd_item_meta_ds"] ) ){
                if( isset( $cart_item_data["nbd_item_meta_ds"]['nbd'] ) ){
                    $nbd_session = $cart_item_data["nbd_item_meta_ds"]['nbd'];
                }

                if( isset( $cart_item_data["nbd_item_meta_ds"]['nbu'] ) ){
                    $nbu_session = $cart_item_data["nbd_item_meta_ds"]['nbu'];
                }
            }

            if( isset( $nbd_session ) || isset( $nbu_session ) ){
                $option = unserialize( get_post_meta( $product_id, '_nbdesigner_option', true ) );
                if( isset( $nbd_session ) ) {
                    $path       = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/config.json';
                    $config     = nbd_get_data_from_json( $path );
                    if( isset( $option['additional_price'] ) && $option['additional_price'] != '' && $option['additional_price'] != 0 && isset( $config->origin_product ) ){
                        $num_additional_side    = count( $config->product ) - count( $config->origin_product );
                        $additional_price       = floatval( $option['additional_price'] );
                        $option['extra_price']  = $num_additional_side * $additional_price;
                    }
                    if( isset( $config->custom_dimension ) && isset( $config->custom_dimension->price ) ){
                        $nbd_variation_price = $config->custom_dimension->price;
                    }
                }
                if( $option['request_quote'] || $option['extra_price'] || ( isset( $nbd_variation_price ) && $nbd_variation_price != 0 ) ){
                    $initial_price = WC()->session->get( $key . '_nbd_initial_price' );
                    if( isset( $cart_item_data["nbd_item_meta_ds"] ) && isset( $cart_item_data["nbd_item_meta_ds"]["initial_price"] ) ){
                        $price = $cart_item_data["nbd_item_meta_ds"]["initial_price"];
                    }
                    if( $initial_price ){
                        $price = $initial_price;
                    } else {
                        $price = $cart_item_data['data']->get_price();
                        WC()->session->set( $key . '_nbd_initial_price', $price );
                    }
                    if( isset( $cart_item_data["nbd_item_meta_ds"] ) ){
                        $cart_item_data["nbd_item_meta_ds"]["initial_price"] = $price;
                        WC()->cart->set_session();
                    }
                }
                if( $option['request_quote'] ){
                    $cart_item_data['data']->set_price( 0 );
                } else if( $option['extra_price'] || ( isset( $nbd_variation_price ) && $nbd_variation_price != 0 ) ){
                    $decimals   = wc_get_price_decimals();  
                    $new_price  = $price;
                    if( $option['type_price'] == 1 ){
                        $new_price += $option['extra_price'];
                    }else{
                        $new_price += $price * $option['extra_price'] / 100;
                    }
                    if( ( isset( $nbd_variation_price ) && $nbd_variation_price != 0) ) $new_price += $nbd_variation_price;
                    $new_price = round( $new_price, $decimals );
                    $cart_item_data['data']->set_price( $new_price );
                }
            } else {
                /* Destroy get a quote or extra price when remove design */
                if ( WC()->session->__isset( $key . '_nbd_initial_price' ) || ( isset( $cart_item_data["nbd_item_meta_ds"] ) && isset( $cart_item_data["nbd_item_meta_ds"]["initial_price"] ) ) ) {
                    $initial_price = WC()->session->get( $key . '_nbd_initial_price' );
                    if( isset( $cart_item_data["nbd_item_meta_ds"] ) && isset( $cart_item_data["nbd_item_meta_ds"]["initial_price"] ) ){
                        $initial_price = $cart_item_data["nbd_item_meta_ds"]["initial_price"];
                        unset( $cart_item_data["nbd_item_meta_ds"]["initial_price"] );
                    }
                    WC()->session->__unset( $key . '_nbd_initial_price' );
                    $cart_item_data['data']->set_price( $initial_price );
                    WC()->cart->set_session();
                }
            }
        }
    }
    // mzl mod 点加入购物车不会进
    public function nbd_add_cart_item( $cart_item_data ){
        $product_id         = $cart_item_data['product_id'];
        error_log('nbd_add_cart_item ------$product_id: ' . $product_id);
        $variation_id       = $cart_item_data['variation_id'];
        $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id;
        $nbd_item_session   = WC()->session->get( 'nbd_item_key_' . $nbd_item_cart_key );
        $nbu_item_session   = WC()->session->get( 'nbu_item_key_' . $nbd_item_cart_key );
        if( isset( $nbd_item_session ) || isset( $nbu_item_session ) ){
            $option = unserialize( get_post_meta( $product_id, '_nbdesigner_option', true ) );
            if( $option['request_quote'] ){
                $cart_item_data['data']->set_price( 0 );
            }
        }
        return $cart_item_data;
    } 
    public function nbdesigner_admin_enqueue_scripts( $hook ){
        wp_register_script( 'angularjs', NBDESIGNER_PLUGIN_URL . 'assets/libs/angular-1.6.9.min.js', array( 'jquery' ), '1.6.9' );
        wp_register_script( 'fontfaceobserver', NBDESIGNER_PLUGIN_URL . 'assets/libs/fontfaceobserver.js', array(), '2.0.13' );
        wp_register_style( 'nbd-general', NBDESIGNER_CSS_URL . 'nbd-general.css', array( 'dashicons' ), NBDESIGNER_VERSION );
        wp_enqueue_style( array( 'nbd-general' ) );

        wp_register_style( 'admin_nbdesigner', NBDESIGNER_CSS_URL . 'admin-nbdesigner.css', array('wp-color-picker'), NBDESIGNER_VERSION );
        if ( in_array( $hook, apply_filters( 'nbd_admin_hooks_need_asset', array( 'post.php', 'post-new.php', 'nbdesigner_page_nbdesigner_manager_fonts', 'nbdesigner_page_nbdesigner_manager_arts', 'toplevel_page_nbdesigner', 'nbdesigner_page_nbdesigner_manager_product', 'toplevel_page_nbdesigner_shoper', 'nbdesigner_page_nbdesigner_frontend_translate', 'nbdesigner_page_nbdesigner_tools', 'nbdesigner_page_manage_color' ) ) ) ){
            wp_register_script( 'admin_nbdesigner', NBDESIGNER_JS_URL . 'admin-nbdesigner.js', array('jquery', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-autocomplete', 'wp-color-picker', 'jquery-ui-datepicker', 'jquery-ui-sortable' ), NBDESIGNER_VERSION );
            wp_localize_script( 'admin_nbdesigner', 'admin_nbds', array(
                'url'               => admin_url( 'admin-ajax.php' ),
                'nonce'             => wp_create_nonce('nbdesigner_add_cat'),
                'mes_success'       => esc_html__( 'Success!', 'web-to-print-online-designer' ),
                'url_check'         => NBDESIGNER_AUTHOR_SITE,
                'sku'               => NBDESIGNER_SKU,
                'url_gif'           => NBDESIGNER_PLUGIN_URL . 'assets/images/loading.gif',
                'assets_images'     => NBDESIGNER_PLUGIN_URL . 'assets/images/',
                'setting_page'      => admin_url( 'admin.php?page=nbdesigner' ),
                'max_file_uploads'  => function_exists( 'ini_get' ) ? ini_get( 'max_file_uploads' ) : 9999,
                'nbds_lang'         => nbd_get_i18n_javascript() ) );
            wp_enqueue_style( array( 'wp-pointer', 'wp-jquery-ui-dialog', 'admin_nbdesigner' ) );
            wp_enqueue_script( array( 'wp-pointer', 'wpdialogs', 'admin_nbdesigner' ) );
        }
        if( $hook == 'admin_page_nbdesigner_detail_order' || $hook == 'nbdesigner_page_nbdesigner_manager_product' ){
            wp_enqueue_media();
            wp_register_style(
                'admin_nbdesigner_detail_order', 
                NBDESIGNER_CSS_URL . 'detail_order.css', 
                array('jquery-ui-style-css'), NBDESIGNER_VERSION );
            wp_register_style(
                'jquery-ui-style-css', 
                '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css', 
                array(), '1.11.4' );
            wp_enqueue_style(array('admin_nbdesigner_detail_order','jquery-ui-style-css'));
            wp_register_script(
                'admin_nbdesigner_detail_order_slider',
                NBDESIGNER_JS_URL . 'owl.carousel.min.js',
                array('jquery', 'jquery-ui-tabs', 'jquery-ui-resizable', 'jquery-ui-draggable'),
                NBDESIGNER_VERSION );
            wp_enqueue_script( array( 'jquery', 'jquery-ui-tabs' ) );
        }
        if( $hook == 'nbdesigner_page_nbdesigner_manager_fonts' ){
            wp_enqueue_script( 'angularjs' );
            wp_enqueue_script( 'fontfaceobserver' );
        }
        if( $hook == 'nbdesigner_page_nbdesigner_frontend_translate' ){
            wp_register_script( 'admin_nbdesigner_jeditable', NBDESIGNER_JS_URL . 'jquery.jeditable.js', array( 'jquery' ) );
            wp_enqueue_script( 'admin_nbdesigner_jeditable' );
        }
        if( $hook == 'nbdesigner_page_nbdesigner_tools' ){
            wp_enqueue_style( 'admin_nbdesigner_codemirror', NBDESIGNER_PLUGIN_URL . 'assets/codemirror/codemirror.css' );
            wp_enqueue_script( 'nbdesigner_codemirror_js', NBDESIGNER_PLUGIN_URL . 'assets/codemirror/codemirror.js' , array() );
            wp_enqueue_script( 'nbdesigner_codemirror_css_js', NBDESIGNER_PLUGIN_URL . 'assets/codemirror/css.js' , array() );
        }
        if( $hook == 'nbdesigner_page_nbdesigner_admin_template' || $hook == 'nbdesigner_page_nbdesigner_manager_arts'
            || $hook == 'admin_page_nbdesigner_detail_order' || $hook == 'nbdesigner_page_nbdesigner_manager_fonts' 
            || $hook == 'nbdesigner_page_nbdesigner_tools' || $hook == 'nbdesigner_page_nbdesigner_frontend_translate' || $hook == 'nbdesigner_page_manage_color' ){
            wp_enqueue_style( 'nbdesigner_sweetalert_css', NBDESIGNER_CSS_URL . 'sweetalert.css' );
            wp_enqueue_script( 'nbdesigner_sweetalert_js', NBDESIGNER_JS_URL . 'sweetalert.min.js' , array( 'jquery' ) );
        }
        if( $hook == 'toplevel_page_nbdesigner' ){
            wp_enqueue_media();
            wp_enqueue_style( 'nbdesigner_settings_css', NBDESIGNER_CSS_URL . 'admin-settings.css', array(), NBDESIGNER_VERSION );
        }
        if( is_rtl() ){
            wp_enqueue_style( 'nbd-rtl', NBDESIGNER_CSS_URL . 'nbd-rtl.css', array(), NBDESIGNER_VERSION );
        }
        error_log('nbdesigner_admin_enqueue_scripts ---');

    }
    public function frontend_enqueue_scripts(){
        // 注册本地 jQuery
        wp_register_script(
            'jquery-local',
            NBDESIGNER_PLUGIN_URL . 'assets/libs/jquery.min.js',
            array(),
            '3.6.0'
        );
        wp_register_style( 'nbdesigner', NBDESIGNER_CSS_URL . 'nbdesigner.css', array(), NBDESIGNER_VERSION );
        wp_enqueue_style( 'nbdesigner' );
        $depend_arr         = array( 'jquery', 'jquery-blockui', 'wp-util' );
        $show_favicon_badge = nbdesigner_get_option( 'nbdesigner_enable_favicon_badge', 'no' );
        if( $show_favicon_badge == 'yes' ){
            wp_register_script( 'nbd_favico', NBDESIGNER_PLUGIN_URL . 'assets/libs/favico.min.js', array(), '0.3.10' );
            $depend_arr[]   = 'nbd_favico';
        }
        $depends            = apply_filters( 'nbd_depend_js', $depend_arr );
        wp_register_script( 'nbdesigner', NBDESIGNER_JS_URL . 'nbdesigner.js', $depends, NBDESIGNER_VERSION );
        $args = apply_filters( 'nbd_js_object', array(
            'url'                   => admin_url( 'admin-ajax.php' ),
            'sid'                   => session_id(),
            'nonce'                 => wp_create_nonce('save-design'),
            'nonce_get'             => wp_create_nonce('nbdesigner-get-data'),
            'cart_url'              => esc_url( wc_get_cart_url() ),
            'hide_cart_button'      => 'no',//nbdesigner_get_option( 'nbdesigner_hide_button_cart_in_detail_page', 'no' ),
            'auto_add_cart'         => nbdesigner_get_option( 'nbdesigner_auto_add_cart_in_detail_page', 'no' ),
            'page_design_tool'      => nbdesigner_get_option( 'nbdesigner_page_design_tool', 1 ),
            'show_favicon_badge'    => $show_favicon_badge,
            'confirm_delete_design' => esc_html__( 'Are you sure you want to delete this design?', 'web-to-print-online-designer' ),
            'delete_success'        => esc_html__( 'Delete successfully!', 'web-to-print-online-designer' ),
            'create_design_url'     => getUrlPageNBD( 'create' ),
            'gallery_url'           => getUrlPageNBD( 'gallery' ),
            'edit_option_mode'      => ( isset( $_GET['nbo_cart_item_key'] ) && $_GET['nbo_cart_item_key'] != '' && isset( $_GET['task'] ) && $_GET['task'] == 'edit' ) ? 1 : 0,
            'is_mobile'             => wp_is_mobile() ? 1 : 0,
            'is_user_logged_in' => is_user_logged_in() ? '1' : '0',
            'login_url' => wp_login_url(get_permalink())
        ) );
        wp_localize_script( 'nbdesigner', 'nbds_frontend', $args );
        wp_enqueue_script( 'nbdesigner' );
        //wp_enqueue_script( 'custom-console-log', NBDESIGNER_JS_URL . 'custom-console-log.js' , array( 'jquery' ) );


        // 获取颜色设置
        $color_settings = get_option('nbdesigner_color_settings', array());
        if (empty($color_settings)) {
            $color_settings = array(
                'combination_colors' => array(
                    'bg_codes' => array('#FF5733', '#33FF57', '#3357FF', '#FF33F6', '#33FFF6', '#F6FF33'),
                    'bg_names' => array('Red', 'Green', 'Blue', 'Pink', 'Cyan', 'Yellow'),
                    'fg_codes' => array('#FFFFFF', '#000000', '#FFFFFF', '#000000', '#000000', '#000000'),
                    'fg_names' => array('White', 'Black', 'White', 'Black', 'Black', 'Black')
                ),
                'background_colors' => array(
                    'codes' => array('#FFFFFF', '#000000', '#FF5733', '#33FF57', '#3357FF'),
                    'names' => array('White', 'Black', 'Red', 'Green', 'Blue')
                )
            );
            update_option('nbdesigner_color_settings', $color_settings);
        }

        // 将颜色设置添加到前端配置
        $settings = array(
            'nbes_settings' => $color_settings
        );

        // 添加调试信息
        // error_log('Frontend settings: ' . print_r($settings, true));

        wp_localize_script('nbdesigner', 'nbdesigner_settings', array(
            'settings' => $settings,
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nbdesigner-nonce')
        ));
    }
    public static function plugin_activation( $network_wide ) {
        if ( is_multisite() && $network_wide ) {
            global $wpdb;
            foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
                switch_to_blog( $blog_id );
                self::_plugin_activation();
                restore_current_blog();
            }
        } else {
            self::_plugin_activation();
        }
    }
    public static function _plugin_activation() {
        if ( version_compare( $GLOBALS['wp_version'], NBDESIGNER_MINIMUM_WP_VERSION, '<' ) ) {
            $message = sprintf( __( '<p>Plugin <strong>not compatible</strong> with WordPress %s. Requires WordPress %s to use this Plugin.</p>', 'web-to-print-online-designer' ), $GLOBALS['wp_version'], NBDESIGNER_MINIMUM_WP_VERSION );
            die( $message );
        }
        if( version_compare( PHP_VERSION, NBDESIGNER_MINIMUM_PHP_VERSION, '<' ) ){
            $message = sprintf(__('<p>Plugin <strong>not compatible</strong> with PHP %s. Requires PHP %s to use this Plugin.</p>', 'web-to-print-online-designer' ), PHP_VERSION, NBDESIGNER_MINIMUM_PHP_VERSION );
            die( $message );
        }
        if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $message = '<div class="error"><p>' . sprintf( __('WooCommerce is not active. Please activate WooCommerce before using %s.', 'web-to-print-online-designer' ), '<b>Nbdesigner</b>' ) . '</p></div>';
            die( $message );
        }
        $woocommer_data = get_plugin_data( WP_PLUGIN_DIR .'/woocommerce/woocommerce.php', false, false );
        if ( version_compare ( $woocommer_data['Version'] , NBDESIGNER_MINIMUM_WC_VERSION, '<' ) ){
            $message = '<div class="error"><p>' . sprintf( __('WoooCommerce %s or greater is required', 'web-to-print-online-designer' ), NBDESIGNER_MINIMUM_WC_VERSION ) . '</p></div>';
            die( $message );
        }

        self::install();
    }
    public static function install(){
        /* Install */
        NBD_Install::create_pages();
        NBD_Install::create_tables();
        NBD_Install::init_files_and_folders();
        NBD_Install::insert_default_files();
        
        /* Update data */
        NBD_Update_Data::install_update();
        
        $design_endpoint = new My_Design_Endpoint();
        $design_endpoint->add_endpoints();
        
        $_nbd_template_loader = new NBD_Template_Loader();
        $_nbd_template_loader->add_rewrites();
        
        if( self::is_new_install() ){
            set_transient( '_nbd_activation_redirect', 1, 30 );
        }
        
        update_option( 'nbdesigner_version_plugin', NBDESIGNER_VERSION );
        do_action( 'nbd_installed' );
        flush_rewrite_rules();
    }
    public function install_in_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ){
        if ( is_plugin_active_for_network( 'web-to-print-online-designer/nbdesigner.php' ) ) {
            switch_to_blog( $blog_id );
            $this->install();
            restore_current_blog();
        }
    }
    private static function is_new_install() {
        return is_null( get_option( 'nbdesigner_version_plugin', null ) );
    }    
    public function set_schedule( $schedules ){
        if( !isset( $schedules['hourly'] ) ){
            $schedules['hourly'] = array('interval' => 60*60, 'display' => esc_html__('Once Hourly'));
        }
        if( !isset( $schedules['every6hours'] ) ){
            $schedules['every6hours'] = array( 'interval' => 6 * HOUR_IN_SECONDS, 'display' => esc_html__( 'Every 6 Hours', 'web-to-print-online-designer' ) );
        }
        return $schedules;
    }
    public function schehule(){
        $timestamp = wp_next_scheduled( 'nbdesigner_lincense_event' );
        if( $timestamp == false ){
            wp_schedule_event( time(), 'daily', 'nbdesigner_lincense_event' );
        }
        $timestamp2     = wp_next_scheduled( 'nbdesigner_admin_notifications_event' );
        $notifications  = get_option( 'nbdesigner_notifications', false );
        $recurrence     = 'hourly';
        if( $timestamp2 == false && $notifications === false ){
            wp_schedule_event( time(), $recurrence, 'nbdesigner_admin_notifications_event' );
        }
        /* Schedule delete upload files */
        $retain_time = nbdesigner_get_option( 'nbdesigner_long_time_retain_upload_fies', '' );
        if( absint( $retain_time ) > 0 ){
            $last_check_time = get_transient( 'nbd_last_time_check_upload_files' );
            if( false === $last_check_time ){
                $last_check_time = time();
                set_transient( 'nbd_last_time_check_upload_files' , $last_check_time, DAY_IN_SECONDS );
                $this->delete_expired_upload_files( $retain_time );
            }
        }
    }
    public function nbdesigner_lincense_event_action(){
        //$path = NBDESIGNER_PLUGIN_DIR . 'data/license.json';
        //$path_data = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
        //if(file_exists($path) || file_exists($path_data)){
            $license    = $this->nbdesigner_check_license();
            $now        = strtotime("now");
            if( isset( $license['type'] ) && $license['type'] != 'free' && isset( $license['expiry'] ) && $license['expiry'] < $now ){
                $result = $this->nbdesiger_request_license( $license['key'], $this->activedomain );
                if( $result ){
                    $data           = (array) json_decode( $result );
                    $data['key']    = $license['key'];
                    if($data['type'] == 'free') $data['number_domain'] = "5";
                    $data['salt'] = md5($license['key'].$data['type']);
                    //$this->nbdesigner_write_license(json_encode($data));
                    update_option( 'nbdesigner_license', json_encode( $data ) );
                }
            }
        //}
        add_action( 'admin_notices', array( $this, 'nbdesigner_lincense_notices' ) );	
    }
    public function nbdesigner_admin_notifications_event_action(){
        $notifications  = get_option( 'nbdesigner_notifications', false );
        $owner_email    = get_option( 'nbdesigner_notifications_emails', false );
        if( $notifications != false ){
            global $woocommerce;
            if( $notifications == 'yes' ){
                if( version_compare( $woocommerce->version, "2.2", ">=" ) ){
                    $post_status = array( 'wc-processing', 'wc-completed', 'wc-on-hold', 'wc-pending' );
                }else{
                    $post_status = 'publish';
                }
                $args = array(
                    'post_type'         => 'shop_order',
                    'orderby'           => 'date',
                    'order'             => 'DESC',
                    'posts_per_page'    => -1,
                    'post_status'       => $post_status,
                    'meta_query'        => array(
                        'relation'  => 'OR',
                        array(
                            'key'   => '_nbdesigner_order_changed',
                            'value' => 1
                        ),
                        array(
                            'key'   => '_nbdesigner_upload_order_changed',
                            'value' => 1
                        )
                    )
                );
                $post_orders    = get_posts( $args );
                $orders         = array();
                foreach ( $post_orders AS $order ) {
                    $the_order          = new WC_Order( $order->ID );
                    $orders[$order->ID] = $the_order->get_order_number();
                }
                if ( count( $orders ) ) {
                    foreach ( $orders AS $order => $order_number ) {
                        update_post_meta( $order, '_nbdesigner_order_changed', 0 );
                        update_post_meta( $order, '_nbdesigner_upload_order_changed', 0 );
                    }
                    $subject    = esc_html__( 'New / Modified order(s)', 'web-to-print-online-designer' );
                    $mailer     = $woocommerce->mailer();
                    ob_start();
                    wc_get_template( 'emails/nbdesigner-admin-notifications.php', array(
                        'plugin_id'     => 'nbdesigner',
                        'orders'        => $orders,
                        'heading'       => $subject
                    ) );
                    $emails         = new WC_Emails();
                    $woo_recipient  = $emails->emails['WC_Email_New_Order']->recipient;
                    if($owner_email == ''){
                        if( !empty( $woo_recipient ) ) {
                            $user_email = esc_attr( $woo_recipient );
                        } else {
                            $user_email = get_option( 'admin_email' );
                        }
                    }else{
                        $user_email = $owner_email;
                    }
                    $body = ob_get_clean();
                    if ( !empty( $user_email ) ) {
                        $mailer->send( $user_email, $subject, $body );
                    }
                }
            }
        }
    }
    public function nbdesigner_add_query_vars_filter( $vars ){
        $vars[] = "nbds-adid";
        $vars[] = "nbds-ref";
        return $vars;
    }
    public function nbdesigner_admin_posts_filter( $query ){
        global $typenow;
        if ( 'product' == $typenow ) {
            if ( !empty( $_GET['has_nbd'] ) ) {
                $query->query_vars['meta_key']      = '_nbdesigner_enable';
                $query->query_vars['meta_value']    = esc_sql( $_GET['has_nbd'] );
            }
        }
    }
    public function nbdesigner_product_sorting_nbd( $views ){
        global $wp_query;
        $class            = '';
        $query_string     = remove_query_arg( array( 'orderby', 'order' ) );
        $query_string     = add_query_arg( 'has_nbd', urlencode('1'), $query_string );
        $views['has_nbd'] = '<a href="' . esc_url( $query_string ) . '" class="' . esc_attr( $class ) . '">' . esc_html__( 'Has NBDesigner', 'web-to-print-online-designer') . '</a>';        
        return $views;
    }
    public function nbdesigner_lincense_notices(){
        $license = $this->nbdesigner_check_license();
        if( $license['status'] == 0 ){
            add_action( 'admin_notices', array( $this, 'nbdesigner_lincense_notices_content' ) );     
        } 
    }
    public function nbdesigner_lincense_notices_content(){
        //$mes = nbd_custom_notices('notices', 'You\'re using NBDesigner free version (full features and function but for max 5 products) or expired pro version. <br /><a class="nbd-notice-action" href="http://cmsmart.net/wordpress-plugins/woocommerce-online-product-designer-plugin" target="_blank">Please buy the Premium version here to use for all product </a>');
        $mes            = nbd_custom_notices( 'notices', '<a class="nbd-license-notice" href="https://cmsmart.net/wordpress-plugins/woocommerce-online-product-designer-plugin?utm_source=backend&utm_medium=cpc&utm_campaign=wpol&utm_content=banner" target="_blank"><img src="'.NBDESIGNER_ASSETS_URL . 'images/lite_version.jpg" alt="Lite version"/></a>' );
        $current_screen = get_current_screen();
        $nbd_pages      = nbd_admin_pages();
        if ( isset( $current_screen->id ) && in_array( $current_screen->id, $nbd_pages ) ) printf( $mes );
    }
    public function translation_load_textdomain() {	 
        load_plugin_textdomain( 'web-to-print-online-designer', false, dirname(dirname( plugin_basename( __FILE__ ) ) ) . '/langs/' );
    }
    public static function plugin_deactivation() {
        wp_clear_scheduled_hook( 'nbdesigner_lincense_event' );
        wp_clear_scheduled_hook( 'nbdesigner_admin_notifications_event' );
        flush_rewrite_rules();
    }
    public function upload_mimes( $mimes ) {
        $mimes['svg']   = 'image/svg+xml';
        $mimes['svgz']  = 'image/svg+xml';
        $mimes['woff']  = 'application/x-font-woff';
        $mimes['ttf']   = 'application/x-font-ttf';
        $mimes['eps']   = 'application/postscript';
        return $mimes;
    }
    public function nbdesigner_settings(){
        $page_id    = 'nbdesigner';
        $tabs       = apply_filters('nbdesigner_settings_tabs', array(
            'general'           => '<span class="dashicons dashicons-admin-generic"></span> ' . esc_html__('General', 'web-to-print-online-designer'),
            'appearance'        => '<span class="dashicons dashicons-admin-appearance"></span> '. esc_html__('Appearance', 'web-to-print-online-designer'),
            'frontend'          => '<span class="dashicons dashicons-admin-customizer"></span> '. esc_html__('Design Tool', 'web-to-print-online-designer'),
            'color'             => '<span class="dashicons dashicons-art"></span> '. esc_html__('Colors', 'web-to-print-online-designer'),
            'upload'            => '<span class="dashicons dashicons-upload"></span> '. esc_html__('Upload design', 'web-to-print-online-designer'),
            'output'            => '<span class="dashicons dashicons-download"></span> '. esc_html__('Output', 'web-to-print-online-designer'),
            'libraries'         => '<span class="dashicons dashicons-paperclip"></span> '. esc_html__('Libraries', 'web-to-print-online-designer'),
            'printing_option'   => '<span class="dashicons dashicons-forms"></span> '. esc_html__('Printing Options', 'web-to-print-online-designer')
        ) );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/general.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/frontend.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/colors.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/upload.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/output.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/libraries.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/appearance.php' );
        require_once( NBDESIGNER_PLUGIN_DIR . 'includes/settings/printing_option.php' );
        do_action( 'nbdesigner_include_settings' );
        $Nbdesigner_Settings = new Nbdesigner_Settings( array(
            'page_id'   => $page_id,
            'tabs'      => $tabs
        ) );
        $blocks = apply_filters( 'nbdesigner_settings_blocks', array(
            'general'           => array(
                'general-settings'      => esc_html__('General Settings', 'web-to-print-online-designer'),
                'admin-notifications'   => esc_html__('Notifications', 'web-to-print-online-designer'),
                'application'           => esc_html__('Application', 'web-to-print-online-designer'),
                'nbd-pages'             => esc_html__('NBD Pages', 'web-to-print-online-designer'),
                'tools'                 => esc_html__('Tools', 'web-to-print-online-designer'),
                'customization'         => esc_html__('Customization', 'web-to-print-online-designer')
            ),
            'appearance'        => array(
                'editor-settings'               => esc_html__('Design editor', 'web-to-print-online-designer'),
                'modern-settings'               => esc_html__('Modern layout', 'web-to-print-online-designer'),
                'product-settings'              => esc_html__('Product', 'web-to-print-online-designer'),
                'category-settings'             => esc_html__('Category', 'web-to-print-online-designer'),
                'cart-checkout-order-settings'  => esc_html__('Cart, order, checkout', 'web-to-print-online-designer'),
                'misc-settings'                 => esc_html__('Misc', 'web-to-print-online-designer')
            ),            
            'frontend'          => array(
                'tool-text'     => esc_html__('Text Options', 'web-to-print-online-designer'),
                'tool-clipart'  => esc_html__('Elements Options', 'web-to-print-online-designer'),
                'tool-image'    => esc_html__('Image Options', 'web-to-print-online-designer'),
                'tool-draw'     => esc_html__('Free draw Options', 'web-to-print-online-designer'),
                'tool-qrcode'   => esc_html__('Qr Code Options', 'web-to-print-online-designer'),
                'misc'          => esc_html__('Misc', 'web-to-print-online-designer')
            ),
            'color'             => array(
                'color-setting' => esc_html__('Setting color', 'web-to-print-online-designer')
            ),
            'upload' => array(
                'upload-settings' => esc_html__('Upload settings', 'web-to-print-online-designer')
            ),
            'output'            => array(
                'output-settings'   => esc_html__('Output general settings', 'web-to-print-online-designer'),
                'synchronize'       => esc_html__('Synchronize', 'web-to-print-online-designer'),
                'jpeg-settings'     => esc_html__('JPG settings', 'web-to-print-online-designer'),
                'svg-settings'      => esc_html__('SVG settings', 'web-to-print-online-designer')
            ),
            'libraries'         => array(
                'js-settings'   => esc_html__('Javacript settings', 'web-to-print-online-designer'),
                'css-settings'  => esc_html__('CSS settings', 'web-to-print-online-designer')
            ), 
            'printing_option'   =>  array(
                'printing-general'  => esc_html__('General', 'web-to-print-online-designer'),
                'printing-catalog'  => esc_html__('Catalog', 'web-to-print-online-designer'),
                'printing-cart'     => esc_html__('Cart', 'web-to-print-online-designer'),
                'printing-order'    => esc_html__('Order', 'web-to-print-online-designer'),
                'printing-editor'   => esc_html__('Design editor', 'web-to-print-online-designer'),
                'printing-admin'    => esc_html__('Admin manager', 'web-to-print-online-designer')
            )
        ));
        $Nbdesigner_Settings->add_blocks( $blocks );
        $Nbdesigner_Settings->add_blocks_description(array());
        $frontend_options       = Nbdesigner_Settings_Frontend::get_options();
        $general_options        = Nbdesigner_Settings_General::get_options();
        $color_options          = Nbdesigner_Settings_Colors::get_options();
        $upload_options         = Nbdesigner_Settings_Upload::get_options();
        $output_options         = Nbdesigner_Settings_Output::get_options();
        $libraries_options      = Nbdesigner_Libraries::get_options();
        $appearance_options     = Nbdesigner_Appearance_Settings::get_options();
        $printing_options       = Nbdesigner_Printing_Options::get_options();
        $options = apply_filters( 'nbdesigner_settings_options', array(
            'general-settings'              => $general_options['general-settings'],
            'admin-notifications'           => $general_options['admin-notifications'],
            'application'                   => $general_options['application'],
            'nbd-pages'                     => $general_options['nbd-pages'],
            'customization'                 => $general_options['customization'],
            'tools'                         => $general_options['tools'],
            'tool-text'                     => $frontend_options['tool-text'],
            'tool-clipart'                  => $frontend_options['tool-clipart'],
            'tool-image'                    => $frontend_options['tool-image'],
            'tool-draw'                     => $frontend_options['tool-draw'],
            'tool-qrcode'                   => $frontend_options['tool-qrcode'],
            'misc'                          => $frontend_options['misc'],
            'color-setting'                 => $color_options['color-setting'],
            'upload-settings'               => $upload_options['upload-settings'],
            'output-settings'               => $output_options['output-settings'],
            'synchronize'                   => $output_options['synchronize'],
            'jpeg-settings'                 => $output_options['jpeg-settings'],
            'svg-settings'                  => $output_options['svg-settings'],
            'js-settings'                   => $libraries_options['js-settings'],
            'css-settings'                  => $libraries_options['css-settings'],
            'product-settings'              => $appearance_options['product'],
            'editor-settings'               => $appearance_options['editor'],
            'modern-settings'               => $appearance_options['modern'],
            'category-settings'             => $appearance_options['category'],
            'cart-checkout-order-settings'  => $appearance_options['cart-checkout-order'],
            'misc-settings'                 => $appearance_options['misc'],
            'printing-general'              => $printing_options['general'],
            'printing-catalog'              => $printing_options['catalog'],
            'printing-cart'                 => $printing_options['cart'],
            'printing-order'                => $printing_options['order'],
            'printing-editor'               => $printing_options['editor'],
            'printing-admin'                => $printing_options['admin']
        ) );
        foreach( $options as $key => $option ){
            $Nbdesigner_Settings->add_block_options( $key, $option );
        }
        do_action( 'nbdesigner_before_options_save', $page_id );
        if ( isset( $_POST['nbdesigner_save_options_'.$page_id] ) ) {
            check_admin_referer( $page_id.'_nonce' );
            $Nbdesigner_Settings->save_options();
        }
        else if( isset( $_POST['nbdesigner_reset_options_'.$page_id] ) ) {
            check_admin_referer( $page_id.'_nonce' );
            $Nbdesigner_Settings->reset_options();
        } 
        add_action( 'nbdesigner_settings_header_start', array( &$this, 'display_license_key' ) );
        $Nbdesigner_Settings->output();
    }
    public function admin_footer_text( $footer_text ){
        $current_screen     = get_current_screen();
        $nbd_pages          = nbd_admin_pages();
        if ( isset( $current_screen->id ) && in_array( $current_screen->id, $nbd_pages ) ){
            $footer_text = sprintf( __( 'If you <span style="color: #e25555;">♥</span> <strong>NBDesigner</strong> please leave us a %s&#9733;&#9733;&#9733;&#9733;&#9733;%s rating. A huge thanks in advance!', 'web-to-print-online-designer'), '<a href="https://cmsmart.net/wordpress-plugins/woocommerce-online-product-designer-plugin" target="_blank" class="nbd-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'web-to-print-online-designer') . '">', '</a>' );
        }
        return $footer_text;
    }
    public function display_license_key(){
        $license        = $this->nbdesigner_check_license();
        $site_title     = get_bloginfo( 'name' );
        $site_url       = base64_encode(rtrim(get_bloginfo('wpurl'), '/'));
        require_once( NBDESIGNER_PLUGIN_DIR . 'views/license-form.php' );
    }
    public function nbdesigner_menu(){
        $layout = nbdesigner_get_option('nbdesigner_design_layout');
        if( isset( $_POST['nbdesigner_design_layout'] ) ){
            $layout = sanitize_text_field( $_POST['nbdesigner_design_layout'] );
        };
        if( current_user_can( 'manage_nbd_setting' ) ) {
            add_menu_page( 'Nbdesigner', 'NBDesigner', 'manage_nbd_setting', 'nbdesigner', array( $this, 'nbdesigner_settings'), NBDESIGNER_PLUGIN_URL . 'assets/images/logo-icon-r.svg', 26 );
            $nbdesigner_manage = add_submenu_page(
                'nbdesigner', esc_html__('NBDesigner Settings', 'web-to-print-online-designer'), esc_html__('Settings', 'web-to-print-online-designer'), 'manage_nbd_setting', 'nbdesigner', array( $this, 'nbdesigner_settings' )
            );
            add_action( 'load-'.$nbdesigner_manage, array( 'Nbdesigner_Helper', 'settings_helper' ) );
        }
        if( current_user_can( 'manage_nbd_product' ) ){
            $product_hook = add_submenu_page(
                'nbdesigner', esc_html__('Manager Products', 'web-to-print-online-designer'), esc_html__( 'Products', 'web-to-print-online-designer' ), 'manage_nbd_product', 'nbdesigner_manager_product', array( $this, 'nbdesigner_manager_product' )
            );
            add_action( "load-$product_hook", array( $this, 'nbdesigner_template_screen_option' ));
            add_submenu_page(
                '', esc_html__('Detail Design Order', 'web-to-print-online-designer'), esc_html__( 'Detail Design Order', 'web-to-print-online-designer' ), 'manage_nbd_product', 'nbdesigner_detail_order', array( $this, 'nbdesigner_detail_order' )
            );
        }
        if( current_user_can( 'manage_nbd_art' ) ){
            add_submenu_page(
                'nbdesigner', esc_html__( 'Manager Cliparts', 'web-to-print-online-designer' ), esc_html__( 'Cliparts', 'web-to-print-online-designer' ), 'manage_nbd_art', 'nbdesigner_manager_arts', array( $this, 'nbdesigner_manager_arts' )
            );
        }
        if( current_user_can( 'manage_nbd_font' ) ){
            add_submenu_page(
                'nbdesigner', esc_html__( 'Manager Fonts', 'web-to-print-online-designer' ), esc_html__( 'Fonts', 'web-to-print-online-designer' ), 'manage_nbd_font', 'nbdesigner_manager_fonts', array( $this, 'nbdesigner_manager_fonts' )
            );
        }
        if( current_user_can( 'manage_nbd_language' ) && $layout == 'c' ){
            add_submenu_page(
                'nbdesigner', esc_html__( 'Frontend Translate', 'web-to-print-online-designer' ), esc_html__( 'Frontend Translate', 'web-to-print-online-designer' ), 'manage_nbd_language', 'nbdesigner_frontend_translate', array( $this, 'nbdesigner_frontend_translate' )
            );
        }
        do_action( 'nbd_menu' );
        if ( current_user_can( 'manage_nbd_tool' ) ) {
            add_submenu_page(
                'nbdesigner', esc_html__( 'System info', 'web-to-print-online-designer' ), esc_html__( 'System info', 'web-to-print-online-designer' ), 'manage_nbd_tool', 'nbdesigner_system_info', array( $this, 'system_info' )
            );
            add_submenu_page(
                'nbdesigner', esc_html__( 'NBDesigner Tools', 'web-to-print-online-designer' ), esc_html__( 'Tools', 'web-to-print-online-designer' ), 'manage_nbd_tool', 'nbdesigner_tools', array( $this, 'nbdesigner_tools' )
            );
        }
        $remote = get_transient( 'nbd_upgrade_news_web-to-print-online-designer' );
        if( $remote ){
            add_submenu_page(
                'nbdesigner', esc_html__( 'NBDesigner Support', 'web-to-print-online-designer' ), esc_html__( 'About', 'web-to-print-online-designer' ), 'manage_nbd_setting', 'nbd_support', array( $this, 'nbd_support' )
            );
        }
    }
    public function nbdesigner_template_screen_option() {
        if( isset( $_GET['view'] ) && $_GET['view'] == 'templates' ){
            $option = 'per_page';
            $args   = array(
                'label'   => 'Templates',
                'default' => 10,
                'option'  => 'templates_per_page'
            );
            add_screen_option( $option, $args );
        }
    }
    public static function set_screen( $status, $option, $value ) {
        return $value;
    }
    public function nbdesigner_get_license_key(){
        if ( !wp_verify_nonce( $_POST['nbdesigner_getkey_hidden'], 'nbdesigner-get-key') || !current_user_can( 'administrator' ) ) {
            die('Security error');
        }
        if( isset( $_POST['nbdesigner'] ) ){
            $data       = wc_clean( $_POST['nbdesigner'] );
            $email      = base64_encode( $data['email'] );
            $domain     = $data['domain'];
            $title      = ( $data['name'] != '' ) ? urlencode( $data['name'] ) : urlencode( $data['title'] );
            $ip         = base64_encode( $this->nbdesigner_get_ip() );
            $url        = NBDESIGNER_AUTHOR_SITE . 'subcrible/WPP1074/' . $email . '/' . $domain . '/' . $title . '/' . $ip;
            $result     = nbd_file_get_contents( $url );
            if( isset( $result ) ) {
                echo $result;
            }else{
                echo 'Please try later!';
            }
            wp_die();
        }
    }
    public function nbdesigner_get_ip(){
        $ip = $_SERVER['REMOTE_ADDR'];
        if( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $ip;
    }
    public static function nbdesigner_add_action_links( $links ){
        $mylinks = array(
            'setting' => '<a href="' . admin_url( 'options-general.php?page=nbdesigner' ) . '">'. esc_html__( 'Settings', 'web-to-print-online-designer' ) .'</a>'
        );
        return array_merge( $mylinks, $links );
    }
    public static function nbdesigner_plugin_row_meta( $links, $file ) {
        if( $file == NBDESIGNER_PLUGIN_BASENAME ){
            $row_meta = array(
                'livedemo'      => '<a href="https://nbdesigner.cmsmart.net" target="_blank"><span class="dashicons  dashicons-laptop"></span>Live Demo</a>',
                'document'      => '<a href="https://cmsmart.net/community/woocommerce-online-product-designer-plugin/userguides" target="_blank"><span class="dashicons dashicons-book-alt"></span>Document</a>',
                'support'       => '<a href="https://cmsmart.net/support_ticket" target="_blank"><span class="dashicons  dashicons-businessman"></span>Support</a>',
                'community'     => '<a href="https://cmsmart.net/community/woocommerce-online-product-designer-plugin" target="_blank"><span class="dashicons dashicons-groups"></span>Community</a>'
            );
            return array_merge( $links, $row_meta );
        }
        return (array) $links;
    }
    public function nbdesigner_manager_product() {
        if( isset( $_GET['view'] ) && $_GET['view'] == "templates" ){
            $pid            = absint( $_GET['pid'] );
            $pro            = wc_get_product( $pid );
            $templates_obj  = new Product_Template_List_Table();
            include_once( NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-admin-template.php' );
        }else{
            $q = '';
            if( isset( $_POST['q'] ) ){
                $q = $_POST['q'] != '' ? sanitize_text_field( $_POST['q'] ) : '';
            }else if( isset( $_GET['s'] ) && $_GET['s'] != '' ){
                $q = sanitize_text_field( $_GET['s'] );
            }
            $products       = get_transient( 'nbd_list_products' );
            $number_pro     = get_transient( 'nbd_number_of_products' );
            $limit          = apply_filters( 'nbd_admin_products_per_page', 20 );
            if ( $q != '' || false === $products ) {
                $args_query = array(
                    'post_type'         => 'product',
                    'post_status'       => 'publish',
                    'meta_key'          => '_nbdesigner_enable',
                    'orderby'           => 'id',
                    'order'             => 'DESC',
                    'posts_per_page'    => -1,
                    'meta_query'        => array(
                        array(
                            'key'   => '_nbdesigner_enable',
                            'value' => 1,
                        )
                    )
                );
                if( $q != '' ) $args_query['s'] = $q;
                $products       = get_posts( $args_query );
                $number_pro     = count( $products );
                if( $q == '' ){
                    set_transient( 'nbd_list_products' , $products, DAY_IN_SECONDS );  
                    set_transient( 'nbd_number_of_products' , $number_pro, DAY_IN_SECONDS );
                }
            }  
            $page = filter_input( INPUT_GET, "p", FILTER_VALIDATE_INT );
            if( isset( $page ) ){
                $_tp = ceil( $number_pro / $limit );
                if( $page > $_tp ) $page = $_tp;
                $products = array_slice( $products, ( $page - 1 ) * $limit, $limit );
            }else{
                $page = 1;
                if( $number_pro > $limit ) $products = array_slice( $products, ( $page - 1 ) * $limit, $limit );	
            }
            $pro = array();
            foreach ( $products as $product ) {
                $product_id = $product->ID;
                $_product   = wc_get_product( $product_id );
                $pro[] = array(
                    'url'               => admin_url( 'post.php?post=' . absint( $product_id ) . '&action=edit' ),
                    'img'               => $_product->get_image(),
                    'name'              => $product->post_title,
                    'id'                => absint( $product_id ),
                    'number_template'   =>  Product_Template_List_Table::count_product_template( $product_id )
                );
            }
            $url = admin_url( 'admin.php?page=nbdesigner_manager_product' );
            if( $q != '' ) $url .= "&s={$q}";
            require_once NBDESIGNER_PLUGIN_DIR . 'includes/class.nbdesigner.pagination.php';
            $paging = new Nbdesigner_Pagination();
            $config = array(
                'current_page'  => isset( $page ) ? $page : 1,
                'total_record'  => $number_pro,
                'limit'         => $limit,
                'link_full'     => $url.'&p={p}',
                'link_first'    => $url
            );
            $paging->init( $config );
            include_once( NBDESIGNER_PLUGIN_DIR . 'views/manager-product.php' );
        }
    }
    public function delete_nbd_product_transient(){
        delete_transient( 'nbd_list_products' );
        delete_transient( 'nbd_number_of_products' );
        delete_transient( 'nbd_frontend_products' );
    }
    public function nbdesigner_add_font_cat() {
        $data = array(
            'mes'   => esc_html__( 'You do not have permission to add/edit font category!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || !current_user_can( 'edit_nbd_font' ) ) {
            echo json_encode( $data );
            wp_die();
        }
        $path = NBDESIGNER_DATA_DIR . '/font_cat.json';
        $list = $this->nbdesigner_read_json_setting($path);
        $cat  = array(
            'name'  => sanitize_text_field( $_POST['name'] ),
            'id'    => absint( $_POST['id'] )
        );
        $this->nbdesigner_update_json_setting( $path, $cat, $cat['id'] );
        $data['mes']    = esc_html__( 'Category has been added/edited successfully!', 'web-to-print-online-designer' );
        $data['flag']   = 1;
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_add_art_cat() {
        $data = array(
            'mes'   => esc_html__( 'You do not have permission to add/edit clipart category!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || !current_user_can( 'edit_nbd_art' ) ) {
            echo json_encode( $data );
            wp_die();
        }
        $path   = NBDESIGNER_DATA_DIR . '/art_cat.json';
        $cat    = array(
            'name'  => sanitize_text_field($_POST['name']),
            'id'    => absint( $_POST['id'] )
        );
        $this->nbdesigner_update_json_setting( $path, $cat, $cat['id'] );
        $data['mes']    = esc_html__( 'Category has been added/edited successfully!', 'web-to-print-online-designer' );
        $data['flag']   = 1;
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_delete_font_cat() {
        $data = array(
            'mes'   => esc_html__( 'You do not have permission to delete font category!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || !current_user_can( 'edit_nbd_font' ) ) {
            echo json_encode( $data );
            wp_die();
        }
        $path   = NBDESIGNER_DATA_DIR . '/font_cat.json';
        $id     = absint( $_POST['id'] );
        $this->nbdesigner_delete_json_setting( $path, $id, true );
        $font_path  = NBDESIGNER_DATA_DIR . '/fonts.json';
        $this->nbdesigner_update_json_setting_depend( $font_path, $id );
        $data['mes']    = esc_html__( 'Category has been delete successfully!', 'web-to-print-online-designer' );
        $data['flag']   = 1;
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_delete_art_cat() {
        $data = array(
            'mes'   => esc_html__( 'You do not have permission to delete clipart category!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if (!wp_verify_nonce($_POST['nonce'], 'nbdesigner_add_cat') || !current_user_can('delete_nbd_art')) {
            echo json_encode( $data );
            wp_die();
        }
        $path   = NBDESIGNER_DATA_DIR . '/art_cat.json';
        $id     = $_POST['id'];
        $this->nbdesigner_delete_json_setting( $path, $id, true );
        $art_path = NBDESIGNER_DATA_DIR . '/arts.json';
        $this->nbdesigner_update_json_setting_depend( $art_path, $id );
        $data['mes']    = esc_html__( 'Category has been delete successfully!', 'web-to-print-online-designer' );
        $data['flag']   = 1;
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_get_list_google_font() {
        $path = NBDESIGNER_PLUGIN_DIR . 'data/listgooglefonts.json';
        $data = (array) $this->nbdesigner_read_json_setting($path);
        return json_encode($data);
    }
    public function nbdesigner_add_google_font() {
        $data = array(
            'mes'   => esc_html__( 'You do not have permission to add font!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || !current_user_can( 'edit_nbd_font' ) ) {
            die('Security error');
        }
        $gg_fonts = array();
        if( !isset( $_POST['fonts'] ) ){
            die( 'Empty data' );
        }else{
            $all_fonts  = json_decode( file_get_contents( NBDESIGNER_PLUGIN_DIR. '/data/google-fonts-ttf.json' ) )->items;
            $fonts      = json_decode( stripslashes( $_POST['fonts'] ) );
            foreach( $fonts as $key => $font ){
                $subset = 'all';
                $file   = array( 'r' => 1 );
                foreach( $all_fonts as $f ){
                    if( $font->name == $f->family ){
                        $subset = $f->subsets[0];
                        if( isset( $f->files->regular ) ){
                            $file['r'] = $f->files->regular;
                        } else {
                            $file['r'] = reset( $f->files );
                        }
                        if( isset($f->files->italic) ){
                            $file['i'] = $f->files->italic;
                        }
                        if( isset($f->files->{"700"}) ){
                            $file['b'] = $f->files->{"700"};
                        }
                        if( isset($f->files->{"700italic"}) ){
                            $file['bi'] = $f->files->{"700italic"};
                        }
                        break;
                    }
                }
                $gg_fonts[] = array(
                    "id"        => $key,
                    "name"      => $font->name,
                    "alias"     => $font->name,
                    "type"      => "google", 
                    "subset"    => $subset, 
                    "file"      => $file, 
                    "cat"       => array("99")
                );
            }
        }
        $path_font      = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        file_put_contents( $path_font, json_encode( $gg_fonts ) );
        $data['mes']    = esc_html__( 'The google fonts have been added successfully!', 'web-to-print-online-designer' );
        $data['flag']   = 1;
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_manager_fonts() {
        $notice                 = '';
        $font_id                = 0;
        $cats                   = array("0");
        $current_font_cat_id    = 0;
        $update                 = false;
        $list                   = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/fonts.json');	
        $cat                    = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/font_cat.json');
        $data_font_google       = $this->nbdesigner_read_json_setting(NBDESIGNER_DATA_DIR . '/googlefonts.json');
        $list_all_google_font   = $this->nbdesigner_get_list_google_font();
        $current_cat            = filter_input(INPUT_GET, "cat_id", FILTER_VALIDATE_INT);
        $font = array(
            'file' => array()
        );
        if (is_array($cat))
            $current_font_cat_id = sizeof( $cat );
        if ( isset( $_GET['id'] ) ) {
            $font_id = $_GET['id'];
            $update = true;
            if ( isset( $list[$font_id] ) ) {
                $font_data      = $list[$font_id];
                $cats           = $font_data->cat;
                $font['url']    = $font_data->url;
                $font['id']     = $font_data->id;
                $font['type']   = $font_data->type;
                $font['alias']  = $font_data->alias;
                $font['subset'] = $font_data->subset;
                $font['file']   = (array)$font_data->file;
            }
        }
        if ( isset( $_POST[$this->plugin_id . '_hidden'] ) && wp_verify_nonce( $_POST[$this->plugin_id . '_hidden'], $this->plugin_id ) && current_user_can( 'edit_nbd_font' ) ) {
            $font['name']   = esc_html($_POST['nbdesigner_font_name']);
            $font['subset'] = $_POST['subset'];
            $font['alias']  = isset($font['alias']) ? $font['alias'] : 'nbfont' . substr( md5( rand( 0, 999999 ) ), 0, 10 );
            $font['cat']    = $cats;
            if ( isset( $_POST['nbdesigner_font_cat'] ) ) $font['cat'] = $_POST['nbdesigner_font_cat'];
            $check = true;
            if ( isset( $_FILES['font']) && array_filter( $_FILES['font']['name'] ) && ( !empty($font['file']['r']) || $_FILES['font']['name'][0] != '' ) && ( $_POST['nbdesigner_font_name'] != '' ) ) {
                foreach( $_FILES['font']['name'] as $key => $font_name ){
                    if( $font_name != '' ){
                        $uploaded_file_name = basename( $font_name );
                        $allowed_file_types = array('ttf');
                        $font['type']       = $this->nbdesigner_get_extension( $uploaded_file_name );
                        if ( Nbdesigner_IO::checkFileType( $uploaded_file_name, $allowed_file_types ) ) {
                            switch ($key) {
                                case 1:
                                    $f_index = 'i';
                                    break;
                                case 2:
                                    $f_index = 'b';
                                    break;
                                case 3:
                                    $f_index = 'bi';
                                    break;
                                default:
                                    $f_index = 'r';
                            }
                            $prefix         = $key ? $f_index : '';
                            $new_path_font  = Nbdesigner_IO::create_file_path( NBDESIGNER_FONT_DIR, $font['alias'].$prefix, $font['type'], true );
                            if( move_uploaded_file( $_FILES['font']["tmp_name"][$key], $new_path_font['full_path'].'.'.$font['type'] ) ){
                                if( !$key ){
                                    $font['url'] = $new_path_font['date_path'] . '.' . $font['type'];
                                }
                                $font['file'][$f_index] = $new_path_font['date_path'] . '.' . $font['type'];
                            }else{
                                $check  = false;
                                $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'error', esc_html__( 'Error while upload font.', 'web-to-print-online-designer' ) ) );
                                break;
                            }
                        }else{
                            $check = false;
                            $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'error', esc_html__( 'Incorrect file extensions.', 'web-to-print-online-designer' ) ) );
                            break;
                        }
                    }
                }
                if( $check ){
                    if ($update) {
                        $this->nbdesigner_update_list_fonts( $font, $font_id );
                    } else {
                        $this->nbdesigner_update_list_fonts( $font );
                    }
                    $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'success', esc_html__( 'Your font has been saved.', 'web-to-print-online-designer' ) ) );
                }
            } else if($update && ($_POST['nbdesigner_font_name'] != '')){
                $font_data->name    = $_POST['nbdesigner_font_name'];
                $font_data->subset  = $_POST['subset'];
                $font_data->cat     = $font['cat'];
                if( isset($_POST['display_name']) ){
                    $font_data->display_name = $_POST['display_name'];
                }
                $this->nbdesigner_update_list_fonts($font_data, $font_id);
                $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('success', esc_html__('Your font has been saved.', 'web-to-print-online-designer' ) ) );
            } else {
                $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'warning', esc_html__( 'Please choose font file or font name.', 'web-to-print-online-designer' ) ) );
            }
            $list = $this->nbdesigner_read_json_setting( NBDESIGNER_DATA_DIR . '/fonts.json' );
            $cats = $font['cat'];
        }
        $current_cat_id = 0;
        if( isset( $current_cat ) ){
            $current_cat_id = $current_cat;
            $new_list       = array();
            foreach( $list as $art ){
                if( in_array( (string)$current_cat, $art->cat ) ) $new_list[] = $art;
            }
            foreach($cat as $c){
                if($c->id == $current_cat){
                    $name_current_cat = $c->name;
                    break;
                } 
                $name_current_cat = 'uploaded';
            }
            $list = $new_list;
        }else{
            $name_current_cat = 'uploaded';
        }
        $total = sizeof($list);
        //NBD_Update_Data::update_fonts();
        include_once( NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-manager-fonts.php' );
    }
    /**
     * Analytics and statistics customer used product design
     * @since 1.5
     * 
    */
    public function nbdesigner_analytics() {
        //TODO something analytics
        include_once( NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-analytics.php' );
    }
    public function nbdesigner_update_list_fonts( $font, $id = null ) {
        if (isset($id)) {
            $this->nbdesigner_update_font($font, $id);
            return;
        }
        $list_font  = array();
        $path       = NBDESIGNER_DATA_DIR . '/fonts.json';
        $list       = $this->nbdesigner_read_json_setting($path);
        if (is_array($list)) {
            $list_font  = $list;
            $id         = sizeOf($list_font);
            $font['id'] = (string) $id;
        }
        $list_font[]    = $font;
        $res            = json_encode( $list_font );
        file_put_contents( $path, $res );
    }
    public function nbdesigner_update_list_arts($art, $id = null) {
        $path = NBDESIGNER_DATA_DIR . '/arts.json';
        if ( isset( $id ) ) {
            $this->nbdesigner_update_json_setting( $path, $art, $id );
            return;
        }
        $list_art = array();
        $list = $this->nbdesigner_read_json_setting($path);
        if (is_array($list)) {
            $list_art   = $list;
            $id         = sizeOf($list_art);
            $art['id']  = (string) $id;
        }
        $list_art[] = $art;
        $res = json_encode($list_art);
        file_put_contents($path, $res);
    }
    public function nbdesigner_read_json_setting($fullname) {
        if (file_exists($fullname)) {
            $list = json_decode(file_get_contents($fullname));
        } else {
            $list = '[]';
            file_put_contents($fullname, $list);
            $list = array();
        }
        return $list;
    }
    public function nbdesigner_delete_json_setting($fullname, $id, $reindex = true) {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (is_array($list)) {
            array_splice($list, $id, 1);
            if ($reindex) {
                $key = 0;
                foreach ($list as $val) {
                    $val->id = (string) $key;
                    $key++;
                }
            }
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }
    public function nbdesigner_update_json_setting($fullname, $data, $id) {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (is_array($list))
            $list[$id] = $data;
        else {
            $list = array();
            $list[] = $data;
        }
        $_list = array();
        foreach ($list as $val) {
            $_list[] = $val;
        }
        $res = json_encode($_list);
        file_put_contents($fullname, $res);
    }
    public function nbdesigner_update_json_setting_depend($fullname, $id) {
        $list = $this->nbdesigner_read_json_setting($fullname);
        if (!is_array($list)) return;
        foreach ($list as $val) {
            if (!((sizeof($val) > 0))) continue;
            //if (!sizeof($val->cat)) break;
            foreach ($val->cat as $k => $v) {
                if ($v == $id) {
                    array_splice($val->cat, $k, 1);
                    break;
                }
            }
            foreach ($val->cat as $k => $v) {
                if ($v > $id) {
                    $new_v = (string) --$v;
                    unset($val->cat[$k]);
                    array_splice($val->cat, $k, 0, $new_v);
                    //$val->cat[$k] = (string)--$v;
                }
            }
        }
        $res = json_encode($list);
        file_put_contents($fullname, $res);
    }
    public function nbdesigner_delete_font() {
        $data = array(
            'mes'   => esc_html__('You do not have permission to delete font!', 'web-to-print-online-designer'),
            'flag'  => 0
        );
        if (!wp_verify_nonce($_POST['nonce'], 'nbdesigner_add_cat') || !current_user_can('delete_nbd_font')) {
            echo json_encode($data);
            wp_die();
        }
        $id = $_POST['id'];
        $type = $_POST['type'];
        if ($type == 'custom') {
            $path = NBDESIGNER_DATA_DIR . '/fonts.json';
            $list = $this->nbdesigner_read_json_setting($path);
        } else
            $path = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        $this->nbdesigner_delete_json_setting($path, $id);
        $data['mes'] = esc_html__('The font has been deleted successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_delete_art() {
        $data = array(
            'mes'   =>  esc_html__('You do not have permission to delete clipart!', 'web-to-print-online-designer'),
            'flag'  => 0
        );
        if (!wp_verify_nonce($_POST['nonce'], 'nbdesigner_add_cat') || !current_user_can('delete_nbd_art')) {
            echo json_encode($data);
            wp_die();
        }
        $id = $_POST['id'];
        $path = NBDESIGNER_DATA_DIR . '/arts.json';
        $list = $this->nbdesigner_read_json_setting($path);
        $file_art = $list[$id]->file;
        if(file_exists($file_art)){
            unlink($file_art);
        }else{
            $file_art = NBDESIGNER_ART_DIR . $list[$id]->file;
            unlink($file_art);
        }
        $this->nbdesigner_delete_json_setting($path, $id);
        $data['mes'] = esc_html__('Clipart has been deleted successfully!', 'web-to-print-online-designer');
        $data['flag'] = 1;
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_update_font($font, $id) {
        $path = NBDESIGNER_DATA_DIR . '/fonts.json';
        $this->nbdesigner_update_json_setting($path, $font, $id);
    }
    public function nbdesigner_notices($value = '') {
        return $value;
    }
    public function nbdesigner_custom_notices($command, $mes) {
        switch ($command) {
            case 'success':
                if (!isset($mes)) $mes = esc_html__('Your settings have been saved.', 'web-to-print-online-designer');
                $notice = '<div class="updated notice notice-success is-dismissible">
                                <p>' . $mes . '</p>
                                <button type="button" class="notice-dismiss">
                                    <span class="screen-reader-text">'. esc_html__('Dismiss this notice.', 'web-to-print-online-designer') .'</span>
                                </button>
                            </div>';
                break;
            case 'error':
                if (!isset($mes)) $mes = esc_html__('Irks! An error has occurred.', 'web-to-print-online-designer');
                $notice = '<div class="notice notice-error is-dismissible">
                                <p>' . $mes . '</p>
                                <button type="button" class="notice-dismiss">
                                    <span class="screen-reader-text">'. esc_html__('Dismiss this notice.', 'web-to-print-online-designer') .'</span>
                                </button>
                            </div>';
                break;
            case 'notices':
                if (!isset($mes)) $mes = esc_html__('Irks! An error has occurred.', 'web-to-print-online-designer');
                $notice = '<div class="notice notice-warning">
                                <p>' . $mes . '</p>
                            </div>';
                break;
            case 'warning':
                if (!isset($mes)) $mes = esc_html__('Warning.', 'web-to-print-online-designer');
                $notice = '<div class="notice notice-warning is-dismissible">
                                <p>' . $mes . '</p>
                                <button type="button" class="notice-dismiss">
                                    <span class="screen-reader-text">'. esc_html__('Dismiss this notice.', 'web-to-print-online-designer') .'</span>
                                </button>
                            </div>';
                break;
            default:
                $notice = '';
        }
        return $notice;
    }
    public function nbdesigner_manager_arts() {
        $notice             = '';
        $current_art_cat_id = 0;
        $art_id             = 0;
        $update             = false;
        $cats               = array("0");
        $list               = $this->nbdesigner_read_json_setting( NBDESIGNER_DATA_DIR . '/arts.json' );
        $cat                = $this->nbdesigner_read_json_setting( NBDESIGNER_DATA_DIR . '/art_cat.json' );
        $total              = sizeof($list);
        $limit              = 40;
        if ( is_array( $cat ) )
            $current_art_cat_id = sizeof( $cat );
        if ( isset( $_GET['id'] ) ) {
            $art_id = absint( $_GET['id'] );
            $update = true;
            if ( isset( $list[$art_id] ) ) {
                $art_data   = $list[$art_id];
                $cats       = $art_data->cat;
            }
        }
        $page           = filter_input( INPUT_GET, "p", FILTER_VALIDATE_INT );
        $current_cat    = filter_input( INPUT_GET, "cat_id", FILTER_VALIDATE_INT );

        if ( isset( $_POST[$this->plugin_id . '_hidden'] ) && wp_verify_nonce( $_POST[$this->plugin_id . '_hidden'], $this->plugin_id ) && current_user_can( 'edit_nbd_art' ) ) {
            $art        = array();
            $art['id']  = absint( $_POST['nbdesigner_art_id'] );
            $art['cat'] = $cats;
            if ( isset( $_POST['nbdesigner_art_cat'] ) ) $art['cat'] = $_POST['nbdesigner_art_cat'];
            if ( isset( $_FILES['svg'] ) ) {
                $files = $_FILES['svg'];
                foreach ( $files['name'] as $key => $value ) {
                    $file = array(
                      'name'     => $files['name'][$key],
                      'type'     => $files['type'][$key],
                      'tmp_name' => $files['tmp_name'][$key],
                      'error'    => $files['error'][$key],
                      'size'     => $files['size'][$key]
                    );
                    $uploaded_file_name = sanitize_file_name( basename($file['name']) );
                    $allowed_file_types = array( 'svg', 'png', 'jpg', 'jpeg' );
                    if ( Nbdesigner_IO::checkFileType( $uploaded_file_name, $allowed_file_types ) ) {
                        $upload_overrides   = array( 'test_form' => false );
                        $uploaded_file      = wp_handle_upload( $file, $upload_overrides );
                        if ( isset( $uploaded_file['url'] ) ) {
                            $new_path_art   = Nbdesigner_IO::create_file_path( NBDESIGNER_ART_DIR, $uploaded_file_name );
                            $art['file']    = $uploaded_file['file'];
                            $art['url']     = $uploaded_file['url'];
                            $art['name']    = pathinfo( $art['file'], PATHINFO_FILENAME );
                            if ( !copy( $art['file'], $new_path_art['full_path'] ) ) {
                                $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'error', esc_html__( 'Failed to copy.', 'web-to-print-online-designer' ) ) );
                            }else{
                                $art['file']    = $new_path_art['date_path'];
                                $art['url']     = $new_path_art['date_path'];
                                //$art['url'] = NBDESIGNER_ART_URL . $new_path_art['date_path'];
                            }
                            if ( $update ) {
                                $this->nbdesigner_update_list_arts( $art, $art_id );
                            } else {
                                $this->nbdesigner_update_list_arts( $art );
                            }
                            $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'success', esc_html__( 'Your art has been saved.', 'web-to-print-online-designer' ) ) );
                        } else {
                            $notice = apply_filters( 'nbdesigner_notices', nbd_custom_notices( 'error', sprintf( __( 'Error while upload art, please try again! <a target="_blank" href="%s">Force upload SVG</a>', 'web-to-print-online-designer' ), esc_url( admin_url( 'admin.php?page=nbdesigner&tab=general#nbdesigner_option_download_type' ) ) ) ) );
                        }
                    } else {
                        $notice = apply_filters('nbdesigner_notices', nbd_custom_notices('error', esc_html__( 'Incorrect file extensions.', 'web-to-print-online-designer' ) ) );
                    }
                }
            }
            $list   = $this->nbdesigner_read_json_setting( NBDESIGNER_DATA_DIR . '/arts.json' );
            $cats   = $art['cat'];
            $total  = sizeof( $list );
            
        }
        $current_cat_id     = 0;
        $name_current_cat   = 'uploaded';
        if( $total ){
            if( isset( $current_cat ) ){
                $current_cat_id = $current_cat;
                $new_list       = array();
                foreach( $list as $art ){
                    if( in_array( (string)$current_cat, $art->cat ) ) $new_list[] = $art;
                    if( ( $current_cat == 0 ) && sizeof( $art->cat ) == 0) $new_list[] = $art;
                }
                foreach( $cat as $c ){
                    if( $c->id == $current_cat ){
                        $name_current_cat = $c->name;
                        break;
                    }
                    $name_current_cat = 'uploaded';
                }
                $list = $new_list;
                $total = sizeof( $list );
            }else{
                $name_current_cat = 'uploaded';
            }
            if( isset( $page ) ){
                $_tp = ceil( $total / $limit );
                if($page > $_tp) $page = $_tp;
                $_list = array_slice( $list, ( $page-1 ) * $limit, $limit );
            }else{
                $_list = $list;
                if($total > $limit) $_list = array_slice( $list, 0, $limit );
            }
        } else{
            $_list = array();
        }
        if( isset( $current_cat ) ){
            $url = add_query_arg( array( 'cat_id' => $current_cat ), admin_url( 'admin.php?page=nbdesigner_manager_arts' ) );
        }else{
            $url = admin_url( 'admin.php?page=nbdesigner_manager_arts' );
        }
        require_once NBDESIGNER_PLUGIN_DIR . 'includes/class.nbdesigner.pagination.php';
        $paging = new Nbdesigner_Pagination();
        $config = array(
            'current_page'  => isset( $page ) ? $page : 1,
            'total_record'  => $total,
            'limit'         => $limit,
            'link_full'     => $url.'&p={p}',
            'link_first'    => $url
        );
        $paging->init( $config );
        include_once( NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-manager-arts.php' );
    }
    public function admin_success() {
        if ( isset( $_POST[$this->plugin_id . '_hidden'] ) && wp_verify_nonce( $_POST[$this->plugin_id . '_hidden'], $this->plugin_id ) ){
            echo '<div class="updated notice notice-success is-dismissible">
                        <p>' . esc_html__('Your settings have been saved.', 'web-to-print-online-designer') . '</p>
                        <button type="button" class="notice-dismiss">
                            <span class="screen-reader-text">'. esc_html__('Dismiss this notice.', 'web-to-print-online-designer') .'</span>
                        </button>
                  </div>';
        }
    }
    public function add_design_box() {
        add_meta_box( 'nbdesigner_setting', esc_html__( 'Setting NBDesigner', 'web-to-print-online-designer' ), array( $this, 'setting_design' ), 'product', 'normal', 'high' );
        add_meta_box( 'nbdesigner_order', esc_html__( 'Customer Design', 'web-to-print-online-designer' ), array( $this, 'order_design' ), 'shop_order', 'side', 'default' );
    }
    public function nbdesigner_detail_order() {
        if( isset( $_GET['order_id'] ) ){
            $order_id   = absint( $_GET['order_id'] );
            $order      = new WC_Order( $order_id );
            $zip_files  = array();
            $files      = array();
            if( isset( $_GET['download-type'] ) && ( $_GET['download-type'] == 'png-hires' || $_GET['download-type'] == 'jpg-hires' || $_GET['download-type'] == 'pdf' || $_GET['download-type'] == 'png' || $_GET['download-type'] == 'jpg' || $_GET['download-type'] == 'cmyk' || $_GET['download-type'] == 'svg' || $_GET['download-type'] == 'ibg' ) ){
                $type           = sanitize_text_field( $_GET['download-type'] );
                $product_id     = absint( $_GET['product_id'] );
                $variation_id   = absint( $_GET['variation_id'] );
                $nbd_item_key   = sanitize_text_field( $_GET['nbd_item_key'] );
                $path           = NBDESIGNER_CUSTOMER_DIR .'/'. $_GET['nbd_item_key'];
                if( $type != 'png' && $type != 'svg' && $type != 'ibg' && $type != 'png-hires' && $type != 'jpg-hires' && $type != 'pdf' ) $path = $path . '/' .$type;
                if( $type == 'ibg' || $type == 'png-hires' || $type == 'jpg-hires' || $type == 'pdf' ){
                    $config     = nbd_get_data_from_json($path . '/config.json');
                    nbd_export_pdfs( $nbd_item_key, false, false, 'no' );
                    $pdf_path   = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key . '/customer-pdfs';
                    $list_pdf   = Nbdesigner_IO::get_list_files_by_type( $pdf_path, 1, 'pdf' );

                    if( !isset( $config->dpi ) ){
                        $option = unserialize( file_get_contents( $path . '/option.json' ) );
                        $dpi    = $option['dpi'];
                    }else{
                        $dpi = $config->dpi;
                    }
                    if( count( $list_pdf ) ){
                        foreach( $list_pdf as $key => $pdf ){
                            if( $type == 'ibg' ){
                                NBD_Image::pdf2image( $pdf, $dpi, 'png' );
                                NBD_Image::pdf2image( $pdf, $dpi, 'jpg' );
                            }else if( $type == 'png-hires' ){
                                NBD_Image::pdf2image( $pdf, $dpi, 'png' );
                            }else if( $type == 'jpg-hires' ){
                                NBD_Image::pdf2image( $pdf, $dpi, 'jpg' );
                            }
                        }
                        if( $type == 'pdf' ){
                            $files = $list_pdf;
                        }
                        $path = $pdf_path;
                    }
                }
                if( $type == 'svg' ){
                    $svg_path = $path . '/svg';
                    if( !file_exists( $svg_path ) ){
                        $this->convert_svg_embed( $path );
                    }
                    $files = Nbdesigner_IO::get_list_files_by_type( $svg_path, 1, 'svg' );
                }else if( $type != 'pdf' ){
                    $files = Nbdesigner_IO::get_list_images( $path, 1 );
                    if( $type == 'jpg-hires' ){
                        $files = Nbdesigner_IO::get_list_files_by_type( $path, 1, 'jpg|jpeg' );
                    }
                    if( $type == 'png-hires' ){
                        $files = Nbdesigner_IO::get_list_files_by_type( $path, 1, 'png' );
                    }
                }
                if( count( $files ) > 0 ){
                    foreach( $files as $key => $file ){
                        $zip_files[] = $file;
                    }
                } else{
                    $arr = array( 'nbd_item_key' => $nbd_item_key, 'order_id' => $order_id, 'product_id' => $product_id, 'variation_id' => $variation_id );
                    $url = add_query_arg( $arr, admin_url( 'admin.php?page=nbdesigner_detail_order' ) );
                    wp_redirect( esc_url( $url ) );
                    exit();
                }
                $pathZip = NBDESIGNER_DATA_DIR . '/download/customer-design-' . $_GET['nbd_item_key'] . '-' . $type . '.zip';
                $nameZip = 'customer-design-' . $_GET['nbd_item_key'] . '-' . $type . '.zip';
                nbd_zip_files_and_download( $zip_files, $pathZip, $nameZip );
                exit();
            }
            if( isset( $_GET['download-all'] ) && ( $_GET['download-all'] == 'true' ) ){
                $products = $order->get_items();
                foreach( $products AS $order_item_id => $product ){
                    if( wc_get_order_item_meta( $order_item_id, '_nbd' ) || wc_get_order_item_meta( $order_item_id, '_nbu' ) ){
                        $nbd_item_key = wc_get_order_item_meta( $order_item_id, '_nbd' );
                        $nbu_item_key = wc_get_order_item_meta( $order_item_id, '_nbu' );
                        if( $nbd_item_key ){
                            $list_images = Nbdesigner_IO::get_list_images( NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key, 1 );
                            if( count( $list_images ) > 0 ){
                                foreach( $list_images as $key => $image ){
                                    $zip_files[] = $image;
                                }
                            }
                        }
                        if( $nbu_item_key ){
                            $files = Nbdesigner_IO::get_list_files( NBDESIGNER_UPLOAD_DIR .'/'. $nbu_item_key );
                            $files = apply_filters( 'nbu_download_upload_files', $files, $product );
                            if( count( $files ) > 0 ){
                                foreach( $files as $key => $file ){
                                    $zip_files[] = $file;
                                }
                            }
                        }
                    }
                }
                if( !count( $zip_files ) ){
                    exit();
                }else{
                    $pathZip = NBDESIGNER_DATA_DIR . '/download/customer-design-' . $_GET['order_id'] . '.zip';
                    $nameZip = 'customer-design-' . $_GET['order_id'] . '.zip';
                    nbd_zip_files_and_download( $zip_files, $pathZip, $nameZip );
                }
            }
            if( isset( $_GET['nbd_item_key'] ) ){
                $license        = $this->nbdesigner_check_license();
                $path           = NBDESIGNER_CUSTOMER_DIR .'/'. $_GET['nbd_item_key'];
                $list_images    = Nbdesigner_IO::get_list_images( $path, 1 );
                $_list_pdfs     = file_exists( $path . '/pdfs' ) ? Nbdesigner_IO::get_list_files($path . '/pdfs', 2) : array();
                $list_design    = array();
                $list_pdfs      = array();
                $datas          = unserialize( file_get_contents( $path . '/product.json' ) );
                $option         = unserialize( file_get_contents( $path . '/option.json' ) );
                $path_config    = $path . '/config.json';
                $resources      = (array)json_decode( file_get_contents( $path. '/design.json' ) );
                $fonts          = array();
                if(file_exists( NBDESIGNER_DATA_DIR . '/fonts.json') ){
                    $fonts = (array)json_decode( file_get_contents( NBDESIGNER_DATA_DIR . '/fonts.json' ) );
                }
                $config = nbd_get_data_from_json( $path_config );
                if( isset( $config->product ) && count( $config->product ) ){
                    $datas = array();
                    foreach( $config->product as $side ){
                        $datas[] = (array)$side;
                    }
                };
                if( isset( $config->custom_dimension ) ){
                    $custom_side    = absint( $config->custom_dimension->side );
                    $custom_width   = $config->custom_dimension->width;
                    $custom_height  = $config->custom_dimension->height;
                    $datas          = $this->merge_product_config( $datas, $custom_width, $custom_height, $custom_side );
                }
                foreach ( $_list_pdfs as $_pdf ){
                    if( is_dir( $_pdf)  ) continue;
                    $list_pdfs[] = array(
                        'title' => basename( $_pdf ),
                        'url'   => Nbdesigner_IO::wp_convert_path_to_url( $_pdf )
                    );
                }
                foreach ($list_images as $img){
                    $name                   = basename($img);
                    $arr                    = explode('.', $name);
                    $_frame                 = explode('_', $arr[0]);
                    $frame                  = $_frame[1];
                    $list_design[$frame]    = Nbdesigner_IO::wp_convert_path_to_url($img);
                }
            }
        }
        require_once NBDESIGNER_PLUGIN_DIR .'views/detail-order.php';
    }
    public function setting_design() {
        $current_screen         = get_current_screen();
        $post_id                = get_the_ID();
        $_designer_setting      = unserialize( get_post_meta( $post_id, '_designer_setting', true ) );
        $enable                 = get_post_meta( $post_id, '_nbdesigner_enable', true );
        $enable_upload          = get_post_meta( $post_id, '_nbdesigner_enable_upload', true );
        $upload_without_design  = get_post_meta( $post_id, '_nbdesigner_enable_upload_without_design', true );
        if ( isset( $_designer_setting[0] ) ){
            foreach ($_designer_setting as $key => $set ){
                $_designer_setting[$key] = array_merge( nbd_default_product_setting(), $set );
            }
            $designer_setting = $_designer_setting;
            if(! isset( $designer_setting[0]['version'] ) || $_designer_setting[0]['version'] < 160) {
                $designer_setting = $this->update_config_product_160( $designer_setting );
            }
            if(! isset( $designer_setting[0]['version'] ) || $_designer_setting[0]['version'] < 180) {
                $designer_setting = NBD_Update_Data::nbd_update_media_v180( $designer_setting );
            }
        }else {
            $designer_setting       = array();
            $designer_setting[0]    = nbd_default_product_setting();
        }
        $option     = unserialize( get_post_meta( $post_id, '_nbdesigner_option', true ) );
        $_option    = nbd_get_default_product_option();
        if( !is_array( $option ) ){
            $option = array();
        }
        $option             = array_merge( $_option, $option );
        $unit               = isset( $option['unit'] ) ? $option['unit'] : nbdesigner_get_option( 'nbdesigner_dimensions_unit', 'cm' );
        $upload_setting     = unserialize( get_post_meta( $post_id, '_nbdesigner_upload', true ) );
        $_upload_setting    = nbd_get_default_upload_setting();
        if( !is_array( $upload_setting ) ){
            $upload_setting = array();
        }
        $upload_setting     = array_merge( $_upload_setting, $upload_setting );
        $designer_setting   = nbd_update_config_default( $designer_setting );
        include_once( NBDESIGNER_PLUGIN_DIR . 'views/metabox-design-setting.php' );
    }
    public function update_config_product_160( $designer_setting ){
        $newSetting = array();
        $default    = nbd_default_product_setting();
        foreach ($designer_setting as $key => $setting){
            $setting160 = array();
            $scale = 1;
            if(($setting['img_src_width'] > $setting['img_src_height']) && ($setting['img_src_width'] < 300)) $scale = 300/ $setting['img_src_width'];
            if(($setting['img_src_width'] < $setting['img_src_height']) && ($setting['img_src_height'] < 300)) $scale = 300/ $setting['img_src_height'];
            $ratio150           = 300 / $setting['area_design_width'] / $scale * $setting['real_width'];
            $product_width      = round( $ratio150 * $setting['img_src_width'] * $scale / 300, 2 );
            $product_height     = round( $ratio150 * $setting['img_src_height'] * $scale / 300, 2 );
            $area_design_width  = round( $setting['area_design_width'] * $scale * 5/3 );
            $area_design_height = round( $setting['area_design_height'] * $scale * 5/3 );
            $old_real_top       = $ratio150 * $setting['area_design_top'] * $scale / 300;
            $old_real_left      = $ratio150 * $setting['area_design_left'] * $scale / 300;
            $ratio              = 500 / $product_height;
            $img_src_top        = 0;
            $img_src_left       = round(($product_height - $product_width) * $ratio / 2, 2);
            $img_src_height     = 500;
            $img_src_width      = round( $product_width * $ratio );
            $real_top           = round( $old_real_top, 2 );
            $real_left          = round( $old_real_left - ( $product_height - $product_width ) / 2, 2 );
            if( $product_width > $product_height ){
                $img_src_top        = round( ( $product_width - $product_height ) * $ratio / 2, 2 );
                $img_src_left       = 0;
                $img_src_width      = 500;
                $img_src_height     = round( $product_height * $ratio );
                $real_left          = round( $old_real_left, 2 );
                $real_top           = round( $old_real_top - ( $product_width - $product_height ) / 2, 2 );
            }
            $area_design_left                   = round( $setting['area_design_left'] * $scale * 5/3 );
            $area_design_top                    = round( $setting['area_design_top'] * $scale * 5/3 );
            $setting160['product_width']        = $product_width;
            $setting160['product_height']       = $product_height;
            $setting160['real_width']           = $setting['real_width'];
            $setting160['real_height']          = $setting['real_height'];
            $setting160['real_left']            = $real_left;
            $setting160['real_top']             = $real_top;
            $setting160['area_design_left']     = $area_design_left;
            $setting160['area_design_top']      = $area_design_top;
            $setting160['area_design_width']    = $area_design_width;
            $setting160['area_design_height']   = $area_design_height;
            $setting160['img_src_top']          = $img_src_top;
            $setting160['img_src_left']         = $img_src_left;
            $setting160['img_src_height']       = $img_src_height;
            $setting160['img_src_width']        = $img_src_width;
            $setting160['img_src']              = $setting['img_src'];
            $setting160['orientation_name']     = $setting['orientation_name'];   
            $setting160                         = array_merge( $default, $setting160 );
            $newSetting[$key]                   = $setting160;
        }
        return $newSetting;
    }
    public function nbdesigner_update_all_product(){
        if ( !wp_verify_nonce( $_POST['_nbdesigner_update_product'], 'nbdesigner-update-product' ) || !current_user_can( 'administrator' ) ) {
            die( 'Security error' );
        }
        $args_query = array(
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'meta_key'          => '_nbdesigner_enable',
            'orderby'           => 'date',
            'order'             => 'DESC',
            'posts_per_page'    => -1,
            'meta_query'        => array(
                array(
                    'key'   => '_nbdesigner_enable',
                    'value' => 1,
                )
            )
        ); 
        $posts  = get_posts( $args_query );
        $result = array('flag' => 1);
        if( is_array( $posts ) ){
            foreach ( $posts as $post ){
                $product = wc_get_product( $post->ID );
                if( $product->is_type( 'variable' ) ) {
                    $variations = $product->get_available_variations( false );
                    foreach ( $variations as $variation ){
                        $vid = $variation['variation_id'];
                        $designer_setting = unserialize( get_post_meta( $vid, '_designer_setting'.$vid, true ) );
//                        if(! isset($designer_setting[0]['version']) || $designer_setting[0]['version'] < 160) {
//                            $newSetting = $this->update_config_product_160($designer_setting);
//                            update_post_meta($vid, '_designer_setting'.$vid, serialize($newSetting));
//                        } 
                        if(! isset( $designer_setting[0]['version'] ) || $designer_setting[0]['version'] < 170 ) {
                            $setting_design = $this->update_config_product_170( $designer_setting );
                            update_post_meta( $vid, '_designer_variation_setting', serialize( $setting_design ) );
                        }
                    }
                }
                $pid = $post->ID;
                $designer_setting = unserialize( get_post_meta( $pid, '_designer_setting', true ) );
//                if(! isset($designer_setting[0]['version']) || $designer_setting[0]['version'] < 160) {
//                    $newSetting = $this->update_config_product_160($designer_setting);
//                    update_post_meta($pid, '_designer_setting', serialize($newSetting));
//                }  
                if( !isset( $designer_setting[0]['version'] ) || $designer_setting[0]['version'] < 170 ) {
                    $dpi            = get_post_meta( $pid, '_nbdesigner_dpi', true );
                    $option         = nbd_get_default_product_option();
                    $option['dpi']  = $dpi;
                    update_post_meta( $pid, '_nbdesigner_option', serialize( $option ) );
                    $setting_upload = nbd_get_default_upload_setting();
                    update_post_meta( $pid, '_nbdesigner_upload', $setting_upload );
                    $setting_design = $this->update_config_product_170( $designer_setting );
                    update_post_meta( $pid, '_designer_setting', serialize( $setting_design ) );
                }
            }
        }
        echo json_encode( $result );
        wp_die();
    }
    public function update_config_product_170( $designer_setting ){
        foreach( $designer_setting as $key => $val ){
            $designer_setting[$key] = array_merge( nbd_default_product_setting(), $val );
        }
        return $designer_setting;
    }
    public function order_design($post) {
        $order_id       = $post->ID;
        $order          = new WC_Order( $order_id );
        $products       = $order->get_items();
        $_data_designs  = unserialize( get_post_meta( $order_id, '_nbdesigner_design_file', true ) );
        if( isset( $_data_designs ) && is_array( $_data_designs ) ) $data_designs = $_data_designs;
        $_data_uploads = unserialize( get_post_meta( $order_id, '_nbdesigner_upload_file', true ) );
        if( isset( $_data_uploads ) && is_array( $_data_uploads ) ) $data_uploads = $_data_uploads;
        $has_design = get_post_meta( $order_id, '_nbd', true );
        $has_upload = get_post_meta( $order_id, '_nbu', true );
        include_once( NBDESIGNER_PLUGIN_DIR . 'views/box-order-metadata.php' );
    }
    public function nbd_delete_order_design(){
        check_admin_referer( 'approve-design-email', 'nonce' );
        $result = array(
            'flag'  => false
        );
        $order_id   = $_POST['order_id'];
        $order      = wc_get_order( $order_id );
        foreach( $order->get_items() as $item_id => $item_product ){
            $result['flag'] = $result['flag'] || wc_delete_order_item_meta($item_id, '_nbd');
            $result['flag'] = $result['flag'] || wc_delete_order_item_meta($item_id, '_nbu');
        }
        $result['flag'] = $result['flag'] || delete_post_meta( $order_id, '_nbd' ) || delete_post_meta( $order_id, '_nbu' );
        wp_send_json( $result );
    }
    public function nbdesigner_allow_create_product( $id ){
        $args = array(
            'post_type'     => 'product',
            'meta_key'      => '_nbdesigner_enable',
            'meta_value'    => 1
        );
        $query = new WP_Query( $args );
        $pros = get_posts( $args );
        $list = array();
        foreach ( $pros as $pro ){
            $list[] = $pro->ID;
        }
        $count      = $query->found_posts;
        $license    = $this->nbdesigner_check_license();
        if( !isset( $license['key'] ) ) return false;
        if( in_array( $id, $list ) ) return true;
        $salt       = md5( $license['key'].'pro' );
        if( ( $salt  != $license['salt'] ) && ( $count > 5 ) ) return false;
        return true;
    }
    public function nbdesigner_get_info_license(){
        if ( !wp_verify_nonce( $_POST['_nbdesigner_license_nonce'], 'nbdesigner-active-key' ) || !current_user_can( 'administrator' ) ) {
            die( 'Security error' );
        } 
        $result = array();
        if( isset( $_POST['nbdesigner']['license'] ) ) {
            $license            = sanitize_text_field( $_POST['nbdesigner']['license'] );
            $result_from_json   = $this->nbdesiger_request_license( $license, $this->activedomain );
            $data               = (array)json_decode( $result_from_json );
            if( isset( $data ) ) {
                switch ( $data["code"] ) {
                    case -1 :
                        $result['mes']  = esc_html__('Missing necessary information!', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 0 :
                        $result['mes']  = esc_html__('Incorrect information, check again license key', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;     
                    case 1 :
                        $result['mes']  = esc_html__('Incorrect License key', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 2 :
                        $result['mes']  = esc_html__('License key is locked ', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break; 
                    case 3 :
                        $result['mes']  = esc_html__('License key have expired', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 4 :
                        $result['mes']  = esc_html__('Link your website incorrect', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;     
                    case 5 :
                        $result['mes']  = esc_html__('License key is can using', 'web-to-print-online-designer');
                        $result['flag'] = 1;
                        break;
                    case 6 :
                        $result['mes']  = esc_html__('Domain has been added successfully', 'web-to-print-online-designer');
                        $result['flag'] = 1;
                        break;     
                    case 7 :
                        $result['mes']  = esc_html__('Exceed your number of domain license', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                    case 8 :
                        $result['mes']  = esc_html__('Unsuccessfully active license key', 'web-to-print-online-designer');
                        $result['flag'] = 0;
                        break;
                }
                $data['key']    = $license;
                $data['salt']   = md5($license.$data['type']);
                if( $data['type'] == 'free' ) $data['number_domain'] = "5";
                if( ( $data["code"] == 5 ) || ( $data["code"] == 6 ) ){
                    //$this->nbdesigner_write_license(json_encode($data));
                    update_option( 'nbdesigner_license', json_encode( $data ) );
                }
            }else{
                $result['mes'] = esc_html__('Try again later!', 'web-to-print-online-designer');
            }
        }else {
            $result['mes'] = esc_html__('Please fill your license!', 'web-to-print-online-designer');
        }
        echo json_encode( $result );
        wp_die();
    }
    public function nbdesigner_remove_license(){
        if( !wp_verify_nonce( $_POST['_nbdesigner_license_nonce'], 'nbdesigner-active-key' ) || !current_user_can( 'administrator' ) ) {
            die('Security error');
        }
        $result         = array();
        $result['flag'] = 0;
        $path           = NBDESIGNER_PLUGIN_DIR . 'data/license.json';
        $path_data      = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
        if( file_exists( $path_data ) ) {
            $path = $path_data;
        }
        $license    = $this->nbdesigner_check_license();
        $key        = ( isset($license['key'] ) ) ? $license['key'] : '';
        $_request   = $this->nbdesiger_request_license( $key, $this->removedomain );
        if( isset( $_request ) ){
            $request = (array)json_decode( $_request );
            switch ($request["code"]) {
                case -1:
                    $result['mes'] = esc_html__( 'Missing necessary information', 'web-to-print-online-designer' );
                    break;
                case 0:
                    $result['mes'] = esc_html__( 'Incorrect information', 'web-to-print-online-designer' );
                    break;
                case 1:
                    $result['mes'] = esc_html__( 'Incorrect License key', 'web-to-print-online-designer' );
                    break;
                case 2: 
                    if( file_exists( $path ) ) unlink( $path );
                    $result['mes']  = esc_html__( 'Remove license key Successfully', 'web-to-print-online-designer' );
                    $result['flag'] = 1;
                    delete_option( 'nbdesigner_license' );
                    break;
                case 3:
                    $result['mes'] = esc_html__( 'Remove license key Unsuccessfully!', 'web-to-print-online-designer' );
                    break;
            }
        }     
        echo json_encode( $result );
        wp_die();
    }
    private function nbdesiger_request_license( $license, $task ){
        $url_root   = base64_encode( rtrim(get_bloginfo( 'wpurl' ), '/' ) );
        $url        = NBDESIGNER_AUTHOR_SITE . $task . NBDESIGNER_SKU . '/' . $license . '/' . $url_root;
        return nbd_file_get_contents( $url );
    }
    private function nbdesigner_write_license( $license ){
        $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/license.json';
        if ( !file_exists( NBDESIGNER_DATA_CONFIG_DIR ) ) {
            wp_mkdir_p( NBDESIGNER_DATA_CONFIG_DIR );
        }
        file_put_contents( $path_data, $license );
        update_option( 'nbdesigner_license', $license );
    }
    private function nbdesigner_check_license(){
    	//bugs
        $result = array();
        $result['status'] = 1;
		$result['key']= '**************';
		$result['type'] = 'pro';
		$result['number_domain'] = '1000';
		$result['featured'] = 'on';
		$result['enabled'] = 'on';
		$result['expiry'] = 0;
		$result['salt']   = md5( 'nullcave' . 'pro' );
		$license        = json_encode( $result );
		update_option( 'nbdesigner_license', $license );
        return $result;
    }
    public function save_settings( $post_id ) {
        if ( !isset($_POST['nbdesigner_setting_box_nonce'] ) || !wp_verify_nonce( $_POST['nbdesigner_setting_box_nonce'], 'nbdesigner_setting_box' )
            || !( current_user_can( 'administrator' ) || current_user_can( 'shop_manager' ) ) ) {
            return $post_id;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        $post                   = $_POST;
        $post                   = apply_filters( 'nbd_post_settings', $post );
        $enable                 = $post['_nbdesigner_enable'];
        $enable_upload          = $post['_nbdesigner_enable_upload'];
        $upload_without_design  = $post['_nbdesigner_enable_upload_without_design'];
        $option                 = serialize( $post['_nbdesigner_option'] );
        $setting_design         = serialize( $post['_designer_setting'] );
        $setting_upload         = serialize( $post['_designer_upload'] );
        if( !$this->nbdesigner_allow_create_product( $post_id ) ) return;
        update_post_meta( $post_id, '_designer_setting', $setting_design );
        update_post_meta( $post_id, '_nbdesigner_option', $option );
        update_post_meta( $post_id, '_nbdesigner_upload', $setting_upload );
        update_post_meta( $post_id, '_nbdesigner_enable_upload_without_design', $upload_without_design );
        update_post_meta( $post_id, '_nbdesigner_enable', $enable );
        update_post_meta( $post_id, '_nbdesigner_enable_upload', $enable_upload );
    }
    public function nbdesigner_get_extension( $file_name ) {
        $filetype   = explode( '.', $file_name );
        $file_exten = $filetype[count( $filetype ) - 1];
        if( false !== strpos( $file_name, '.jpg?' ) || false !== strpos( $file_name, '.jpeg?' ) || false !== strpos( $file_name, 'fm=jpg' ) ) $file_exten = 'jpg';
        return strtolower( $file_exten );
    }
    public function nbdesigner_button2() {
        global $product;
        if( $product->get_type() == 'variable' ){
            $position   = nbdesigner_get_option( 'nbdesigner_position_button_product_detail', 1 );
            if( $position == 2 ){
                add_action( 'woocommerce_before_variations_form', array( $this, 'nbdesigner_button' ), 30 );
            }else{
                $this->nbdesigner_button();
            }
        }else{
            $this->nbdesigner_button();
        }
    }
    public function nbdesigner_button() {
        global $wp_query;
        if( isset( $wp_query->query_vars['request-design'] ) || isset( $wp_query->query_vars['upload-design'] ) ){
            return '';
        }

        $check_conditional_show_btn = apply_filters( 'nbd_conditional_show_design_btn', true );
        if( !$check_conditional_show_btn ) return '';

        /* Quick view */
        if( isset($_REQUEST['wc-api']) && $_REQUEST['wc-api'] == 'NBO_Quick_View' ){
            if( !isset( $_REQUEST['mode'] ) || $_REQUEST['mode'] != 'catalog' ){
                return '';
            }
        }
        if( ( isset( $_REQUEST['wc-api'] ) && ( $_REQUEST['wc-api'] == 'WC_Quick_View' || $_REQUEST['wc-api'] == 'NBO_Quick_View' ) ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'yith_load_product_quick_view' ) ){
            global $product;
            $pid            = $product->get_id();
            $is_nbdesign    = get_post_meta( $pid, '_nbdesigner_enable', true );
            if( $is_nbdesign ){
                $url            = esc_url( get_permalink( $pid ) );
                $type           = $product->get_type();
                $_enable_upload = get_post_meta( $pid, '_nbdesigner_enable_upload', true );
                $_enable_upload_without_design = get_post_meta( $pid, '_nbdesigner_enable_upload_without_design', true );
                $label = apply_filters( 'nbd_start_design_label', esc_html__( 'Start Design', 'web-to-print-online-designer' ) );
                if( $_enable_upload ){
                    $label = apply_filters( 'nbd_start_design_and_upload_label', esc_html__( 'Start and upload design', 'web-to-print-online-designer' ) );
                }
                if( $_enable_upload_without_design ){
                    $label = apply_filters( 'nbd_upload_design_label', esc_html__( 'Upload Design', 'web-to-print-online-designer' ) );
                } 
                $option = unserialize( get_post_meta( $pid, '_nbdesigner_option', true ) );
                $layout = isset( $option['layout'] ) ? $option['layout'] : 'm';
                if( $layout == 'v' ){
                    $url = $product->get_permalink();
                } else {
                    $url = add_query_arg( array(
                        'product_id'    => $pid,
                        'view'          => $layout
                    ), getUrlPageNBD( 'create' ) );
                    if( $_enable_upload_without_design ){
                        $upload_option  = unserialize( get_post_meta( $pid, '_nbdesigner_upload', true ) );
                        $upload_style   = nbdesigner_get_option( 'nbdesigner_upload_popup_style', 's' );
                        if( $upload_style == 'a' && isset( $upload_option['advanced_upload'] ) && $upload_option['advanced_upload'] == 1 ){
                            $url = add_query_arg( array(
                                'product_id'    => $pid
                            ), getUrlPageNBD( 'advanced_upload' ) );
                        } else {
                            $url = add_query_arg( array(
                                'product_id'    => $pid
                            ), getUrlPageNBD( 'simple_upload' ) );
                        }
                    }
                }
                ?>
                <p>
                    <a class="button alt nbdesign-button" href="<?php echo esc_url( $url ); ?>" >
                        <?php esc_html_e( $label ); ?>
                    </a>
                </p>
        <?php
            }
            return;
        }
        if(isset($_POST['action'])) return ''; /*Hidden button Start Design on third-party plugin as Quick view*/
        /* Hidden button Start Design on upload page */
        if( is_page( nbd_get_page_id( 'advanced_upload' ) ) || is_page( nbd_get_page_id( 'simple_upload' ) ) ){
            return '';
        }
        $pid            = get_the_ID();
        $pid            = get_wpml_original_id( $pid );
        $is_nbdesign    = get_post_meta( $pid, '_nbdesigner_enable', true ); 
        $variation_id   = 0;
        if ( $is_nbdesign ) {
            $product    = wc_get_product( $pid );
            $type       = $product->get_type();
            $option     = unserialize( get_post_meta( $pid, '_nbdesigner_option', true ) );
            $layout     = isset( $option['layout'] ) ? $option['layout'] : 'm';
            if( $layout == 'v' ) {
                $_enable_upload_without_design = get_post_meta( $pid, '_nbdesigner_enable_upload_without_design', true );
                if( !$_enable_upload_without_design ) return;
            }
            /* Multi language with WPML */
            if( count( $_REQUEST ) ){
                $attributes = array();
                $layout     = nbdesigner_get_option( 'nbdesigner_design_layout' );
                if( $layout == "c" ){
                    foreach ( $_REQUEST as $key => $value ){
                        if ( strpos( $key, 'attribute_' ) === 0 ) {
                            $attributes[$key] = $value;
                        }
                    }
                    if( count( $attributes ) ){
                        if ( class_exists( 'WC_Data_Store' ) ) {
                            $data_store     = WC_Data_Store::load( 'product' );
                            $variation_id   = $data_store->find_matching_product_variation( $product, $attributes );
                        }else{
                            $variation_id = $product->get_matching_variation( $attributes );
                        }
                    }    
                }
            }
            $site_url = site_url();
            if ( class_exists( 'SitePress' ) ) {
                $site_url = home_url();
            }
            // mzl 没有走这个分支
            // error_log('[class.nbdesigner.php] nbdesigner_button click ----');
            $src = add_query_arg( array( 'action' => 'nbdesigner_editor_html', 'product_id' => $pid ), $site_url . '/' );
            if( isset( $_POST['variation_id'] ) &&  $_POST['variation_id'] != '' ){
                $src .= '&variation_id='. absint( $_POST['variation_id'] );
            }
            if( isset( $_GET['nbds-ref'] ) ){
                $src .= '&reference='. $_GET['nbds-ref'];
            }
            if( isset( $_GET['nbo_cart_item_key'] ) && $_GET['nbo_cart_item_key'] !='' ){
                error_log('nbdesigner_button has nbo_cart_item_key');
                $nbd_item_key = WC()->session->get( $_GET['nbo_cart_item_key'] . '_nbd' );
                if( $nbd_item_key ) {
                    error_log('nbdesigner_button has $nbd_item_key ' . $nbd_item_key);
                    $src .= '&task=edit&nbd_item_key=' . $nbd_item_key . '&cik=' . $_GET['nbo_cart_item_key'];
                }
            }
            if( $variation_id != 0 ){
                $src .= '&variation_id='. $variation_id;
            }
            if( $type == 'variable' && isset( $option['bulk_variation'] ) && $option['bulk_variation'] == 1 ){
                $src .= '&variation_id=0';
            }
            if( isset( $_GET['nbo_cart_item_key'] ) && $_GET['nbo_cart_item_key'] != '' ){
                $src .= '&nbo_cart_item_key=' . $_GET['nbo_cart_item_key'];
            }
            $extra_price = '';
            if( $option['extra_price'] && ! $option['request_quote'] ){
                $extra_price = $option['type_price'] == 1 ? wc_price( $option['extra_price'] ) : $option['extra_price'] . ' %';
            }
            ob_start();
            nbdesigner_get_template( 'single-product/nbd-section.php', array( 'src' => $src, 'extra_price' => $extra_price, 'pid' =>  $pid, 'option' => $option ) );
            $content    = ob_get_clean();
            $position   = nbdesigner_get_option( 'nbdesigner_position_button_product_detail', 1 );
            if( $position == 4 ){
                return $content;
            } else {
                echo $content;
            }
        }
    }
    public function nbd_loggin_redirect_func(){
        if( is_admin() ) return;
        ob_start();
        nbdesigner_get_template( 'auth-wp.php' );
        $content = ob_get_clean();
        echo $content;
    }
    public function nbd_get_nbd_products(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        $result         = array();
        $nbd_products   = get_transient( 'nbd_frontend_products' );
        if( false === $nbd_products ){
            $products = $this->get_all_product_has_design();
            foreach ( $products as $pro ){
                $product    = wc_get_product( $pro->ID );
                $type       = $product->get_type();
                $image      = get_the_post_thumbnail_url( $pro->ID, 'post-thumbnail' );
                if( !$image ) $image = wc_placeholder_img_src();
                $result[] = array(
                    'product_id'    => $pro->ID,
                    'name'          => $pro->post_title,
                    'src'           => $image,
                    'type'          => $type,
                    'url'           => get_permalink( $pro->ID )
                );
            }
            set_transient( 'nbd_frontend_products' , $result );
        }else{
            $result = $nbd_products;
        }
        echo json_encode( $result );
        wp_die();
    }
    public function nbd_get_product_description(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        $product_id = $_POST['product_id'];
        $result     = $this->_get_product_description( $product_id );
        wp_send_json( $result );
    }
    public function _get_product_description( $product_id ){
        $product = wc_get_product( $product_id );
        ob_start();
        $image = get_the_post_thumbnail_url($product_id, 'post-thumbnail');
        $image = $image ? $image : wc_placeholder_img_src();  
        nbdesigner_get_template('product-info.php', array(
            'id'                    => $product_id,
            'title'                 => $product->get_title(),
            'description'           => $product->get_description(),
            'short_description'     => $product->get_short_description(),
            'price'                 => $product->get_price(),
            'permalink'             => $product->get_permalink(),
            'image'                 => $image
        ));
        $html = ob_get_clean();
        $result = array(
            'product_id'    => $product_id,
            'html'          => $html,
            'type'          => $product->get_type()
        );
        return $result;
    }
    public function get_all_product_has_design(){
        $args_query = array(
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'meta_key'          => '_nbdesigner_enable',
            'orderby'           => 'date',
            'order'             => 'DESC',
            'posts_per_page'    => -1,
            'meta_query'        => array(
                array(
                    'key'   => '_nbdesigner_enable',
                    'value' => 1,
                ),
                array(
                    'key'   => '_nbdesigner_enable_upload_without_design',
                    'value' => 0,
                )
            )
        ); 
        $posts = get_posts( $args_query );
        return $posts;
    }
    public function nbd_get_variation_form( $product_id ){
        $_product               = wc_get_product( $product_id );
        $attributes             = $_product->get_variation_attributes();
        $available_variations   = $_product->get_available_variations();
        $selected_attributes    = $_product->get_default_attributes();
        $attribute_keys         = array_keys( $attributes );  
        ob_start();
        nbdesigner_get_template('product-variation-form.php', array(
            'attributes'            => $attributes,
            'available_variations'  => $available_variations,
            'selected_attributes'   => $selected_attributes,
            'attribute_keys'        => $attribute_keys,
            '_product'              => $_product
         ));
        $html = ob_get_clean();
        return $html;
    }
    public function nbdesigner_get_product_info() {
        if (!wp_verify_nonce($_POST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }
        $data               = array();
        $product_id         = (isset($_POST['product_id']) &&  $_POST['product_id'] != '') ? absint($_POST['product_id']) : '';
        $variation_id       = (isset($_POST['variation_id']) &&  $_POST['variation_id'] != '') ? absint($_POST['variation_id']) : 0;
        error_log('[nbdesigner] nbdesigner_get_product_info variation_id: ' . $variation_id);
        $template_id        = (isset($_POST['template_id']) &&  $_POST['template_id'] != '') ? $_POST['template_id'] : '';
        $need_templates     = (isset($_POST['need_templates']) &&  $_POST['need_templates'] != '') ? true : false;
        if( $template_id != '' ){
            $path                   = NBDESIGNER_CUSTOMER_DIR . '/' . $template_id;
            $data['fonts']          = nbd_get_data_from_json( $path . '/used_font.json' );
            $data['design']         = nbd_get_data_from_json( $path . '/design.json' );
            $data['config']         = nbd_get_data_from_json( $path . '/config.json' );
            $data['design_id']      = $template_id;
            nbd_update_hit_template( false, $template_id );
        }else {
            if($product_id) $product_id = get_wpml_original_id($product_id);
            if($variation_id) $variation_id = get_wpml_original_id($variation_id);
            $data = nbd_get_product_info( $product_id, $variation_id, '', 'new', '', '', $need_templates );  
        } 
        if( isset( $_POST['attach_product_info'] ) && $_POST['attach_product_info'] == 1 ){
            if( isset( $_POST['product_type'] ) && $_POST['product_type'] == 'variable' ){
                $data['variation_form'] = $this->nbd_get_variation_form($product_id);
            }
            $data['product_info'] = $this->_get_product_description($product_id);
        }
        echo json_encode($data);
        wp_die();
    }
    public function nbdesigner_save_partial_customer_design(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        } 
        $result             = array();
        $force_new_folder   = true;
        $result['flag']     = 0;
        $sid                = esc_html( $_POST['sid'] );
        $pid                = absint( $_POST['product_id'] );
        $flag               = absint( $_POST['flag'] );
        $order              = 'nb_order';
        if( !$flag ){
            $config = str_replace( '\"', '"', $_POST['config'] );
            $fonts  = str_replace( '\"', '"', $_POST['fonts'] );
        }
        if( isset( $_POST['image'] ) ){
            $data = $_POST['image']['img'];
            if( !$flag ){
                $json = str_replace( '\"', '"', $_POST['image']['json'] );
                $json = str_replace( '\\\\', '\\', $json );
            }
        } else{
            die( 'Incorect data!' );
        }
        $uid = get_current_user_id(); 
        $iid = $sid;
        if($uid > 0) $iid = $uid;
        $path       = NBDESIGNER_CUSTOMER_DIR . '/' . $iid . '/' . $order . '/' . $pid;
        $path_thumb = $path . '/thumbs';
        if(!$flag){      
            $json_file          = $path . '/design.json';
            $json_config        = $path . '/config.json';
            $json_used_font     = $path . '/used_font.json';
            file_put_contents( $json_file, $json );
            file_put_contents( $json_config, $config );
            file_put_contents( $json_used_font, $fonts );
        }
        if( file_exists( $path ) && !$flag ){
            Nbdesigner_IO::delete_folder( $path );
        }    
        if ( !file_exists( $path ) ) {
            if ( wp_mkdir_p( $path ) ) {
                if ( !file_exists( $path_thumb ) )
                    if ( !wp_mkdir_p( $path_thumb ) ) {
                        $result['mes'] = esc_html__( 'Your server not allow creat folder', 'web-to-print-online-designer' );
                    }
            } else {
                $result['mes'] = esc_html__( 'Your server not allow creat folder', 'web-to-print-online-designer' );
            }
        }
        $configs        = unserialize( get_post_meta( $pid, '_designer_setting', true ) );
        $thumb_width    = nbdesigner_get_option( 'nbdesigner_thumbnail_width' );
        $thumb_height   = nbdesigner_get_option( 'nbdesigner_thumbnail_height' );
        $thumb_quality  = nbdesigner_get_option( 'nbdesigner_thumbnail_quality' );
        if( $configs[$flag]["area_design_width"] > $configs[$flag]["area_design_height"] ){
            $thumb_height = round( $thumb_width * $configs[$flag]['area_design_height'] / $configs[$flag]['area_design_width'] );
        }else {
            $thumb_width = round( $thumb_height * $configs[$flag]['area_design_width'] / $configs[$flag]['area_design_height'] );
        }
        foreach ( $data as $key => $val ) {
            $temp       = explode( ';base64,', $val );
            $buffer     = base64_decode( $temp[1] );
            $full_name  = $path . '/' . $key . '.png';
            if ( Nbdesigner_IO::save_data_to_file( $full_name, $buffer ) ) {
                $image = wp_get_image_editor( $full_name );
                if ( !is_wp_error( $image ) ) {
                    $thumb_file = $path_thumb . '/' . $key . '.png';
                    $image->resize( $thumb_width, $thumb_height, 1 );
                    $image->set_quality( $thumb_quality );
                    if ( $image->save( $thumb_file, 'image/png' ) ) $result['link'] = Nbdesigner_IO::secret_image_url( $thumb_file );
                }
            } else {
                $result['mes'] = esc_html__( 'Your server not allow writable file', 'web-to-print-online-designer' );
            }
        }
        $result['flag'] = "success";
        echo json_encode( $result );
        wp_die();
    }
    private function store_design_data( $nbd_item_key, $data, $product_config, $product_option, $product_upload ){
        error_log('store_design_data nbd_item_key: ' . $nbd_item_key);
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
        if( file_exists( $path . '_old' ) ) Nbdesigner_IO::delete_folder( $path . '_old' );
        if( file_exists( $path ) ) rename( $path, $path . '_old' );
        if ( wp_mkdir_p( $path ) ) {
            file_put_contents( $path . '/product.json', serialize( $product_config ) );
            file_put_contents( $path . '/option.json', $product_option );
            file_put_contents( $path . '/upload.json', $product_upload );

            $partial_path = $path . '/part';
            wp_mkdir_p( $partial_path );

            foreach ( $data as $key => $val ) {
                if( $key == 'design' ){
                    $full_name = $path . '/design.json';
                }else if( $key == 'used_font' ){
                    $full_name = $path . '/used_font.json';
                }else if( $key == 'config' ){
                    $full_name = $path . '/config.json';
                }else if( $key != 'template_thumb' ){
                    if( strpos( $key, '_svg' ) > 0 ){
                        $ext = 'svg';
                    }else {
                        $ext = explode( '/', $val["type"] )[1];
                    }
                    if( strpos( $key, '_svg_part' ) > 0 ){
                        $full_name = $partial_path . '/' . $key . '.' . $ext;
                    } else {
                        $full_name = $path . '/' . $key . '.' . $ext;
                    }
                }
                if( $key != 'template_thumb' ){
                    if ( !move_uploaded_file( $val["tmp_name"], $full_name ) ) {
                        return false;
                    }
                }
            }
        } else {
            Nbdesigner_DebugTool::wirite_log( 'Your server not allow creat folder', 'save design' );
            rename( $path.'_old', $path );
            return false;
        }
        return true;
    }
    private function create_preview_design( $path_src, $path_dst, $product_config, $width, $height, $limit = false ){
        if( !file_exists( $path_dst ) ){
            wp_mkdir_p( $path_dst );
        }
        $scale = $width / 500;
        $count = 0;
        foreach ( $product_config as $key => $val ){
            $p_img          = $path_src . '/frame_' . $key . '.png';
            $design_format  = 'png';
            if( file_exists( $path_src . '/frame_' . $key . '.jpeg' ) ){
                $p_img          = $path_src . '/frame_' . $key . '.jpeg';
                $design_format  = 'jpeg';
            }
            if( file_exists( $p_img ) ){
                $bg_width   = $val["img_src_width"] * $scale;
                $bg_height  = $val["img_src_height"] * $scale;
                $ds_width   = $val["area_design_width"] * $scale;
                $ds_height  = $val["area_design_height"] * $scale;
                if( $design_format == 'png' ){
                    $image_design = NBD_Image::nbdesigner_resize_imagepng( $p_img, $ds_width, $ds_height );
                }else if( $design_format == 'jpeg' ){
                    $image_design = NBD_Image::nbdesigner_resize_imagejpg( $p_img, $ds_width, $ds_height );
                }else if( $design_format == 'svg' ){
                    error_log('nbdesigner create_preview_design svg');
                    $image_design = NBD_Image::nbdesigner_resize_imagesvg( $p_img, $ds_width, $ds_height );
                }
                $image_product_ext = pathinfo( $val["img_src"] );
                if( $val["bg_type"] == 'image' ){
                    $path_img_src  = Nbdesigner_IO::convert_url_to_path( $val["img_src"] );
                    if( $image_product_ext['extension'] == "png" ){
                        $image_product = NBD_Image::nbdesigner_resize_imagepng( $path_img_src, $bg_width, $bg_height );
                    }else if( $image_product_ext['extension'] == "jpg" || $image_product_ext['extension'] == "jpeg" ){
                        $image_product = NBD_Image::nbdesigner_resize_imagejpg( $path_img_src, $bg_width, $bg_height );
                    }else if( $image_product_ext['extension'] == "svg" ){
                        error_log('nbdesigner create_preview_design2 svg');
                        $image_product = NBD_Image::nbdesigner_resize_imagesvg( $path_img_src, $bg_width, $bg_height );
                    }
                }
                if( $val["show_overlay"] == '1' ){
                    $path_img_overlay   = Nbdesigner_IO::convert_url_to_path( $val["img_overlay"] );
                    $overlay_ext        = pathinfo( $val["img_overlay"] );
                    $over_ext           = strtolower( $overlay_ext['extension'] );
                    if( $over_ext == "png" ){
                        $image_overlay = NBD_Image::nbdesigner_resize_imagepng( $path_img_overlay, $bg_width, $bg_height );
                    }else if( $over_ext == "jpg" || $over_ext == "jpeg" ){
                        $image_overlay = NBD_Image::nbdesigner_resize_imagejpg( $path_img_overlay, $bg_width, $bg_height );
                    }else{
                        $val["show_overlay"] = '0';
                    }
                }
                if( $design_format == 'png' ){
                    $image = imagecreatetruecolor( $bg_width, $bg_height );
                    imagesavealpha( $image, true );
                    $color = imagecolorallocatealpha( $image, 255, 255, 255, 127 );
                    imagefill( $image, 0, 0, $color );
                }else{
                    $image  = imagecreatetruecolor($bg_width, $bg_height);
                    $bg     = imagecolorallocate( $image, 255, 255, 255 );
                    imagefilledrectangle($image,0,0,$bg_width,$bg_height,$bg);
                }
                if( $val["bg_type"] == 'image' ){
                    error_log('nbdesigner create_preview_design image_product ' . print_r($image_product, true) . ' image: ' . print_r($image, true));
                    imagecopy( $image, $image_product, 0, 0, 0, 0, $bg_width, $bg_height );
                } else if( $val["bg_type"] == 'color' ){
                    $show_bg_color = true;
                    if( isset( $val["areaDesignShape"] ) && $val["areaDesignShape"] ){
                        $show_bg_color = false;
                    }
                    if( $val["show_overlay"] == '1' && $image_overlay ){
                        $show_bg_color = true;
                    }
                    if( $show_bg_color ){
                        $_color = hex_code_to_rgb( $val["bg_color_value"] );
                        $color  = imagecolorallocate( $image, $_color[0], $_color[1], $_color[2] );
                        imagefilledrectangle( $image, 0, 0, $bg_width, $bg_height, $color );
                    }
                } else if( $design_format == 'jpeg' ) {
                    $color = imagecolorallocate( $image, 255, 255, 255 );
                    imagefilledrectangle( $image, 0, 0, $bg_width, $bg_height, $color );
                }
                imagecopy( $image, $image_design, ( $val["area_design_left"] - $val["img_src_left"] ) * $scale, ( $val["area_design_top"] - $val["img_src_top"] ) * $scale, 0, 0, $ds_width, $ds_height );
                if( $val["show_overlay"] == '1' && $image_overlay ){
                    imagecopy( $image, $image_overlay, 0, 0, 0, 0, $bg_width, $bg_height );
                }
                if( $design_format == 'png' ){
                    imagepng( $image, $path_dst . '/frame_' . $key . '.png' );
                }else{
                    imagejpeg( $image, $path_dst . '/frame_' . $key . '.jpeg' );
                }
                imagedestroy( $image );
                imagedestroy( $image_design );
                $count++;
            }
            if( $limit && $count == $limit ) break;
        }
    }
    public function create_mockup_preview( $path, $option, $product_config ){
        $mockup_images = array();
        if( isset( $option['mockup_preview'] ) ){
            $mockup_path = $path . '/mockup_preview';
            if( !file_exists( $mockup_path ) ){
                wp_mkdir_p( $mockup_path );
            }
            $preview_images = Nbdesigner_IO::get_list_images( $path.'/preview', 1 );
            foreach( $option['mockup_preview'] as $key => $mockup ){
                $src = get_attached_file( $mockup['sid'] );
                if( $src ){
                    $name       = sanitize_file_name($mockup['name']);
                    $m_path     = $mockup_path . '/' . $name . '.png';
                    $image      = imagecreatetruecolor( 1000, 1000 );
                    $color      = imagecolorallocatealpha( $image, 255, 255, 255, 127 );
                    imagefill( $image, 0, 0, $color );
                    $ext = strtolower( pathinfo($src, PATHINFO_EXTENSION ) );
                    if( $ext == 'jpg' || $ext == 'jpeg' ){
                        $s_image = imagecreatefromjpeg( $src );
                    }else{
                        $s_image = imagecreatefrompng( $src );
                    }
                    list($width, $height) = getimagesize( $src );
                    imagecopyresized($image, $s_image, 2 * $mockup['s_left'], 2 * $mockup['s_top'], 0, 0, 2 * $mockup['s_width'], 2 * $mockup['s_height'], $width, $height);
                    list($p_width, $p_height) = getimagesize($preview_images[0]);
                    $scaleX = 2 * $mockup['width'] / $p_width;
                    $scaleY = 2 * $mockup['height'] / $p_height;
                    if( ($p_width / $p_height) > ($mockup['width'] / $mockup['height']) ){
                        $new_width = 2 * $mockup['width'];
                        $new_height = $p_height * $scaleX;
                    }else{
                        $new_height = 2 * $mockup['height'];
                        $new_width = $p_width * $scaleY;
                    }
                    switch( $mockup['anchor'] ){
                        case 'nw':
                            $new_top = 2 * $mockup['top'];
                            $new_left = 2 * $mockup['left'];
                            break;
                        case 'n':
                            $new_top = 2 * $mockup['top'];
                            $new_left = 2 * $mockup['left'] + $mockup['width'] - $new_width / 2;
                            break;
                        case 'ne':
                            $new_top = 2 * $mockup['top'];
                            $new_left = 2 * $mockup['left'] + 2 * $mockup['width'] - $new_width;
                            break;
                        case 'w':
                            $new_left = 2 * $mockup['left'];
                            $new_top = 2 * $mockup['top'] + $mockup['height'] - $new_height / 2;
                            break;
                        case 'c':
                            $new_left = 2 * $mockup['left'] + $mockup['width'] - $new_width / 2;
                            $new_top = 2 * $mockup['top'] + $mockup['height'] - $new_height / 2;
                            break;
                        case 'e':
                            $new_left = 2 * $mockup['left'] + 2 * $mockup['width'] - $new_width;
                            $new_top = 2 * $mockup['top'] + $mockup['height'] - $new_height / 2;
                            break;
                        case 'sw':
                            $new_left = 2 * $mockup['left'];
                            $new_top = 2 * $mockup['top'] + 2 * $mockup['height'] - $new_height;
                            break;
                        case 's':
                            $new_left = 2 * $mockup['left'] + $mockup['width'] - $new_width / 2;
                            $new_top = 2 * $mockup['top'] + 2 * $mockup['height'] - $new_height;
                            break;
                        case 'se':
                            $new_left = 2 * $mockup['left'] + 2 * $mockup['width'] - $new_width;
                            $new_top = 2 * $mockup['top'] + 2 * $mockup['height'] - $new_height;
                            break;
                    }
                    $p_image = imagecreatefrompng($preview_images[0]);
                    imagecopyresized($image, $p_image, $new_left, $new_top, 0, 0, $new_width, $new_height, $p_width, $p_height);
                    imagepng($image, $m_path );
                    imagedestroy($p_image);
                    imagedestroy($image);
                    imagedestroy($s_image);
                    $mockup_images[] = array(
                        'name'  => $mockup['name'],
                        'src'   => Nbdesigner_IO::wp_convert_path_to_url( $m_path ),
                        'path'  => 'mockup_preview/' . $name . '.png'
                    );
                }
            }
            foreach($preview_images as $image){
                $path_parts = pathinfo($image);
                $key = substr($path_parts['filename'], 6);
                $mockup_images[] = array(
                    'name'  => $product_config[$key]['orientation_name'],
                    'src'   => Nbdesigner_IO::wp_convert_path_to_url( $image ),
                    'path'  => 'preview/' . $path_parts['basename']
                );
            }
        }
        return $mockup_images;
    }
    /**
     * Save design, product configuration into NBDESIGNER_CUSTOMER_DIR <br />
     * Create folder based on the microtime or replace old folder when edit design <br />
     * Store folder name into WC()->session  
     */
    public function nbd_save_cart_design(){
        if ( ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) || !isset( $_POST['product_id'] ) ) {
            die( 'Security error' );
        }
        $result = array(
            'flag'  => 'success'
        );
        do_action( 'before_nbd_save_cart_design', $_POST );
        $product_id         = absint( $_POST['product_id'] );
        $variation_id       = ( isset( $_POST['variation_id'] ) && $_POST['variation_id'] != '' ) ? absint( $_POST['variation_id'] ) : 0;  
        $enable_upload_without_design = ( isset( $_POST['enable_upload_without_design'] ) && $_POST['enable_upload_without_design'] != '') ? absint( $_POST['enable_upload_without_design'] ) : 1;
        /* Save custom design */
        $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id;
        $nbd_item_session   = WC()->session->get( 'nbd_item_key_' . $nbd_item_cart_key );
        $nbd_item_key       = isset( $nbd_item_session ) ? $nbd_item_session : substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
        $path               = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
        if( $variation_id > 0 ){
            $product_config     = unserialize( get_post_meta($variation_id, '_designer_variation_setting', true ) );
            $enable_variation   = get_post_meta( $variation_id, '_nbdesigner_variation_enable', true );
            if ( !( $enable_variation && isset( $product_config[0] ) ) ){
                $product_config = unserialize( get_post_meta( $product_id, '_designer_setting', true ) ); 
            }
        } else {
            $product_config = unserialize( get_post_meta( $product_id, '_designer_setting', true ) ); 
        } 
        $product_option     = get_post_meta( $product_id, '_nbdesigner_option', true ); 
        $product_upload     = get_post_meta( $product_id, '_nbdesigner_upload', true ); 
        $add_file           = false;
        $design_id          = ( isset( $_POST['design_id'] ) && $_POST['design_id'] != '' ) ? sanitize_text_field( $_POST['design_id'] ) : '';  
        if( isset( $_POST['task2'] ) && $_POST['task2'] == 'add_file' ) {
            $enable_upload_without_design = 2;
            $add_file = true;
        }
        if( $enable_upload_without_design == 1 ){
            $save_status = $this->store_design_data( $nbd_item_key, $_FILES, $product_config, $product_option, $product_upload );
            $width = absint( nbdesigner_get_option( 'nbdesigner_thumbnail_width', 300 ) ) ? absint( nbdesigner_get_option( 'nbdesigner_thumbnail_width', 300 ) ) : 300;
            if( false != $save_status ){
                $path_config = $path . '/config.json';
                $config = nbd_get_data_from_json( $path_config );
                if( count( $config->product ) ){
                    $product_config = array();
                    foreach( $config->product as $side ){
                        $product_config[] = (array)$side;
                    }
                }; 
                if( isset( $config->custom_dimension ) ){
                    $custom_side        = absint( $config->custom_dimension->side );
                    $custom_width       = $config->custom_dimension->width;
                    $custom_height      = $config->custom_dimension->height;
                    $product_config     = $this->merge_product_config( $product_config, $custom_width, $custom_height, $custom_side );
                }
                if( isset( $config->areaDesignShapes ) ){
                    foreach ( $product_config as $key => $_config ){
                        if( $config->areaDesignShapes[$key] ) $product_config[$key]["areaDesignShape"] = true;
                    }
                }
                $this->create_preview_design( $path, $path.'/preview', $product_config, $width, $width );
                do_action( 'after_nbd_create_preview_design', $nbd_item_key );
                WC()->session->set( 'nbd_item_key_'.$nbd_item_cart_key, $nbd_item_key ); 
            } else {
                $result['flag'] = 'failure';
            }
        }
        /* Save upload design */
        if( isset( $_POST['nbd_file'] ) && $_POST['nbd_file'] != '' ){
            $nbu_item_session = WC()->session->get( 'nbu_item_key_' . $nbd_item_cart_key );
            if( $nbu_item_session ){
                $this->update_files_upload( $_POST['nbd_file'], $nbu_item_session );
//                if( !$add_file ){
//                    WC()->session->set('nbu_item_key_'.$nbd_item_cart_key, $nbu_item_session);
//                }
            }
        }
        if( isset( $_POST['task2'] ) && $_POST['task2'] != '' ){
            /* Destroy nbd_item session and init cart session */
            if( $_POST['task2'] != 'cuz' ){
                $cart_item_key = $_POST['cart_item_key'];
                WC()->session->__unset( 'nbd_item_key_' . $nbd_item_cart_key );
                WC()->session->__unset( 'nbu_item_key_' . $nbd_item_cart_key );
                if( !isset( WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] ) ) WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] = array();
                if( !$add_file ){
                    WC()->session->set( $cart_item_key . '_nbd', $nbd_item_key );
                    WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbd'] = $nbd_item_key;
                    if( $design_id ){
                        WC()->cart->cart_contents[ $cart_item_key ]['nbd_design_id'] = $design_id;
                    }
                }
                if( isset( $_POST['nbd_file'] ) && $_POST['nbd_file'] != '' ){
                    WC()->session->set( $cart_item_key . '_nbu', $nbu_item_session );
                    WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbu'] = $nbu_item_session;

                    $cart_item_keys = WC()->session->get( 'nbd_cart_item_keys' );
                    if( $cart_item_keys ){
                        $cart_item_keys_arr = explode( '|', $cart_item_keys );
                        if( in_array( $cart_item_key, $cart_item_keys_arr ) ){
                            foreach( $cart_item_keys_arr as $cik ){
                                WC()->session->set( $cik . '_nbu', $nbu_item_session );
                                if( !isset( WC()->cart->cart_contents[ $cik ]['nbd_item_meta_ds'] ) ) WC()->cart->cart_contents[ $cik ]['nbd_item_meta_ds'] = array();
                                WC()->cart->cart_contents[ $cik ]['nbd_item_meta_ds']['nbu'] = $nbu_item_session;
                            }
                            WC()->session->__unset( 'nbd_cart_item_keys' );
                        }
                    }
                }
                WC()->cart->set_session();
            }
        }else {
            /* Add to cart directly in custom page */
            $quantity       = ( isset( $_POST['qty'] ) && absint( $_POST['qty'] ) > 0 ) ? absint( $_POST['qty'] ) : 1;
            $product_status = get_post_status( $product_id );
            if ( false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id ) && 'publish' === $product_status ) {
                do_action( 'woocommerce_ajax_added_to_cart', $product_id );
            } else {
                $result['flag'] = 'failure';
                $result['mes']  = __( 'Error adding to the cart', 'web-to-print-online-designer' );
            }
        }
        do_action( 'after_nbd_save_cart_design', $_POST, $result );
        echo json_encode( $result );
        wp_die();
    }
    public function nbd_save_draft_design(){
        if ( ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) || !isset( $_POST['product_id'] ) ) {
            die( 'Security error' );
        }
        $result         = array(
            'flag'  => 0
        );
        $stored         = false;
        $product_id     = absint( $_POST['product_id'] );
        $variation_id   = ( isset($_POST['variation_id'] ) && $_POST['variation_id'] != '') ? absint( $_POST['variation_id'] ) : 0;
        $product_option = get_post_meta( $product_id, '_nbdesigner_option', true ); 
        $draft_folder   = substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
        if( isset( $_POST['draft_folder'] ) && $_POST['draft_folder'] != '' ){
            $draft_folder   = $_POST['draft_folder'];
            $stored         = true;
        }
        if( $variation_id > 0 ){
            $product_config     = unserialize( get_post_meta( $variation_id, '_designer_variation_setting', true ) );
            $enable_variation   = get_post_meta( $variation_id, '_nbdesigner_variation_enable', true );
            if ( !( $enable_variation && isset( $product_config[0] ) ) ){
                $product_config = unserialize( get_post_meta( $product_id, '_designer_setting', true ) );
            }
        } else {
            $product_config = unserialize( get_post_meta( $product_id, '_designer_setting', true ) );
        }
        $product_upload = get_post_meta( $product_id, '_nbdesigner_upload', true );
        $save_status    = $this->store_design_data( $draft_folder, $_FILES, $product_config, $product_option, $product_upload ); 
        if( $save_status ){
            $path   = NBDESIGNER_CUSTOMER_DIR . '/' . $draft_folder;
            $width  = absint( nbdesigner_get_option( 'nbdesigner_thumbnail_width', 300 ) ) ? absint( nbdesigner_get_option( 'nbdesigner_thumbnail_width', 300 ) ) : 300;
            $this->create_preview_design( $path, $path . '/preview', $product_config, $width, $width );
            if( $stored ){
                $result['flag']         = 'success';
                $result['draft_folder'] = $draft_folder;
            }else{
                global $wpdb;
                $created_date   = new DateTime();
                $user_id        = wp_get_current_user()->ID;
                $table_name     = $wpdb->prefix . 'nbdesigner_mydesigns';
                $re             = $wpdb->insert($table_name, array(
                    'product_id'    => $product_id,
                    'variation_id'  => $variation_id,
                    'price'         => 0,
                    'folder'        => $draft_folder,
                    'user_id'       => $user_id,
                    'publish'       => 1,
                    'created_date'  => $created_date->format( 'Y-m-d H:i:s' )
                ));
                if( $re ){
                    $result['flag']         = 'success';
                    $result['draft_folder'] = $draft_folder;
                }
            }
        }
        wp_send_json($result);
    }
    public function generate_design_id(){
        if (!isset($_POST['design_id']) || $_POST['design_id'] == '') {
            // 生成唯一的design_id
            $product_id = isset($_POST['product_id']) ? sanitize_text_field($_POST['product_id']) : '';
            $random_part = substr(md5(uniqid()), 0, 5);  // 取MD5的前5位
            $random_number = rand(1, 100);               // 1-100的随机数
            $timestamp = time();                         // 当前时间戳
            
            $design_id = $product_id . '_' . $random_part . $random_number . $timestamp;
            error_log('nbd_save_customer_design generated new design_id: ' . $design_id);
        } else {
            $design_id = sanitize_text_field($_POST['design_id']);
            error_log('nbd_save_customer_design using existing design_id: ' . $design_id);
        }
        return $design_id;
    }

    public function nbd_update_design_data($product_id, $variation_id, $nbd_item_cart_key,$nbd_item_key, $design_id){    
        global $wpdb;
        $user_id = wp_get_current_user()->ID;
        error_log("nbd_update_design_data Checking designs for product_id: $product_id, variation_id: $variation_id");
    
        // 1. 查询现有设计记录
        $existing_designs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}nbdesigner_mydesigns 
            WHERE user_id = %d AND product_id = %d AND variation_id = %d",
            $user_id,
            $product_id,
            $variation_id
        ));
        
        error_log('nbd_update_design_dataExisting designs found: ' . count($existing_designs));
    
        // 2. 如果存在记录,检查购物车中是否使用这些设计
        if(!empty($existing_designs)) {
            foreach($existing_designs as $design) {
                $design_found_in_cart = false;
                
                // 检查购物车中的每个项目
                if(WC()->cart && !empty(WC()->cart->cart_contents)) {
                    foreach(WC()->cart->cart_contents as $cart_item_key => $cart_item) {
                        error_log('nbd_update_design_data cart_item_key: ' . $cart_item_key . ' folder: ' . $design->folder);
                        if(isset($cart_item['nbd_design_id']) && $cart_item['nbd_design_id'] == $design->folder) {
                            $design_found_in_cart = true;
                            error_log('nbd_update_design_data found design in cart: ' . $design->folder);
                            break;
                        }
                    }
                }
                
                // 如果设计未在购物车中使用,删除该记录和对应文件夹
                if(!$design_found_in_cart) {
                    error_log("Deleting unused design ID: {$design->id}");
                    
                    // 删除设计文件夹
                    if(!empty($design->folder)) {
                        $design_path = NBDESIGNER_CUSTOMER_DIR . '/' . $design->folder;
                        error_log("Deleting design folder: $design_path");
                        
                        // 使用 Nbdesigner_IO::delete_folder 删除文件夹
                        if(file_exists($design_path)) {
                            try {
                                Nbdesigner_IO::delete_folder($design_path);
                                error_log("Design folder deleted successfully");
                            } catch(Exception $e) {
                                error_log("Error deleting design folder: " . $e->getMessage());
                            }
                        }
                    }
                    
                    // 删除数据库记录
                    $re = My_Design_Endpoint::nbd_delete_my_design_inner(
                        $design->product_id,
                        $design->variation_id,
                        $design->folder
                    );
                    if($re){
                        error_log("Design deleted successfully");
                    }else{
                        error_log("Error deleting design");
                    }
                }
            }
        }
    
        // 3. 插入新的设计记录
        $result = My_Design_Endpoint::nbdesigner_insert_table_my_design( $product_id, $variation_id, $nbd_item_key );
        error_log('nbd_update_design_data task == create type == art');
    
        if($result) {
            error_log("New design saved successfully. ID: $design_id");
            WC()->session->set( 'nbd_item_key_' . $nbd_item_cart_key, $nbd_item_key );
            if( $design_id ){
                WC()->session->set( 'nbd_design_id_' . $nbd_item_cart_key, $design_id );
                error_log('nbd_update_design_data save4 nbd_design_id: ' . $design_id . ' nb_item_cart_key: ' . $nbd_item_cart_key);
            }
            // wp_send_json_success(array(
            //     'design_id' => $design_id,
            //     'message' => 'Design saved successfully'
            // ));
        } else {
            error_log("Error saving new design");
            // wp_send_json_error(array(
            //     'message' => 'Failed to save design'
            // ));
        }
    }

    public function nbd_save_customer_design(){
        error_log('nbd_save_customer_design begin');
        if ( ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) || !isset( $_POST['product_id'] ) ) {
            die( 'Security error' );
        }
        $result = array(
            'flag'  => 'failure',
            'link'  => ''
        );
        do_action( 'before_nbd_save_customer_design' );
        /* Get data from $_POST */
        $product_id         = absint( $_POST['product_id'] );
        $product            = wc_get_product( $product_id );
        $product_option     = get_post_meta( $product_id, '_nbdesigner_option', true ); 
        $variation_id       = ( isset( $_POST['variation_id'] ) && $_POST['variation_id'] != '' ) ? absint($_POST['variation_id']) : 0;
        $cart_item_key      = ( isset( $_POST['cart_item_key'] ) && $_POST['cart_item_key'] != '' ) ? sanitize_text_field( $_POST['cart_item_key'] ) : '';
        $task               = "create";//( isset( $_POST['task'] ) && $_POST['task'] != '' ) ? sanitize_text_field( $_POST['task'] ) : 'new';
        $task2              = ( isset( $_POST['task2'] ) && $_POST['task2'] != '' ) ? sanitize_text_field( $_POST['task2'] ) : '';
        $auto_add_to_cart   = ( isset( $_POST['auto_add_to_cart'] ) && $_POST['auto_add_to_cart'] != '' ) ? $_POST['auto_add_to_cart'] : 'no';
        $design_type        = "art";//( isset( $_POST['design_type'] ) && $_POST['design_type'] != '' ) ? wc_clean( $_POST['design_type'] ) : '';
        $generate_mockup    = ( isset( $_POST['generate_mockup'] ) && $_POST['generate_mockup'] != '' ) ? true : false;
        $ui_mode            = ( isset( $_POST['ui_mode'] ) && $_POST['ui_mode'] != '' ) ? wc_clean( $_POST['ui_mode'] ) : '1';
        $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id; 
        $type               = $product->get_type();
        $option             = unserialize( $product_option );
        $design_id          = $this->generate_design_id();//( isset( $_POST['design_id'] ) && $_POST['design_id'] != '' ) ? sanitize_text_field( $_POST['design_id'] ) : '';
        error_log('nbd_save_customer_design design_id: ' . $design_id . ' task: ' . $task . ' task2: ' . $task2 . ' auto_add_to_cart: ' . $auto_add_to_cart . ' design_type: ' . $design_type . ' generate_mockup: ' . $generate_mockup . ' ui_mode: ' . $ui_mode . ' nbd_item_cart_key: ' . $nbd_item_cart_key);
        $switched_product   = ( isset( $_POST['switched_product'] ) && $_POST['switched_product'] != '' ) ? absint( $_POST['switched_product'] ) : '';
        error_log('nbd_save_customer_design product_id: ' . $product_id . ' variation_id: ' . $variation_id . ' design_id: ' . $design_id);
        if( $type == 'variable' && isset( $option['bulk_variation'] ) && $option['bulk_variation'] == 1 ){
            $nbd_item_cart_key = $product_id . '_0';
        }
        $result[ 'product_id' ]     = $product_id;
        $result[ 'variation_id' ]   = $variation_id;
        if( isset( $_POST['nbd_item_key'] ) && $_POST['nbd_item_key'] != '' ){
            /* Edit design 
             * In case edit template, $design_type = 'template'
             */
            $nbd_item_key = $_POST['nbd_item_key'];
            error_log('nbd_save_customer_design not null nbd_item_key: ' . $nbd_item_key);
            if( ( $design_type == '' ) && !nbd_check_cart_item_exist( $cart_item_key ) ) {
                $result['mes'] = esc_html__('Item not exist in cart', 'web-to-print-online-designer');
                error_log('nbd_save_customer_design nbd_die1');
                nbd_die( $result );
            }
        }else {
            /* Create new design */
            $nbd_item_session = WC()->session->get( 'nbd_item_key_' . $nbd_item_cart_key . ' task: ' . $task . ' task2: ' . $task2 . '  isset nbd_item_session: ' . isset( $nbd_item_session ));
            if( ($task == 'new' && $task2 == 'update' ) || $task == 'create' || !isset( $nbd_item_session ) ){
                //$nbd_item_key = substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
                $nbd_item_key = $design_id;
                error_log('nbd_save_customer_design new design_id: ' . $nbd_item_key);
            }else {
                $nbd_item_key = $nbd_item_session;
                error_log('nbd_save_customer_design old design_id: ' . $nbd_item_key);
            }
            // if ( $nbd_item_session ) {
            //     error_log('nbd_save_customer_design set ------$nbd_item_session ' . $nbd_item_session);
            //     WC()->session->set('nbd_session_saved', $nbd_item_session);
            // }
        }
        $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key;
        if( $task == 'new' || $task == 'create' ){
            if( $variation_id > 0 ){
                $product_config     = unserialize( get_post_meta( $variation_id, '_designer_variation_setting', true ) );
                $enable_variation   = get_post_meta( $variation_id, '_nbdesigner_variation_enable', true );
                if ( !( $enable_variation && isset( $product_config[0] ) ) ){
                    $product_config = unserialize( get_post_meta( $product_id, '_designer_setting', true ) ); 
                }
            }else {
                $product_config = unserialize( get_post_meta( $product_id, '_designer_setting', true ) );
            } 
        }else{
            $product_config = unserialize( file_get_contents( $path . '/product.json' ) );
        }
        $att_swatch         = (isset($_POST['att_swatch']) && $_POST['att_swatch'] != '') ? $_POST['att_swatch'] : '';
        $product_config     = apply_filters( 'nbd_save_customer_design_product_config', $product_config, $product_option, $att_swatch );
        $product_upload     = get_post_meta( $product_id, '_nbdesigner_upload', true );
        $save_status        = $this->store_design_data( $nbd_item_key, $_FILES, $product_config, $product_option, $product_upload );
        $width              = absint( nbdesigner_get_option( 'nbdesigner_thumbnail_width' ) ) ? absint( nbdesigner_get_option( 'nbdesigner_thumbnail_width', 300 ) ) : 300;
        if( $task == 'create' || $design_type == 'template' ){
            $width = absint( nbdesigner_get_option( 'nbdesigner_template_width', 500 ) ) ? absint( nbdesigner_get_option( 'nbdesigner_template_width', 500 ) ) : 500;
        }
        if( false != $save_status ){
            /* todo edit $product_config if has custom dimension */
            $path_config    = $path . '/config.json';
            $config         = nbd_get_data_from_json( $path_config );
            if( count( $config->product ) ){
                $product_config = array();
                foreach( $config->product as $side ){
                    $product_config[] = (array)$side;
                }
            };
            if( isset( $config->custom_dimension ) ){
                $custom_side        = absint( $config->custom_dimension->side );
                $custom_width       = $config->custom_dimension->width;
                $custom_height      = $config->custom_dimension->height;
                $product_config     = $this->merge_product_config( $product_config, $custom_width, $custom_height, $custom_side );
            }
            if( isset( $config->areaDesignShapes ) ){
                foreach ( $product_config as $key => $_config ){
                    if( $config->areaDesignShapes[$key] ) $product_config[$key]["areaDesignShape"] = true;
                }
            }
            $this->create_preview_design( $path, $path.'/preview', $product_config, $width, $width );
            if( isset( $_POST['share'] ) ){
                Nbdesigner_IO::copy_dir( $path, $path . 's' );
                $result['sfolder'] = $nbd_item_key . 's';
            }
            if( isset( $_POST['switched_product'] ) && $switched_product > 0 ){
                Nbdesigner_IO::copy_dir( $path, $path . 'r' );
                $layout         = nbd_get_product_layout( $switched_product );
                $redirect_url   =  add_query_arg( array(
                    'product_id'    => $switched_product,
                    'view'          => $layout,
                    'reference'     => $nbd_item_key . 'r'
                ),  getUrlPageNBD( 'create' ) );
                $result['redirect_url'] = $redirect_url;
            }
            if( $generate_mockup ){
                $result['mockups'] = $this->create_mockup_preview( $path, $option, $product_config );
            }
            $result['image']    = array();
            $images             = Nbdesigner_IO::get_list_images( $path.'/preview', 1 );
            $images             = nbd_sort_file_by_side( $images );
            foreach( $images as $image ){
                $filename = pathinfo( $image, PATHINFO_FILENAME );
                $result['image'][$filename] = Nbdesigner_IO::wp_convert_path_to_url( $image );
            }
            $result['flag']     = 'success';
            $result['folder']   = $nbd_item_key;
            if( $ui_mode == '1' && nbdesigner_get_option( 'nbdesigner_enable_gallery_api', 'no' ) == 'yes' ){
                $result['gallery']  = array();
                $g_images           = $images;
                ksort( $g_images );
                foreach( $g_images as $key => $image ){
                    list( $width, $height ) = getimagesize( $image );
                    $result['gallery'][] = array(
                        'src'       => Nbdesigner_IO::wp_convert_path_to_url( $image ),
                        'width'     => $width,
                        'height'    => $height,
                        'title'     => isset( $product_config[$key] ) ? $product_config[$key]['orientation_name'] : '',
                        'sizes'     => $width . 'x' . $height
                    );
                }
            }
            if( $task == 'new' ){
                error_log('nbd_save_customer_design task == new');
                if( $task2 == 'update' ){
                    error_log('nbd_save_customer_design task2 == update');
                    WC()->session->set( $cart_item_key. '_nbd', $nbd_item_key );
                    if( !isset(WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']) ) WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds'] = array();
                    WC()->cart->cart_contents[ $cart_item_key ]['nbd_item_meta_ds']['nbd'] = $nbd_item_key;
                    if( $design_id ){
                        WC()->cart->cart_contents[ $cart_item_key ]['nbd_design_id'] = $design_id;
                        error_log('nbd_save_customer_design save1 nbd_design_id: ' . $design_id);
                    }
                    
                    $cart_item_keys = WC()->session->get( 'nbd_cart_item_keys' );
                    error_log('nbd_save_customer_design cart_item_keys: ' . $cart_item_keys);
                    if( $cart_item_keys ){
                        $cart_item_keys_arr = explode( '|', $cart_item_keys );
                        if( in_array( $cart_item_key, $cart_item_keys_arr ) ){
                            error_log('nbd_save_customer_design cart_item_keys_arr: ' . $cart_item_keys_arr);
                            foreach( $cart_item_keys_arr as $cik ){
                                WC()->session->set( $cik . '_nbd', $nbd_item_key );
                                if( !isset( WC()->cart->cart_contents[ $cik ]['nbd_item_meta_ds'] ) ) WC()->cart->cart_contents[ $cik ]['nbd_item_meta_ds'] = array();
                                WC()->cart->cart_contents[ $cik ]['nbd_item_meta_ds']['nbd'] = $nbd_item_key;
                                if( $design_id ){
                                    WC()->cart->cart_contents[ $cik ]['nbd_design_id'] = $design_id;
                                    error_log('nbd_save_customer_design save2 nbd_design_id: ' . $design_id);
                                }
                            }
                            WC()->session->__unset( 'nbd_cart_item_keys' );
                        }
                    }
                    
                    WC()->cart->set_session();
                    WC()->session->__unset( 'nbd_item_key_' . $nbd_item_cart_key );
                }else{
                    error_log('nbd_save_customer_design task == new task2 == update else');
                    WC()->session->set( 'nbd_item_key_' . $nbd_item_cart_key, $nbd_item_key );
                    if( $design_id ){
                        WC()->session->set( 'nbd_design_id_' . $nbd_item_cart_key, $design_id );
                        error_log('nbd_save_customer_design save3 nbd_design_id: ' . $design_id . ' nb_item_cart_key: ' . $nbd_item_cart_key);
                    }
                }
            }
            if( $task == 'create' ){
                if( $design_type == 'art' ){
                    error_log('nbd_save_customer_design task == create design_type == art');
                    $this->nbd_update_design_data( $product_id, $variation_id, $nbd_item_cart_key, $nbd_item_key, $design_id );
                } else {
                    if( !can_edit_nbd_template() ){
                        $result['mes'] = esc_html__( 'You have not permission to create or edit template', 'web-to-print-online-designer' ); 
                        echo json_encode( $result );
                        wp_die();
                    }else{
                        $this->nbdesigner_insert_table_templates( $product_id, $variation_id, $nbd_item_key, 0, 1, 0 );
                    }
                }
            }
            if( $task == 'new' && $auto_add_to_cart == 'yes' ){
                if( nbd_add_to_cart( $product_id, $variation_id, 1 ) ){
                    $result['added'] = 1;
                }
            }
            if( $task == 'edit' && isset( $_POST['order_id'] ) && $design_type == 'edit_order' ){
                $order_id = absint( $_POST['order_id'] );
                update_post_meta( $order_id, '_nbdesigner_order_changed', 1 );
            }
            if( $task == 'edit' && ( isset( $_POST['cart_item_key'] ) && $_POST['cart_item_key'] != '' ) ){
                if( isset( $_POST['qty'] ) && absint( $_POST['qty'] ) > 0 ){
                    global $woocommerce;
                    $woocommerce->cart->set_quantity( $cart_item_key, absint( $_POST['qty'] ) );
                }
            }
        }
        do_action( 'after_nbd_save_customer_design', $result );
        $result = apply_filters( 'filter_after_nbd_save_customer_design', $result );
        error_log('nbd_save_customer_design done');
        echo json_encode( $result );
        wp_die();
    }
    public function nbd_remove_design_and_file(){
        if ( ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) || !isset( $_POST['product_id'] ) ) {
            die( 'Security error' );
        }
        $product_id         = absint( $_POST['product_id'] );
        $variation_id       = ( isset( $_POST['variation_id'] ) && $_POST['variation_id'] != '' ) ? absint( $_POST['variation_id'] ) : 0; 
        $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id; 
        WC()->session->__unset( 'nbd_item_key_'.$nbd_item_cart_key );
        WC()->session->__unset( 'nbu_item_key_'.$nbd_item_cart_key );
        echo 'success';
        wp_die(); 
    }
    public function merge_product_config( $product_config, $width, $height, $number_side ){
        foreach ( $product_config as $key => $side ) {
            $product_config[$key]["real_width"]     = $product_config[$key]["product_width"] = $width;
            $product_config[$key]["real_height"]    = $product_config[$key]["product_height"] = $height;
            if( $width >= $height ) {
                $ratio = 500 / $width;
                $product_config[$key]["img_src_width"]          = $product_config[$key]["area_design_width"]    = 500;
                $product_config[$key]["img_src_left"]           = $product_config[$key]["area_design_left"]     = 0;
                $product_config[$key]["area_design_height"]     = $product_config[$key]["img_src_height"]       = round( $ratio * $height );
                $product_config[$key]["img_src_top"]            = $product_config[$key]["area_design_top"]      = round( 250 - $product_config[$key]["area_design_height"] / 2 );
            }else {
                $ratio = 500 / $height;
                $product_config[$key]["img_src_height"]     = $product_config[$key]["area_design_height"]   = 500;
                $product_config[$key]["img_src_top"]        = $product_config[$key]["area_design_top"]      = 0;
                $product_config[$key]["area_design_width"]  = $product_config[$key]["img_src_width"]        = round( $ratio * $width );
                $product_config[$key]["img_src_left"]       = $product_config[$key]["area_design_left"]     = round( 250 - $product_config[$key]["area_design_width"] / 2 );
            }
        }
        $len = count( $product_config );
        if( $len >= $number_side ){
            array_splice($product_config, $number_side, $len - $number_side);
        }else {
            for( $i = $len; $i < $number_side; $i++ ){
                $product_config[$i] = $product_config[0];
            }
        } 
        return $product_config;
    }
    public function nbdesigner_user_role(){
        $capabilities = array(
            2    => 'manage_nbd_art',
            1    => 'manage_nbd_tool',
            3    => 'manage_nbd_language',
            4    => 'manage_nbd_template',
            5    => 'manage_nbd_product',
            6    => 'manage_nbd_analytic',
            7    => 'manage_nbd_design_store',
            8    => 'manage_nbd_font',
            9    => 'export_nbd_font',
            10   => 'export_nbd_art',
            11   => 'export_nbd_product',
            12   => 'export_nbd_language',
            13   => 'import_nbd_font',
            14   => 'import_nbd_art',
            15   => 'import_nbd_product',
            16   => 'import_nbd_language',
            17   => 'edit_nbd_font',
            18   => 'edit_nbd_art',
            19   => 'edit_nbd_language',
            20   => 'edit_nbd_template',
            21   => 'delete_nbd_font',
            22   => 'delete_nbd_art',
            23   => 'delete_nbd_language',
            24   => 'delete_nbd_template',
            25   => 'sell_nbd_design',
            26   => 'update_nbd_data',
            27   => 'manage_nbd_setting'
        );
        $admin_role = get_role('administrator');
        if (null != $admin_role) {
            foreach ($capabilities as $cap){
                $admin_role->add_cap($cap);
            }
        }
        $shop_manager_role = get_role('shop_manager');
        if (null != $shop_manager_role) {
            foreach ($capabilities as $cap){
                $shop_manager_role->add_cap($cap);
            }
        }
        $shop_capabilities = $shop_manager_role->capabilities;
        $shop_capabilities['export_nbd_font']       = false;
        $shop_capabilities['export_nbd_art']        = false;
        $shop_capabilities['export_nbd_product']    = false;
        $shop_capabilities['export_nbd_language']   = false;
        $shop_capabilities['import_nbd_font']       = false;
        $shop_capabilities['import_nbd_art']        = false;
        $shop_capabilities['import_nbd_product']    = false;
        $shop_capabilities['import_nbd_language']   = false;
        $shop_capabilities['edit_nbd_font']         = false;
        $shop_capabilities['edit_nbd_art']          = false;
        $shop_capabilities['edit_nbd_language']     = false;
        $shop_capabilities['edit_nbd_template']     = false;
        $shop_capabilities['delete_nbd_font']       = false;
        $shop_capabilities['delete_nbd_art']        = false;
        $shop_capabilities['delete_nbd_language']   = false;
        $shop_capabilities['delete_nbd_template']   = false;
        $shop_capabilities['update_nbd_data']       = false;
        $nbd_viewer = add_role('nbdesigner_viewer', 'NBDesigner Viewer', $shop_manager_role->capabilities);
        if ( null === $nbd_viewer ) {
            $nbd_viewer = get_role('nbdesigner_viewer');
            $nbd_viewer->remove_cap('export_nbd_font');
            $nbd_viewer->remove_cap('export_nbd_art');
            $nbd_viewer->remove_cap('export_nbd_product');
            $nbd_viewer->remove_cap('export_nbd_language');
            $nbd_viewer->remove_cap('import_nbd_font');
            $nbd_viewer->remove_cap('import_nbd_art');
            $nbd_viewer->remove_cap('import_nbd_product');
            $nbd_viewer->remove_cap('import_nbd_language');
            $nbd_viewer->remove_cap('edit_nbd_font');
            $nbd_viewer->remove_cap('edit_nbd_art');
            $nbd_viewer->remove_cap('edit_nbd_language');
            $nbd_viewer->remove_cap('edit_nbd_template');
            $nbd_viewer->remove_cap('delete_nbd_font');
            $nbd_viewer->remove_cap('delete_nbd_art');
            $nbd_viewer->remove_cap('delete_nbd_language');
            $nbd_viewer->remove_cap('delete_nbd_template');
            $nbd_viewer->remove_cap('update_nbd_data');
        }
    }
    /**
     * Insert table templates
     * @since 1.5.0
     * 
     * @global type $wpdb
     * @param int $product_id
     * @param varchar $folder
     * @param int $priority
     * @param int $publish
     * @param int $private
     */
    private function nbdesigner_insert_table_templates( $product_id, $variation_id, $folder, $priority, $publish = 1, $private = 0 ){
        global $wpdb;
        $created_date   = new DateTime();
        $user_id        = wp_get_current_user()->ID;
        $table_name     = $wpdb->prefix . 'nbdesigner_templates';
        $publish        = !nbd_check_publish_design_permission( $user_id ) ? 0 : $publish;
        $wpdb->insert( $table_name, array(
            'product_id'    => $product_id,
            'variation_id'  => $variation_id,
            'folder'        => $folder,
            'user_id'       => $user_id,
            'created_date'  => current_time( 'mysql' ),
            'publish'       => $publish,
            'private'       => $private,
            'priority'      => $priority
        ) );
        return true;
    }
    public function nbdesigner_save_webcam_image(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        $result     = array();
        $img        = $_POST['image'];
        $data       = base64_decode( $img );
        $full_name  = NBDESIGNER_DATA_DIR . '/temp/' . time() .'.png';
        $success    = file_put_contents( $full_name, $data );
        if($success){
            $result['flag'] = 'success';
            $result['url']  = Nbdesigner_IO::wp_convert_path_to_url( $full_name );
        }else{
            $result['flag'] = 'false';
        }
        echo json_encode( $result );
        wp_die();
    }
    private function nbdesigner_create_thumbnail_design( $from_path, $to_path, $pid, $vid = 0, $_width = 500, $_height = 500 ){
        if($vid > 0){
            $configs = unserialize( get_post_meta( $vid, '_designer_variation_setting', true ) );
            if( !isset( $configs[0] ) ){
                $configs = unserialize( get_post_meta( $pid, '_designer_setting', true ) );
            }
        }else {
            $configs = unserialize( get_post_meta( $pid, '_designer_setting', true ) );
        }
        $path_preview = $to_path;
        if( !file_exists( $path_preview ) ){
            wp_mkdir_p( $path_preview );
        }
        foreach ( $configs as $key => $val ){
            $p_img = $from_path . '/frame_' . $key . '.png';
            if( file_exists( $p_img ) ){
                $image_design       = NBD_Image::nbdesigner_resize_imagepng( $p_img, $val["area_design_width"], $val["area_design_height"] );
                $image_product_ext  = pathinfo( $val["img_src"] );
                if( $val["bg_type"] == 'image' ){
                    if( $image_product_ext['extension'] == "png" ){
                        $image_product = NBD_Image::nbdesigner_resize_imagepng( $val["img_src"], $val["img_src_width"], $val["img_src_height"] );
                    } elseif ( $image_product_ext['extension'] == "svg" ) {
                        error_log('nbdesigner image_product_ext svg');
                        $image_product = NBD_Image::nbdesigner_resize_imagesvg( $val["img_src"], $val["img_src_width"], $val["img_src_height"] );
                    } else {
                        $image_product = NBD_Image::nbdesigner_resize_imagejpg( $val["img_src"], $val["img_src_width"], $val["img_src_height"] );
                    }
                }
                $image = imagecreatetruecolor( $_width, $_height );
                imagesavealpha( $image, true );
                $color = imagecolorallocatealpha( $image, 255, 255, 255, 127 );
                imagefill( $image, 0, 0, $color );
                if($val["bg_type"] == 'image'){
                    imagecopy( $image, $image_product, $val["img_src_left"], $val["img_src_top"], 0, 0, $val["img_src_width"], $val["img_src_height"] );
                } else if( $val["bg_type"] == 'color' ){
                    $_color = hex_code_to_rgb( $val["bg_color_value"] );
                    $color  = imagecolorallocate( $image, $_color[0], $_color[1], $_color[2] );
                    imagefilledrectangle( $image, $val["img_src_left"], $val["img_src_top"], $val["img_src_left"] + $val["img_src_width"], $val["img_src_top"] + $val["img_src_height"], $color );
                }
                imagecopy( $image, $image_design, $val["area_design_left"], $val["area_design_top"], 0, 0, $val["area_design_width"], $val["area_design_height"] );
                imagepng( $image, $path_preview . '/frame_' . $key . '.png' );
                imagedestroy( $image );
                imagedestroy( $image_design );
            }
        }
    }
    public function set_nbd_order_data( $order_id ) {
        global $woocommerce;
        $items      = $woocommerce->cart->get_cart();
        $has_nbd    = false;
        $has_nbu    = false;
        foreach( $items as $item => $values ){
            /* custom design */
            $nbd_session = WC()->session->get( $item . '_nbd' );
            if( !$nbd_session ){
                if( isset( $values['nbd_item_meta_ds'] ) && isset( $values['nbd_item_meta_ds']['nbd'] ) ){
                    $nbd_session = $values['nbd_item_meta_ds']['nbd'];
                }
            }
            if( $nbd_session ){
                WC()->session->__unset( $item . '_nbd' ); 
                $src = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session;
                $dst = NBDESIGNER_CUSTOMER_DIR . '/' . $order_id . '_' . $nbd_session;
                if( $nbd_session != '' && file_exists( $src ) ){
                    Nbdesigner_IO::copy_dir( $src, $dst );
                    $has_nbd = true;
                }
            }
            /* upload design */
            $nbu_session = WC()->session->get( $item . '_nbu' );
            if( !$nbu_session ){
                if( isset( $values['nbd_item_meta_ds'] ) && isset( $values['nbd_item_meta_ds']['nbu'] ) ){
                    $nbu_session = $values['nbd_item_meta_ds']['nbu'];
                }
            }
            if( isset( $nbu_session ) ){
                WC()->session->__unset( $item . '_nbu' ); 
                $has_nbu = true;
            }
            WC()->session->__unset( $item . '_nbd_initial_price' );
        }
        if( $has_nbd ) {
            add_post_meta( $order_id, '_nbdesigner_order_changed', 1 );
            add_post_meta( $order_id, '_nbd', 1 );
        }
        if( $has_nbu ) {
            add_post_meta( $order_id, '_nbdesigner_upload_order_changed', 1 );
            add_post_meta( $order_id, '_nbu', 1 );
        }
    }
    public function nbdesigner_display_posts_design( $column, $post_id ) {
        if ( $column == 'design' ) {
            $is_design = get_post_meta( $post_id, '_nbdesigner_enable', true );
            echo '<input type="checkbox" disabled', ( $is_design ? ' checked' : '' ), '/>';
        }
    }
    public function nbdesigner_add_design_column( $columns ) {
        return array_merge( $columns, array( 'design' => esc_html__( 'Design', 'web-to-print-online-designer' ) ) );
    }
    public function render_cart( $title = null, $cart_item = null, $cart_item_key = null ) {
        // error_log('nbdesigner render_cart ------$cart_item_key ' . $cart_item_key . ' is_cart() ' . is_cart() . ' is_checkout() ' . is_checkout());
        // && ( is_cart() || is_checkout() ) mzl note 检查是购物车还是结账页面 ，不需要
        if ($cart_item_key) {
            $nbd_session = WC()->session->get($cart_item_key . '_nbd');
            $nbu_session = WC()->session->get($cart_item_key . '_nbu');
            if( isset($cart_item['nbd_item_meta_ds']) ){
                if( isset($cart_item['nbd_item_meta_ds']['nbd']) ) $nbd_session = $cart_item['nbd_item_meta_ds']['nbd'];
                if( isset($cart_item['nbd_item_meta_ds']['nbu']) ) $nbu_session = $cart_item['nbd_item_meta_ds']['nbu'];
            }
            // error_log('nbdesigner render_cart ------$nbd_session ' . $nbd_session . ' $nbu_session ' . $nbu_session);
            $_show_design                   = nbdesigner_get_option('nbdesigner_show_in_cart', 'yes');
            $_show_design                   = apply_filters( 'nbd_show_design_section_in_cart', $_show_design, $cart_item );
            $enable_edit_design             = nbdesigner_get_option('nbdesigner_show_button_edit_design_in_cart', 'yes') == 'yes' ? true : false;
            $show_edit_link                 = apply_filters('nbd_show_edit_design_link_in_cart', $enable_edit_design, $cart_item);
            $product_id                     = $cart_item['product_id'];
            $variation_id                   = $cart_item['variation_id'];
            $product_id                     = get_wpml_original_id( $product_id );
            $is_nbdesign                    = get_post_meta($product_id, '_nbdesigner_enable', true);
            $_enable_upload                 = get_post_meta($product_id, '_nbdesigner_enable_upload', true);
            $_enable_upload_without_design  = get_post_meta($product_id, '_nbdesigner_enable_upload_without_design', true);
            $_product                       = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_permalink              = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );            
            // error_log('nbdesigner render_cart ------$is_nbdesign ' . $is_nbdesign . ' $_show_design ' . $_show_design . ' $show_edit_link ' . $show_edit_link . ' $enable_edit_design ' . $enable_edit_design);
            if ( $is_nbdesign && $_show_design == 'yes' ) {
                // error_log('nbdesigner render_cart ------$nbd_session ' . $nbd_session . ' $nbu_session ' . $nbu_session);
                if($nbd_session || $nbu_session){
                    $html = is_checkout() ? $title . ' &times; <strong>' . $cart_item['quantity'] .'</strong>' : $title;
                }else{
                    $html = $title;
                }
                $layout = nbd_get_product_layout( $product_id );
                if( isset( $nbd_session ) ){
                    error_log('nbdesigner get render_cart ------$nbd_session ' . $nbd_session);
                    $id             = 'nbd' . $cart_item_key;
                    $redirect       = is_cart() ? 'cart' : 'checkout';
                    $html          .= '<div id="' . $id . '" class="nbd-custom-dsign nbd-cart-item-design">';
                    $remove_design  = is_cart() ? '<a class="remove nbd-remove-design nbd-cart-item-remove-design" href="#" data-type="custom" data-cart-item="' . $cart_item_key . '">&times;</a>' : '';
                    $html          .= '<p>' . esc_html__('Custom design', 'web-to-print-online-designer') . $remove_design . '</p>';
                    $list           = Nbdesigner_IO::get_list_images( NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/preview' );
                    $list           = nbd_sort_file_by_side( $list );
                    foreach ( $list as $img ) {
                        // error_log('nbdesigner render_cart ------$img ' . $img);
                        $src    = Nbdesigner_IO::convert_path_to_url( $img ) . '?&t=' . round( microtime( true ) * 1000 );
                        $html  .= '<img class="nbd_cart_item_design_preview" src="' . $src . '"/>';
                        // error_log('nbdesigner render_cart ------$src ' . $src);
                    }
                    if (!empty($list)) {
                        // 获取第一张预览图的路径
                        $preview_path = $list[0];
                        $preview_url = Nbdesigner_IO::convert_path_to_url($preview_path);
                        
                        // 更新购物车项数据
                        // WC()->cart->cart_contents[$cart_item_key]['preview_url'] = $preview_url;
                        WC()->cart->cart_contents[$cart_item_key]['nbo_meta']['cart_image'] = $preview_url;
                        WC()->cart->set_session();
                        
                        error_log('Updated cart item preview URL start $product_id ' . $product_id);
                        error_log(print_r([
                            'cart_item_key' => $cart_item_key,
                            'preview_url' => isset(WC()->cart->cart_contents[$cart_item_key]['preview_url']) 
                                ? WC()->cart->cart_contents[$cart_item_key]['preview_url'] 
                                : 'not set',
                            'nbo_meta' => isset(WC()->cart->cart_contents[$cart_item_key]['nbo_meta']) 
                                ? WC()->cart->cart_contents[$cart_item_key]['nbo_meta'] 
                                : 'not set'
                        ], true));
                        error_log('Updated cart item preview URL end $product_id ' . $product_id);

                    }
                    error_log('nbdesigner render_cart 2 $show_edit_link ' . $show_edit_link);
                    if( $show_edit_link ){
                        $link_edit_design = add_query_arg(
                            array(
                                'task'          => 'edit',
                                'product_id'    => $product_id,
                                'nbd_item_key'  => $nbd_session,
                                'cik'           => $cart_item_key,
                                'view'          => $layout,
                                'rd'            => $redirect),
                            getUrlPageNBD('create'));
                        if( $product_permalink ){
                            $att_query = parse_url( $product_permalink, PHP_URL_QUERY );
                            $link_edit_design .= '&'.$att_query;
                        }    
                        if( $layout == 'v' ){
                            $link_edit_design = add_query_arg(
                                array(
                                    'nbdv-task'     => 'edit',
                                    'task'          => 'edit',
                                    'product_id'    => $product_id,
                                    'nbd_item_key'  => $nbd_session,
                                    'cik'           => $cart_item_key,
                                    'rd'            => $redirect),
                                $product_permalink );
                        }
                        if($cart_item['variation_id'] > 0){
                            $link_edit_design .= '&variation_id=' . $cart_item['variation_id'];
                        }
                        error_log('nbdesigner render_cart 3 link_edit_design: ' . $link_edit_design);
                        $html .= '<br /><a class="button nbd-edit-design" href="'.$link_edit_design.'">'. esc_html__('Edit design', 'web-to-print-online-designer') .'</a>';
                    }
                    $html .= '</div>';
                }else if( $is_nbdesign && !$_enable_upload_without_design && $show_edit_link ){
                    $id = 'nbd' . $cart_item_key; 
                    $redirect = is_cart() ? 'cart' : 'checkout';
                    $link_create_design = add_query_arg(
                        array(
                            'task'          => 'new',
                            'task2'         => 'update',
                            'product_id'    => $product_id,
                            'variation_id'  => $variation_id,
                            'cik'           => $cart_item_key,
                            'view'          => $layout,
                            'rd'            => $redirect),
                        getUrlPageNBD('create'));
                    if( $layout == 'v' ){
                        $link_create_design = add_query_arg(
                            array(
                                'nbdv-task'     => 'new',
                                'task'          => 'new',
                                'task2'         => 'update',
                                'product_id'    => $product_id,
                                'variation_id'  => $variation_id,
                                'cik'           => $cart_item_key,
                                'view'          => $layout,
                                'rd'            => $redirect),
                            $product_permalink );
                    }
                    if( $product_permalink ){
                        $att_query = parse_url( $product_permalink, PHP_URL_QUERY );
                        $link_create_design .= '&'.$att_query;
                    }
                    $html .= '<div class="nbd-cart-upload-file nbd-cart-item-add-design">';
                    $html .=    '<a class="button nbd-create-design" href="' . $link_create_design . '">'. esc_html__('Add design', 'web-to-print-online-designer') .'</a>';
                    $html .= '</div>';
                }
                if( isset( $nbu_session ) ){
                    $id             = 'nbu' . $cart_item_key; 
                    $redirect       = is_cart() ? 'cart' : 'checkout';
                    $html          .= '<div id="'.$id.'" class="nbd-cart-upload-file nbd-cart-item-upload-file">';
                    $remove_upload  = is_cart() ? '<a class="remove nbd-cart-item-remove-file" href="#" data-type="upload" data-cart-item="' . $cart_item_key . '">&times;</a>' : '';
                    $html          .= '<p>' . esc_html__('Upload file', 'web-to-print-online-designer') . $remove_upload . '</p>';
                    $files          = Nbdesigner_IO::get_list_files( NBDESIGNER_UPLOAD_DIR . '/' . $nbu_session );
                    $create_preview = nbdesigner_get_option('nbdesigner_create_preview_image_file_upload');
                    $upload_html    = '';
                    foreach ( $files as $file ) {
                        $ext        = pathinfo( $file, PATHINFO_EXTENSION );
                        $src        = Nbdesigner_IO::get_thumb_file( pathinfo( $file, PATHINFO_EXTENSION ), '');
                        $file_url   = Nbdesigner_IO::wp_convert_path_to_url( $file );
                        if(  $create_preview == 'yes' && ( $ext == 'png' || $ext == 'jpg' || $ext == 'pdf' ) ){
                            $dir        = pathinfo( $file, PATHINFO_DIRNAME );
                            $filename   = pathinfo( $file, PATHINFO_BASENAME );
                            if( file_exists($dir.'_preview/'.$filename) ){
                                $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename );
                            }else if( $ext == 'pdf' && file_exists($dir.'_preview/'.$filename.'.jpg' ) ){
                                $src = Nbdesigner_IO::wp_convert_path_to_url( $dir.'_preview/'.$filename.'.jpg' );
                            }else{
                                $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                            }
                        }else {
                            $src = Nbdesigner_IO::get_thumb_file( $ext, '' );
                        }
                        $upload_html .= '<div class="nbd-cart-item-upload-preview-wrap"><a target="_blank" href='.$file_url.'><img class="nbd-cart-item-upload-preview" src="' . $src . '"/></a><p class="nbd-cart-item-upload-preview-title">'. basename($file).'</p></div>';
                    }
                    $upload_html = apply_filters('nbu_cart_item_html', $upload_html, $cart_item, $nbu_session);
                    $html .= $upload_html;
                    if( $show_edit_link ){
                        $link_reup_design = add_query_arg(
                            array(
                                'task'          => 'reup',
                                'product_id'    => $product_id,
                                'nbu_item_key'  => $nbu_session,
                                'cik'           => $cart_item_key,
                                'rd'            => $redirect),
                            getUrlPageNBD('create'));
                        if($cart_item['variation_id'] > 0){
                            $link_reup_design .= '&variation_id=' . $cart_item['variation_id'];
                        }
                        $link_reup_design = apply_filters( 'nbu_cart_item_reup_link', $link_reup_design, $cart_item, $cart_item_key, $redirect );
                        $html .= '<br /><a class="button nbd-reup-design" href="' . $link_reup_design . '">'. esc_html__('Reupload design', 'web-to-print-online-designer') .'</a>';
                    }
                    $html .= '</div>';
                }else if( $_enable_upload && $show_edit_link ){
                    $id = 'nbd' . $cart_item_key;     
                    $redirect = is_cart() ? 'cart' : 'checkout';
                    $link_create_design = add_query_arg(
                        array(
                            'task'          => 'new',
                            'task2'         => 'add_file',
                            'product_id'    => $product_id,
                            'variation_id'  => $variation_id,
                            'cik'           => $cart_item_key,
                            'rd'            => $redirect),
                        getUrlPageNBD('create'));
                    $link_upload_design = apply_filters('nbu_cart_item_upload_link', $link_create_design, $cart_item, $cart_item_key, $redirect);
                    $html .= '<div class="nbd-cart-upload-file nbd-cart-item-upload-file">';
                    $html .=    '<a class="button nbd-upload-design" href="' . $link_upload_design . '">' . esc_html__('Upload design', 'web-to-print-online-designer') . '</a>';
                    $html .= '</div>';
                }
                $option = unserialize(get_post_meta($product_id, '_nbdesigner_option', true)); 
                if( isset($nbd_session) ) {
                    $path = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/config.json';
                    $config = nbd_get_data_from_json($path);
                    if( isset( $config->custom_dimension ) && isset( $config->custom_dimension->price ) ){
                        $nbd_variation_price = $config->custom_dimension->price;
                    }
                }
                if( ( ( isset( $nbd_variation_price ) && $nbd_variation_price != 0 ) || $option['extra_price'] ) && ! $option['request_quote'] ){
                    $decimals = wc_get_price_decimals();
                    $extra_price = $option['extra_price'] ? $option['extra_price'] : 0;
                    if( (isset($nbd_variation_price) && $nbd_variation_price != 0) ) {
                        $extra_price = $option['type_price'] == 1 ? wc_price($extra_price + $nbd_variation_price) : $extra_price . ' % + ' . wc_price($nbd_variation_price);
                    }else {
                        $extra_price = $option['type_price'] == 1 ? wc_price($extra_price) : $extra_price . '%';
                    }
                    $html .= '<p id="nbx'.$cart_item_key.'">' . esc_html__('Extra price for design','web-to-print-online-designer') . ' + ' .  $extra_price . '</p>';
                }
                error_log('nbdesigner render_cart return ------$nbd_session ' . $nbd_session . ' $cart_item_key ' . $cart_item_key);
                return $html;
            } else {
                return $title;
            }
        }else{
            return $title;
        }
    }
    /**
     * Set session cart if manual add to cart <br />
     * Store last cart_item_key into session if add to cart before start design
     * @since 1.7.0
     * 
     * @param text $cart_item_key 
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     */
    public function set_nbd_session_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
        $product_id                 = get_wpml_original_id( $product_id );
        $recent_variation_id        = $variation_id;
        $variation_id               = get_wpml_original_id( $variation_id );
        $nbd_item_cart_key          = ($variation_id > 0) ? $product_id . '_' . $variation_id : $product_id; 
        $recent_nbd_item_cart_key   = ($recent_variation_id > 0) ? $product_id . '_' . $recent_variation_id : $product_id;
        error_log('nbdesigner set_nbd_session_cart ------ recent_nbd_item_cart_key: ' . $recent_nbd_item_cart_key . ' nbd_item_cart_key: ' . $nbd_item_cart_key);
        $e = new Exception();
        error_log('NBDesigner Call Trace -----');
        error_log('Stack trace: ' . $e->getTraceAsString());
        error_log('---------------------');
        /* add to cart in custom design page */
        if( is_nbdesigner_product( $product_id ) ){
            WC()->session->set( 'nbd_last_item_cart', $cart_item_key );
        }
        /* custom design */
        $nbd_session        = WC()->session->get( 'nbd_item_key_'.$nbd_item_cart_key );
        $recent_nbd_session = WC()->session->get( 'nbd_item_key_'.$recent_nbd_item_cart_key );
        error_log('nbdesigner set_nbd_session_cart ------ recent_nbd_session: ' . $recent_nbd_session . ' nbd_session: ' . $nbd_session);
        if( $recent_nbd_session ){
            $nbd_session = $recent_nbd_session;
        }
        $_enable_upload_without_design = get_post_meta( $product_id, '_nbdesigner_enable_upload_without_design', true );
        if( isset( $nbd_session ) && $nbd_session != '' ){
            WC()->session->set( $cart_item_key. '_nbd', $nbd_session );
            WC()->session->__unset( 'nbd_item_key_' . $nbd_item_cart_key );
            if( $_enable_upload_without_design ) WC()->session->__unset( $cart_item_key . '_nbd' );
        }else{
//            $nbd_session2 = WC()->session->get('nbd_item_key_'.$product_id . '_0');
//            if(isset($nbd_session2) && $nbd_session2 != ''){
//                if( !$_enable_upload_without_design ){
//                    WC()->session->set($cart_item_key. '_nbd', $nbd_session2);
//                    WC()->session->__unset('nbd_item_key_'.$product_id . '_0');
//                }
//            }
        }
        /* up design */
        $nbu_session = WC()->session->get( 'nbu_item_key_' . $nbd_item_cart_key );
        if( isset( $nbu_session ) && $nbu_session != '' ){
            WC()->session->set( $cart_item_key. '_nbu', $nbu_session );
            WC()->session->__unset( 'nbu_item_key_' . $nbd_item_cart_key );

            $_enable_upload = get_post_meta( $product_id, '_nbdesigner_enable_upload', true );
            if( !$_enable_upload ) WC()->session->__unset( $cart_item_key . '_nbu' );
        }
        /* order again */
        if( isset( $cart_item_data['_nbd'] ) ){
            WC()->session->set( $cart_item_key . '_nbd', $cart_item_data['_nbd'] );
        }
        if( isset( $cart_item_data['_nbu'] ) ){
            WC()->session->set( $cart_item_key . '_nbu', $cart_item_data['_nbu'] );
        }
    }
    public function nbdesigner_remove_cart_item_design( $removed_cart_item_key, $instance ){
        //$line_item = $instance->removed_cart_contents[ $removed_cart_item_key ];
        /* custom design */
        $nbd_session = WC()->session->get( $removed_cart_item_key . '_nbd' );
        if($nbd_session){  
            WC()->session->__unset( $removed_cart_item_key . '_nbd' );
        }  
        /* up design */
        $nbu_session = WC()->session->get( $removed_cart_item_key . '_nbu' );
        if($nbu_session){
            WC()->session->__unset( $removed_cart_item_key . '_nbu' );
        }
        /* remove extra price session */
        if( WC()->session->__isset( $removed_cart_item_key . '_nbd_initial_price' ) ){
            WC()->session->__unset( $removed_cart_item_key . '_nbd_initial_price' );
        }
    }
    public function order_line_item( $item, $cart_item_key, $values ){
        if ( isset( $values['nbd_item_meta_ds'] ) ) {
            if ( isset( $values['nbd_item_meta_ds']['nbd'] ) ) $item->add_meta_data( '_nbd', $values['nbd_item_meta_ds']['nbd'] );
            if ( isset( $values['nbd_item_meta_ds']['nbu'] ) ) $item->add_meta_data( '_nbu', $values['nbd_item_meta_ds']['nbu'] );
        }
        if ( isset( $values['nbd_design_id'] ) ) {
            $item->add_meta_data( '_nbd_design_id', $values['nbd_design_id'] );
        }
    }
    public function nbdesigner_add_new_order_item($item_id, $item, $order_id){
        if ( isset( $item->legacy_cart_item_key ) ){
            if( is_null( WC()->session ) ) return;
            /* custom design */
            if ( WC()->session->__isset( $item->legacy_cart_item_key . '_nbd' ) || isset( $item->legacy_values["nbd_item_meta_ds"]["nbd"] ) ) {
                $nbd_session = WC()->session->get( $item->legacy_cart_item_key . '_nbd' );
                if( isset( $item->legacy_values["nbd_item_meta_ds"]["nbd"] ) ) $nbd_session = $item->legacy_values["nbd_item_meta_ds"]["nbd"];
                wc_add_order_item_meta( $item_id, "_nbd", $nbd_session );
            }
            /* up design */
            if ( WC()->session->__isset( $item->legacy_cart_item_key . '_nbu' ) || isset( $item->legacy_values["nbd_item_meta_ds"]["nbu"] ) ) {
                $nbu_session = WC()->session->get($item->legacy_cart_item_key . '_nbu');
                if( isset( $item->legacy_values["nbd_item_meta_ds"]["nbu"] ) ) $nbu_session = $item->legacy_values["nbd_item_meta_ds"]["nbu"];
                wc_add_order_item_meta( $item_id, "_nbu", $nbu_session );
            }
            if( WC()->session->__isset( $item->legacy_cart_item_key . '_nbd_initial_price' ) || isset( $item->legacy_values["nbd_item_meta_ds"]["initial_price"] ) ){
                $product_id = $item->legacy_values['product_id'];
                $product_id = get_wpml_original_id( $product_id );
                $option     = unserialize( get_post_meta( $product_id, '_nbdesigner_option', true ) );
                if( isset( $nbd_session ) ) {
                    $path   = NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_session . '/config.json';
                    $config = nbd_get_data_from_json( $path );
                    if( isset( $config->custom_dimension ) && isset( $config->custom_dimension->price ) ){
                        $nbd_variation_price = $config->custom_dimension->price;
                    }
                }
                if( ( ( isset( $nbd_variation_price ) && $nbd_variation_price != 0 ) || $option['extra_price'] ) && ! $option['request_quote'] ){
                    $extra_price = $option['extra_price'] ? $option['extra_price'] : 0;
                    if( ( isset( $nbd_variation_price ) && $nbd_variation_price != 0 ) ) {
                        $extra_price = $option['type_price'] == 1 ? wc_price( $extra_price + $nbd_variation_price ) : $extra_price . '% + ' . wc_price( $nbd_variation_price );
                    }else {
                        $extra_price = wc_price( $extra_price );
                    }
                    wc_add_order_item_meta( $item_id, "_nbd_extra_price", $extra_price );
                }
            }
        }
    }
    public function nbdesigner_add_order_design_data( $item_id, $values, $cart_item_key ) {
        /* custom design */
        if (WC()->session->__isset($cart_item_key . '_nbd')) {
            wc_add_order_item_meta($item_id, "_nbd", WC()->session->get($cart_item_key . '_nbd'));
        }
        /* up design */
        if (WC()->session->__isset($cart_item_key . '_nbu')) {
            wc_add_order_item_meta($item_id, "_nbu", WC()->session->get($cart_item_key . '_nbu'));
        }
    }
    public function nbdesigner_design_approve(){
        check_admin_referer( 'approve-designs', '_nbdesigner_approve_nonce' );
        $order_id           = absint( $_POST['nbdesigner_design_order_id'] );
        $_design_action     = sanitize_text_field( $_POST['nbdesigner_order_file_approve'] );
        $response['mes']    = '';
        if (is_numeric( $order_id ) && ( isset($_POST['_nbdesigner_design_file'] ) && is_array( $_POST['_nbdesigner_design_file'] ) || isset( $_POST['_nbdesigner_upload_file'] ) && is_array( $_POST['_nbdesigner_upload_file'] ) ) ) {
            $has_design = $has_upload = $update_design = $update_upload = false;
            if( isset( $_POST['_nbdesigner_design_file'] ) ){
                $_design_file   = wc_clean( $_POST['_nbdesigner_design_file'] );
                $has_design     = true;
            }
            if( isset( $_POST['_nbdesigner_upload_file'] ) ){
                $_upload_file   = wc_clean( $_POST['_nbdesigner_upload_file'] );
                $has_upload     = true;
            }
            $design_data = unserialize( get_post_meta( $order_id, '_nbdesigner_design_file', true ) );
            $upload_data = unserialize( get_post_meta( $order_id, '_nbdesigner_upload_file', true ) );
            if( $has_design ){
                if( is_array( $design_data ) ){
                    foreach( $_design_file as $val ){
                        $check = false;
                        foreach( $design_data as $key => $status ){
                            $_key = str_replace( 'nbds_', '', trim( $key ) );
                            if($_key == $val){
                                $design_data[$key]  = $_design_action;
                                $check              = true;
                            }
                        }
                        if( !$check ) $design_data['nbds_'.$val] = $_design_action;
                    }
                }else{
                    $design_data = array();
                    foreach ( $_design_file as $val ){
                        $design_data['nbds_'.$val] = $_design_action;
                    }
                }
                $design_data    = serialize( $design_data );
                $update_design  = update_post_meta( $order_id, '_nbdesigner_design_file', $design_data );
            }
            if( $has_upload ){
                if( is_array( $upload_data ) ){
                    foreach( $_upload_file as $val ){
                        $check = false;
                        foreach( $upload_data as $key => $status ){
                            $_key = str_replace( 'nbds_', '', trim( $key ) );
                            if($_key == $val){
                                $upload_data[$key]  = $_design_action;
                                $check              = true;
                            }
                        }
                        if( !$check ) $upload_data['nbds_'.$val] = $_design_action;
                    }
                }else{
                    $upload_data = array();
                    foreach ( $_upload_file as $val ){
                        $upload_data['nbds_'.$val] = $_design_action;
                    }
                } 
                $upload_data    = serialize( $upload_data );
                $update_upload  = update_post_meta( $order_id, '_nbdesigner_upload_file', $upload_data );
            }
            if ( $update_design || $update_upload ){
                update_post_meta( $order_id, '_nbdesigner_order_changed', 0 );
                $response['mes'] = 'success';
            }else{
                update_post_meta( $order_id, '_nbdesigner_order_changed', 0 );
                $response['mes'] = esc_html__( 'You don\'t change anything? Or an error occured saving the data, please refresh this page and check if changes took place.', 'web-to-print-online-designer' );
            }
        } else {
            $response['mes'] = esc_html__( 'You haven\'t chosen a item.', 'web-to-print-online-designer' );
        }
        echo json_encode( $response );
        wp_die();
    }
    public function nbdesigner_design_order_email(){
        check_admin_referer( 'approve-design-email', '_nbdesigner_design_email_nonce' );
        $response['success'] = 0;
        if ( empty( $_POST['nbdesigner_design_email_order_content'] ) ) {
            $response['error'] = esc_html__( 'The reason cannot be empty', 'web-to-print-online-designer' );
        } elseif ( !is_numeric( $_POST['nbdesigner_design_email_order_id'] ) ) {
            $response['error'] = esc_html__( 'Error while sending mail', 'web-to-print-online-designer' );
        }
        if ( empty( $response['error'] ) ) {
            $message    = sanitize_text_field( $_POST['nbdesigner_design_email_order_content'] );
            $order      = new WC_Order( $_POST['nbdesigner_design_email_order_id'] );
            $reason     = ( $_POST['nbdesigner_design_email_reason'] == 'approved' ) ? esc_html__( 'Your design accepted', 'web-to-print-online-designer' ) : esc_html__( 'Your design rejected', 'web-to-print-online-designer' );
            $send_email = $this->nbdesigner_send_email( $order, $reason, $message );
            if ( $send_email )
                $response['success'] = 1;
            else
                $response['error'] = esc_html__( 'Error while sending mail', 'web-to-print-online-designer' );
        }
        echo json_encode( $response );
        wp_die();
    }
    public function nbdesigner_send_email( $order, $reason, $message ){
        global $woocommerce;
        $user_email = $order->get_billing_email();
        if ( !empty( $user_email ) ) {
            $mailer = $woocommerce->mailer();
            ob_start();
            wc_get_template('emails/nbdesigner-approve-order-design.php', array(
                'plugin_id'     => 'nbdesigner',
                'order'         => $order,
                'reason'        => $reason,
                'message'       => $message,
                'my_order_url'  => $order->get_view_order_url(),
            ));
            $body       = ob_get_clean();
            $subject    = $reason . esc_html__(' - Order ','web-to-print-online-designer') . $order->get_order_number();
            $mailer->send($user_email, $subject, $body);
            
            //Send mail to admin
            $notifications = get_option('nbdesigner_enable_send_mail_when_approve', false);
            if( $notifications == 'yes' && $reason == 'Your design accepted' ){
                ob_start();
                wc_get_template('emails/notify-admin-when-approve-design.php', array(
                    'order_number'  => $order->get_order_number(),
                    'order_id'      => $order->get_id()
                )); 
                $subject = esc_html__('Approve designs - Order ','web-to-print-online-designer') . $order->get_order_number();
                $owner_email = get_option('nbdesigner_admin_emails', false);
                $emails = new WC_Emails();
                $woo_recipient = $emails->emails['WC_Email_New_Order']->recipient;
                if($owner_email == ''){
                    if(!empty($woo_recipient)) {
                        $user_email = esc_attr($woo_recipient);
                    } else {
                        $user_email = get_option( 'admin_email' );
                    }
                }else{
                    $user_email = $owner_email;
                }
                $body       = ob_get_clean();
                 
                $products   = $order->get_items();
                $attachment = array();
                if( get_option('nbdesigner_attachment_admin_email', false) == 'yes' ){
                    foreach($products AS $order_item_id => $product){
                        $nbd_item_key = wc_get_order_item_meta($order_item_id, '_nbd');
                        if( $nbd_item_key ){
                            $list_images = Nbdesigner_IO::get_list_images(NBDESIGNER_CUSTOMER_DIR .'/'. $nbd_item_key, 1);
                            foreach ($list_images as $image) {
                                $attachment[] = $image;
                            }
                        }
                    }
                }
                if (!empty($user_email)) {
                    $mailer->send($user_email, $subject, $body, '', $attachment);
                }
            }
            return true;
        } else {
            return false;
        }
    }
    public function update_order_item_meta_data( $order_id ){
        $order          = new WC_Order( $order_id );
        $order_items    = $order->get_items();
        foreach( $order_items AS $order_item_id => $order_item ){
            $nbd_item_key = wc_get_order_item_meta( $order_item_id, '_nbd' );
            if( $nbd_item_key ){
                $watermark  = false;
                $showBleed  = false;
                $force      = true;
                $files      = nbd_export_pdfs( $nbd_item_key, $watermark, $force, $showBleed );
                $urls       = '';
                foreach( $files as $key => $file ){
                    $urls .= ($key > 0 ? '|' : '') . Nbdesigner_IO::wp_convert_path_to_url( $file );
                };
                wc_update_order_item_meta( $order_item_id, '_pdfs', $urls );
            }
            $nbu_item_key = wc_get_order_item_meta( $order_item_id, '_nbu' );
            if( $nbu_item_key ){
                $files = Nbdesigner_IO::get_list_files( NBDESIGNER_UPLOAD_DIR . '/' . $nbu_item_key );
                foreach( $files as $key => $file ){
                    $urls .= ($key > 0 ? '|' : '') . Nbdesigner_IO::wp_convert_path_to_url( $file );
                };
                wc_update_order_item_meta( $order_item_id, '_nbd_upload_files', $urls );
            }
        }
    }
    public function attach_design_to_admin_email2( $attachments, $type, $object ){
        if( 'new_order' === $type ){
            $products   = $object->get_items();
            $has_design = false;
            foreach( $products AS $order_item_id => $product ){
                $nbd_item_key = wc_get_order_item_meta( $order_item_id, '_nbd' );
                if( $nbd_item_key ){
                    $watermark  = false;
                    $showBleed  = true;
                    $force      = true;
                    $files      = nbd_export_pdfs( $nbd_item_key, $watermark, $force, $showBleed );
                    foreach( $files as $key => $file ){
                        $zip_files[] = $file;
                    }
                    $has_design = true;
                }
            }
            if( $has_design ){
                $pathZip        = NBDESIGNER_DATA_DIR . '/download/customer-design-' . time() . '.zip';
                nbd_zip_files_and_download( $zip_files, $pathZip, 'Canvas.zip', array(), false );
                $attachments[]  = $pathZip;
            }
        }
        return $attachments;
    }
    public function attach_design_to_admin_email( $attachments, $type, $object ){
        //if( 'new_order' === $type || $type == 'customer_completed_order' ){
        if( 'new_order' === $type ){
            $products = $object->get_items();
            foreach( $products AS $order_item_id => $product ){
                $nbd_item_key = wc_get_order_item_meta( $order_item_id, '_nbd' );
                if( $nbd_item_key ){
                    $list_images = array();
                    if( nbdesigner_get_option( 'nbdesigner_attach_design_svg', 0 ) == 1 ){
                        $list_images = Nbdesigner_IO::get_list_files_by_type( NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key, 1, 'svg' );
                    }
                    if( nbdesigner_get_option( 'nbdesigner_attach_design_png', 1 ) == 1 ){
                        $list_images = array_merge( Nbdesigner_IO::get_list_images( NBDESIGNER_CUSTOMER_DIR . '/' . $nbd_item_key, 1 ), $list_images );
                    }
                    asort( $list_images );
                    foreach ( $list_images as $image ) {
                        $attachments[] = $image;
                    }
                }
            }
        }
        return $attachments;
    }
    public function nbdesigner_locate_plugin_template( $template, $template_name, $template_path ){
        global $woocommerce;
        $_template = $template;
        if ( ! $template_path ) $template_path = $woocommerce->template_url;
        $plugin_path  = NBDESIGNER_PLUGIN_DIR . 'templates/';
        $template = locate_template(array(
            $template_path . $template_name,
            $template_name
        ));
        if ( ! $template && file_exists( $plugin_path . $template_name ) )
            $template = $plugin_path . $template_name;
        if ( ! $template )
          $template = $_template;
        return $template;
    }
    public function nbdesigner_order_item_quantity_html( $strong, $item ){
        $show_design_in_order = nbdesigner_get_option( 'nbdesigner_show_in_order', 'yes' );
        if( $show_design_in_order == 'yes' ){
            if( isset( $item["item_meta"]["_nbd"] ) || isset( $item["item_meta"]["_nbu"] ) ) return '';
        }
        return ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>';
    }
    public function nbd_checkout_cart_item_quantity( $quantity_html, $cart_item, $cart_item_key ){
        $show_design_in_cart = nbdesigner_get_option( 'nbdesigner_show_in_cart' );
        if( $show_design_in_cart == 'yes' ){
            if( WC()->session->__isset( $cart_item_key . '_nbd' ) || WC()->session->__isset( $cart_item_key . '_nbu' ) ) return '';
        }
        return $quantity_html;
    }
    public function nbdesigner_hidden_custom_order_item_metada( $order_items ){
        $order_items[] = '_nbdesigner_has_design';
        $order_items[] = '_nbdesigner_folder_design';
        $order_items[] = '_nbdesigner_version_design';
        $order_items[] = '_nbdesign_order';
        $order_items[] = '_nbdesign_order_item_id';
        $order_items[] = '_nbd';
        $order_items[] = '_nbu';
        $order_items[] = '_nbd_extra_price';
        $order_items[] = '_pdfs';
        $order_items[] = '_nbd_synchronized';
        $order_items[] = '_nbd_upload_files';
        return $order_items;
    }
    public function nbd_upload_design_file(){
        if ( ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) || !isset( $_FILES ) ) {
            die( 'Security error' );
        }
        $result = array(
            'flag'  => 0,
            'mes'   => '',
            'src'   => ''
        );
        $product_id         = absint( $_POST['product_id'] );
        $variation_id       = absint( $_POST['variation_id'] );
        $task               = sanitize_text_field( $_POST['task'] );
        $first_time         = absint( $_POST['first_time'] );
        $nbd_item_cart_key  = ( $variation_id > 0 ) ? $product_id . '_' . $variation_id : $product_id;
        $upload_setting     = unserialize( get_post_meta( $product_id, '_nbdesigner_upload', true ) );
        if( $upload_setting['allow_type'] == '' ) $upload_setting['allow_type'] = nbdesigner_get_option( 'nbdesigner_allow_upload_file_type', '' );
        if( $upload_setting['disallow_type'] == '' ) $upload_setting['disallow_type'] = nbdesigner_get_option( 'nbdesigner_disallow_upload_file_type', '' );
        if( $upload_setting['maxsize'] == '' ) $upload_setting['maxsize'] = nbdesigner_get_option( 'nbdesigner_maxsize_upload_file', nbd_get_max_upload_default() );
        if( $upload_setting['minsize'] == '' ) $upload_setting['minsize'] = nbdesigner_get_option( 'nbdesigner_minsize_upload_file', 0 );
        $allow_ext          = explode( ',', preg_replace('/\s+/', '', strtolower( $upload_setting['allow_type'] ) ) );
        $disallow_ext       = explode( ',', preg_replace('/\s+/', '', strtolower( $upload_setting['disallow_type'] ) ) );
        $ext                = strtolower( $this->nbdesigner_get_extension( $_FILES['file']["name"] ) );
        $ext                = $ext == 'jpeg' ? 'jpg' : $ext;
        $max_size           = $upload_setting['maxsize'] * 1024 * 1024;
        $minsize            = $upload_setting['minsize'] * 1024 * 1024;
        $checkSize          = $checkExt = $checkDPI = false;
        if( $upload_setting['mindpi'] > 0 && ( $ext == 'jpg' || $ext == 'jpeg' ) ){
            $dpi = nbd_get_dpi( $_FILES['file']["tmp_name"] );
            if( $dpi['x'] < $upload_setting['mindpi'] ) $checkDPI = true;
        }
        if (( count( $allow_ext ) >0
               && $allow_ext[0] != '' 
               && !in_array( strtolower( $ext ), $allow_ext ) 
               ) || 
            ( count( $disallow_ext ) >0
              && $disallow_ext[0] != '' 
              && in_array( strtolower( $ext ), $disallow_ext ) 
              )) {
            $checkExt = true;
        }
        if( $minsize > $_FILES['file']["size"] || ( $max_size != 0 && $max_size < $_FILES['file']["size"] ) ){
            $checkSize = true;
        }
        if( $checkSize || $checkExt || $checkDPI ){
            if( $checkSize ) $result['mes'] = esc_html__( 'File size too small or large!', 'web-to-print-online-designer' );
            if( $checkExt ) $result['mes']  = esc_html__( 'Extension not allowed!', 'web-to-print-online-designer' );
            if( $checkDPI ) $result['mes']  = esc_html__( 'Min DPI required for JPG', 'web-to-print-online-designer' ) . ' ' . $upload_setting['mindpi'];
        } else {
            if( isset( $_POST['nbu_item_key'] ) && $_POST['nbu_item_key'] != ''){
                /* reup */
                $nbu_item_key = sanitize_text_field( $_POST['nbu_item_key'] );
            } else {
                $nbu_item_session   = WC()->session->get( 'nbu_item_key_' . $nbd_item_cart_key );
                $nbu_item_key       = isset( $nbu_item_session ) ? $nbu_item_session : substr( md5( uniqid() ), 0, 5 ) . rand( 1, 100 ) . time();
            }
            $path_dir   = NBDESIGNER_UPLOAD_DIR . '/' . $nbu_item_key; 
            $new_name   = time() . '_' . sanitize_file_name( $_FILES['file']["name"] );
            $new_name   = nbd_alias( $new_name );
            $path       = $path_dir . '/' . $new_name;
            if ( !file_exists( $path_dir ) ) wp_mkdir_p( $path_dir );
            $image_exts = array(
                0   => 'jpg',
                1   => 'jpeg',
                2   => 'png'
            );
            if( $task == 'reup' && $first_time == 1 ){
                if( file_exists( $path_dir.'_old' ) ) Nbdesigner_IO::delete_folder( $path_dir . '_old' );
                if( file_exists( $path_dir ) ){
                    rename( $path_dir, $path_dir.'_old' );
                    wp_mkdir_p( $path_dir );
                }
                //maybe implement other method
            }
            if( move_uploaded_file( $_FILES['file']["tmp_name"], $path ) ){
                $result['mes'] = esc_html__( 'Upload success !', 'web-to-print-online-designer' );
                /** Allow create preview for images **/
                if( nbdesigner_get_option( 'nbdesigner_create_preview_image_file_upload' ) == 'yes' && !isset( $_POST['nbu_adu'] ) ){
                    $path_preview = $path_dir . '_preview/';
                    $preview_file = Nbdesigner_IO::wp_convert_path_to_url( $path_preview . $new_name );
                    if( !file_exists( $path_preview ) ){
                        wp_mkdir_p( $path_preview );
                    }
                    $preview_width = nbdesigner_get_option( 'nbdesigner_file_upload_preview_width' );
                    if( $ext == 'png' ){
                        NBD_Image::nbdesigner_resize_imagepng( $path, $preview_width, $preview_width, $path_preview . $new_name );
                    }else if( $ext == 'jpg' ){
                        NBD_Image::nbdesigner_resize_imagejpg( $path, $preview_width, $preview_width, $path_preview . $new_name );
                    }else if( $ext == 'svg' ){
                        error_log('nbdesigner nbd_upload_design_file svg');
                        NBD_Image::nbdesigner_resize_imagesvg( $path, $preview_width, $preview_width, $path_preview . $new_name );
                    }else if( is_available_imagick() && $ext == 'pdf' ){
                        $jpg_preview = $path_preview . $new_name . '.jpg';
                        NBD_Image::imagick_convert_pdf_to_jpg( $path, $jpg_preview );
                        if( file_exists( $jpg_preview ) ){
                            $preview_file = Nbdesigner_IO::wp_convert_path_to_url( $jpg_preview );
                        }else{
                            $preview_file = Nbdesigner_IO::get_thumb_file( $ext, $path );
                        }
                    } else{
                        $preview_file = Nbdesigner_IO::get_thumb_file( $ext, $path );
                    }
                    $src = $preview_file;
                }else{
                    $src = Nbdesigner_IO::get_thumb_file( $ext, $path );
                }
                $result['src']  = $src;
                $result['name'] = $new_name;
                $result['flag'] = 1;
                if( $task == 'new' && $first_time == 1 ) WC()->session->set( 'nbu_item_key_' . $nbd_item_cart_key, $nbu_item_key );
            }else{
                $result['mes'] = esc_html__( 'Error occurred with file upload!', 'web-to-print-online-designer' );
            }
        }
        do_action( 'nbu_upload_design_file', $nbu_item_key, $path, $result );
        echo json_encode( $result );
        wp_die();
    }

    /**
     * 清空用户相册
     */
    public function nbdesigner_clear_customer_album() {
        error_log('nbdesigner nbdesigner_clear_customer_album');
        // 验证 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Security error', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }

        // 检查用户是否登录
        if (!is_user_logged_in()) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Please login to clear album', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }

        $user_id = get_current_user_id();
        $upload_dir = wp_upload_dir();
        $nbd_upload_dir = $upload_dir['basedir'] . '/nbdesigner/';
        $user_dir = $nbd_upload_dir . $user_id . '/';
        error_log('nbdesigner nbdesigner_clear_customer_album user_dir: ' . $user_dir);
        // 检查用户目录是否存在
        if (!file_exists($user_dir)) {
            $res['flag'] = 1;
            $res['mes'] = esc_html__('Album is already empty', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }

        // 递归删除用户目录下的所有文件
        $this->delete_directory($user_dir);

        $res['flag'] = 1;
        $res['mes'] = esc_html__('Album cleared successfully', 'web-to-print-online-designer');
        echo json_encode($res);
        wp_die();
    }

    /**
     * 递归删除目录
     */
    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }
    public function nbdesigner_get_customer_files() {
        error_log('nbdesigner nbdesigner_get_customer_files');
        // 验证 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Security error', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }
    
        // 检查用户是否登录
        if (!is_user_logged_in()) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Please login to view your files', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }
    
        $user_id = get_current_user_id();
        $upload_dir = wp_upload_dir();
        $nbd_upload_dir = $upload_dir['basedir'] . '/nbdesigner/';
        $user_dir = $nbd_upload_dir . $user_id . '/';
    
        // 获取用户上传的文件列表
        $files = array();
        $allowed_types = array('jpg', 'jpeg', 'png', 'svg');
        error_log('nbdesigner nbdesigner_get_customer_files user_dir: ' . $user_dir);

        if (file_exists($user_dir)) {
            // 遍历年份目录
            $year_dirs = scandir($user_dir);
            foreach ($year_dirs as $year) {
                if ($year === '.' || $year === '..' || !is_dir($user_dir . $year)) continue;
                
                // 遍历月份目录
                $month_dirs = scandir($user_dir . $year);
                foreach ($month_dirs as $month) {
                    if ($month === '.' || $month === '..' || !is_dir($user_dir . $year . '/' . $month)) continue;
                    
                    // 遍历日期目录
                    $day_dirs = scandir($user_dir . $year . '/' . $month);
                    foreach ($day_dirs as $day) {
                        if ($day === '.' || $day === '..' || !is_dir($user_dir . $year . '/' . $month . '/' . $day)) continue;
                        
                        // 遍历文件
                        $file_dir = $user_dir . $year . '/' . $month . '/' . $day . '/';
                        $dir_files = scandir($file_dir);
                        foreach ($dir_files as $file) {
                            if ($file === '.' || $file === '..') continue;
                            
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (!in_array($ext, $allowed_types)) continue;

                            $file_path = $file_dir . $file;
                            $file_url = $upload_dir['baseurl'] . '/nbdesigner/' . $user_id . '/' . $year . '/' . $month . '/' . $day . '/' . $file;
                            
                            $file_info = array(
                                'id' => md5($file),
                                'name' => $file,
                                'url' => $file_url,
                                'path' => $file_path,
                                'size' => filesize($file_path),
                                'type' => $ext,
                                'date' => filemtime($file_path),
                                'year' => $year,
                                'month' => $month,
                                'day' => $day
                            );

                            // 检查缩略图
                            $thumb_path = $file_dir . 'thumb_' . $file;
                            if (file_exists($thumb_path)) {
                                $file_info['thumb'] = $upload_dir['baseurl'] . '/nbdesigner/' . $user_id . '/' . $year . '/' . $month . '/' . $day . '/thumb_' . $file;
                            }

                            $files[] = $file_info;
                        }
                    }
                }
            }
        }
    
        // 按日期排序
        usort($files, function($a, $b) {
            return $b['date'] - $a['date'];
        });
    
        $res['flag'] = 1;
        $res['files'] = $files;
        
        echo json_encode($res);
        wp_die();
    }
    public function nbdesigner_delete_customer_files() {
        error_log('nbdesigner nbdesigner_delete_customer_files');
        // 验证 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Security error', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }

        // 检查用户是否登录
        if (!is_user_logged_in()) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Please login to delete files', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }

        // 检查是否提供了文件ID
        if (!isset($_POST['photo_id']) || empty($_POST['photo_id'])) {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('Photo ID is required', 'web-to-print-online-designer');
            echo json_encode($res);
            wp_die();
        }

        $user_id = get_current_user_id();
        $upload_dir = wp_upload_dir();
        $nbd_upload_dir = $upload_dir['basedir'] . '/nbdesigner/';
        $user_dir = $nbd_upload_dir . $user_id . '/';
        $photo_id = sanitize_text_field($_POST['photo_id']);
        error_log('nbdesigner nbdesigner_delete_customer_files photo_id: ' . $photo_id);
        // 遍历目录查找文件
        $file_found = false;
        if (file_exists($user_dir)) {
            $year_dirs = scandir($user_dir);
            foreach ($year_dirs as $year) {
                if ($year === '.' || $year === '..' || !is_dir($user_dir . $year)) continue;
                
                $month_dirs = scandir($user_dir . $year);
                foreach ($month_dirs as $month) {
                    if ($month === '.' || $month === '..' || !is_dir($user_dir . $year . '/' . $month)) continue;
                    
                    $day_dirs = scandir($user_dir . $year . '/' . $month);
                    foreach ($day_dirs as $day) {
                        if ($day === '.' || $day === '..' || !is_dir($user_dir . $year . '/' . $month . '/' . $day)) continue;
                        
                        $file_dir = $user_dir . $year . '/' . $month . '/' . $day . '/';
                        $dir_files = scandir($file_dir);
                        foreach ($dir_files as $file) {
                            if ($file === '.' || $file === '..') continue;
                            
                            // 检查文件ID是否匹配
                            if (md5($file) === $photo_id) {
                                $file_path = $file_dir . $file;
                                $thumb_path = $file_dir . 'thumb_' . $file;
                                
                                // 删除原文件
                                if (file_exists($file_path)) {
                                    unlink($file_path);
                                }
                                
                                // 删除缩略图（如果存在）
                                if (file_exists($thumb_path)) {
                                    unlink($thumb_path);
                                }
                                
                                $file_found = true;
                                break 4; // 跳出所有循环
                            }
                        }
                    }
                }
            }
        }

        if ($file_found) {
            $res['flag'] = 1;
            $res['mes'] = esc_html__('File deleted successfully', 'web-to-print-online-designer');
        } else {
            $res['flag'] = 0;
            $res['mes'] = esc_html__('File not found', 'web-to-print-online-designer');
        }
        
        echo json_encode($res);
        wp_die();
    }
    public function nbdesigner_customer_upload(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die('Security error');
        }

        if( !isset( $_FILES['file'] ) ){
            $res['flag']    = 0;
            $res['mes']     = esc_html__( 'Please select at least one image!', 'web-to-print-online-designer' );
            echo json_encode( $res );
            wp_die();
        }

        $allow_extension    = array( 'jpg', 'jpeg', 'png', 'svg' );
        $max_size           = nbdesigner_get_option( 'nbdesigner_maxsize_upload' );
        $min_dpi            = nbdesigner_get_option( 'nbdesigner_mindpi_upload', 0 );
        $allow_max_size     = $max_size * 1024 * 1024;
        $result             = true;
        $res                = array();
        $size               = $_FILES['file']["size"];
        $name               = $_FILES['file']["name"];
        $ext                = $this->nbdesigner_get_extension( $name );
        $new_name           = strtotime( "now" ) . substr( md5( rand( 1111, 9999 ) ), 0, 8 ) . '.' . $ext;
        error_log('nbdesigner nbdesigner_customer_upload new_name: ' . $new_name);
        if( is_available_imagick() || nbdesigner_get_option( 'nbdesigner_enable_pdf2img_cloud2print_api', 'no' ) == 'yes' ){
            $allow_extension    = array( 'jpg', 'jpeg', 'png', 'svg', 'pdf' );
        }

        if( empty( $name ) ) {
            $result         = false;
            $res['mes']     = esc_html__( 'Error occurred with file upload!', 'web-to-print-online-designer' );
        }

        if( $size > $allow_max_size ){
            $result         = false;
            $res['mes']     = esc_html__( 'Too large file!', 'web-to-print-online-designer' );
        }

        $check = Nbdesigner_IO::checkFileType( $name, $allow_extension );
        if( !$check ){
            $result         = false;
            $res['mes']     = esc_html__( 'Invalid file format!', 'web-to-print-online-designer' );
        }

        if( ( $ext != 'svg' && $ext != 'pdf' ) && $min_dpi && $min_dpi > 0 ) {
            $dpi = nbd_get_dpi( $_FILES['file']["tmp_name"] );
            if( $dpi['x'] < $min_dpi ) {
                $result     = nbdesigner_get_option( 'nbdesigner_enable_low_resolution_image', 'no' ) == 'yes' ? true : false;
                $res['mes'] = esc_html__( 'Image resolution too low!', 'web-to-print-online-designer' );
                $res['ilr'] = 1;
            }
        }
        $user_id = get_current_user_id();
        $upload_dir = wp_upload_dir();
        $nbd_upload_dir = $upload_dir['basedir'] . '/nbdesigner/';
        $user_dir = $nbd_upload_dir . $user_id . '/';
        error_log('nbdesigner nbdesigner_customer_upload user_dir: ' . $user_dir);
        //NBDESIGNER_TEMP_DIR
        $path = Nbdesigner_IO::create_file_path( $user_dir, $new_name );
        error_log('nbdesigner nbdesigner_customer_upload path: ' . $path['full_path']);
        if( $result ){
            if( move_uploaded_file( $_FILES['file']["tmp_name"], $path['full_path'] ) ){
                $res['mes'] = esc_html__( 'Upload success !', 'web-to-print-online-designer' );
            }else{
                $result     = false;
                $res['mes'] = esc_html__( 'Error occurred with file upload!', 'web-to-print-online-designer' );
            }
        }

        if( $result ){
            $res['src']     = NBDESIGNER_TEMP_URL . $path['date_path'];
            $res['flag']    = 1;

            if( $ext == 'pdf' ){
                $_dpi       = 72;
                $new_name   = str_replace( ".pdf", ".png", $new_name );

                if( is_available_imagick() && nbdesigner_get_option( 'nbdesigner_enable_pdf2img_cloud2print_api', 'no' ) == 'no' ){
                    try {
                        $im = new Imagick();
                        $im->setResolution( $_dpi, $_dpi );
                        $im->readImage( $path['full_path'] . '[0]' );
                        $im->setImageFormat( 'png32' );
                        $im->setImageUnits( imagick::RESOLUTION_PIXELSPERINCH );
                        $im->setResolution( $_dpi, $_dpi );
                        $path['full_path']  = str_replace( ".pdf", ".png", $path['full_path'] );
                        $im->writeImage( $path['full_path'] );
                        $im->clear();
                        $im->destroy();
                        $ext                    = 'png';
                        $res['origin_pdf']      = $path['date_path'];
                        $res['src']             = str_replace( ".pdf", ".png", $res['src'] );
                    } catch( Exception $e ) {
                        $res['flag']    = 0;
                        $res['mes']     = esc_html__( 'Error occurred when convert pdf!', 'web-to-print-online-designer' );
                    }
                } else {
                    NBD_Image::cloud_pdf2image( $path['full_path'], $_dpi, 'png' );
                    $img_path   = str_replace( ".pdf", "_1.png", $path['full_path'] );
                    if( file_exists( $img_path ) ){
                        $path['full_path']      = str_replace( ".pdf", ".png", $path['full_path'] );
                        $ext                    = 'png';
                        $res['origin_pdf']      = $path['date_path'];
                        $res['src']             = str_replace( ".pdf", ".png", $res['src'] );
                        copy( $img_path, $path['full_path'] );
                    }else{
                        $res['flag']    = 0;
                        $res['mes']     = esc_html__( 'Error occurred when convert pdf!!', 'web-to-print-online-designer' );
                    }
                }
            }

            if( ( $ext == 'jpg' || $ext == 'jpeg' ) && ( extension_loaded( 'exif' ) && function_exists( 'exif_read_data' ) ) ){
                $exif = @exif_read_data( $path['full_path'] );

                if( $exif && isset( $exif['Orientation'] ) ) {
                    $orientation = $exif['Orientation'];
                    if( $orientation != 1 ){
                        NBD_Image::strip_exif_orientation( $path['full_path'], $orientation );
                    }
                }
            }

            if( nbdesigner_get_option( 'nbdesigner_enable_generate_photo_thumb', 'no' ) == 'yes' ){
                $resizable_extensions = array( 'jpg', 'jpeg', 'png' );
                if( in_array( $ext, $resizable_extensions ) ){
                    $preview_size               = apply_filters( 'nbd_max_photo_thumb_size', 800 );
                    list( $width, $height )     = getimagesize( $path['full_path'] );
                    if( $width > $preview_size || $height > $preview_size ){
                        $infos          = pathinfo( $path['full_path'] );
                        $path_preview   = $infos['dirname'] . '/' . $infos['filename'] . '_preview.' . $infos['extension'];
                        if( $ext == 'png' ){
                            NBD_Image::nbdesigner_resize_imagepng( $path['full_path'], $preview_size, $preview_size, $path_preview );
                        } else if( $ext == 'svg' ) {
                            error_log('nbdesigner nbd_upload_design_file svg');
                            NBD_Image::nbdesigner_resize_imagesvg( $path['full_path'], $preview_size, $preview_size, $path_preview );
                        } else {
                            NBD_Image::nbdesigner_resize_imagejpg( $path['full_path'], $preview_size, $preview_size, $path_preview );
                        }

                        if( file_exists( $path_preview ) ){
                            $res['origin_url']  = $res['src'];
                            $res['src']         = Nbdesigner_IO::wp_convert_path_to_url( $path_preview );
                            $res['width']       = $width;
                            $res['height']      = $height;
                        }
                    }
                }
            }
        } else {
            $res['flag'] = 0;
        }
        echo json_encode( $res );
        wp_die();
    }
    public function nbdesigner_get_qrcode(){
        $result         = array();
        $result['flag'] = 0;
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        } 
        if( isset( $_REQUEST['data'] ) ){
            $content    = sanitize_text_field( $_REQUEST['data'] );
            include_once NBDESIGNER_PLUGIN_DIR . 'includes/class-qrcode.php';
            $qr         = new Nbdesigner_Qrcode();
            $qr->setText( $content );
            $image      = $qr->getImage( 500 );
            $file_name  = strtotime( "now" ) . '.png';
            $full_name  = NBDESIGNER_DATA_DIR . '/temp/' . $file_name;
            if( Nbdesigner_IO::save_data_to_file( $full_name, $image ) ){
                $result['flag'] = 1;
                $result['src']  =  content_url() . '/uploads/nbdesigner/temp/' . $file_name;
            }
        }
        echo json_encode( $result );
        wp_die();
    }
    public function nbdesigner_get_facebook_photo(){
        if (!wp_verify_nonce($_POST['nonce'], 'save-design') && NBDESIGNER_ENABLE_NONCE) {
            die('Security error');
        }
        $result         = array();
        $_accessToken   = $_POST['accessToken'];
        require_once NBDESIGNER_PLUGIN_DIR.'includes/class.nbdesigner.facebook.php';
        echo json_encode( $result );
        wp_die();
    }
    public function _nbdesigner_get_art(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'nbdesigner-get-data' ) && NBDESIGNER_ENABLE_NONCE ) {
            die('Security error');
        }   
        $result = array();
        $path   = NBDESIGNER_DATA_DIR . '/cliparts';
        $cats   = Nbdesigner_IO::get_list_folder( $path, 1 );
        foreach ( $cats as $key => $cat ){
            $result['cat'][] = array(
                'name'  => basename( $cat ),
                'id'    => $key
            );
            $list = Nbdesigner_IO::get_list_files( $path . '/' . basename( $cat ), 1 );
            $arts = preg_grep( '/\.(svg)(?:[\?\#].*)?$/i', $list );
            foreach( $arts as $k => $art ) {
                $result['arts'][] = array(
                    'name'  => basename( $art ),
                    'id'    => $k,
                    'cat'   => array( $key ),
                    'file'  => '',
                    'url'   => Nbdesigner_IO::wp_convert_path_to_url( $art )
                );
            }
        }
        $result['flag'] = 1;
  
        echo json_encode( $result );
        wp_die();
    }
    public function nbdesigner_get_art(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'nbdesigner-get-data' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        $result         = array();
        $path_cat       = NBDESIGNER_DATA_DIR . '/art_cat.json';
        $path_art       = NBDESIGNER_DATA_DIR . '/arts.json';
        $result['flag'] = 1;
        $result['cat']  = $this->nbdesigner_read_json_setting( $path_cat );
        $result['arts'] = $this->nbdesigner_read_json_setting( $path_art );
        echo json_encode( $result );
        wp_die();
    }
    public function _nbdesigner_get_font(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'nbdesigner-get-data' ) && NBDESIGNER_ENABLE_NONCE ) {
            die('Security error');
        }
        $result = array();
        $path   = NBDESIGNER_DATA_DIR . '/fonts';
        $cats   = Nbdesigner_IO::get_list_folder( $path, 1 );
        foreach ( $cats as $key => $cat ){
            $result['cat'][] = array(
                'name'  => basename( $cat ),
                'id'    => $key
            );
            $list = Nbdesigner_IO::get_list_files( $path . '/' . basename( $cat ), 1 );
            $arts = preg_grep( '/\.(ttf|woff)(?:[\?\#].*)?$/i', $list );
            foreach( $arts as $k => $art ) {
                $result['fonts'][] = array(
                    'name'  => pathinfo( $art, PATHINFO_FILENAME ),
                    'id'    => $k,
                    'cat'   => array( $key ),
                    'alias' => 'nbfont' . substr( md5( rand( 0, 999999 ) ), 0, 10),
                    'file'  => '',
                    'url'   => Nbdesigner_IO::wp_convert_path_to_url( $art )
                );
            }
        }
        $path_google_font       = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        $result['google_font']  = $this->nbdesigner_read_json_setting( $path_google_font );
        $result['flag']         = 1;
        echo json_encode( $result );
        wp_die();
    }    
    public function nbdesigner_get_font(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'nbdesigner-get-data' ) && NBDESIGNER_ENABLE_NONCE ) {
            die('Security error');
        }
        $result                 = array();
        $path_cat               = NBDESIGNER_DATA_DIR . '/font_cat.json';
        $path_font              = NBDESIGNER_DATA_DIR . '/fonts.json';
        $path_google_font       = NBDESIGNER_DATA_DIR . '/googlefonts.json';
        $result['flag']         = 1;
        $result['cat']          = $this->nbdesigner_read_json_setting( $path_cat );
        $result['fonts']        = $this->nbdesigner_read_json_setting( $path_font );
        $result['google_font']  = $this->nbdesigner_read_json_setting( $path_google_font );
        echo json_encode( $result );
        wp_die();
    }
    public function nbdesigner_get_pattern(){
        if ( !wp_verify_nonce( $_REQUEST['nonce'], 'nbdesigner-get-data' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        $result         = array();
        $path           = NBDESIGNER_PLUGIN_DIR . 'data/pattern.json';
        $result['flag'] = 1;
        $result['data'] = $this->nbdesigner_read_json_setting( $path );
        echo json_encode( $result );
        wp_die();
    }
    public function add_display_post_states( $post_states, $post ){
        if ( nbd_get_page_id( 'gallery' ) === $post->ID ) {
            $post_states['nbd_gallery_page'] = esc_html__( 'NBD Gallery Page', 'web-to-print-online-designer' );
        }
        if ( nbd_get_page_id( 'create_your_own' ) === $post->ID ) {
            $post_states['nbd_create_your_own_page'] = esc_html__( 'NBD Design Editor Page', 'web-to-print-online-designer' );
        }
        if ( nbd_get_page_id( 'designer' ) === $post->ID ) {
            $post_states['nbd_designer_page'] = esc_html__( 'NBD Designer gallery Page', 'web-to-print-online-designer' );
        }   
        if ( nbd_get_page_id( 'product_builder' ) === $post->ID ) {
            $post_states['nbd_product_builder_page'] = esc_html__( 'NBD Product builder Page', 'web-to-print-online-designer' );
        }
        if ( nbd_get_page_id( 'studio' ) === $post->ID ) {
            $post_states['nbd_studio_page'] = esc_html__( 'NBD Studio Page', 'web-to-print-online-designer' );
        }
        return $post_states;
    }
    public function nbdesigner_frontend_translate(){
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $languages  = wp_get_available_translations();
        $path       = NBDESIGNER_PLUGIN_DIR . 'data/language.json';
        $path_data  = NBDESIGNER_DATA_CONFIG_DIR . '/language.json';
        if( file_exists( $path_data ) ) $path = $path_data;
        $path_lang      = NBDESIGNER_PLUGIN_DIR . 'data/language/en_US.json';
        $path_data_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/en_US.json';
        if( file_exists( $path_data_lang ) ) $path_lang = $path_data_lang;
        $list = json_decode( file_get_contents( $path ) );
        $lang = json_decode( file_get_contents( $path_lang ) );
        if( is_array( $lang ) ){
            $langs = (array)$lang[0];
            asort( $langs );
        }
        require( NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-translate.php' );
    }
    public function nbdesigner_save_language(){
        $data = array(
            'mes'   => esc_html__('You do not have permission to edit language!', 'web-to-print-online-designer'),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || !current_user_can( 'edit_nbd_language' ) ) {
            echo json_encode( $data );
            wp_die();
        }
        if( isset( $_POST['langs'] ) ){
            $langs      = array();
            $langs[0]   = $_POST['langs'];
        }
        if( isset( $_POST['code'] ) ){
            $code       = $_POST['code'];
        } 
        if( isset( $langs ) && isset( $code ) ){
            $path_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/' . $code . '.json';
            $path_data = NBDESIGNER_DATA_CONFIG_DIR . '/language/' . $code . '.json';
            if( file_exists( $path_data ) ){
                $path_lang = $path_data;
            }else{
                if( $code = "en_US" ) {
                    $path_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language/en_US.json';
                }
            } 
            foreach ( $langs[0] as $key => $lang ){
                //$lang = preg_replace('#\\\\{2,}#','\\', $lang);
                //$lang = str_replace('\\\\','\\', $lang);
                $lang           = stripslashes( $lang );
                $langs[0][$key] = strip_tags( $lang );
            }
            $res            = json_encode( $langs );
            file_put_contents( $path_lang, $res );
            $data['mes']    = esc_html__( 'Update language success!', 'web-to-print-online-designer' );
            $data['flag']   = 1;
        }else{
            $data['mes'] = esc_html__( 'Update language failed!', 'web-to-print-online-designer' );
        }
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_get_language( $code ){
        if ( !( wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || wp_verify_nonce( $_POST['nonce'], 'save-design' ) ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        if( !isset( $code ) ){
            $code = "en";
        }else if( isset( $_POST['code'] ) ) {
            $code = $_POST['code'];
        }
        $data = nbd_get_language( $code );
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_delete_language(){
        $data = array(
            'mes'   =>  esc_html__( 'You do not have permission to delete language!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nonce'], 'nbdesigner_add_cat' ) || !current_user_can( 'edit_nbd_language' ) ) {
            echo json_encode( $data );
            wp_die();
        }
        $code                   = sanitize_text_field( $_POST['code'] );
        $index                  = absint( $_POST['index'] );
        $path_lang              = NBDESIGNER_DATA_CONFIG_DIR . '/language/' . $code . '.json';
        $path_data_cat_lang     = NBDESIGNER_DATA_CONFIG_DIR . '/language.json';
        $cats                   = json_decode(file_get_contents( $path_data_cat_lang ) );
        $primary_lang_code      = $cats[0]->code;
        $path_primary_lang      = NBDESIGNER_DATA_CONFIG_DIR . '/language/'.$primary_lang_code . '.json';
        if( !file_exists( $path_primary_lang ) ){
            $path_primary_lang = NBDESIGNER_PLUGIN_DIR . 'data/language/'.$primary_lang_code . '.json';
        }
        if( $index != 0 ){
            if( unlink( $path_lang ) ) {
                $data['flag'] = 1;
                $this->nbdesigner_delete_json_setting( $path_data_cat_lang, $index );
                $data['mes']    = esc_html__( 'Delete language success!', 'web-to-print-online-designer' );
                $langs          = json_decode( file_get_contents( $path_primary_lang ) ); 
                $data['langs']  = (array)$langs[0];
            }
        }else {
            $data['mes'] = esc_html__( 'Oops! Can not delete primary language!', 'web-to-print-online-designer' );
        }
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_create_language(){
        $data = array(
            'mes'   =>  esc_html__( 'You do not have permission to create language!', 'web-to-print-online-designer' ),
            'flag'  => 0
        );
        if ( !wp_verify_nonce( $_POST['nbdesigner_newlang_hidden'], 'nbdesigner-new-lang' ) || !current_user_can( 'edit_nbd_language' ) ) {
            echo json_encode( $data );
            wp_die();
        } 
        if( isset( $_POST['nbdesigner_codelang'] ) && isset( $_POST['nbdesigner_namelang'] ) ){
            $code                       = sanitize_text_field( $_POST['nbdesigner_codelang'] );
            $path_lang                  = NBDESIGNER_DATA_CONFIG_DIR . '/language/' . $code . '.json';
            $path_original_lang         = NBDESIGNER_PLUGIN_DIR . 'data/language/en_US.json';
            $path_original_data_lang    = NBDESIGNER_DATA_CONFIG_DIR . '/language/en_US.json';
            if( file_exists( $path_original_data_lang ) ) $path_original_lang = $path_original_data_lang;
            $path_cat_lang      = NBDESIGNER_PLUGIN_DIR . 'data/language.json';
            $path_data_cat_lang = NBDESIGNER_DATA_CONFIG_DIR . '/language.json';
            if( file_exists( $path_data_cat_lang ) ) $path_cat_lang = $path_data_cat_lang;
            $cats       = json_decode( file_get_contents( $path_cat_lang ) );
            $lang       = json_decode( file_get_contents( $path_original_lang ) );
            $_cat       = array();
            $_cat['id'] = 1;
            if( is_array( $cats ) ){
                foreach( $cats as $cat ){
                    if( $cat->code == $code ){
                        $code .=  rand( 1, 100 );
                    }
                    $_cat['id'] = $cat->id;
                }
                $_cat['id'] += 1;
            }else{
                $data['mes'] = 'error';
                echo json_encode( $data );
                wp_die();
            } 
            if( is_array( $lang ) ){
                $data['langs']  = (array)$lang[0];
                $data['code']   = $code;
                $_cat['code']   = $code;
                $data['name']   = sanitize_text_field( $_POST['nbdesigner_namelang'] );
                $_cat['name']   = $data['name'];
                if( !copy( $path_original_lang, $path_lang ) ) {
                    $data['mes'] = 'error';
                }else{
                    array_push( $cats, $_cat );
                    file_put_contents( $path_cat_lang, json_encode( $cats ) );
                    file_put_contents( $path_data_cat_lang, json_encode( $cats ) );
                }
                $data['mes']    = esc_html__( 'Your language has been created successfully!', 'web-to-print-online-designer' );
                $data['flag']   = 1;
            }else{
                $data['mes'] = 'error';
            }
        }
        echo json_encode( $data );
        wp_die();
    }
    public function nbdesigner_editor_html(){
        // error_log('[class.nbdesigner.php] nbdesigner_editor_html ------');
        // $stack = debug_backtrace();
        // foreach ($stack as $index => $trace) {
        //     error_log(sprintf(
        //         "#%d %s:%d - %s%s%s()",
        //         $index,
        //         isset($trace['file']) ? $trace['file'] : 'unknown file',
        //         isset($trace['line']) ? $trace['line'] : 0,
        //         isset($trace['class']) ? $trace['class'] : '',
        //         isset($trace['type']) ? $trace['type'] : '',
        //         $trace['function']
        //     ));
        // }
        // error_log('=== End Call Stack ===');
        if( isset( $_GET['nbd-route'] ) && $_REQUEST['nbd-route'] == 'nbd-3d-preview' ){
            $path = NBDESIGNER_PLUGIN_DIR . 'views/3d-preview.php';
            include( $path ); exit();
            return;
        }
        $task       = (isset($_GET['task']) && $_GET['task'] != '' ) ? $_GET['task'] : '';
        $task2      = (isset($_GET['task2']) && $_GET['task2'] != '' ) ? $_GET['task2'] : '';
        $product_id = (isset($_GET['product_id']) && absint($_GET['product_id']) != 0 ) ? $_GET['product_id'] : 0;
        if( $product_id == 0 ) return;
        $layout = nbd_get_product_layout( $product_id );
        $view   = (isset($_GET['view']) && $_GET['view'] != '' ) ? $_GET['view'] : $layout;
        error_log('[class.nbdesigner.php] nbdesigner_editor_html view: ' . $view);
        if( $view == 'm' || $view == 'v' ){
            $path = NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-frontend-modern.php';
        } else {
            $path = NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-frontend-template.php';
        }
        if($task == 'reup' || $task2 == 'add_file' || $view == 'c'){
            $path = NBDESIGNER_PLUGIN_DIR . 'views/nbdesigner-frontend-template.php';
        }
        if( is_nbd_design_page() ){
            include( $path ); exit();
        }else{
            if ( ( ! defined('DOING_AJAX') || ! DOING_AJAX ) && ( ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'nbdesigner_editor_html' ) ) return;
            include( $path ); exit();
        }
    }
    public function nbdesigner_make_primary_design(){
        if (!wp_verify_nonce($_POST['nonce'], 'nbdesigner_template_nonce') || !current_user_can('administrator')) {
            die('Security error');
        }
        $result = array();
        if(isset($_POST['id']) && isset($_POST['folder']) && isset($_POST['task'])){
            $pid    = absint( $_POST['id'] );
            $folder = sanitize_text_field( $_POST['folder'] );
            $task   = sanitize_text_field( $_POST['task'] );
            $check  = true;
            if($task == 'primary'){
                $path_primary = NBDESIGNER_DATA_DIR . '/admindesign/' . $pid . '/primary'; 
                $path_primary_old = NBDESIGNER_DATA_DIR . '/admindesign/' . $pid . '/primary_old'; 
                $path_primary_new = NBDESIGNER_DATA_DIR . '/admindesign/' . $pid . '/' .$folder; 
                if(!rename($path_primary, $path_primary_old)) $check = false; 
                if(!rename($path_primary_new, $path_primary)) $check = false; 
                if(!rename($path_primary_old, $path_primary_new)) $check = false;
            }
            if( $check ) $result['mes'] = 'success'; else $result['mes'] = 'error';
        }else{
            $result['mes'] = esc_html__('Invalid data', 'web-to-print-online-designer');
        }  
        echo json_encode($result);
        wp_die();
    }
    public function nbdesigner_load_admin_design(){
        if ( !wp_verify_nonce( $_POST['nonce'], 'save-design' ) && NBDESIGNER_ENABLE_NONCE ) {
            die( 'Security error' );
        }
        $result = array();
        if( isset( $_POST['product_id'] ) ){
            $product_id     = absint($_POST['product_id']);
            $variation_id   = absint($_POST['variation_id']);
            $list_design    = array();
            $templates      = nbd_get_templates( $product_id, $variation_id );
            foreach ( $templates as $tem ){
                $path_preview   = NBDESIGNER_CUSTOMER_DIR . '/' . $tem['folder'] . '/preview';
                $listThumb      = Nbdesigner_IO::get_list_images( $path_preview );
                $image          = '';
                if( count( $listThumb ) ){
                    $image = Nbdesigner_IO::wp_convert_path_to_url( end( $listThumb ) );
                    $list_design[] = array(
                        'id'    => $tem['folder'],
                        'src'   => $image
                    );
                }
            }
            $result['data']     = $list_design;
            $result['mes']      = 'success';
        }else{
            $result['mes'] = esc_html__( 'Invalid data', 'web-to-print-online-designer' );
        }
        echo json_encode($result);
        wp_die();
    }
    public function nbd_frontend_config(){
        $config = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('save-design'),
            'assets_url' => NBDESIGNER_ASSETS_URL,
            'plg_url' => NBDESIGNER_PLUGIN_URL,
            // ... 其他配置项
        );
        
        // 添加调试日志
        error_log('NBDesigner original config: ' . print_r($config, true));
        $filtered_config = apply_filters('nbdesigner_frontend_config', $config);
        error_log('NBDesigner filtered config: ' . print_r($filtered_config, true));
        
        return $filtered_config;
    }
}