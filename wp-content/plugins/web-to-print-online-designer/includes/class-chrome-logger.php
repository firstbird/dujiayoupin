<?php
if (!defined('ABSPATH')) {
    exit;
}

class Chrome_Logger {
    private static $instance = null;
    private $log_file;
    private $debug = true;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        try {
            $upload_dir = wp_upload_dir();
            $this->log_file = $upload_dir['basedir'] . '/chrome-console.log';
            
            // 调试信息
            if ($this->debug) {
                error_log('Chrome Logger: Initializing...');
                error_log('Chrome Logger: Upload dir: ' . $upload_dir['basedir']);
                error_log('Chrome Logger: Log file: ' . $this->log_file);
                error_log('Chrome Logger: PHP version: ' . PHP_VERSION);
                error_log('Chrome Logger: WordPress version: ' . get_bloginfo('version'));
                error_log('Chrome Logger: Current user: ' . get_current_user());
                error_log('Chrome Logger: Process user: ' . getenv('USER'));
                error_log('Chrome Logger: Process group: ' . getenv('GROUP'));
            }
            
            // 检查目录是否可写
            if (!is_writable($upload_dir['basedir'])) {
                error_log('Chrome Logger: Upload directory is not writable: ' . $upload_dir['basedir']);
                error_log('Chrome Logger: Directory permissions: ' . substr(sprintf('%o', fileperms($upload_dir['basedir'])), -4));
                throw new Exception('Upload directory is not writable');
            }
            
            // 如果文件不存在，尝试创建
            if (!file_exists($this->log_file)) {
                $result = file_put_contents($this->log_file, '');
                if ($result === false) {
                    error_log('Chrome Logger: Could not create log file: ' . $this->log_file);
                    error_log('Chrome Logger: Error: ' . error_get_last()['message']);
                    throw new Exception('Could not create log file');
                }
                if ($this->debug) {
                    error_log('Chrome Logger: Created new log file');
                }
            }
            
            // 检查文件是否可写
            if (!is_writable($this->log_file)) {
                error_log('Chrome Logger: Log file is not writable: ' . $this->log_file);
                error_log('Chrome Logger: File permissions: ' . substr(sprintf('%o', fileperms($this->log_file)), -4));
                throw new Exception('Log file is not writable');
            } else if ($this->debug) {
                error_log('Chrome Logger: Log file is writable');
            }
        } catch (Exception $e) {
            error_log('Chrome Logger: Constructor error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function log($message) {
        try {
            if (!is_writable($this->log_file)) {
                error_log('Chrome Logger: Cannot write to log file');
                throw new Exception('Cannot write to log file');
            }

            if (!is_string($message)) {
                $message = print_r($message, true);
            }

            $timestamp = current_time('Y-m-d H:i:s');
            $log_entry = sprintf("[%s] %s\n", $timestamp, $message);

            $result = file_put_contents($this->log_file, $log_entry, FILE_APPEND);
            if ($result === false) {
                error_log('Chrome Logger: Failed to write to log file');
                error_log('Chrome Logger: Error: ' . error_get_last()['message']);
                throw new Exception('Failed to write to log file');
            }
            
            if ($this->debug) {
                error_log('Chrome Logger: Successfully wrote to log file');
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Chrome Logger: Log error: ' . $e->getMessage());
            throw $e;
        }
    }
}

// 添加 REST API 端点
add_action('rest_api_init', function () {
    register_rest_route('chrome-logger/v1', '/log', array(
        'methods' => 'POST',
        'callback' => 'handle_chrome_log',
        'permission_callback' => function() {
            return true;
        }
    ));
});

function handle_chrome_log($request) {
    try {
        // 记录请求信息
        error_log('Chrome Logger: Received request');
        error_log('Chrome Logger: Request method: ' . $_SERVER['REQUEST_METHOD']);
        error_log('Chrome Logger: Content type: ' . $_SERVER['CONTENT_TYPE']);
        
        // 获取请求体
        $body = $request->get_body();
        error_log('Chrome Logger: Raw request body: ' . $body);
        
        $data = json_decode($body, true);
        error_log('Chrome Logger: Decoded data: ' . print_r($data, true));
        
        if (empty($data)) {
            error_log('Chrome Logger: Empty data received');
            return new WP_Error('invalid_data', 'No data received', array('status' => 400));
        }
        
        // 记录日志
        $logger = Chrome_Logger::get_instance();
        $result = $logger->log($data);
        
        if ($result === false) {
            error_log('Chrome Logger: Failed to save log');
            return new WP_Error('write_failed', 'Failed to write log', array('status' => 500));
        }
        
        error_log('Chrome Logger: Successfully saved log');
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Log saved successfully'
        ), 200);
    } catch (Exception $e) {
        error_log('Chrome Logger: Exception: ' . $e->getMessage());
        error_log('Chrome Logger: Stack trace: ' . $e->getTraceAsString());
        return new WP_Error('server_error', $e->getMessage(), array('status' => 500));
    }
} 