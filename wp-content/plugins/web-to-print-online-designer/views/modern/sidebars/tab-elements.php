<div class="<?php if( $active_elements ) echo 'active'; ?> tab" id="tab-element" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-element" data-type="element" data-offset="20">
    <!-- <div class="nbd-search">
        <input ng-class="(!(resource.element.type == 'icon' || resource.element.type == 'flaticon' || resource.element.type == 'storyset') || !resource.element.onclick) ? 'nbd-disabled' : ''" ng-keyup="$event.keyCode == 13 && getMedia(resource.element.type, 'search')" type="text" name="search" placeholder="<?php esc_html_e('Search element', 'web-to-print-online-designer'); ?>" ng-model="resource.element.contentSearch"/>
        <i class="icon-nbd icon-nbd-fomat-search"></i>
    </div>      -->
    <!-- <div class="tab-main tab-scroll" > -->
        <div class="elements-content tab-scroll">
            <div class="main-items">
                <div class="items">
                    <!-- 已删除 Draw, Shapes, Icons 按钮 -->
                </div>
                <div class="pointer"></div>
            </div>
            <div class="result-loaded">
                <div class="content-items">
                    <div class="content-item type-draw" data-type="draw">
                        <div class="main-type">
                            <div class="free-draw-settings">
                                <span class="section-title"><?php esc_html_e('画笔','web-to-print-online-designer'); ?></span>
                                <div class="draw-item" ng-class="{'active': resource.drawMode.status}" ng-click="onSwitchDrawMode()" title="<?php esc_html_e('Free Draw','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd icon-nbd-drawing"></i>
                                </div>
                                <!-- <div class="brush" >
                                    <h3 class="color-palette-label" ><?php esc_html_e('Choose ','web-to-print-online-designer'); ?></h3>
                                    <button class="nbd-button nbd-dropdown">
                                        <?php esc_html_e('Brush','web-to-print-online-designer'); ?> <i class="icon-nbd icon-nbd-arrow-drop-down"></i>
                                        <div class="nbd-sub-dropdown" data-pos="left">
                                            <ul class="tab-scroll">
                                                <li ng-click="resource.drawMode.brushType = 'Pencil';changeBush()" ng-class="resource.drawMode.brushType == 'Pencil' ? 'active' : ''"><span><?php esc_html_e('Pencil','web-to-print-online-designer'); ?></span></li>
                                                <li ng-click="resource.drawMode.brushType = 'Circle';changeBush()" ng-class="resource.drawMode.brushType == 'Circle' ? 'active' : ''"><span><?php esc_html_e('Circle','web-to-print-online-designer'); ?></span></li>
                                                <li ng-click="resource.drawMode.brushType = 'Spray';changeBush()" ng-class="resource.drawMode.brushType == 'Spray' ? 'active' : ''"><span><?php esc_html_e('Spray','web-to-print-online-designer'); ?></span></li>
                                            </ul>
                                        </div>
                                    </button>
                                </div> -->
                                <div class="range">
                                    <div class="section-title"><?php esc_html_e('画笔宽度','web-to-print-online-designer'); ?></div>
                                    <div class="main-track">
                                        <input class="slide-input" type="range" step="1" min="1" max="100" ng-change="changeBush()" ng-model="resource.drawMode.brushWidth">
                                    </div>
                                    <span class="value-display">{{resource.drawMode.brushWidth}}</span>
                                </div>
                                <div class="color">
                                    <span class="section-title"><?php esc_html_e('画笔颜色','web-to-print-online-designer'); ?></span>
                                    <ul class="main-color-palette nbd-perfect-scroll" >
                                        <li class="color-palette-add" ng-init="showBrushColorPicker = false" ng-click="showBrushColorPicker = !showBrushColorPicker;" ng-style="{'background-color': currentColor}"></li>
                                        <li ng-repeat="color in listAddedColor track by $index" ng-click="resource.drawMode.brushColor=color; changeBush()" class="color-palette-item" data-color="{{color}}" title="{{color}}" ng-style="{'background-color': color}"></li>
                                    </ul>
                                    <div class="pinned-palette default-palette" >
                                        <!-- <h3 class="color-palette-label" ><?php esc_html_e('Default palette','web-to-print-online-designer'); ?></h3>
                                        <ul class="main-color-palette" ng-repeat="palette in resource.defaultPalette" >
                                            <li ng-class="{'first-left': $first, 'last-right': $last}" ng-repeat="color in palette track by $index" ng-click="resource.drawMode.brushColor=color; changeBush()" class="color-palette-item" data-color="{{color}}" title="{{color}}" ng-style="{'background': color}"></li>
                                        </ul> -->
                                    </div>
                                    <div class="nbd-text-color-picker" id="nbd-bg-color-picker" ng-class="showBrushColorPicker ? 'active' : ''" >
                                        <spectrum-colorpicker
                                            ng-model="currentColor"
                                            options="{
                                                    preferredFormat: 'hex',
                                                    color: '#fff',
                                                    flat: true,
                                                    showButtons: false,
                                                    showInput: true,
                                                    containerClassName: 'nbd-sp'
                                            }">
                                        </spectrum-colorpicker>
                                        <div style="text-align: <?php echo (is_rtl()) ? 'right' : 'left'?>">
                                            <button class="nbd-button" ng-click="addColor();changeBush(currentColor);showBrushColorPicker = false;"><?php esc_html_e('Choose','web-to-print-online-designer'); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="nbd-color-palette-inner" >
                                    <div class="working-palette" ng-if="settings['nbdesigner_show_all_color'] == 'yes'" >
                                        <ul class="main-color-palette tab-scroll">
                                            <li class="color-palette-item color-palette-add" ng-click="stageBgColorPicker.status = !stageBgColorPicker.status;" ></li>
                                            <li ng-repeat="color in listAddedColor track by $index"
                                                ng-click="changeBackgroundCanvas(color)"
                                                class="color-palette-item"
                                                data-color="{{color}}"
                                                title="{{color}}"
                                                ng-style="{'background-color': color}">
                                            </li>
                                        </ul>
                                        <div class="nbd-text-color-picker" id="nbd-stage-bg-color-picker" ng-class="stageBgColorPicker.status ? 'active' : ''">
                                            <spectrum-colorpicker
                                                ng-model="stageBgColorPicker.currentColor"
                                                options="{
                                                preferredFormat: 'hex',
                                                color: '#fff',
                                                flat: true,
                                                showButtons: false,
                                                showInput: true,
                                                containerClassName: 'nbd-sp'
                                                }">
                                            </spectrum-colorpicker>
                                            <div>
                                                <button class="nbd-button"
                                                    ng-click="changeBackgroundCanvas(stageBgColorPicker.currentColor);">
                                                        <?php esc_html_e('Choose', 'web-to-print-online-designer'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pinned-palette default-palette" ng-if="settings['nbdesigner_show_all_color'] == 'yes'">
                                        <h3 class="color-palette-label default" ><?php esc_html_e('Default palette', 'web-to-print-online-designer'); ?></h3>
                                        <ul class="main-color-palette tab-scroll" ng-repeat="palette in resource.defaultPalette">
                                            <li ng-class="{'first-left': $first, 'last-right': $last}"
                                                ng-repeat="color in palette track by $index"
                                                ng-click="changeBackgroundCanvas(color)"
                                                class="color-palette-item"
                                                data-color="{{color}}"
                                                title="{{color}}"
                                                ng-style="{'background': color}">
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="pinned-palette default-palette" ng-if="settings['nbdesigner_show_all_color'] == 'no'">
                                        <h3 class="color-palette-label"><?php esc_html_e('Color palette', 'web-to-print-online-designer'); ?></h3>
                                        <ul class="main-color-palette settings" >
                                            <li ng-repeat="color in __colorPalette track by $index" ng-class="{'first-left': $first, 'last-right': $last}" ng-click="changeBackgroundCanvas(color)" class="color-palette-item" data-color="{{color}}" title="{{color}}" ng-style="{'background': color}"></li>
                                        </ul>
                                    </div>
                                    <div><button class="nbd-button" ng-click="removeBackgroundCanvas()"><?php esc_html_e('Remove background', 'web-to-print-online-designer'); ?></button></div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div ng-if="!showSubPage">
                        <div class="element-section">
                            <div class="section-header">
                                <span class="section-title">形状</span>
                                <span class="section-more" ng-click="openSubPage('shape')">更多 ></span>
                            </div>
                            <div class="section-list">
                                <div class="draw-item" ng-click="addGeometricalObject( 'circle' )" title="<?php esc_html_e('Circle','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd icon-nbd-layer-circle"></i>
                                </div>
                                <div class="draw-item" ng-click="addGeometricalObject( 'triangle' )" title="<?php esc_html_e('Triangle','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd icon-nbd-layer-triangle"></i>
                                </div>
                                <div class="draw-item" ng-click="addGeometricalObject( 'rect' )" title="<?php esc_html_e('Rectangle','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd icon-nbd-square" style="font-size: 14px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.87);"></i>
                                </div>
                                <div class="draw-item" ng-click="addGeometricalObject( 'roundedRect' )" title="<?php esc_html_e('Rounded Rectangle','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd icon-nbd-layer-rect" style="border-radius: 8px;"></i>
                                </div>
                                <div class="draw-item" ng-click="addGeometricalObject( 'hexagon' )" title="<?php esc_html_e('Hexagon','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd icon-nbd-layer-polygon"></i>
                                </div>
                                <div class="draw-item" ng-click="addGeometricalObject( 'line' )" title="<?php esc_html_e('Line','web-to-print-online-designer'); ?>">
                                    <i class="icon-nbd" style="font-size: 14px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(0.87); width: 50px; height: 6px; background: currentColor;"></i>
                                </div>
                            </div>
                            <!-- <div class="section-list">
                                <div class="section-item" ng-repeat="art in resource.shape.data | limitTo:3" ng-click="addSvgFromMedia(art)">
                                    <img ng-src="{{art.url}}" alt="{{art.name}}">
                                </div>
                            </div> -->
                        </div>
                        <div class="element-section">
                            <div class="section-header">
                                <span class="section-title">图标</span>
                                <span class="section-more" ng-click="openSubPage('icon')">更多 ></span>
                            </div>
                            <div class="section-list">
                                <div class="section-item" ng-repeat="art in resource.icon.data | limitTo:3" ng-click="addSvgFromMedia(art)">
                                    <img ng-src="{{art.url}}" alt="{{art.name}}">
                                </div>
                            </div>
                        </div>
                        <div class="element-section">
                            <div class="section-header">
                                <span class="section-title">插画</span>
                                <span class="section-more" ng-click="openSubPage('illustration')">更多 ></span>
                            </div>
                            <div class="section-list">
                                <div class="section-item" ng-repeat="art in resource.illustration.data | limitTo:3" ng-click="addSvgFromMedia(art)">
                                    <img ng-src="{{art.url}}" alt="{{art.name}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-item type-flaticon" data-type="flaticon" id="nbd-flaticon-wrap">
                        <div class="mansory-wrap">
                            <div nbd-drag="art.url" extenal="true" type="svg" class="mansory-item" ng-click="addArt(art, true, true)" ng-repeat="art in resource.flaticon.data" repeat-end="onEndRepeat('flaticon')"><img ng-src="{{art.url}}"><span class="photo-desc">{{art.name}}</span></div>
                        </div>
                    </div>
                    <div class="content-item type-storyset" data-type="storyset" id="nbd-storyset-wrap">
                        <div class="mansory-wrap">
                            <div nbd-drag="art.url" extenal="true" type="svg" class="mansory-item" ng-click="addArt(art, true, true)" ng-repeat="art in resource.storyset.data" repeat-end="onEndRepeat('storyset')"><img ng-src="{{art.url}}"><span class="photo-desc">{{art.name}}</span></div>
                        </div>
                    </div>
                    <div class="content-item type-lines" data-type="lines" id="nbd-line-wrap">
                        <div class="mansory-wrap">
                            <div nbd-drag="art.url" extenal="true" type="svg" class="mansory-item" ng-click="addSvgFromMedia(art)" ng-repeat="art in resource.line.data" repeat-end="onEndRepeat('line')"><img ng-src="{{art.url}}"><span class="photo-desc">{{art.name}}</span></div>
                        </div>
                    </div>
                    <div class="content-item type-qrcode" data-type="qr-code">
                        <div class="main-type">
                            <div class="main-input">
                                <input ng-model="resource.qrText" type="text" class="nbd-input input-qrcode" name="qr-code" placeholder="https://yourcompany.com">
                            </div>
                            <button ng-class="resource.qrText != '' ? '' : 'nbd-disabled'" class="nbd-button" ng-click="addQrCode()"><?php esc_html_e('Create QRCode','web-to-print-online-designer'); ?></button>
                            <button ng-class="resource.qrText != '' ? '' : 'nbd-disabled'" class="nbd-button" ng-click="addBarCode()"><?php esc_html_e('Create BarCode','web-to-print-online-designer'); ?></button>
                            <div class="main-qrcode">
                                
                            </div>
                            <svg id="barcode" ></svg>
                        </div>
                    </div>
                    <div ng-if="settings['nbdesigner_enable_vcard'] == 'yes'" class="content-item type-vcard" data-type="vcard">
                        <p><?php esc_html_e('Your information','web-to-print-online-designer'); ?></p>
                        <div ng-repeat="field in settings.vcard_fields" class="md-input-wrap">
                            <input id="vf-{{field.key}}" ng-model="field.value" ng-class="field.value.length > 0 ? 'holder' : ''"/>
                            <label for="vf-{{field.key}}" >{{field.name}}<label/>
                        </div>
                        <p>
                            <span class="nbd-button" ng-click="generateVcard()"><?php esc_html_e('Generate','web-to-print-online-designer'); ?></span>
                        </p>
                    </div>
                    <div class="content-item type-frame" data-type="frame">
                        <div class="frames-wrapper">
                            <div class="frame-wrap" ng-repeat="frame in resource.frames track by $index" ng-click="addFrame(frame)">
                                <photo-frame data-frame="frame"></photo-frame>
                            </div>
                        </div>
                    </div>
                    <div class="content-item type-photoFrame" data-type="photoFrame" id="nbd-photoFrame-wrap">
                        <div class="mansory-wrap">
                            <div class="mansory-item" ng-click="addPhotoFrame(frame)" ng-repeat="frame in [] | range: ( resource.photoFrame.filter.currentPage * resource.photoFrame.filter.perPage > resource.photoFrame.filter.total ? resource.photoFrame.filter.total : ( resource.photoFrame.filter.currentPage * resource.photoFrame.filter.perPage ) )" repeat-end="onEndRepeat('photoFrame')">
                                <img ng-src="{{'//dpeuzbvf3y4lr.cloudfront.net/frames/preview/f' + ( frame + 1 ) + '.png'}}" alt="Photo Frame" />
                            </div>
                        </div>
                    </div>
                    <?php if( $task == 'create' || ( $task == 'edit' && $design_type == 'template' ) ): ?>
                    <div class="content-item type-image-shape" data-type="image-shape">
                        <div class="image-shape-wrapper">
                            <div>
                                <span class="shape_mask shape-type-{{n}}" ng-click="addMask(n)" ng-repeat="n in [] | range:25"></span>
                            </div>
                            <div class="custom_image_shape-wrapper">
                                <div><?php esc_html_e('Custom Shape','web-to-print-online-designer'); ?></div>
                                <textarea class="form-control hover-shadow nbdesigner_svg_code" rows="5" ng-change="getPathCommand()" ng-model="svgPath" placeholder="<?php esc_html_e('Enter svg code','web-to-print-online-designer'); ?>"/></textarea>
                                <button ng-class="pathCommand !='' ? '' : 'nbd-disabled'" class="nbd-button" ng-click="addMask(-1)"><?php esc_html_e('Aadd Shape','web-to-print-online-designer'); ?></button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div ng-if="settings['nbdesigner_enable_google_maps'] == 'yes' && settings['nbdesigner_static_map_api_key'] != ''" class="content-item type-maps" data-type="maps">
                        <div class="google-maps-options">
                            <div class="google-maps-search">
                                <input ng-keyup="$event.keyCode == 13 && updateMapUrl()" ng-model="resource.maps.address" type="text" name="search" placeholder="<?php esc_attr_e('Your address','web-to-print-online-designer'); ?>">
                                <i class="icon-nbd icon-nbd-fomat-search" ng-click="updateMapUrl()"></i>
                            </div>
                            <div class="google-maps-option">
                                <select id="google-maps-maptype" ng-change="updateMapUrl()" ng-model="resource.maps.maptype">
                                    <option value="roadmap"><?php esc_html_e('Roadmap','web-to-print-online-designer'); ?></option>
                                    <option value="satellite"><?php esc_html_e('Satellite','web-to-print-online-designer'); ?></option>
                                    <option value="terrain"><?php esc_html_e('Terrain','web-to-print-online-designer'); ?></option>
                                    <option value="hybrid"><?php esc_html_e('Hybrid','web-to-print-online-designer'); ?></option>
                                </select>
                                <label for="google-maps-maptype" ><?php esc_html_e('Map type','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-option">
                                <select id="google-maps-maptype" ng-change="updateMapUrl()" ng-model="resource.maps.zoom">
                                    <?php foreach( range(1, 20) as $range ): ?>
                                    <option value="<?php echo $range; ?>"><?php echo $range; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="google-maps-maptype" ><?php esc_html_e('Map zoom','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-option">
                                <input type="number" ng-min="100" ng-max="640" ng-step="1" ng-model="resource.maps.width" id="google-maps-width" />
                                <label for="google-maps-width" ><?php esc_html_e('Map width','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-option">
                                <input type="number" ng-min="100" ng-max="640" ng-step="1" ng-model="resource.maps.height" id="google-maps-height" />
                                <label for="google-maps-height" ><?php esc_html_e('Map height','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-option">
                                <select id="google-maps-markers-label" ng-change="updateMapUrl()" ng-model="resource.maps.markers.label">
                                    <?php 
                                        $marker_labels = array_merge( array(''), range( 0, 9 ), range( 'A', 'Z' ) );
                                        foreach( $marker_labels as $marker_label ): 
                                    ?>
                                    <option value="<?php echo $marker_label; ?>"><?php echo $marker_label === '' ? __('None','web-to-print-online-designer') : $marker_label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label for="google-maps-markers-label" ><?php esc_html_e('Marker label','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-option">
                                <select id="google-maps-markers-size" ng-change="updateMapUrl()" ng-model="resource.maps.markers.size">
                                    <option value="normal"><?php esc_html_e('Normal','web-to-print-online-designer'); ?></option>
                                    <option value="mid"><?php esc_html_e('Mid','web-to-print-online-designer'); ?></option>
                                    <option value="small"><?php esc_html_e('Small','web-to-print-online-designer'); ?></option>
                                </select>
                                <label for="google-maps-markers-size" ><?php esc_html_e('Marker size','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-option">
                                <select id="google-maps-markers-color" ng-change="updateMapUrl()" ng-model="resource.maps.markers.color">
                                    <option ng-style="{'background-color': color, color: '#fff'}" ng-repeat="color in resource.defaultPalette[0]" value="{{color}}">{{color}}</option>
                                </select>
                                <label for="google-maps-markers-color" ><?php esc_html_e('Marker color','web-to-print-online-designer'); ?><label/>
                            </div>
                            <div class="google-maps-preview google-maps-option" ng-class="resource.maps.loading ? 'loading' : ''" ng-if="resource.maps.url !=''">
                                <span class="nbd-button" ng-click="addImageFromUrl( resource.maps.url )"><?php esc_html_e('Insert map','web-to-print-online-designer'); ?></span>
                                <img ng-click="addImageFromUrl( resource.maps.url )" ng-src="{{resource.maps.url}}" title="<?php esc_html_e('Click to insert this map','web-to-print-online-designer'); ?>"/>
                                <div class="loading-maps" >
                                    <svg class="circular" viewBox="25 25 50 50">
                                        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nbdesigner-gallery" id="nbdesigner-gallery">
                </div>
                <!-- <div class="loading-photo" >
                    <svg class="circular" viewBox="25 25 50 50">
                        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                    </svg>
                </div> -->
            </div>
            <div class="info-support">
                <span>Facebook</span>
                <i class="icon-nbd icon-nbd-clear close-result-loaded" ng-click="onClickTab('', 'element')"></i>
            </div>
        </div>
    <!-- </div> -->
</div>

<!-- 子页面 -->
<div class="subpage" ng-if="showSubPage">
    <div class="subpage-header" style="display: flex; align-items: center; padding: 16px 12px 8px 12px; background: #3a495a; z-index: 9999; position: relative;">
        <span class="icon-back" ng-click="closeSubPage()" style="font-size:22px;margin-right:12px;cursor:pointer;color:#fff;display:inline-block;">&#8592;</span>
        <span class="subpage-title" style="color:#fff;font-size:18px;font-weight:bold;display:inline-block;">{{subPageTitle}}</span>
    </div>
    <div class="subpage-search">
        <input type="text" ng-model="subPageSearch" placeholder="搜索{{subPageTitle}}">
        <i class="icon-nbd icon-nbd-fomat-search"></i>
    </div>
    <div class="subpage-content">
        <!-- 形状：无子分组，直接展示全部 -->
        <div ng-if="subPageType === 'shape'">
            <div class="nbd-search">
                <input type="text" name="search" placeholder="<?php esc_html_e('Search shape', 'web-to-print-online-designer'); ?>" ng-model="resource.shape.filter.search"/>
                <i class="icon-nbd icon-nbd-fomat-search"></i>
            </div>
            <div class="content-item type-shape" data-type="shape" id="nbd-shape-wrap">  
                <div class="mansory-wrap">
                    <div nbd-drag="shape.url" extenal="true" type="svg" class="mansory-item" ng-click="addArt(shape, true, true)" ng-repeat="shape in resource.shape.filteredShapes | limitTo: resource.shape.filter.currentPage * resource.shape.filter.perPage" repeat-end="onEndRepeat('shape')">
                        <div class="mansory-item__inner">
                            <img ng-src="{{shape.url}}" /><span class="photo-desc">{{shape.name}}</span>
                            <span class="nbd-pro-mark-wrap" ng-if="$index > 20">
                                <svg class="nbd-pro-mark" fill="#F3B600" xmlns="http://www.w3.org/2000/svg" viewBox="-505 380 12 10"><path d="M-503 388h8v1h-8zM-494 382.2c-.4 0-.8.3-.8.8 0 .1 0 .2.1.3l-2.3.7-1.5-2.2c.3-.2.5-.5.5-.8 0-.6-.4-1-1-1s-1 .4-1 1c0 .3.2.6.5.8l-1.5 2.2-2.3-.8c0-.1.1-.2.1-.3 0-.4-.3-.8-.8-.8s-.8.4-.8.8.3.8.8.8h.2l.8 3.3h8l.8-3.3h.2c.4 0 .8-.3.8-.8 0-.4-.4-.7-.8-.7z"></path></svg>
                                <?php esc_html_e('Pro','web-to-print-online-designer'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <!-- 图标：有动物、植物分组 -->
        <div ng-if="subPageType === 'icon'">
            <div class="element-section" ng-repeat="cat in iconSubGroups">
                <div class="section-header">
                    <span class="section-title">{{cat.title}}</span>
                    <span class="section-more" ng-click="closeSubPage()">更多 ></span>
                </div>
                <div class="section-list">
                    <div class="section-item" ng-repeat="art in cat.items | filter:subPageSearch" ng-click="addSvgFromMedia(art)">
                        <img ng-src="{{art.url}}" alt="{{art.name}}">
                    </div>
                </div>
            </div>
            <!-- 调试信息 -->
            <div style="margin-top: 20px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
                <h4>调试信息：</h4>
                <pre>{{iconSubGroups | json}}</pre>
            </div>
        </div>
        <!-- 插画：有卡通、手绘分组 -->
        <div ng-if="subPageType === 'illustration'">
            <div class="element-section" ng-repeat="cat in illustrationSubGroups">
                <div class="section-header">
                    <span class="section-title">{{cat.title}}</span>
                </div>
                <div class="section-list">
                    <div class="section-item" ng-repeat="art in cat.items | filter:subPageSearch" ng-click="addSvgFromMedia(art)">
                        <img ng-src="{{art.url}}" alt="{{art.name}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.elements-content {
    padding: 10px;
    background: #3a495a;
}

.tab-main {
    background: #3a495a;
}

.icon-nbd-square {
    display: inline-block;
    line-height: 1;
    background: currentColor;
    position: relative;
    font-family: 'nbd' !important;
}

.draw-item {
    position: relative;
    width: 90px;
    height: 90px;
    min-width: 90px;
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #fff;
    background: transparent;
    padding: 0;
    margin-right: 8px;
}

.draw-item:hover {
    background: rgba(255, 255, 255, 0.1);
}

.draw-item.active {
    background: rgba(255, 236, 179, 0.3);
    color: #FFD54F;
}

.draw-item.active:hover {
    background: rgba(255, 236, 179, 0.4);
}

.draw-item img, .draw-item svg {
    width: 24px;
    height: 24px;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

.section-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}

.section-item {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.section-item:hover {
    background: rgba(255, 255, 255, 0.2);
}

.section-item img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.element-section {
    margin-bottom: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    color: white;
    height: 36px;
}

.section-title {
    font-size: 14px !important;
    color: #fff !important;
    border-bottom: none;
    margin: 0;
    padding: 0;
    line-height: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    font-weight: normal;
}

.section-more {
    font-size: 14px !important;
    color:rgb(117, 188, 243) !important;
    cursor: pointer;
    margin: 0;
    padding: 0;
    line-height: 30px;
    height: 30px;
    display: flex;
    align-items: center;
}

/* 确保所有文字在深色背景上清晰可见 */
.heading-title,
.color-palette-label,
label,
.nbd-button {
    color: #fff !important;
}

/* 调整输入框和按钮在深色背景下的样式 */
input[type="text"],
input[type="range"],
.nbd-button {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
}

input[type="text"]:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

/* 调整下拉菜单样式 */
.nbd-dropdown {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.nbd-sub-dropdown {
    background: #3a495a;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.nbd-sub-dropdown li {
    color: #fff;
}

.nbd-sub-dropdown li:hover {
    background: rgba(255, 255, 255, 0.1);
}

/* 调整颜色选择器样式 */
.main-color-palette {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.color-palette-item {
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* 调整滚动条样式 */
.tab-scroll::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.tab-scroll::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

.tab-scroll::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

.tab-scroll::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

.subpage {
    background: #3a495a;
    height: 100%;
    color: #fff;
    position: absolute;
    left: 0; top: 0; right: 0; bottom: 0;
    z-index: 100;
}

.subpage-header {
    display: flex;
    align-items: center;
    padding: 16px 12px 8px 12px;
    font-size: 18px;
    font-weight: bold;
}

.icon-back {
    font-size: 22px;
    margin-right: 12px;
    cursor: pointer;
}

.subpage-search {
    display: flex;
    align-items: center;
    background: #2d3845;
    border-radius: 6px;
    margin: 0 12px 16px 12px;
    padding: 6px 10px;
}

.subpage-search input {
    background: transparent;
    border: none;
    color: #fff;
    flex: 1;
    font-size: 15px;
    outline: none;
}

.icon-nbd-fomat-search {
    color: #bbb;
    font-size: 18px;
}

.subpage-content {
    padding: 0 12px;
}

/* .shape-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    align-items: left;
    background: #f5f5f5;
    padding: 12px;
    border-radius: 8px;
}

.shape-grid .section-item {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s;
}

.shape-grid .section-item:hover {
    transform: scale(1.05);
}

.shape-grid .section-item img {
    width: 100%;
    height: 100%;
    object-fit: contain;
} */

.subpage-header {
    background: #3a495a;
    z-index: 9999;
    position: relative;
}

.subpage-title {
    color: #fff !important;
    font-size: 18px !important;
    font-weight: bold !important;
    display: inline-block !important;
}

.icon-back {
    color: #fff !important;
    display: inline-block !important;
}

.draw-item .icon-nbd {
    color: #fff !important;
    fill: #fff !important;
}

.range {
    margin-bottom: 16px;
    text-align: left;
}

.range .section-title {
    display: block;
    margin-bottom: 8px;
    text-align: left;
}

.range .main-track {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 8px;
}

.range .slide-input {
    flex: 1;
}

.range .value-display {
    min-width: 30px;
    text-align: right;
    color: #fff;
}

</style>

<script>
// 伪代码，需放到AngularJS控制器中
$scope.showSubPage = false;
$scope.subPageType = '';
$scope.subPageTitle = '';
$scope.subPageSearch = '';

// 假设icon和illustration的子分组数据结构如下
$scope.iconSubGroups = [
    { title: '动物', items: [
        { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/02/vecteezy_cute-cartoon-sea-animal-shark-character_10838184.png', name: '鲨鱼' },
        { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/elephant-1837462.svg', name: '大象' },
        { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/teddy_bear1-1.jpg', name: '泰迪熊' }
    ]},
    { title: '植物', items: [
        { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/Bag2.jpg', name: '包1' },
        { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/Bag2-2.jpg', name: '包2' },
        { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/Bag1.jpg', name: '包3' }
    ]}
];
$scope.illustrationSubGroups = [
    { title: '卡通', items: [] },
    { title: '手绘', items: [] }
];

$scope.openSubPage = function(type) {
    $scope.showSubPage = true;
    $scope.subPageType = type;
    $scope.subPageSearch = '';
    if(type === 'shape') {
        $scope.subPageTitle = '形状';
    } else if(type === 'icon') {
        $scope.subPageTitle = '图标';
        // 手动填充 iconSubGroups 数据
        $scope.iconSubGroups = [
            { title: '动物', items: [
                { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/02/vecteezy_cute-cartoon-sea-animal-shark-character_10838184.png', name: '鲨鱼' },
                { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/elephant-1837462.svg', name: '大象' },
                { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/teddy_bear1-1.jpg', name: '泰迪熊' }
            ]},
            { title: '植物', items: [
                { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/Bag2.jpg', name: '包1' },
                { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/Bag2-2.jpg', name: '包2' },
                { url: 'https://www.dujiayoupin.com/wp-content/uploads/2025/01/Bag1.jpg', name: '包3' }
            ]}
        ];
        console.log('iconSubGroups:', $scope.iconSubGroups); // 调试日志
    } else if(type === 'illustration') {
        $scope.subPageTitle = '插画';
        // 这里需要将 resource.illustration.data 按"卡通/手绘"分组
        $scope.illustrationSubGroups[0].items = $scope.resource.illustration.data.filter(function(item){ return item.category === '卡通'; });
        $scope.illustrationSubGroups[1].items = $scope.resource.illustration.data.filter(function(item){ return item.category === '手绘'; });
    }
};
$scope.closeSubPage = function() {
    $scope.showSubPage = false;
};

$scope.handleDrawMode = function() {
    // 先切换状态
    $scope.resource.drawMode.status = !$scope.resource.drawMode.status;
    
    // 如果状态为true，启用画笔
    if ($scope.resource.drawMode.status) {
        $scope.enableDrawMode();
    } else {
        // 如果状态为false，禁用画笔
        $scope.resource.drawMode.status = false;
        $scope.enableDrawMode();
    }
};
</script>