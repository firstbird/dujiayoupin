<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('NBD_RESOURCE')){
    class NBD_RESOURCE {
        protected static $instance;
        public function __construct() {
            //todo something
        }
	public static function instance() {
            if ( is_null( self::$instance ) ) {
                    self::$instance = new self();
            }
            return self::$instance;
	}        
        public function init(){
            // 确保 AJAX 动作在所有情况下都被注册
            $this->ajax();
            
            // 添加调试日志
            error_log('NBD_RESOURCE init() called');
            error_log('AJAX actions registered for nbd_get_resource');
        }   
        public function ajax(){
            $ajax_events = array(
                'nbd_get_resource'    => true
            );
            foreach ($ajax_events as $ajax_event => $nopriv) {
                add_action('wp_ajax_' . $ajax_event, array($this, $ajax_event));
                if ($nopriv) {
                    add_action('wp_ajax_nopriv_' . $ajax_event, array($this, $ajax_event));
                }
                error_log("NBD_RESOURCE: Registered AJAX action 'wp_ajax_{$ajax_event}'");
                if ($nopriv) {
                    error_log("NBD_RESOURCE: Registered AJAX action 'wp_ajax_nopriv_{$ajax_event}'");
                }
            }
            error_log('NBD_RESOURCE: AJAX actions registration completed');
        }
        public function nbd_get_resource(){
            $flag = 1;
            $data = array();
            error_log('nbd_get_resource begin ---');
            error_log('REQUEST data: ' . print_r($_REQUEST, true));
            error_log('POST data: ' . print_r($_POST, true));
            error_log('GET data: ' . print_r($_GET, true));
            
            if (!wp_verify_nonce($_REQUEST['nonce'], 'nbdesigner-get-data') && NBDESIGNER_ENABLE_NONCE) {
                error_log('nbd_get_resource --- wp_verify_nonce failed');
                error_log('Nonce received: ' . $_REQUEST['nonce']);
                error_log('Nonce expected: ' . wp_create_nonce('nbdesigner-get-data'));
                $flag = 0;
            }else{     
                $rq_type = wc_clean( $_REQUEST['type'] );
                error_log('nbd_get_resource --- $rq_type:' . $rq_type);
                switch ($rq_type) {
                    case 'typography':
                        $path = $_REQUEST['task'] == 'typography' ? NBDESIGNER_PLUGIN_DIR . '/data/typography/typography.json' : NBDESIGNER_PLUGIN_DIR . '/data/typography/typo.json';
                        if(file_exists($path) ) {
                            $data = json_decode( file_get_contents($path) );
                            error_log('typography data loaded from: ' . $path);
                        } else {
                            error_log('typography file not found: ' . $path);
                        }
                        break;  
                    case 'get_typo':
                        $path = NBDESIGNER_PLUGIN_DIR . '/data/typography/local/'.$_REQUEST['folder'];
                        if (file_exists($path.'/used_font.json') && file_exists($path.'/design.json')) {
                            $data['font'] = json_decode( file_get_contents($path.'/used_font.json') );
                            $data['design'] = json_decode( file_get_contents($path.'/design.json') );
                            error_log('typo data loaded from: ' . $path);
                        } else {
                            error_log('Typography files not found: ' . $path);
                            $data = array('error' => 'Typography files not found');
                        }
                        break;
                    case 'clipart':
                        $path_cat = NBDESIGNER_DATA_DIR . '/art_cat.json';
                        $path_art = NBDESIGNER_DATA_DIR . '/arts.json'; 
                        $data['cat'] = $data['arts'] = array();
                        if( file_exists($path_cat) ){
                            $_cat = file_get_contents($path_cat);
                            $data['cat'] = $_cat == '' ? array() : json_decode($_cat);
                        }
                        if( file_exists($path_art) ){
                            $_art = file_get_contents($path_art);
                            $data['arts'] = $_art == '' ? array() : json_decode($_art);
                        }
                        break;
                    case 'background':
                        error_log('nbd_get_resource --- get_background');
                        $response = array(
                            'status' => 'success',
                            'message' => '获取背景图片成功',
                            'data' => array()
                        );
                        
                        // 从Bing API获取图片
                        $images = array();
                        for($i = 0; $i < 20; $i++) {
                            $url = "https://bingw.jasonzeng.dev?resolution=UHD&index=" . $i;
                            $image_data = array(
                                'id' => $i + 1,
                                'name' => 'Bing Image ' . ($i + 1),
                                'url' => $url,
                                'thumbnail' => $url,
                                'type' => 'image',
                                'category' => 'background',
                                'tags' => array('background', 'bing'),
                                'width' => 1920,
                                'height' => 1080
                            );
                            $images[] = $image_data;
                        }
                        
                        $response['data'] = array(
                            'bgs' => $images,
                            'length' => count($images)
                        );
                        
                        wp_send_json($response);
                        break;
                }
            }
            wp_send_json(
                array( 
                    'flag' =>  $flag, 
                    'data'  =>  $data
                )
            );
        }
    }
}
$nbd_resource = NBD_RESOURCE::instance();
$nbd_resource->init();