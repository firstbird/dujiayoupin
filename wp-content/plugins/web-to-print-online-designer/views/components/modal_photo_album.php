<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- 相册模态框 -->
<div class="modal fade" id="nbd-photo-album-modal" tabindex="-1" role="dialog" aria-labelledby="nbd-photo-album-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nbd-photo-album-title">我的相册</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- 隐藏的nonce字段 -->
                <input type="hidden" id="nbd-photo-album-nonce" value="<?php echo wp_create_nonce('nbdesigner_photo_album'); ?>">
                
                <!-- 上传区域 -->
                <div id="nbd-photo-upload-area" class="upload-area">
                    <input type="file" id="nbd-photo-upload" accept="image/*" style="display: none;">
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('nbd-photo-upload').click()">
                        <i class="fa fa-upload"></i> 上传照片
                    </button>
                    <p class="upload-tip">支持jpg、png、gif格式，单个文件不超过2MB</p>
                </div>

                <!-- 照片网格 -->
                <div id="nbd-photo-grid" class="photo-grid">
                    <!-- 照片将通过JavaScript动态加载 -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-area {
    text-align: center;
    padding: 20px;
    border: 2px dashed #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
}

.upload-area.uploading {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.upload-tip {
    margin-top: 10px;
    color: #6c757d;
    font-size: 0.9em;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    padding: 15px;
}

.photo-item {
    position: relative;
    border-radius: 5px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.photo-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.photo-actions {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    padding: 5px;
    display: flex;
    justify-content: space-around;
}

.photo-actions button {
    background: none;
    border: none;
    color: white;
    padding: 5px 10px;
    cursor: pointer;
    font-size: 0.9em;
}

.photo-actions button:hover {
    color: #007bff;
}

.loading, .error, .no-photos {
    grid-column: 1 / -1;
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.error {
    color: #dc3545;
}
</style>