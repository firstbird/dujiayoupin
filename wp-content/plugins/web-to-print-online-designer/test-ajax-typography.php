<?php
/**
 * 字体数据加载测试文件
 * 用于测试字体数据是否能正确加载
 */

// 检查是否通过AJAX请求
if (!defined('ABSPATH')) {
    // 如果不是通过WordPress加载，模拟WordPress环境
    require_once('../../../wp-load.php');
}

// 设置响应头
header('Content-Type: application/json');

// 模拟AJAX请求参数
$_REQUEST['action'] = 'nbd_get_resource';
$_REQUEST['type'] = 'typography';
$_REQUEST['task'] = 'new';
$_REQUEST['nonce'] = 'test-nonce';

// 包含字体资源类
require_once('includes/class.resource.php');

// 创建资源实例
$resource = NBD_RESOURCE::instance();

// 测试字体数据加载
try {
    // 直接调用字体数据加载逻辑
    $flag = 1;
    $data = array();
    
    $path = NBDESIGNER_PLUGIN_DIR . '/data/typography/typo.json';
    
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path));
        $response = array(
            'flag' => 1,
            'data' => $data,
            'message' => '字体数据加载成功',
            'file_path' => $path,
            'file_exists' => true
        );
    } else {
        $response = array(
            'flag' => 0,
            'data' => array(),
            'message' => '字体数据文件不存在',
            'file_path' => $path,
            'file_exists' => false
        );
    }
    
} catch (Exception $e) {
    $response = array(
        'flag' => 0,
        'data' => array(),
        'message' => '字体数据加载失败: ' . $e->getMessage(),
        'error' => $e->getMessage()
    );
}

// 输出JSON响应
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
