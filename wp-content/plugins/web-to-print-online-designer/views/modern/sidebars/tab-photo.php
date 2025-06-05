<div class="<?php if( $active_photos ) echo 'active'; ?> tab" ng-if="settings['nbdesigner_enable_image'] == 'yes'" id="tab-photo" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-photo" data-type="photo" data-offset="30">
    <div class="tab-main tab-scroll" style="margin-top: 15px; padding-top: 0;">            
            <div class="result-loaded">
                <div class="content-items">
                    <div ng-class="settings['nbdesigner_upload_show_term'] !== 'yes' ? 'accept' : '' " class="content-item type-upload" data-type="image-upload">
                        <div ng-show="settings.nbdesigner_upload_designs_php_logged_in == 'yes' && !settings.is_logged">
                            <p><?php esc_html_e('You need to be logged in to upload images!','web-to-print-online-designer'); ?></p>
                            <button class="nbd-button nbd-hover-shadow" ng-click="login()"><?php esc_html_e('Login','web-to-print-online-designer'); ?></button>
                        </div>
                        <div ng-hide="settings.nbdesigner_upload_designs_php_logged_in == 'yes' && !settings.is_logged">
                            <div class="item select-photo-btn" ng-click="openPhotoAlbum()" data-type="photo-album" data-api="false" style="text-align: left;">
                                <button type="button" class="nbd-select-photo-btn"><?php esc_html_e('上传或加载图片','web-to-print-online-designer'); ?></button>
                            </div>    
                        </div>
                    </div>
                    
                    <!-- 最近加载的图片 -->
                    <div class="recent-photos-section">
                        <h3 class="section-title">最近加载的图片</h3>
                        <div class="empty-photos-tip" ng-if="!recentPhotos || recentPhotos.length === 0">
                            <i class="icon-nbd icon-nbd-fomat-info-outline"></i>
                            <p>未加载任何图片 todo picture</p>
                        </div>
                        <div class="recent-photos-grid" ng-if="recentPhotos && recentPhotos.length > 0">
                            <div ng-repeat="photo in recentPhotos" class="recent-photo-item">
                                <img ng-src="{{photo.url}}" alt="{{photo.name}}" ng-click="addImage(photo.url, false)">
                                <div class="photo-info">
                                    <span class="photo-name">{{photo.name}}</span>
                                    <span class="photo-date">{{photo.date}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php do_action('nbd_modern_sidebar_photo_images'); ?>
                </div>                
            </div>
    </div>
</div>

<!-- 相册模态框 -->
<div id="nbd-photo-album-modal" class="nbd-modal">
    <div class="nbd-modal-container">
        <div class="nbd-modal-wrapper">
            <div class="nbd-modal-header">
                <h4 class="nbd-modal-title">我的相册</h4>
                <div class="nbd-modal-actions">
                    <button type="button" class="nbd-button nbd-button-danger" id="nbd-clear-album">清空相册</button>
                    <button type="button" class="nbd-modal-close">&times;</button>
                </div>
            </div>
            <div class="nbd-modal-content">
                <?php wp_nonce_field('nbdesigner_get_user_photos', 'nbd-photo-album-nonce', true); ?>
                <div id="nbd-photo-upload-area" class="upload-area">
                    <input type="file" id="nbd-photo-upload" accept="image/*">
                    <div class="upload-tip">点击或拖拽图片到此处上传</div>
                </div>

                <div class="photo-tip"></div>
                <div id="nbd-photo-grid" class="photo-grid"></div>
            </div>
        </div>
    </div>
</div>

<style>
/* 模态窗口基础样式 */
.nbd-modal {
    z-index: 9999999 !important;
    display: none;
    align-items: center;
    justify-content: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.nbd-modal.show {
    display: flex !important;
    pointer-events: auto;
}

.nbd-modal-container {
    z-index: 10000000 !important;
    margin: 0 auto;
    max-width: 800px;
    width: 90%;
    position: relative;
    pointer-events: auto;
}

.nbd-modal-wrapper {
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    position: relative;
}

.nbd-modal-header {
    padding: 8px 20px;
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
    border-radius: 5px 5px 0 0;
    position: relative;
    flex-shrink: 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nbd-modal-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 500;
    color: #333;
}

.nbd-modal-actions {
    display: flex;
    align-items: center;
    gap: 15px;
}

.nbd-modal-close {
    position: static;
    cursor: pointer;
    opacity: 0.5;
    transition: opacity 0.3s;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    font-size: 1.5rem;
    line-height: 1;
    color: #666;
}

.nbd-modal-close:hover {
    opacity: 1;
    color: #333;
}

.nbd-modal-content {
    padding: 20px;
    background: #fff;
    position: relative;
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}

/* 上传区域样式 */
.upload-area {
    text-align: center;
    padding: 20px;
    border: 2px dashed #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    background: #fff;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    flex-shrink: 0;
}

.upload-area:hover {
    border-color: #007bff;
}

.upload-area.uploading {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.upload-area input[type="file"] {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    opacity: 0;
    cursor: pointer;
}

.upload-tip {
    margin-top: 10px;
    color: #6c757d;
    font-size: 0.9em;
}

/* 照片网格样式 */
.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    padding: 15px;
    max-height: 400px;
    overflow-y: auto;
}

.photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.photo-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: all 0.3s ease;
}

.photo-item:hover img {
    transform: scale(1.05);
}

.photo-delete {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 20px;
    height: 20px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transition: opacity 0.3s ease, transform 0.2s ease;
    z-index: 2;
    transform: scale(1);
    will-change: transform, opacity;
}

.photo-item:hover .photo-delete {
    opacity: 1;
}

.photo-delete:hover {
    background: #fff;
    transform: scale(1.1);
}

.photo-delete::before,
.photo-delete::after {
    content: '';
    position: absolute;
    width: 12px;
    height: 2px;
    background-color: #dc3545;
    transition: width 0.2s ease;
    transform-origin: center;
    will-change: width;
}

.photo-delete::before {
    transform: rotate(45deg);
}

.photo-delete::after {
    transform: rotate(-45deg);
}

.photo-delete:hover::before,
.photo-delete:hover::after {
    background-color: #dc3545;
    width: 14px;
}

/* 状态提示样式 */
.loading, .error, .no-photos {
    grid-column: 1 / -1;
    text-align: center;
    padding: 20px;
    color: #6c757d;
    background: #fff;
}

.error {
    color: #dc3545;
}

/* 清空相册按钮悬停样式 */
#nbd-clear-album:hover {
    color: #dc3545;
}

