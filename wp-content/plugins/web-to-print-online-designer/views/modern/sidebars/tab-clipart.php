<div class="<?php if( $active_cliparts ) echo 'active'; ?> tab nbd-onload" id="tab-svg" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-svg" data-type="clipart" data-offset="20">
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
                
                <!-- 当前颜色显示区域 -->
                <div class="current-color-display">
                    <span class="current-color-label"><?php esc_html_e('当前颜色','web-to-print-online-designer'); ?></span>
                    <div class="current-color-preview" ng-style="{'background-color': currentColor}"></div>
                    <span class="current-color-code">{{currentColor}}</span>
                </div>

                <div class="nbd-text-color-picker" id="nbd-bg-color-picker" class="active" >
                    <div class="color-picker-content">
                        <spectrum-colorpicker
                            ng-model="tempBrushColor"
                            options="{
                                    preferredFormat: 'hex',
                                    color: '#fff',
                                    flat: true,
                                    showButtons: false,
                                    showInput: false,
                                    containerClassName: 'nbd-sp'
                            }">
                        </spectrum-colorpicker>
                        <div class="color-input-row">
                            <input type="text" class="color-hex-input" ng-model="tempBrushColor" placeholder="#000000" />
                            <span class="custom-choose-btn" ng-click="applyBrushColor();"><?php esc_html_e('应用','web-to-print-online-designer'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nbd-color-palette-inner" >
                <div class="working-palette" ng-if="settings['nbdesigner_show_all_color'] == 'yes'" >
                    <!-- 当前背景颜色显示区域 -->
                    <div class="current-color-display">
                        <span class="current-color-label"><?php esc_html_e('当前背景颜色','web-to-print-online-designer'); ?></span>
                        <div class="current-color-preview" ng-style="{'background-color': stageBgColorPicker.currentColor}"></div>
                        <span class="current-color-code">{{stageBgColorPicker.currentColor}}</span>
                    </div>
                    
                    <div class="nbd-text-color-picker" id="nbd-stage-bg-color-picker" class="active">
                        <div class="color-picker-content">
                            <spectrum-colorpicker
                                ng-model="tempStageBgColor"
                                options="{
                                preferredFormat: 'hex',
                                color: '#fff',
                                flat: true,
                                showButtons: false,
                                showInput: false,
                                containerClassName: 'nbd-sp'
                                }">
                            </spectrum-colorpicker>
                            <div class="color-input-row">
                                <input type="text" class="color-hex-input" ng-model="tempStageBgColor" placeholder="#000000" />
                                <span class="custom-choose-btn"
                                    ng-click="applyStageBgColor();">
                                        <?php esc_html_e('应用', 'web-to-print-online-designer'); ?>
                                </span>
                            </div>
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
                <div><span class="custom-choose-btn" ng-click="removeBackgroundCanvas()"><?php esc_html_e('Remove background', 'web-to-print-online-designer'); ?></span></div>
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

/* 自定义按钮样式 */
.custom-choose-btn {
    display: inline-block !important;
    background: #4a90e2 !important;
    border: 1px solid #357abd !important;
    color: #fff !important;
    padding: 2px 4px !important;
    border-radius: 3px !important;
    font-weight: 400 !important;
    font-size: 11px !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    text-align: center !important;
    line-height: 1.2 !important;
    height: 20px !important;
    box-sizing: border-box !important;
    margin-left: 0 !important;
}

.custom-choose-btn:hover {
    background: #357abd !important;
    border-color: #2d6da3 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
}

/* 颜色输入行布局 */
.color-input-row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 20px !important;
    padding: 10px 0 !important;
    margin-top: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.color-input-row .custom-choose-btn {
    flex: 1 !important;
    margin: 0 !important;
    width: calc(33.33% - 10px) !important;
}

/* 调整颜色选择器容器样式 */
.nbd-text-color-picker {
    padding: 0 !important;
    box-sizing: border-box !important;
    position: relative !important;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    width: 100% !important;
    margin-top: 10px !important;
    left: 0 !important;
    right: 0 !important;
}

/* 颜色选择器遮罩层 */
.color-picker-overlay {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: transparent !important;
    z-index: 9998 !important;
    cursor: pointer !important;
}

/* 颜色选择器内容区域 */
.color-picker-content {
    position: relative !important;
    z-index: 9999 !important;
    background: #fff !important;
    border: 1px solid #ddd !important;
    border-radius: 4px !important;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1) !important;
    padding: 20px !important;
    padding-bottom: 0 !important;
    width: 100% !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    margin-top: 10px !important;
}

/* 当前颜色显示区域样式 */
.current-color-display {
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
    margin-bottom: 22px !important;
    padding: 8px 12px !important;
    background: rgba(255, 255, 255, 0.05) !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 4px !important;
}

.current-color-label {
    color: #fff !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    white-space: nowrap !important;
}

.current-color-preview {
    width: 24px !important;
    height: 24px !important;
    border: 2px solid rgba(255, 255, 255, 0.3) !important;
    border-radius: 4px !important;
    flex-shrink: 0 !important;
}

