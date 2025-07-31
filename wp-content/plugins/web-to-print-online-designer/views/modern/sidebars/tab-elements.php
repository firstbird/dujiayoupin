<div class="<?php if( $active_elements ) echo 'active'; ?> tab tab-last" id="tab-element">
<?php
        // 定义所有需要的内容类型
        $content_types = array(
            'shape' => array(
                'title' => '形状',
            ),
            'icon' => array(
                'title' => '图标',
            ),
            'paint' => array(
                'title' => '插画',
            ),
            'text' => array(
                'title' => '文字',
            ),
            'background' => array(
                'title' => '背景',
            ),
            'character' => array(
                'title' => '人物',
            ),
            'pet' => array(
                'title' => '宠物',
            ),
            'chineseStyle' => array(
                'title' => '中国风',
            ),
            'promotion' => array(
                'title' => '促销',
            ),
            'food' => array(
                'title' => '食物',
            ),
            'material' => array(
                'title' => '材质',
            ),
            'cute' => array(
                'title' => '可爱',
            ),
            'emoticon' => array(
                'title' => '表情',
            ),
            'textBox' => array(
                'title' => '文本框',
            ),
            'plant' => array(
                'title' => '植物',
            ),
            'music' => array(
                'title' => '音乐',
            ),
            'celestialBody' => array(
                'title' => '天体',
            ),
            'starrySky' => array(
                'title' => '星空',
            ),
            'weather' => array(
                'title' => '天气',
            ),
            'game' => array(
                'title' => '游戏',
            ),
            'cartoon' => array(
                'title' => '卡通',
            ),
            'scenery' => array(
                'title' => '风景',
            ),
            'fruit' => array(
                'title' => '水果',
            ),
            'flower' => array(
                'title' => '花朵',
            ),
            'blessing' => array(
                'title' => '祝福',
            ),
            'fluid' => array(
                'title' => '流体',
            ),
            'animal' => array(
                'title' => '动物',
            ),
            'frame' => array(
                'title' => '相框',
            ),
            'line' => array(
                'title' => '线条',
            ),
            'dialogBox' => array(
                'title' => '对话框',
            ),
            'expression' => array(
                'title' => '表情',
            )
        );
?>    
    <div class="element-main tab-scroll">
        <!-- 全局搜索栏 -->
        <div class="global-search-bar">
            <div class="search-input-wrapper">
                <input type="text" 
                       placeholder="搜索所有元素..." 
                       ng-model="globalSearchTerm"
                       ng-keyup="$event.keyCode === 13 && performGlobalSearch()"/>
                <i class="icon-nbd icon-nbd-fomat-search" 
                   ng-click="performGlobalSearch()" 
                   style="cursor: pointer; color: #fff;"></i>
            </div>
        </div>
        
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
                        <!-- <div class="element-section">
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
                        </div> -->
                        <div class="element-section" ng-repeat="(type, config) in resource">
                            <div class="section-header" ng-if="config.subPageTitle">
                                <span class="section-title">{{config.subPageTitle}}</span>
                                <span class="section-more" ng-click="openSubPage(type)">更多 ></span>
                            </div>
                            <div class="section-list" ng-if="config.subPageTitle">
                                <div class="draw-item" ng-repeat="item in getListByType(type)" ng-click="addImage(item)" title="{{item.title}}">
                                    <img ng-src="{{item}}">
                                </div>
                            </div>
                        </div>
                    </div>  
                </div>
    </div>
</div>

