<?php
/**
 * NBDesigner AJAX 调试脚本
 * 用于诊断 AJAX 请求问题
 */

// 加载 WordPress
require_once('../../../wp-load.php');

// 检查是否可以直接访问
if (!defined('ABSPATH')) {
    echo "无法直接访问此文件";
    exit;
}

echo "<h1>NBDesigner AJAX 调试报告</h1>";

// 1. 检查插件状态
echo "<h2>1. 插件状态检查</h2>";
$active_plugins = get_option('active_plugins');
$plugin_name = 'web-to-print-online-designer/nbdesigner.php';
if (in_array($plugin_name, $active_plugins)) {
    echo "<p style='color: green;'>✓ 插件已激活</p>";
} else {
    echo "<p style='color: red;'>✗ 插件未激活</p>";
}

// 2. 检查类是否存在
echo "<h2>2. 类存在性检查</h2>";
if (class_exists('NBD_RESOURCE')) {
    echo "<p style='color: green;'>✓ NBD_RESOURCE 类存在</p>";
} else {
    echo "<p style='color: red;'>✗ NBD_RESOURCE 类不存在</p>";
}

// 3. 检查 AJAX 动作注册
echo "<h2>3. AJAX 动作注册检查</h2>";
global $wp_filter;

$ajax_actions = array(
    'wp_ajax_nbd_get_resource',
    'wp_ajax_nopriv_nbd_get_resource'
);

foreach ($ajax_actions as $action) {
    if (isset($wp_filter[$action])) {
        $callback_count = count($wp_filter[$action]->callbacks);
        echo "<p style='color: green;'>✓ $action 已注册 ($callback_count 个回调)</p>";
        
        // 显示回调详情
        foreach ($wp_filter[$action]->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && is_object($callback['function'][0])) {
                    $class_name = get_class($callback['function'][0]);
                    $method_name = $callback['function'][1];
                    echo "<p style='margin-left: 20px;'>- 优先级: $priority, 类: $class_name, 方法: $method_name</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red;'>✗ $action 未注册</p>";
    }
}

// 4. 检查常量定义
echo "<h2>4. 常量定义检查</h2>";
$constants = array(
    'NBDESIGNER_ENABLE_NONCE',
    'NBDESIGNER_PLUGIN_DIR',
    'NBDESIGNER_DATA_DIR'
);

foreach ($constants as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        echo "<p style='color: green;'>✓ $constant = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ $constant 未定义</p>";
    }
}

// 5. 检查 WordPress AJAX 状态
echo "<h2>5. WordPress AJAX 状态</h2>";
echo "<p>DOING_AJAX: " . (defined('DOING_AJAX') ? 'true' : 'false') . "</p>";
echo "<p>WP_DEBUG: " . (defined('WP_DEBUG') ? 'true' : 'false') . "</p>";
echo "<p>admin-ajax.php 路径: " . admin_url('admin-ajax.php') . "</p>";

// 6. 测试实例化
echo "<h2>6. 实例化测试</h2>";
try {
    if (class_exists('NBD_RESOURCE')) {
        $resource = NBD_RESOURCE::instance();
        echo "<p style='color: green;'>✓ NBD_RESOURCE 实例创建成功</p>";
        
        // 检查方法是否存在
        if (method_exists($resource, 'nbd_get_resource')) {
            echo "<p style='color: green;'>✓ nbd_get_resource 方法存在</p>";
        } else {
            echo "<p style='color: red;'>✗ nbd_get_resource 方法不存在</p>";
        }
        
        if (method_exists($resource, 'ajax')) {
            echo "<p style='color: green;'>✓ ajax 方法存在</p>";
        } else {
            echo "<p style='color: red;'>✗ ajax 方法不存在</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ 无法创建实例，类不存在</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ 实例化错误: " . $e->getMessage() . "</p>";
}

// 7. 检查文件存在性
echo "<h2>7. 文件存在性检查</h2>";
$files_to_check = array(
    'includes/class.resource.php' => NBDESIGNER_PLUGIN_DIR . 'includes/class.resource.php',
    'nbdesigner.php' => NBDESIGNER_PLUGIN_DIR . 'nbdesigner.php',
    'admin-ajax.php' => ABSPATH . 'wp-admin/admin-ajax.php'
);

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        echo "<p style='color: green;'>✓ $name 存在</p>";
    } else {
        echo "<p style='color: red;'>✗ $name 不存在 ($path)</p>";
    }
}

// 8. 检查 WordPress 钩子系统
echo "<h2>8. WordPress 钩子系统检查</h2>";
if (function_exists('add_action')) {
    echo "<p style='color: green;'>✓ add_action 函数可用</p>";
} else {
    echo "<p style='color: red;'>✗ add_action 函数不可用</p>";
}

if (function_exists('has_action')) {
    echo "<p style='color: green;'>✓ has_action 函数可用</p>";
} else {
    echo "<p style='color: red;'>✗ has_action 函数不可用</p>";
}

// 9. 检查插件加载顺序
echo "<h2>9. 插件加载顺序</h2>";
$plugin_order = array();
foreach ($active_plugins as $index => $plugin) {
    if (strpos($plugin, 'web-to-print-online-designer') !== false) {
        $plugin_order[] = "位置 " . ($index + 1) . ": " . $plugin;
    }
}

if (!empty($plugin_order)) {
    foreach ($plugin_order as $order) {
        echo "<p style='color: blue;'>$order</p>";
    }
} else {
    echo "<p style='color: red;'>✗ 未找到 NBDesigner 插件</p>";
}

// 10. 建议的解决方案
echo "<h2>10. 建议的解决方案</h2>";
echo "<ol>";
echo "<li>确保插件在 WordPress 初始化早期加载</li>";
echo "<li>检查是否有其他插件冲突</li>";
echo "<li>验证 WordPress 版本兼容性</li>";
echo "<li>检查服务器错误日志</li>";
echo "<li>尝试禁用其他插件进行测试</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>调试完成时间:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>WordPress 版本:</strong> " . get_bloginfo('version') . "</p>";
echo "<p><strong>PHP 版本:</strong> " . phpversion() . "</p>";
?>









