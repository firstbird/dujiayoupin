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
                            <div class="nbd-progress-bar">
                                <div class="nbd-progress-bar-inner" ng-style="{'width': resource.upload.progressBar + '%'}">
                                    <span class="indicator" ng-style="{'left': 'calc(' + resource.upload.progressBar + '% - 15px)'}">{{resource.upload.progressBar}}</span>
                                </div>
                            </div>
                            <div class="item" ng-click="openPhotoAlbum()" data-type="photo-album" data-api="false">
                                <div class="main-item">
                                    <div class="item-icon"><i class="icon-nbd icon-nbd-photo-album"></i></div>
                                    <div class="item-info">
                                        <span class="item-name" title="我的相册"><?php esc_html_e('我的相册','web-to-print-online-designer'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="allow-size">
                                <span><?php esc_html_e('Accept file types','web-to-print-online-designer'); ?>: <strong><?php echo is_available_imagick() ? 'png, jpg, svg, pdf' : 'png, jpg, svg'; ?></strong></span>
                                <span><?php esc_html_e('Max file size','web-to-print-online-designer'); ?>: <strong>{{settings['nbdesigner_maxsize_upload']}} MB</strong></span>
                                <span><?php esc_html_e('Min file size','web-to-print-online-designer'); ?>: <strong>{{settings['nbdesigner_minsize_upload']}} MB</strong></span>
                            </div>
                            <div class="clear-local-images-wrap" style="margin-left: 12px;">
                                <button type="button" class="nbd-clear-btn" ng-click="clearBackgroundFiles()">
                                    <?php esc_html_e('清除所有图片','web-to-print-online-designer'); ?>
                                </button>
                            </div>
                                <div class="nbd-term" ng-if="settings['nbdesigner_upload_show_term'] == 'yes'">
                                <div class="nbd-checkbox">
                                    <input id="accept-term" type="checkbox">
                                    <label for="accept-term">&nbsp;</label>
                                </div>
                                <span class="term-read"><?php esc_html_e('I accept the terms','web-to-print-online-designer'); ?></span>
                            </div>
                            <div id="nbd-background-wrap" ng-show="resource.background.data.length > 0">
                                <div class="mansory-wrap">
                                    <div nbd-drag="img.url" nbd-img="img" extenal="false" type="image" class="mansory-item" ng-click="resource.addImageContext = 'manual'; setBackgroundUrl(img.url);" ng-repeat="img in resource.background.data track by $index" repeat-end="onEndRepeat('background')"><img ng-src="{{img.url}}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php do_action('nbd_modern_sidebar_photo_images'); ?>
                </div>
                <div class="nbdesigner-gallery" id="nbdesigner-gallery">
                    <!-- 没有图片时显示默认图片 -->
                    <div ng-if="!resource.background.data || (resource.background.data && resource.background.data.length == 0)" class="nbdesigner-default-image">
                        <img src="https://www.dujiayoupin.com/wp-content/uploads/2025/01/placeholder-289.png" alt="暂无图片" style="width: 120px; opacity: 0.5;">
                        <div style="color: #999; margin-top: 8px;">暂无图片</div>
                    </div>
                    <!-- 有图片时正常显示 -->
              
                </div>
                <div class="loading-photo" ng-show="resource.background.onload">
                    <svg class="circular" viewBox="25 25 50 50">
                        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                    </svg>
                </div>
                <div class="tab-load-more" ng-show="!resource.background.onload && resource.background.data.length && (resource.background.filter.totalPage == 0 || resource.background.filter.currentPage < resource.background.filter.totalPage)">
                    <a class="nbd-button" ng-click="scrollLoadMore('#tab-background', 'background')"><?php esc_html_e('Load more','web-to-print-online-designer'); ?></a>
                </div>
            </div>
    </div>
</div>