<!-- 子页面 -->
<div class="subpage" ng-if="showSubPage">
    <div class="subpage-header" style="display: flex; align-items: center; padding: 16px 12px 8px 12px; background: #3a495a; z-index: 9999; position: relative;">
        <span class="icon-back" ng-click="closeSubPage()" style="font-size:22px;margin-right:12px;cursor:pointer;color:#fff;display:inline-block;">&#8592;</span>
        <span class="subpage-title" style="color:#fff;font-size:18px;font-weight:bold;display:inline-block;">{{subPageTitle}}</span>
    </div>
    <div class="loading-overlay" ng-if="resource[subPageType].onload">
            <div class="loading-spinner">
                <svg viewBox="25 25 50 50">
                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="4" stroke-miterlimit="10"/>
                </svg>
                <span style="margin-left:10px;">正在加载...</span>
            </div>
    </div>
    <div class="subpage-search" ng-if="!resource[subPageType].onload">
        <?php
        foreach ($content_types as $type => $config) {
        ?>
        <input ng-if="subPageType === '<?php echo $type; ?>'" 
               type="text" 
               name="search" 
               placeholder="<?php esc_html_e('搜索' . $config['title'], 'web-to-print-online-designer'); ?>" 
               ng-model="resource.<?php echo $type; ?>.filter.search"
               ng-keyup="$event.keyCode === 13 && performSearch('<?php echo $type; ?>')"/>
        
        <?php
        }
        ?>
        <i class="icon-nbd icon-nbd-fomat-search" ng-click="performSearch(subPageType)" style="cursor: pointer;"></i>
    </div>
    <!-- <div class="subpage-search">
        <input type="text" ng-model="subPageSearch" placeholder="搜索{{subPageTitle}}">
        <i class="icon-nbd icon-nbd-fomat-search"></i>
    </div> -->
    <div class="subpage-content">
        <?php
        foreach ($content_types as $type => $config) {
        ?>
        <!-- <?php echo $config['title']; ?>：<?php echo $config['description']; ?> -->
        <div ng-if="subPageType === '<?php echo $type; ?>'" class="subpage-content-inner">
            <div class="content-items">    
                
                <div class="content-item type-<?php echo $type; ?>" data-type="<?php echo $type; ?>" id="nbd-<?php echo $type; ?>-wrap">  
                    <div class="mansory-wrap">
                        <div nbd-drag="<?php echo $type; ?>.url" extenal="true" type="svg" class="mansory-item" ng-click="addArt(<?php echo $type; ?>, true, true)" ng-repeat="<?php echo $type; ?> in resource.<?php echo $type; ?>.filtered" repeat-end="onEndRepeat('<?php echo $type; ?>')">
                            <div class="mansory-item__inner">
                                <img ng-src="{{<?php echo $type; ?>.url}}" /><span class="photo-desc">{{<?php echo $type; ?>.name}}</span>
                                <span class="nbd-pro-mark-wrap" ng-if="$index > 20">
                                    <svg class="nbd-pro-mark" fill="#F3B600" xmlns="http://www.w3.org/2000/svg" viewBox="-505 380 12 10"><path d="M-503 388h8v1h-8zM-494 382.2c-.4 0-.8.3-.8.8 0 .1 0 .2.1.3l-2.3.7-1.5-2.2c.3-.2.5-.5.5-.8 0-.6-.4-1-1-1s-1 .4-1 1c0 .3.2.6.5.8l-1.5 2.2-2.3-.8c0-.1.1-.2.1-.3 0-.4-.3-.8-.8-.8s-.8.4-.8.8.3.8.8.8h.2l.8 3.3h8l.8-3.3h.2c.4 0 .8-.3.8-.8 0-.4-.4-.7-.8-.7z"></path></svg>
                                    <?php esc_html_e('Pro','web-to-print-online-designer'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- 底部提示文字 -->
                    <div class="bottom-tip" ng-if="resource.<?php echo $type; ?>.filtered && resource.<?php echo $type; ?>.filtered.length > 0">
                        <!-- 加载指示器 -->
                        <div class="loading-indicator" ng-if="resource[subPageType].onload && resource[subPageType].hasMore == 'true'">
                            <svg class="circular" viewBox="25 25 50 50">
                                <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                            </svg>
                            <?php echo '正在加载更多' . $config['title'] . '...'; ?>
                        </div>
                        <!-- 到底部提示 -->
                        <span class="loading-indicator" ng-if="!resource[subPageType].onload && resource[subPageType].hasMore == 'false'">已经到底部了</span>
                    </div>
                </div>

            </div>
        </div>
        <?php
        }
        ?>
        
        <!-- 全局搜索结果 -->
        <div ng-if="subPageType === 'globalSearch'" class="subpage-content-inner">
            <div class="content-items">    
                <div class="content-item type-globalSearch" data-type="globalSearch" id="nbd-globalSearch-wrap">  
                    <div class="mansory-wrap">
                        <div nbd-drag="item.url" extenal="true" type="svg" class="mansory-item" ng-click="addArt(item, true, true)" ng-repeat="item in resource.globalSearch.filtered" repeat-end="onEndRepeat('globalSearch')">
                            <div class="mansory-item__inner">
                                <img ng-src="{{item.url}}" />
                                <span class="photo-desc">{{item.key}}</span>
                                <span class="nbd-pro-mark-wrap" ng-if="$index > 20">
                                    <svg class="nbd-pro-mark" fill="#F3B600" xmlns="http://www.w3.org/2000/svg" viewBox="-505 380 12 10"><path d="M-503 388h8v1h-8zM-494 382.2c-.4 0-.8.3-.8.8 0 .1 0 .2.1.3l-2.3.7-1.5-2.2c.3-.2.5-.5.5-.8 0-.6-.4-1-1-1s-1 .4-1 1c0 .3.2.6.5.8l-1.5 2.2-2.3-.8c0-.1.1-.2.1-.3 0-.4-.3-.8-.8-.8s-.8.4-.8.8.3.8.8.8h.2l.8 3.3h8l.8-3.3h.2c.4 0 .8-.3.8-.8 0-.4-.4-.7-.8-.7z"></path></svg>
                                    <?php esc_html_e('Pro','web-to-print-online-designer'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- 底部提示文字 -->
                    <div class="bottom-tip" ng-if="resource.globalSearch.filtered && resource.globalSearch.filtered.length > 0">
                        <span class="loading-indicator">找到 {{resource.globalSearch.filtered.length}} 个结果</span>
                    </div>
                    <div class="bottom-tip" ng-if="!resource.globalSearch.filtered || resource.globalSearch.filtered.length === 0">
                        <span class="loading-indicator">没有找到相关结果</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>

.tab.active {
    background:rgb(7, 129, 154) !important;
    /* color:rgb(255, 255, 255) !important; */
}
.tab-main {
    background:rgb(7, 129, 154);
    /* z-index: 9999; */
    /* position: relat/ive; */
    /* padding-top: 0px; */
}

/*
siderbar
.tabs-nav {
    background:rgb(208, 38, 185) !important;
    color:rgb(193, 212, 26) !important;
} */

/* main-tabs 被tab覆盖了*/
/* .main-tabs { */
    /* background:rgb(208, 19, 19); */
    /* color:rgb(26, 187, 212) */
/* } */
.tab-nav {
    background: #3a495a;
} 
.tab {
    background: #3a495a;
}
.element-main {
    background: rgb(7, 129, 154);
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


.tab-scroll {
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}

.tab-scroll::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}

/* 全局搜索栏样式 */
.global-search-bar {
    padding: 16px 12px;
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 16px;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.search-input-wrapper input {
    flex: 1;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 14px;
    outline: none;
    padding: 0;
    margin: 0;
}

.search-input-wrapper input::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

.search-input-wrapper .icon-nbd-fomat-search {
    margin-left: 8px;
    font-size: 16px;
    color: rgba(255, 255, 255, 0.8);
    transition: color 0.2s ease;
}

.search-input-wrapper .icon-nbd-fomat-search:hover {
    color: #fff;
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
    position: relative;
}

/* 图标子页面滚动样式 */
.subpage-content-inner {
    height: calc(100vh - 120px); /* 减去头部和搜索框的高度 */
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
}

.subpage-content-inner::-webkit-scrollbar {
    width: 8px;
}

.subpage-content-inner::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.subpage-content-inner::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    transition: background 0.2s ease;
}

.subpage-content-inner::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* 确保内容区域有足够的高度来触发滚动 */
.subpage-content-inner .content-item {
    min-height: 100%;
}

/* 滚动加载时的加载指示器 */
.subpage-content-inner .loading-indicator {
    text-align: center;
    padding: 20px;
    color: #fff;
    font-size: 14px;
}

.subpage-content-inner .loading-indicator svg {
    width: 20px;
    height: 20px;
    margin-right: 8px;
    animation: spin 1s linear infinite;
}
/* icon page */
.icon-subpage-content {
    height: calc(100vh - 120px); /* 减去头部和搜索框的高度 */
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
}

.icon-subpage-content::-webkit-scrollbar {
    width: 8px;
}

.icon-subpage-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.icon-subpage-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    transition: background 0.2s ease;
}

.icon-subpage-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* 确保内容区域有足够的高度来触发滚动 */
.icon-subpage-content .content-item {
    min-height: 100%;
}

/* 滚动加载时的加载指示器 */
.icon-subpage-content .loading-indicator {
    text-align: center;
    padding: 20px;
    color: #fff;
    font-size: 14px;
}

.icon-subpage-content .loading-indicator svg {
    width: 20px;
    height: 20px;
    margin-right: 8px;
    animation: spin 1s linear infinite;
}

/* paint page */
.paint-subpage-content {
    height: calc(100vh - 120px); /* 减去头部和搜索框的高度 */
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 20px;
}

.paint-subpage-content::-webkit-scrollbar {
    width: 8px;
}

.paint-subpage-content::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.paint-subpage-content::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    transition: background 0.2s ease;
}

