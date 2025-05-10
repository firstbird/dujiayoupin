<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly  ?>
<!-- 相册模态框 -->
<div class="modal fade" id="nbd-photo-album-modal" tabindex="-1" role="dialog" aria-labelledby="nbd-photo-album-title" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="nbd-photo-album-title">我的相册</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="nbd-photo-album-nonce" value="<?php echo wp_create_nonce('nbdesigner_ajax_nonce'); ?>">
                
                <div class="photo-upload-area" id="nbd-photo-upload-area">
                    <input type="file" id="nbd-photo-upload" accept="image/*" style="display: none;">
                    <button type="button" class="upload-btn" onclick="document.getElementById('nbd-photo-upload').click();">
                        <i class="fa fa-upload"></i> 上传照片
                    </button>
                    <p class="upload-tip">支持jpg、png、gif格式，单个文件不超过5MB</p>
                </div>

                <div class="photo-grid" id="nbd-photo-grid">
                    <!-- 照片将通过JavaScript动态加载 -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.photo-upload-area {
    border: 2px dashed #ddd;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
    background: #f9f9f9;
}

.photo-upload-area.uploading {
    opacity: 0.7;
    pointer-events: none;
}

.upload-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.upload-btn:hover {
    background: #0056b3;
}

.upload-tip {
    margin: 10px 0 0;
    color: #666;
    font-size: 12px;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.photo-item {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
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
    background: rgba(0, 0, 0, 0.7);
    padding: 8px;
    display: flex;
    justify-content: space-around;
}

.photo-actions button {
    background: none;
    border: 1px solid white;
    color: white;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 12px;
}

.photo-actions button:hover {
    background: rgba(255, 255, 255, 0.2);
}

.loading, .error, .no-photos {
    text-align: center;
    padding: 20px;
    color: #666;
}

.error {
    color: #dc3545;
}
</style> 