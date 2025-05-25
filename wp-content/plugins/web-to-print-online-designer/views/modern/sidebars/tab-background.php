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
                            
                            <!-- 当前背景图片 -->
                            <div class="recent-photos-section">
                                <h3 class="section-title">
                                    当前背景
                                    <div class="background-tip" ng-click="showBackgroundTip()">
                                        <i class="icon-nbd icon-nbd-info-circle"></i>
                                    </div>
                                </h3>
                                <div id="background-tip-text" class="nbd-tooltip-content" style="display: none;">
                                    <div class="tooltip-content">
                                        <div class="tooltip-header">
                                            <h4>背景说明</h4>
                                            <i class="icon-nbd icon-nbd-clear" ng-click="hideBackgroundTip()"></i>
                                        </div>
                                        <div class="tooltip-body">
                                            图片背景覆盖在颜色背景之上，包含透明区域的图片才能和颜色背景同时显示
                                        </div>
                                    </div>
                                </div>
                                <div class="recent-photos-grid">
                                    <div class="recent-photo-item" ng-if="stages[currentStage].canvas.backgroundImage">
                                        <img ng-src="{{stages[currentStage].canvas.backgroundImage._element.src}}" alt="当前背景">
                                        <div class="photo-info">
                                            <span class="photo-name">清除背景图片</span>
                                        </div>
                                        <div class="photo-action" ng-click="setBackgroundInner('')">
                                            <i class="icon-nbd icon-nbd-delete"></i>
                                        </div>
                                    </div>
                                    <div class="recent-photo-item no-image" ng-if="!stages[currentStage].canvas.backgroundImage">
                                        <div class="no-image-icon">
                                            <i class="icon-nbd icon-nbd-image"></i>
                                            <span>暂无背景图片</span>
                                        </div>
                                    </div>
                                    
                                    <!-- 当前颜色背景 -->
                                    <div class="recent-photo-item" ng-if="stages[currentStage].canvas.backgroundColor">
                                        <div class="color-bg" ng-style="{'background-color': stages[currentStage].canvas.backgroundColor}"></div>
                                        <div class="photo-info">
                                            <span class="photo-name">清除颜色背景</span>
                                        </div>
                                        <div class="photo-action" ng-click="changeBackgroundCanvas('')">
                                            <i class="icon-nbd icon-nbd-delete"></i>
                                        </div>
                                    </div>
                                    <div class="recent-photo-item no-image" ng-if="!stages[currentStage].canvas.backgroundColor">
                                        <div class="no-image-icon">
                                            <i class="icon-nbd icon-nbd-color"></i>
                                            <span>暂无颜色背景</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="divider"></div>
                    <h3 class="color-palette-label"><?php esc_html_e('颜色背景','web-to-print-online-designer'); ?></h3>
                    <div>
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
                    <h3 class="color-palette-label"><?php esc_html_e('调色板','web-to-print-online-designer'); ?></h3>
                    <div class="color-picker-wrapper">
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
    font-size: 14px;
    font-weight: 450;
    color: #333;
    margin: 12px 12px 8px;
    display: block;
    text-align: left;
}
.nbes-colors {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    justify-content: space-between;
    margin-left: 12px;
    padding-left: 0;
}
.color-palette-label {
    font-size: 14px;
    font-weight: 450;
    color: #333;
    margin: 12px 12px 8px;
    text-align: left;
}
.color-picker-wrapper {
    display: flex;
    align-items: center;
    margin: 0 12px 12px;
}
.color-picker-label {
    font-size: 14px;
    font-weight: 450;
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
.divider {
    height: 1px;
    background-color: #fff;
    margin: 12px 12px;
}
.recent-images {
    margin: 12px 12px;
}
.recent-images-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-top: 8px;
}
.recent-image-item {
    aspect-ratio: 1;
    border-radius: 4px;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid #ddd;
    transition: all 0.3s ease;
}
.recent-image-item:hover {
    border-color: #999;
    transform: scale(1.02);
}
.recent-image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* 当前背景图片样式 */
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
    display: flex;
    align-items: center;
}

.recent-photos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    padding: 0;
    position: relative;
    background: transparent;
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
    text-align: center;
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

.photo-action {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    cursor: pointer;
}

