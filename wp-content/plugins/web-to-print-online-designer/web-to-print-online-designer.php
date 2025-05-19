<?php
// 移除所有日志相关的代码

// 添加 AJAX 处理
add_action('wp_ajax_nbdesigner_log', 'nbdesigner_handle_log');
add_action('wp_ajax_nopriv_nbdesigner_log', 'nbdesigner_handle_log');

// 添加必要的 JavaScript 变量
add_action('wp_enqueue_scripts', 'nbdesigner_enqueue_scripts', 5); // 提前加载优先级
function nbdesigner_enqueue_scripts() {
    // 添加必要的 JavaScript 变量到所有相关脚本
    $nbd_window_data = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nbdesigner_log_nonce' => wp_create_nonce('nbdesigner_log_nonce')
    );
    
    // 注册并加载日志 JavaScript
    wp_enqueue_script(
        'nbdesigner-logger',
        NBDESIGNER_PLUGIN_URL . 'assets/js/nbdesigner-logger.js',
        array('jquery'), // 只依赖 jQuery
        NBDESIGNER_VERSION,
        false // 在头部加载
    );

    // 添加变量到日志脚本
    wp_localize_script('nbdesigner-logger', 'nbd_window', $nbd_window_data);
    
    // 如果 nbdesigner 脚本存在，也添加变量
    if (wp_script_is('nbdesigner', 'registered')) {
        wp_localize_script('nbdesigner', 'nbd_window', $nbd_window_data);
    }
}

// 加载 Chrome 日志类
require_once plugin_dir_path(__FILE__) . 'includes/class-chrome-logger.php'; 