.paint-subpage-content::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* 确保内容区域有足够的高度来触发滚动 */
.paint-subpage-content .content-item {
    min-height: 100%;
}

/* 滚动加载时的加载指示器 */
.paint-subpage-content .loading-indicator {
    text-align: center;
    padding: 20px;
    color: #fff;
    font-size: 14px;
}

.paint-subpage-content .loading-indicator svg {
    width: 20px;
    height: 20px;
    margin-right: 8px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

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

.draw-item img {
    width: 100%;
    height: 100%;
    object-fit: contain;   /* 保证图片完整显示且不变形 */
    object-position: center;
    display: block;
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

/* 底部提示样式 */
.bottom-tip {
    text-align: center;
    padding: 20px 0;
    margin-bottom: 20px;
}

.bottom-tip-text {
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
    font-style: italic;
    display: inline-block;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* 底部提示中的加载指示器样式 */
.bottom-tip .loading-indicator {
    text-align: center;
    padding: 20px;
    color: #fff;
    font-size: 14px;
}

.bottom-tip .loading-indicator svg {
    width: 20px;
    height: 20px;
    margin-right: 8px;
    animation: spin 1s linear infinite;
}

.loading-overlay {
    position: absolute;
    left: 0; top: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.25);
    z-index: 2000;
    pointer-events: all;
    cursor: wait;
    display: flex;
    align-items: center;
    justify-content: center;
}
.loading-spinner {
    display: flex;
    align-items: center;
    color: #fff;
    font-size: 16px;
}
.loading-spinner svg {
    width: 32px;
    height: 32px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// 伪代码，需放到AngularJS控制器中
$scope.showSubPage = false;
$scope.subPageType = '';
$scope.subPageTitle = '';
$scope.subPageSearch = '';

// 滚动位置恢复配置
$scope.scrollRestoreConfig = {
    enabled: true,           // 是否启用滚动位置恢复
    method: 'simple',        // 恢复方法: 'simple' 或 'advanced'
    delay: 50,              // DOM更新延迟时间(ms)
    verify: true            // 是否验证恢复结果
};

// 假设icon和paint的子分组数据结构如下
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


// 恢复滚动位置
$scope.restoreScrollPosition = function(container, oldScrollTop, oldScrollHeight) {
    // 使用配置中的延迟时间
    var delay = $scope.scrollRestoreConfig.delay || 50;
    
    // 使用 $timeout 确保DOM更新完成后再恢复滚动位置
    $timeout(function() {
        if (container) {
            var newScrollHeight = container.scrollHeight;
            var heightDifference = newScrollHeight - oldScrollHeight;
            
            // 计算新的滚动位置
            var newScrollTop = oldScrollTop + heightDifference;
            
            console.log('恢复滚动位置:', {
                oldScrollTop: oldScrollTop,
                oldScrollHeight: oldScrollHeight,
                newScrollHeight: newScrollHeight,
                heightDifference: heightDifference,
                newScrollTop: newScrollTop
            });
            
            // 设置新的滚动位置
            container.scrollTop = newScrollTop;
            
            // 根据配置决定是否验证滚动位置
            if ($scope.scrollRestoreConfig.verify) {
                setTimeout(function() {
                    console.log('验证滚动位置:', {
                        expected: newScrollTop,
                        actual: container.scrollTop,
                        difference: Math.abs(container.scrollTop - newScrollTop),
                        success: Math.abs(container.scrollTop - newScrollTop) < 5
                    });
                }, 100);
            }
        }
    }, delay);
};

// 高级滚动位置保持功能
$scope.advancedScrollPositionRestore = function(container, oldScrollTop, oldScrollHeight) {
    // 创建临时锚点元素
    var anchorElement = document.createElement('div');
    anchorElement.style.position = 'absolute';
    anchorElement.style.top = oldScrollTop + 'px';
    anchorElement.style.left = '0';
    anchorElement.style.width = '1px';
    anchorElement.style.height = '1px';
    anchorElement.style.pointerEvents = 'none';
    anchorElement.style.opacity = '0';
    anchorElement.id = 'scroll-anchor-' + Date.now();
    
    // 将锚点添加到容器中
    container.appendChild(anchorElement);
    
    // 使用 $timeout 确保DOM更新完成
    $timeout(function() {
        if (container && anchorElement) {
            // 滚动到锚点位置
            anchorElement.scrollIntoView({ behavior: 'instant', block: 'start' });
            
            // 移除锚点元素
            setTimeout(function() {
                if (anchorElement.parentNode) {
                    anchorElement.parentNode.removeChild(anchorElement);
                }
            }, 100);
            
            console.log('使用锚点恢复滚动位置完成');
        }
    }, 100);
};

$scope.closeSubPage = function() {
    // 清理滚动事件监听器
    $scope.cleanupIconScrollEvent();
    
    $scope.showSubPage = false;
    $scope.subPageType = '';
    $scope.subPageTitle = '';
    $scope.subPageSearch = '';
    // $scope.isLoadingMoreResources = false;
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

// 监听自定义滚动事件（供其他组件使用）
$scope.$on('iconPageScrolled', function(event, data) {
    console.log('收到图标页面滚动事件:', data);
    
    // 这里可以添加其他处理逻辑
    // 例如：更新进度条、触发动画等
    
    // 示例：更新滚动进度
    if (data.scrollPercentage !== undefined) {
        $scope.scrollProgress = data.scrollPercentage;
    }
    
    // 示例：当滚动到顶部时执行某些操作
    if (data.scrollTop === 0) {
        console.log('滚动到顶部');
    }
    
    // 示例：当滚动到底部时执行某些操作
    if (data.isAtBottom) {
        console.log('滚动到底部');
    }
});

// 清理滚动事件监听器
$scope.cleanupIconScrollEvent = function() {
    if ($scope.iconScrollContainer) {
        $scope.iconScrollContainer.removeEventListener('scroll', $scope.handleIconScroll);
        $scope.iconScrollContainer = null;
        console.log('已清理图标页面滚动事件监听器');
    }
};
</script>