<div class="<?php if( $active_backgrounds ) echo 'active'; ?> tab" ng-if="settings['nbdesigner_enable_image'] == 'yes'" id="tab-photo" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-photo" data-type="background" data-offset="30">
    <div class="nbd-search">
        <input ng-class="(resource.personal.status || !resource.photo.onclick) ? 'nbd-disabled' : ''" ng-keyup="$event.keyCode == 13 && getPhoto(resource.photo.type, 'search')" type="text" name="search" placeholder="<?php esc_html_e('Search photo', 'web-to-print-online-designer'); ?>" ng-model="resource.photo.photoSearch"/>
        <i class="icon-nbd icon-nbd-fomat-search"></i>
    </div>
    <div class="tab-main tab-scroll">
        <div class="nbd-items-dropdown">
            <div class="main-items">
                <div class="items">
                    <div class="item" ng-click="onClickTab('upload', 'photo')" ng-if="settings['nbdesigner_enable_upload_image'] == 'yes'" data-type="image-upload" data-api="false">
                        <div class="main-item">
                            <div class="item-icon"><i class="icon-nbd icon-nbd-file-upload"></i></div>
                            <div class="item-info">
                                <span class="item-name" title="Image upload"><?php esc_html_e('Upload','web-to-print-online-designer'); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <?php do_action('nbd_modern_sidebar_photo_icons'); ?>
                </div>
                <div class="pointer"></div>
            </div>
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
                            <div class="form-upload nbd-dnd-file" nbd-dnd-file="setBackgroundFile(files[0])">
                                <i class="icon-nbd icon-nbd-cloud-upload"></i>
                                <span><?php esc_html_e('点击或拖动图片上传','web-to-print-online-designer'); ?></span>
                                <input type="file" 
                                    <?php echo is_available_imagick() ? 'accept="image/*, .pdf"' : 'accept="image/*"'; ?> 
                                    style="display: none;"/>
                            </div>
                            <div class="allow-size">
                                <span><?php esc_html_e('Accept file types','web-to-print-online-designer'); ?>: <strong><?php echo is_available_imagick() ? 'png, jpg, svg, pdf' : 'png, jpg, svg'; ?></strong></span>
                                <span><?php esc_html_e('Max file size','web-to-print-online-designer'); ?>: <strong>{{settings['nbdesigner_maxsize_upload']}} MB</strong></span>
                                <span><?php esc_html_e('Min file size','web-to-print-online-designer'); ?>: <strong>{{settings['nbdesigner_minsize_upload']}} MB</strong></span>
                            </div>
                            <div class="nbd-term" ng-if="settings['nbdesigner_upload_show_term'] == 'yes'">
                                <div class="nbd-checkbox">
                                    <input id="accept-term" type="checkbox">
                                    <label for="accept-term">&nbsp;</label>
                                </div>
                                <span class="term-read"><?php esc_html_e('I accept the terms','web-to-print-online-designer'); ?></span>
                            </div>
                            <div id="nbd-upload-wrap">
                                <div class="mansory-wrap">
                                    <div nbd-drag="img.url" nbd-img="img" extenal="false" type="image" class="mansory-item" ng-click="resource.addImageContext = 'manual'; setBackgroundUrl(img.url);" ng-repeat="img in resource.upload.data track by $index" repeat-end="onEndRepeat('upload')"><img ng-src="{{img.url}}"></div>
                                </div>
                            </div>
                            <div class="clear-local-images-wrap" ><span ng-click="_localStorage.delete('nbduploaded')"><?php esc_html_e('Clear all uploaded images','web-to-print-online-designer'); ?></span></div>
                        </div>
                    </div>
                    
                    <?php do_action('nbd_modern_sidebar_photo_images'); ?>
                </div>
                <div class="nbdesigner-gallery" id="nbdesigner-gallery">
                    <div nbd-drag="img.url" extenal="true" type="image" class="nbdesigner-item" ng-click="resource.addImageContext = 'manual'; setBackgroundUrl(img.url)" ng-repeat="img in resource.photo.data" repeat-end="onEndRepeat('photo')"><img ng-src="{{img.preview}}"><span class="photo-desc">{{img.des}}</span></div>
                </div>
                <div class="loading-photo" >
                    <svg class="circular" viewBox="25 25 50 50">
                        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                    </svg>
                </div>
                <div class="tab-load-more" style="display: none;" ng-show="!resource.photo.onload && resource.photo.data.length && (resource.photo.filter.totalPage == 0 || resource.photo.filter.currentPage < resource.photo.filter.totalPage)">
                    <a class="nbd-button" ng-click="scrollLoadMore('#tab-photo', 'photo')"><?php esc_html_e('Load more','web-to-print-online-designer'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>