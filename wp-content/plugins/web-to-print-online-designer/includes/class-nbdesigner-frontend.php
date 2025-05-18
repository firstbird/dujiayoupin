<?php

class NBDesignerFrontend {

    public function __construct() {
        // 确保调试模式已启用
        if (!defined('WP_DEBUG')) {
            define('WP_DEBUG', true);
        }
        if (!defined('WP_DEBUG_LOG')) {
            define('WP_DEBUG_LOG', true);
        }
        if (!defined('WP_DEBUG_DISPLAY')) {
            define('WP_DEBUG_DISPLAY', false);
        }
        @ini_set('display_errors', 0);
        
        // 初始化日志文件
        $log_file = WP_CONTENT_DIR . '/debug.log';
        if (!file_exists($log_file)) {
            touch($log_file);
            chmod($log_file, 0666);
        }
        
        error_log('NBDesigner 插件初始化 - ' . date('Y-m-d H:i:s'));
    }

    public function enqueue_scripts() {
        wp_localize_script('nbdesigner-frontend', 'nbdesigner', array(
            'recentImages' => array_map(function($url) {
                return array('url' => $url);
            }, $this->get_recent_images()),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nbdesigner_ajax_nonce'),
            'debug' => WP_DEBUG
        ));
    }

    private function get_recent_images() {
        $recent_images = get_option('nbdesigner_recent_images', array());
        return array_slice($recent_images, 0, 9); // 只返回最近9张图片
    }

    public function save_recent_image($image_url) {
        $recent_images = get_option('nbdesigner_recent_images', array());
        // 如果图片已存在，先移除它
        $recent_images = array_filter($recent_images, function($url) use ($image_url) {
            return $url !== $image_url;
        });
        // 将新图片添加到数组开头
        array_unshift($recent_images, $image_url);
        // 只保留最近9张图片
        $recent_images = array_slice($recent_images, 0, 9);
        update_option('nbdesigner_recent_images', $recent_images);
    }
} 