.current-color-code {
    color: #fff !important;
    font-size: 12px !important;
    font-family: monospace !important;
    font-weight: 500 !important;
    background: rgba(255, 255, 255, 0.1) !important;
    padding: 2px 6px !important;
    border-radius: 3px !important;
    min-width: 60px !important;
    text-align: center !important;
}

/* 调整颜色选择器内部间距 */
.nbd-text-color-picker .sp-container {
    margin-bottom: 0 !important;
    padding: 0 !important;
    display: block !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

.nbd-text-color-picker .sp-picker-container {
    margin-bottom: 0 !important;
    padding: 0 !important;
    display: block !important;
    width: 100% !important;
    box-sizing: border-box !important;
}

/* 确保颜色选择器与输入行对齐 */
.nbd-text-color-picker .sp-picker-container .sp-palette {
    margin: 0 !important;
}

/* 移除颜色选择器的底部边距 */
.nbd-text-color-picker .sp-picker-container .sp-palette-container {
    margin-bottom: 0 !important;
}

/* 确保颜色选择器正常显示 */
.nbd-text-color-picker .sp-picker-container {
    display: block !important;
    width: 100% !important;
    visibility: visible !important;
    opacity: 1 !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    padding: 0 !important;
}

.nbd-text-color-picker .sp-picker-container .sp-palette {
    display: block !important;
    width: 100% !important;
    visibility: visible !important;
    opacity: 1 !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    padding: 0 !important;
}

.nbd-text-color-picker .sp-picker-container .sp-palette .sp-palette-row {
    display: block !important;
    width: 100% !important;
    visibility: visible !important;
    opacity: 1 !important;
    box-sizing: border-box !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* 强制显示颜色选择器 */
.nbd-text-color-picker.active,
.nbd-text-color-picker {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    transform: none !important;
    scale: 1 !important;
}

/* 覆盖可能的隐藏规则 */
#nbd-bg-color-picker,
#nbd-stage-bg-color-picker {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
    width: 100% !important;
    margin-top: 10px !important;
    padding: 0 !important;
    left: 0 !important;
    right: 0 !important;
    box-sizing: border-box !important;
}

/* 确保父容器不影响间距 */
.color .nbd-text-color-picker,
.working-palette .nbd-text-color-picker {
    margin-top: 10px !important;
}

/* 确保spectrum-colorpicker组件显示 */
.nbd-text-color-picker spectrum-colorpicker {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* 颜色十六进制输入框样式 */
.color-hex-input {
    background: #fff !important;
    border: 1px solid #ddd !important;
    border-radius: 3px !important;
    padding: 2px 4px !important;
    font-size: 11px !important;
    font-family: monospace !important;
    flex: 2 !important;
    text-align: center !important;
    color: #333 !important;
    height: 20px !important;
    box-sizing: border-box !important;
    margin-right: 0 !important;
    width: calc(66.67% - 10px) !important;
}

.color-hex-input:focus {
    outline: none !important;
    border-color: #4a90e2 !important;
    box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2) !important;
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

/* 调色板选择按钮样式 */
.color-palette-add {
    background: #4a90e2 !important;
    border: 2px solid #357abd !important;
    position: relative !important;
    cursor: pointer !important;
    transition: all 0.3s ease !important;
}

.color-palette-add:hover {
    background: #357abd !important;
    border-color: #2d6da3 !important;
    transform: scale(1.05) !important;
}

.color-palette-add::before {
    content: "+" !important;
    position: absolute !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    color: #fff !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    pointer-events: none !important;
}

</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 确保颜色选择器变量被正确初始化
    setTimeout(function() {
        var scope = angular.element(document.querySelector('#nbd-bg-color-picker')).scope();
        if (scope) {
            // 初始化当前画笔颜色
            if (!scope.currentColor) {
                scope.currentColor = '#ffffff';
            }
            // 初始化临时画笔颜色
            if (!scope.tempBrushColor) {
                scope.tempBrushColor = scope.currentColor;
            }
            
            // 添加应用画笔颜色的函数
            scope.applyBrushColor = function() {
                scope.currentColor = scope.tempBrushColor;
                scope.addColor();
                scope.changeBush(scope.currentColor);
            };
            
            scope.$apply();
        }
        
        var stageScope = angular.element(document.querySelector('#nbd-stage-bg-color-picker')).scope();
        if (stageScope) {
            // 初始化当前背景颜色
            if (!stageScope.stageBgColorPicker) {
                stageScope.stageBgColorPicker = {};
            }
            if (!stageScope.stageBgColorPicker.currentColor) {
                stageScope.stageBgColorPicker.currentColor = '#ffffff';
            }
            // 初始化临时背景颜色
            if (!stageScope.tempStageBgColor) {
                stageScope.tempStageBgColor = stageScope.stageBgColorPicker.currentColor;
            }
            
            // 添加应用背景颜色的函数
            stageScope.applyStageBgColor = function() {
                stageScope.stageBgColorPicker.currentColor = stageScope.tempStageBgColor;
                stageScope.changeBackgroundCanvas(stageScope.stageBgColorPicker.currentColor);
            };
            
            stageScope.$apply();
        }
    }, 100);
});
</script>