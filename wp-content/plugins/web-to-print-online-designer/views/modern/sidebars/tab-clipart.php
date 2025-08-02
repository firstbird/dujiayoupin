<div class="<?php if( $active_cliparts ) echo 'active'; ?> tab nbd-onload" id="tab-svg" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-svg" data-type="clipart" data-offset="20">
    <div class="nbd-search">
        <input type="text" name="search" placeholder="<?php esc_html_e('Search clipart', 'web-to-print-online-designer'); ?>" ng-model="resource.clipart.filter.search"/>
        <i class="icon-nbd icon-nbd-fomat-search"></i>
    </div>
    <div class="cliparts-category" ng-class="resource.clipart.data.cat.length > 0 ? '' : 'nbd-hiden'">
        <div class="nbd-button nbd-dropdown">
            <span>{{resource.clipart.filter.currentCat.name}}</span>
            <i class="icon-nbd icon-nbd-chevron-right rotate90"></i>
            <div class="nbd-sub-dropdown" data-pos="center">
                <ul class="nbd-perfect-scroll">
                    <li ng-click="changeCat('clipart', cat)" ng-repeat="cat in resource.clipart.data.cat"><span>{{cat.name}}</span><span>{{cat.amount}}</span></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- 画笔工具区域 -->
    <div class="content-item type-draw" data-type="draw">
        <div class="main-type">
            <div class="free-draw-settings">
            <span class="section-title"><?php esc_html_e('画笔','web-to-print-online-designer'); ?></span>
            <div class="draw-item" ng-class="{'active': resource.drawMode.status}" ng-click="onSwitchDrawMode(); console.log('Draw button clicked, status:', resource.drawMode.status);" title="<?php esc_html_e('Free Draw','web-to-print-online-designer'); ?>">
                <i class="icon-nbd icon-nbd-drawing"></i>
                <!-- 调试信息 -->
                <!-- <div style="position: absolute; top: -20px; left: 0; font-size: 10px; color: #fff; background: rgba(0,0,0,0.7); padding: 2px 4px; border-radius: 2px; white-space: nowrap;">
                    Status: {{resource.drawMode.status}}
                </div> -->
            </div>
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
    <div class="tab-main tab-scroll">
        <div class="nbd-items-dropdown" >
            <div>
                <div class="clipart-wrap">
                    <div class="clipart-item" nbd-drag="art.url" extenal="false" type="svg"  ng-repeat="art in resource.clipart.filteredArts | limitTo: resource.clipart.filter.currentPage * resource.clipart.filter.perPage" repeat-end="onEndRepeat('clipart')">
                        <img  ng-src="{{art.url}}" ng-click="addArt(art, true, true)" alt="{{art.name}}">
                    </div>
                </div>
                <div class="loading-photo" >
                    <svg class="circular" viewBox="25 25 50 50">
                        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                    </svg>
                </div>
                <div class="tab-load-more" style="display: none;" ng-show="!resource.clipart.onload && resource.clipart.filteredArts.length && resource.clipart.filter.currentPage * resource.clipart.filter.perPage < resource.clipart.filter.total">
                    <a class="nbd-button" ng-click="scrollLoadMore('#tab-svg', 'clipart')"><?php esc_html_e('Load more','web-to-print-online-designer'); ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
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

.draw-item .icon-nbd {
    color: #fff !important;
    fill: #fff !important;
}

.draw-item img {
    width: 100%;
    height: 100%;
    object-fit: contain;   /* 保证图片完整显示且不变形 */
    object-position: center;
    display: block;
}

/* 画笔工具区域样式 */
.draw-tools-section {
    background: rgba(255, 255, 255, 0.05) !important;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    margin-bottom: 16px !important;
    padding: 16px 12px !important;
}

.draw-tools-section .section-title {
    color: #fff !important;
    font-size: 14px !important;
    margin-bottom: 12px !important;
    display: block !important;
}

.draw-tools-section .range {
    margin-bottom: 16px !important;
}

.draw-tools-section .range .section-title {
    margin-bottom: 8px !important;
}

.draw-tools-section .range .value-display {
    color: #fff !important;
    min-width: 30px !important;
    text-align: right !important;
}

.draw-tools-section .color .section-title {
    margin-bottom: 8px !important;
}

/* 画笔工具区域样式 */
.content-item.type-draw .nbd-button {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

.content-item.type-draw .nbd-button:hover {
    background: rgba(255, 255, 255, 0.2) !important;
}

.content-item.type-draw .color-palette-label {
    color: #fff !important;
}

.content-item.type-draw .main-color-palette {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.content-item.type-draw .color-palette-item {
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

.content-item.type-draw .slide-input {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

.content-item.type-draw .slide-input:focus {
    background: rgba(255, 255, 255, 0.15) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}

.content-item.type-draw .section-title {
    color: #fff !important;
    font-size: 14px !important;
    margin-bottom: 12px !important;
    display: block !important;
}

.content-item.type-draw .range {
    margin-bottom: 16px !important;
}

.content-item.type-draw .range .section-title {
    margin-bottom: 8px !important;
}

.content-item.type-draw .range .value-display {
    color: #fff !important;
    min-width: 30px !important;
    text-align: right !important;
}

.content-item.type-draw .color .section-title {
    margin-bottom: 8px !important;
}

/* 保留原有的 draw-tools-section 样式以防其他地方使用 */
.draw-tools-section .nbd-button {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

.draw-tools-section .nbd-button:hover {
    background: rgba(255, 255, 255, 0.2) !important;
}

.draw-tools-section .color-palette-label {
    color: #fff !important;
}

.draw-tools-section .main-color-palette {
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
}

.draw-tools-section .color-palette-item {
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
}

.draw-tools-section .slide-input {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

.draw-tools-section .slide-input:focus {
    background: rgba(255, 255, 255, 0.15) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}
</style>