<?php
// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 设置响应头
header('Content-Type: application/json');

// 获取POST数据
$logData = file_get_contents('php://input');

// 检查数据是否为空
if (empty($logData)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => '日志数据为空']);
    exit;
}

// 设置日志文件路径
$logFile = dirname(dirname(dirname(dirname(__DIR__)))) . '/front.log';

// 确保日志目录存在
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0777, true);
}

// 添加时间戳
$logEntry = date('Y-m-d H:i:s') . ' - ' . $logData . "\n";

// 写入日志文件
if (file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX)) {
    echo json_encode(['success' => true, 'message' => '日志保存成功']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => '无法写入日志文件']);
} 