/* 确保模态窗口内容可点击 */
.nbd-modal.show .nbd-modal-container,
.nbd-modal.show .nbd-modal-wrapper,
.nbd-modal.show .nbd-modal-header,
.nbd-modal.show .nbd-modal-content,
.nbd-modal.show .upload-area,
.nbd-modal.show .photo-grid,
.nbd-modal.show .photo-item,
.nbd-modal.show .photo-actions button {
    pointer-events: auto;
}

/* 添加遮罩层 */
.nbd-modal::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: -1;
    pointer-events: none;
}

/* 相册按钮样式 */
.nbd-select-photo-btn {
    background: #fff;
    color: #333;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-left: 12px;
}

.nbd-select-photo-btn:hover {
    background: #f8f9fa;
    border-color: #ccc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

/* 清除按钮样式 */
.nbd-clear-btn {
    background: none;
    border: none;
    color: #666;
    padding: 8px 16px;
    font-size: 14px;
    cursor: pointer;
    transition: color 0.3s ease;
}

.nbd-clear-btn:hover {
    color: #dc3545;
}

/* 相册提示文字样式 */
.photo-tip {
    color: #666;
    font-size: 13px;
    margin: 0 15px 10px;
    padding: 5px 0;
    border-bottom: 1px solid #eee;
}

/* 最近加载图片样式 */
.recent-photos-section {
    margin: 20px 12px;
    padding: 0;
    position: relative;
    background: transparent;
}

.section-title {
    font-size: 14px;
    color: #666;
    margin: 0 0 10px 0;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
    position: relative;
    background: transparent;
    z-index: 1;
}

.recent-photos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    padding: 0;
    position: relative;
    background: transparent;
    max-height: calc(100vh * 0.66);
    overflow-y: auto;
}

.recent-photos-grid::-webkit-scrollbar {
    width: 6px;
}

.recent-photos-grid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.recent-photos-grid::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.recent-photos-grid::-webkit-scrollbar-thumb:hover {
    background: #999;
}

.recent-photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
}

.recent-photo-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.recent-photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.recent-photo-item:hover img {
    transform: scale(1.05);
}

.photo-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 6px;
    background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    color: #fff;
    font-size: 11px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.recent-photo-item:hover .photo-info {
    opacity: 1;
}

.photo-name {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.photo-date {
    display: block;
    font-size: 10px;
    opacity: 0.8;
}

/* 移除之前的滚动禁用样式 */
.tab-main.tab-scroll {
    overflow: auto !important;
}

.tab[data-container="#tab-photo"] {
    overflow: auto !important;
}

.ps__scrollbar-y-rail {
    display: block !important;
    pointer-events: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.ps__rail-y {
    display: block !important;
    pointer-events: auto !important;
    opacity: 1 !important;
    visibility: visible !important;
}

/* 空状态提示样式 */
.empty-photos-tip {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
    color: #999;
    margin: 10px 0;
}

.empty-photos-tip .icon-nbd {
    font-size: 32px;
    margin-bottom: 12px;
    color: #ccc;
}

.empty-photos-tip p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}
</style>

<script>
// 移除Perfect Scrollbar禁用代码
if (typeof Ps !== 'undefined') {
    var $tabMain = $('.tab-main.tab-scroll');
    if ($tabMain.length) {
        Ps.initialize($tabMain[0]);
    }
}
</script>