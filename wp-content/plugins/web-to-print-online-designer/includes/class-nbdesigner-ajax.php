<?php
if (!defined('ABSPATH')) {
    exit;
}

class Nbdesigner_Ajax {
    public function __construct() {
        // 添加获取nonce的AJAX端点
        add_action('wp_ajax_nbdesigner_get_nonce', array($this, 'nbdesigner_get_nonce'));
        add_action('wp_ajax_nopriv_nbdesigner_get_nonce', array($this, 'nbdesigner_get_nonce'));
        
        // 其他AJAX端点
        add_action('wp_ajax_nbdesigner_get_user_photos', array($this, 'nbdesigner_get_user_photos'));
        add_action('wp_ajax_nbdesigner_upload_user_photo', array($this, 'nbdesigner_upload_user_photo'));
        add_action('wp_ajax_nbdesigner_delete_user_photo', array($this, 'nbdesigner_delete_user_photo'));
        
        // 为非登录用户添加AJAX处理
        add_action('wp_ajax_nopriv_nbdesigner_get_user_photos', array($this, 'nbdesigner_get_user_photos'));
        add_action('wp_ajax_nopriv_nbdesigner_upload_user_photo', array($this, 'nbdesigner_upload_user_photo'));
        add_action('wp_ajax_nopriv_nbdesigner_delete_user_photo', array($this, 'nbdesigner_delete_user_photo'));

        // 添加保存最近图片的AJAX处理
        add_action('wp_ajax_nbdesigner_save_recent_image', array($this, 'save_recent_image'));
        add_action('wp_ajax_nopriv_nbdesigner_save_recent_image', array($this, 'save_recent_image'));

        // 添加前端日志处理
        add_action('wp_ajax_nbdesigner_log_frontend', array($this, 'log_frontend'));
        add_action('wp_ajax_nopriv_nbdesigner_log_frontend', array($this, 'log_frontend'));
    }

    public function nbdesigner_get_nonce() {
        error_log('Generating new nonce');
        $nonce = wp_create_nonce('nbdesigner_get_user_photos');
        error_log('Generated nonce: ' . $nonce);
        wp_send_json_success(array('nonce' => $nonce));
    }

    public function nbdesigner_get_user_photos() {
        error_log('nbdesigner_get_user_photos called');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('Current user ID: ' . get_current_user_id());
        
        // 检查用户是否登录
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json_error(array('message' => '请先登录'));
            return;
        }
        
