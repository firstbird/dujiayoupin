<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- 相册模态框 -->
<div id="nbd-photo-album" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">我的相册</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="nbd-upload-area mb-3">
                    <input type="file" id="nbd-photo-upload" accept="image/*" style="display: none;">
                    <button class="btn btn-primary" onclick="document.getElementById('nbd-photo-upload').click()">
                        上传图片
                    </button>
                </div>
                <div class="nbd-photo-grid" ng-if="!isLoadingPhotos">
                    <div class="nbd-photo-item" ng-repeat="photo in userPhotos">
                        <img ng-src="{{photo.url}}" alt="用户照片">
                        <div class="nbd-photo-actions">
                            <button class="btn btn-success btn-sm" ng-click="selectPhoto(photo)">选择</button>
                            <button class="btn btn-danger btn-sm" ng-click="deletePhoto(photo)">删除</button>
                        </div>
                    </div>
                </div>
                <div class="nbd-loading" ng-if="isLoadingPhotos">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">加载中...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nbd-photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    padding: 15px;
}

.nbd-photo-item {
    position: relative;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.nbd-photo-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.nbd-photo-actions {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    padding: 10px;
    display: flex;
    justify-content: space-around;
}

.nbd-loading {
    text-align: center;
    padding: 20px;
}

.nbd-upload-area {
    text-align: center;
    padding: 20px;
    border: 2px dashed #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
}
</style>