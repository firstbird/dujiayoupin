<?php
if (!defined('ABSPATH')) {
    exit;
}

class Nbdesigner_Ajax {
    public function __construct() {
        add_action('wp_ajax_nbdesigner_get_user_photos', array($this, 'nbdesigner_get_user_photos'));
        add_action('wp_ajax_nbdesigner_upload_user_photo', array($this, 'nbdesigner_upload_user_photo'));
        add_action('wp_ajax_nbdesigner_delete_user_photo', array($this, 'nbdesigner_delete_user_photo'));
        
        // 为非登录用户添加AJAX处理
        add_action('wp_ajax_nopriv_nbdesigner_get_user_photos', array($this, 'nbdesigner_get_user_photos'));
        add_action('wp_ajax_nopriv_nbdesigner_upload_user_photo', array($this, 'nbdesigner_upload_user_photo'));
        add_action('wp_ajax_nopriv_nbdesigner_delete_user_photo', array($this, 'nbdesigner_delete_user_photo'));
    }

    public function nbdesigner_get_user_photos() {
        error_log('nbdesigner_get_user_photos called');
        
        // 检查nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nbdesigner_ajax_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error(array('message' => '安全验证失败'));
            return;
        }
        
        // 检查用户登录状态
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json_error(array('message' => '请先登录'));
            return;
        }
        
        try {
            $user_id = get_current_user_id();
            error_log('Getting photos for user: ' . $user_id);
            
            $photos = get_user_meta($user_id, 'nbd_user_photos', true);
            
            if (!is_array($photos)) {
                $photos = array();
            }
            
            error_log('Photos retrieved: ' . count($photos));
            wp_send_json_success(array('photos' => $photos));
        } catch (Exception $e) {
            error_log('Error in nbdesigner_get_user_photos: ' . $e->getMessage());
            wp_send_json_error(array('message' => '获取相册失败：' . $e->getMessage()));
        }
    }

    public function nbdesigner_upload_user_photo() {
        error_log('nbdesigner_upload_user_photo called');
        
        // 检查nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nbdesigner_ajax_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error(array('message' => '安全验证失败'));
            return;
        }
        
        // 检查用户登录状态
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json_error(array('message' => '请先登录'));
            return;
        }
        
        try {
            if (!isset($_FILES['file'])) {
                error_log('No file uploaded');
                wp_send_json_error(array('message' => '没有上传文件'));
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
                    wp_send_json_error(array('message' => '创建目录失败'));
                    return;
                }
            }
            
            $file_name = wp_unique_filename($user_dir, $file['name']);
            $file_path = $user_dir . '/' . $file_name;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $file_url = $upload_dir['baseurl'] . '/nbd-user-photos/' . $user_id . '/' . $file_name;
                
                $photos = get_user_meta($user_id, 'nbd_user_photos', true);
                if (!is_array($photos)) {
                    $photos = array();
                }
                
                $photo_id = uniqid();
                $photos[] = array(
                    'id' => $photo_id,
                    'url' => $file_url
                );
                
                update_user_meta($user_id, 'nbd_user_photos', $photos);
                
                error_log('Photo uploaded successfully: ' . $file_url);
                wp_send_json_success(array(
                    'id' => $photo_id,
                    'url' => $file_url
                ));
            } else {
                error_log('Failed to move uploaded file');
                wp_send_json_error(array('message' => '上传失败'));
            }
        } catch (Exception $e) {
            error_log('Error in nbdesigner_upload_user_photo: ' . $e->getMessage());
            wp_send_json_error(array('message' => '上传失败：' . $e->getMessage()));
        }
    }

    public function nbdesigner_delete_user_photo() {
        error_log('nbdesigner_delete_user_photo called');
        
        // 检查nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'nbdesigner_ajax_nonce')) {
            error_log('Nonce verification failed');
            wp_send_json_error(array('message' => '安全验证失败'));
            return;
        }
        
        // 检查用户登录状态
        if (!is_user_logged_in()) {
            error_log('User not logged in');
            wp_send_json_error(array('message' => '请先登录'));
            return;
        }
        
        try {
            $photo_id = isset($_POST['photo_id']) ? sanitize_text_field($_POST['photo_id']) : '';
            if (empty($photo_id)) {
                error_log('No photo ID provided');
                wp_send_json_error(array('message' => '参数错误'));
                return;
            }
            
            $user_id = get_current_user_id();
            $photos = get_user_meta($user_id, 'nbd_user_photos', true);
            
            if (!is_array($photos)) {
                error_log('No photos found for user');
                wp_send_json_error(array('message' => '没有找到图片'));
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
                update_user_meta($user_id, 'nbd_user_photos', $photos);
                
                error_log('Photo deleted successfully');
                wp_send_json_success();
            } else {
                error_log('Photo not found');
                wp_send_json_error(array('message' => '没有找到图片'));
            }
        } catch (Exception $e) {
            error_log('Error in nbdesigner_delete_user_photo: ' . $e->getMessage());
            wp_send_json_error(array('message' => '删除失败：' . $e->getMessage()));
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