        try {
            $user_id = get_current_user_id();
            $photos = get_user_meta($user_id, 'nbdesigner_user_photos', true);
            
            if (!is_array($photos)) {
                $photos = array();
            }
            
            error_log('Retrieved photos: ' . print_r($photos, true));
            
            wp_send_json(array(
                'success' => true,
                'photos' => $photos
            ));
        } catch (Exception $e) {
            error_log('Error in nbdesigner_get_user_photos: ' . $e->getMessage());
            wp_send_json(array(
                'success' => false,
                'message' => '获取照片失败'
            ));
        }
    }

    public function nbdesigner_upload_user_photo() {
        error_log('nbdesigner_upload_user_photo called');
        
        // 检查用户是否登录
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json(array(
                'success' => false,
                'message' => '请先登录'
            ));
            return;
        }
        
        try {
            if (!isset($_FILES['file'])) {
                error_log('No file uploaded');
                wp_send_json(array(
                    'success' => false,
                    'message' => '没有上传文件'
                ));
                return;
            }
            
            $file = $_FILES['file'];
            $upload_dir = wp_upload_dir();
            $user_id = get_current_user_id();
            $user_dir = $upload_dir['basedir'] . '/nbd-user-photos/' . $user_id;
            
            // 创建用户目录
            if (!file_exists($user_dir)) {
                if (!wp_mkdir_p($user_dir)) {
                    error_log('Failed to create directory: ' . $user_dir);
                    wp_send_json(array(
                        'success' => false,
                        'message' => '创建目录失败'
                    ));
                    return;
                }
            }
            
            $file_name = wp_unique_filename($user_dir, $file['name']);
            $file_path = $user_dir . '/' . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $file_url = $upload_dir['baseurl'] . '/nbd-user-photos/' . $user_id . '/' . $file_name;
                
                $photos = get_user_meta($user_id, 'nbdesigner_user_photos', true);
                if (!is_array($photos)) {
                    $photos = array();
                }
                
                $photo_id = uniqid();
                $photos[] = array(
                    'id' => $photo_id,
                    'url' => $file_url
                );
                
                update_user_meta($user_id, 'nbdesigner_user_photos', $photos);
                
                error_log('Photo uploaded successfully: ' . $file_url);
                wp_send_json(array(
                    'success' => true,
                    'id' => $photo_id,
                    'url' => $file_url
                ));
            } else {
                error_log('Failed to move uploaded file');
                wp_send_json(array(
                    'success' => false,
                    'message' => '上传失败'
                ));
            }
        } catch (Exception $e) {
            error_log('Error in nbdesigner_upload_user_photo: ' . $e->getMessage());
            wp_send_json(array(
                'success' => false,
                'message' => '上传失败：' . $e->getMessage()
            ));
        }
    }

    public function nbdesigner_delete_user_photo() {
        error_log('nbdesigner_delete_user_photo called');
        
        // 检查用户是否登录
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json(array(
                'success' => false,
                'message' => '请先登录'
            ));
            return;
        }
        
        try {
            $photo_id = isset($_POST['photo_id']) ? sanitize_text_field($_POST['photo_id']) : '';
            if (empty($photo_id)) {
                error_log('No photo ID provided');
                wp_send_json(array(
                    'success' => false,
                    'message' => '参数错误'
                ));
                return;
            }
            
            $user_id = get_current_user_id();
            $photos = get_user_meta($user_id, 'nbdesigner_user_photos', true);
            
            if (!is_array($photos)) {
                error_log('No photos found for user');
                wp_send_json(array(
                    'success' => false,
                    'message' => '没有找到图片'
                ));
                return;
            }
            
            $photo_index = -1;
            foreach ($photos as $index => $photo) {
                if ($photo['id'] === $photo_id) {
                    $photo_index = $index;
                    break;
                }
            }
            
            if ($photo_index > -1) {
                $photo_url = $photos[$photo_index]['url'];
                $photo_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $photo_url);
                
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
                
                array_splice($photos, $photo_index, 1);
                update_user_meta($user_id, 'nbdesigner_user_photos', $photos);
                
                error_log('Photo deleted successfully');
                wp_send_json(array(
                    'success' => true
                ));
            } else {
                error_log('Photo not found');
                wp_send_json(array(
                    'success' => false,
                    'message' => '没有找到图片'
                ));
            }
        } catch (Exception $e) {
            error_log('Error in nbdesigner_delete_user_photo: ' . $e->getMessage());
            wp_send_json(array(
                'success' => false,
                'message' => '删除失败：' . $e->getMessage()
            ));
        }
    }

    public function save_recent_image() {
        check_ajax_referer('nbdesigner_ajax_nonce', 'nonce');
        $image_url = sanitize_text_field($_POST['image_url']);
        if(!empty($image_url)) {
            $frontend = new NBDesignerFrontend();
            $frontend->save_recent_image($image_url);
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function log_frontend() {
        try {
            // 检查日志文件权限
            $log_file = WP_CONTENT_DIR . '/debug.log';
            if (!is_writable($log_file)) {
                error_log('日志文件不可写: ' . $log_file);
                wp_send_json_error('日志文件不可写');
                return;
            }

            error_log('开始处理前端日志请求 - ' . date('Y-m-d H:i:s'));
            error_log('POST数据: ' . print_r($_POST, true));
            
            if (!check_ajax_referer('nbdesigner_ajax_nonce', 'nonce', false)) {
                error_log('nonce验证失败 - 期望: ' . wp_create_nonce('nbdesigner_ajax_nonce') . ', 收到: ' . $_POST['nonce']);
                wp_send_json_error('nonce验证失败');
                return;
            }
            
            $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
            $data = isset($_POST['data']) ? $_POST['data'] : array();
            
            if (!empty($message)) {
                $log_message = sprintf(
                    "[前端日志] %s - 用户: %s - 时间: %s - 数据: %s",
                    $message,
                    is_user_logged_in() ? get_current_user_id() : '游客',
                    date('Y-m-d H:i:s'),
                    json_encode($data, JSON_UNESCAPED_UNICODE)
                );
                
                // 尝试直接写入文件
                $result = file_put_contents($log_file, $log_message . "\n", FILE_APPEND);
                if ($result === false) {
                    error_log('直接写入日志文件失败');
                    // 尝试使用error_log
                    if (!error_log($log_message)) {
                        error_log('error_log写入失败');
                        wp_send_json_error('日志写入失败');
                        return;
                    }
                }
                
                wp_send_json_success(array('message' => '日志写入成功'));
            } else {
                error_log('日志消息为空');
                wp_send_json_error('日志消息为空');
            }
        } catch (Exception $e) {
            error_log('日志处理异常: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            wp_send_json_error('日志处理异常: ' . $e->getMessage());
        }
    }
}

// 实例化类
function nbdesigner_init_ajax() {
    global $nbdesigner_ajax;
    if (!isset($nbdesigner_ajax)) {
        $nbdesigner_ajax = new Nbdesigner_Ajax();
    }
    return $nbdesigner_ajax;
}

// 在插件初始化时调用
add_action('init', 'nbdesigner_init_ajax'); 