.photo-action i {
    color: #fff;
    font-size: 20px;
}

.recent-photo-item:hover .photo-action {
    opacity: 1;
}

.photo-action:hover {
    background: rgba(0, 0, 0, 0.7);
    transform: translate(-50%, -50%) scale(1.1);
}

/* 无图片状态样式 */
.recent-photo-item.no-image {
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px dashed #ddd;
}

.no-image-icon {
    text-align: center;
    color: #999;
}

.no-image-icon i {
    font-size: 24px;
    margin-bottom: 8px;
    display: block;
}

.no-image-icon span {
    font-size: 12px;
    display: block;
}

/* 颜色背景样式 */
.color-bg {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
}

.recent-photo-item .icon-nbd-color {
    font-size: 24px;
    margin-bottom: 8px;
    display: block;
    color: #999;
}

/* 帮助图标样式 */
.icon-nbd-info-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    color: #666;
    font-size: 16px;
    margin-left: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    vertical-align: middle;
    position: relative;
    top: -1px;
}

.icon-nbd-info-circle:hover {
    color: #333;
}

.nbd-hover-shadow:hover {
    text-shadow: 0 0 3px rgba(0,0,0,0.2);
}

/* Tooltipster 样式覆盖 */
.tooltipster-sidetip.tooltipster-borderless .tooltipster-box {
    border: none;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.tooltipster-sidetip.tooltipster-borderless .tooltipster-content {
    color: #fff;
    font-size: 12px;
    line-height: 1.5;
    padding: 8px 12px;
}

.tooltipster-sidetip.tooltipster-borderless .tooltipster-arrow {
    height: 6px;
    margin-left: -4px;
    width: 12px;
}

.tooltipster-sidetip.tooltipster-borderless .tooltipster-arrow-border {
    border: 6px solid transparent;
}

.tooltipster-sidetip.tooltipster-borderless.tooltipster-right .tooltipster-arrow-border {
    border-right-color: rgba(0, 0, 0, 0.8);
}

.tooltipster-sidetip.tooltipster-borderless .tooltipster-arrow-background {
    display: none;
}

/* 提示框样式 */
.background-tip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-left: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.background-tip .icon-nbd-info-circle {
    font-size: 16px;
    color: #666;
    transition: all 0.3s ease;
}

.background-tip:hover .icon-nbd-info-circle {
    color: #333;
    text-shadow: 0 0 3px rgba(0,0,0,0.2);
}

.nbd-tooltip-content {
    position: fixed;
    z-index: 9999;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    min-width: 280px;
    max-width: 320px;
    display: block !important;
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    pointer-events: none;
}

.tooltip-content {
    padding: 12px;
}

.tooltip-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.tooltip-header h4 {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.tooltip-header .icon-nbd-clear {
    font-size: 16px;
    color: #999;
    cursor: pointer;
    transition: all 0.3s ease;
}

.tooltip-header .icon-nbd-clear:hover {
    color: #333;
}

.tooltip-body {
    font-size: 12px;
    line-height: 1.5;
    color: #666;
    text-align: left;
    padding: 0;
    margin: 0;
}

.nbd-tooltip-content.show {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
</style>

<script>
angular.module('nbdesigner').controller('nbdesignerController', ['$scope', '$document', '$timeout', function($scope, $document, $timeout) {
    // $document.on('click', function(event) {
    //     console.log('document click');
    //     if (!angular.element(event.target).closest('.color-picker-wrapper').length) {
    //         $scope.$apply(function() {
    //             $scope.showColorPicker = false;
    //         });
    //     }
    // });
    
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
    
    // $scope.$watch('showColorPicker', function(newVal) {
    //     if (newVal) {
    //         $document.on('click', closeColorPicker);
    //     } else {
    //         $document.off('click', closeColorPicker);
    //     }
    // });

    // 初始化tooltip
    $timeout(function() {
        console.log('开始初始化tooltip');
        jQuery('.nbd-tooltip-hover').tooltipster({
            theme: 'tooltipster-borderless',
            side: 'right',
            animation: 'fade',
            delay: 200,
            distance: 10,
            contentAsHTML: true,
            interactive: true,
            trigger: 'hover',
            maxWidth: 300
        });
    });

    

}]);
</script>