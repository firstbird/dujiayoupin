<div class="<?php if( $active_backgrounds ) echo 'active'; ?> tab" ng-if="settings['nbdesigner_enable_image'] == 'yes'" id="tab-photo" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-photo" data-type="background" data-offset="30">
    <div class="nbd-search">
        <input type="text" name="search" placeholder="<?php esc_html_e('Search background', 'web-to-print-online-designer'); ?>" ng-model="resource.photo.photoSearch"/>
        <i class="icon-nbd icon-nbd-fomat-search"></i>
    </div>
    <div class="tab-main tab-scroll">
            
            <div class="result-loaded">
                <div class="content-items">
                    <div ng-class="settings['nbdesigner_upload_show_term'] !== 'yes' ? 'accept' : '' " class="content-item type-upload" data-type="image-upload">
                        <div ng-show="settings.nbdesigner_upload_designs_php_logged_in == 'yes' && !settings.is_logged">
                            <p><?php esc_html_e('You need to be logged in to upload images!','web-to-print-online-designer'); ?></p>
                            <button class="nbd-button nbd-hover-shadow" ng-click="login()"><?php esc_html_e('Login','web-to-print-online-designer'); ?></button>
                        </div>
                        <div ng-hide="settings.nbdesigner_upload_designs_php_logged_in == 'yes' && !settings.is_logged">
                            <div class="item select-bg-btn" ng-click="openPhotoAlbum()" data-type="photo-album" data-api="false" style="text-align: left;">
                                <button type="button" class="nbd-select-bg-btn"><?php esc_html_e('选择图片背景','web-to-print-online-designer'); ?></button>
                            </div>
                            <!-- 
                            <div class="clear-local-images-wrap" style="margin-left: 12px;">
                                <button type="button" class="nbd-clear-btn" ng-click="clearBackgroundFiles()">
                                    <?php //esc_html_e('清除所有图片','web-to-print-online-designer'); ?>
                                </button>
                            </div>
                            <div class="nbd-term" ng-if="settings['nbdesigner_upload_show_term'] == 'yes'">
                                <div class="nbd-checkbox">
                                    <input id="accept-term" type="checkbox">
                                    <label for="accept-term">&nbsp;</label>
                                </div>
                                <span class="term-read"><?php //esc_html_e('I accept the terms','web-to-print-online-designer'); ?></span>
                            </div>
                            <div id="nbd-background-wrap" ng-show="resource.background.data.length > 0">
                                <div class="mansory-wrap">
                                    <div nbd-drag="img.url" nbd-img="img" extenal="false" type="image" class="mansory-item" ng-click="resource.addImageContext = 'manual'; setBackgroundUrl(img.url);" ng-repeat="img in resource.background.data track by $index" repeat-end="onEndRepeat('background')"><img ng-src="{{img.url}}"></div>
                                </div>
                            </div>
                             -->
                        </div>
                        <div>
                            <span class="heading-title"><?php esc_html_e('Background colors','web-to-print-online-designer'); ?></span>
                            <div class="nbes-colors">
                                <div class="nbes-color bg-color" ng-repeat="bg_code in settings.nbes_settings.background_colors.codes track by $index">
                                    <div ng-style="{'background-color': bg_code}" class="bg_color" 
                                            ng-click="_changeBackgroundCanvas($index)"
                                            title="{{settings.nbes_settings.background_colors.names[$index]}}">
                                        <span ng-style="{'color': bg_code}"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="color-picker-wrapper">
                        <div class="color-picker-label">选择颜色背景</div>
                        <input type="color" class="color-bar" ng-model="currentColor" ng-change="changeBackgroundCanvas(currentColor)">
                    </div>
                    <?php do_action('nbd_modern_sidebar_photo_images'); ?>
                </div>
                <!--
                <div class="nbdesigner-gallery" id="nbdesigner-gallery">
                    <div ng-if="!resource.background.data || (resource.background.data && resource.background.data.length == 0)" class="nbdesigner-default-image">
                        <img src="https://www.dujiayoupin.com/wp-content/uploads/2025/01/placeholder-289.png" alt="暂无图片" style="width: 120px; opacity: 0.5;">
                        <div style="color: #999; margin-top: 8px;">暂无图片</div>
                    </div>              
                </div>
                <div class="loading-photo" ng-show="resource.background.onload">
                    <svg class="circular" viewBox="25 25 50 50">
                        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                    </svg>
                </div>
                <div class="tab-load-more" ng-show="!resource.background.onload && resource.background.data.length && (resource.background.filter.totalPage == 0 || resource.background.filter.currentPage < resource.background.filter.totalPage)">
                    <a class="nbd-button" ng-click="scrollLoadMore('#tab-background', 'background')"><?php esc_html_e('Load more','web-to-print-online-designer'); ?></a>
                </div>
                -->
            </div>
    </div>
</div>

<style>
.nbd-select-bg-btn {
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

.nbd-select-bg-btn:hover {
    background: #f8f9fa;
    border-color: #ccc;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.heading-title {
    font-size: 16px;
    font-weight: 600;
    margin: 20px 0 15px;
    display: block;
}
.nbes-colors {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    justify-content: space-between;
    margin-left: 12px;
    padding-left: 0;
}
.color-picker-wrapper {
    display: flex;
    align-items: center;
    margin: 20px 12px;
}
.color-picker-label {
    font-size: 14px;
    color: #333;
    margin-right: 12px;
    white-space: nowrap;
}
.color-bar {
    width: 200px;
    height: 32px;
    border-radius: 4px;
    border: 1px solid #ddd;
    cursor: pointer;
    padding: 0;
    transition: all 0.3s ease;
}
.color-bar:hover {
    border-color: #999;
}
</style>

<script>
angular.module('nbdesigner').controller('nbdesignerController', ['$scope', '$document', function($scope, $document) {
    $document.on('click', function(event) {
        if (!angular.element(event.target).closest('.color-picker-wrapper').length) {
            $scope.$apply(function() {
                $scope.showColorPicker = false;
            });
        }
    });
    
    $scope.closeColorPicker = function() {
        $scope.showColorPicker = false;
    };

    $scope.$on('$locationChangeStart', function() {
        $scope.showColorPicker = false;
    });
    
    function closeColorPicker(event) {
        var colorPicker = angular.element(event.target).closest('.nbd-text-color-picker');
        var colorBar = angular.element(event.target).closest('.color-bar');
        if (!colorPicker.length && !colorBar.length) {
            $scope.$apply(function() {
                $scope.showColorPicker = false;
            });
        }
    }
    
    $scope.$watch('showColorPicker', function(newVal) {
        if (newVal) {
            $document.on('click', closeColorPicker);
        } else {
            $document.off('click', closeColorPicker);
        }
    });
}]);
</script>