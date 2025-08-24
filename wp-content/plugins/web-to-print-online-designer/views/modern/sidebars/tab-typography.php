<div class="tab <?php if( $active_typos ) echo 'active'; ?> " ng-if="settings['nbdesigner_enable_text'] == 'yes'" id="tab-typography" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-typography" data-type="typography" data-offset="20">
    <div class="tab-main tab-scroll">
        <!-- 字体类型切换按钮 -->
        <div class="font-type-buttons">
            <button class="font-type-btn" ng-class="{'active': currentLanguage === 'chinese'}" ng-click="switchLanguage('chinese')">
                中文字体
            </button>
            <button class="font-type-btn" ng-class="{'active': currentLanguage === 'english'}" ng-click="switchLanguage('english')">
                英文字体
            </button>
        </div>
        
        <div class="typography-head">
            <span class="text-guide" ><?php esc_html_e('Click to add text','web-to-print-online-designer'); ?></span>
            <div class="head-main">
                <span class="text-heading" ng-click='addText("<?php esc_html_e('Heading','web-to-print-online-designer'); ?>", "heading")' ><?php esc_html_e('Add heading','web-to-print-online-designer'); ?></span>
                <span class="text-sub-heading" ng-click="addText('<?php esc_html_e('Subheading','web-to-print-online-designer');?>', 'subheading')" ><?php esc_html_e('Add subheading','web-to-print-online-designer');?></span>
                <span ng-click="addText('<?php echo str_replace( "&#039;", "\'", esc_attr__('Add a little bit of body text','web-to-print-online-designer') ); ?>')" class="text-body" ><?php esc_html_e('Add a little bit of body text','web-to-print-online-designer'); ?></span>
                <span ng-show="settings.nbdesigner_enable_curvedtext == 'yes'" ng-click="addCurvedText('<?php esc_html_e('Curved text','web-to-print-online-designer'); ?>')" class="text-body text-curved"><?php esc_html_e('Add curved text','web-to-print-online-designer'); ?></span>
            </div>
        </div>
        <hr class="seperate" ng-if="settings.nbdesigner_hide_typo_section == 'no'" />
        
        <div class="typography-body">
            <ul class="typography-items">
                <li nbd-drag="typo.folder" type="typo" ng-click="insertTypography(typo)" class="typography-item" ng-repeat="typo in filteredTypographyData | limitTo: resource.typography.filter.currentPage * resource.typography.filter.perPage track by typo.id" repeat-end="onEndRepeat('typography')">
                    <div class="typo-item-content">
                        <img ng-src="{{typo._cachedSrc || (typo._cachedSrc = generateTypoLink(typo))}}" 
                             alt="Typography" 
                             class="typo-preview" 
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';"
                             onload="this.onload=null; this.nextElementSibling.style.display='none';" />
                        <div class="typo-fallback" style="width: 100%; height: 80px; background: #f5f5f5; border-radius: 4px; display: none; align-items: center; justify-content: center; color: #999; font-size: 12px;">
                            <span>图片加载失败</span>
                        </div>
                        <div class="typo-name">{{typo.name || 'Font ' + typo.id}}</div>
                    </div>
                </li>
            </ul>
            <div class="loading-photo" >
                <svg class="circular" viewBox="25 25 50 50">
                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
                </svg>
            </div>
        </div>
    </div>
</div>
<style>
.nbd-sidebar {
    background: #3a495a !important;
    color: #3a495a !important;
}

/* 字体tab页面整体样式 */
.nbd-sidebar .typography-body {
    padding: 0 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

/* 确保字体列表容器居中 */
.nbd-sidebar .typography-items,
.nbd-sidebar ul.typography-items,
.nbd-sidebar .typography-body .typography-items {
    width: 100% !important;
    max-width: 400px !important; /* 增加最大宽度以适应两列 */
    margin: 0 auto !important;
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 15px !important;
}

/* 响应式布局 */
@media (max-width: 480px) {
    .font-type-buttons {
        padding: 12px 8px;
        gap: 8px;
    }
    
    .font-type-btn {
        padding: 8px 16px;
        font-size: 13px;
        min-width: 70px;
    }
    
    .typography-items {
        grid-template-columns: 1fr; /* 小屏幕时改为单列 */
        max-width: 280px;
    }
    
    .typo-preview {
        max-width: 150px; /* 小屏幕时增加图片宽度 */
    }
    
    .typo-name {
        font-size: 13px; /* 小屏幕时增加字体大小 */
    }
}

/* 字体类型切换按钮样式 */
.font-type-buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 15px 10px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 15px;
}

.font-type-btn {
    background: white;
    border: 2px solid #007cba;
    border-radius: 6px;
    padding: 10px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: #007cba;
    min-width: 80px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.font-type-btn:hover {
    background: #e7f3ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.font-type-btn.active {
    background: #007cba;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,124,186,0.3);
}

/* 语言切换标签样式 */
.language-tabs {
    padding: 20px 0;
    border-bottom: 1px solid #e0e0e0;
    margin-bottom: 20px;
    background: #fafafa;
    border-radius: 8px;
    margin: 0 10px 20px 10px;
}

.tab-switcher {
    display: flex;
    justify-content: center;
    gap: 15px;
    align-items: center;
}

.lang-tab {
    background: white;
    border: 2px solid #e0e0e0;
    border-radius: 25px;
    padding: 10px 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    min-width: 80px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.lang-tab:hover {
    background: #f8f9fa;
    border-color: #007cba;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.lang-tab.active {
    background: #007cba;
    border-color: #007cba;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,124,186,0.3);
}

.lang-text {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-weight: 500;
}

/* 字体列表样式 */
.typography-items {
    list-style: none !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    flex-wrap: wrap !important; /* 允许换行 */
    gap: 15px !important;
    width: 100% !important;
    max-width: 400px !important; /* 增加最大宽度以适应两列 */
    margin: 0 auto !important;
}

.typography-item {
    background: white !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    transition: all 0.3s ease !important;
    cursor: pointer !important;
    overflow: hidden !important;
    width: calc(50% - 7.5px) !important; /* 50%宽度减去gap的一半 */
    height: 120px !important; /* 固定高度 */
    min-width: 0 !important;
    display: block !important; /* 确保显示为块级元素 */
    flex-shrink: 0 !important; /* 防止收缩 */
}

.typography-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.typo-item-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center; /* 垂直居中 */
    padding: 20px 10px; /* 增加垂直内边距，因为不再有字体名称 */
    text-align: center; /* 文本居中 */
    width: 100%;
    height: 100%; /* 占满整个字体项目高度 */
    box-sizing: border-box; /* 确保padding不会增加总高度 */
}

.typo-preview {
    width: 100%;
    max-width: 120px; /* 减少最大宽度以适应两列 */
    max-height: 80px; /* 增加最大高度，因为不再有字体名称 */
    height: auto;
    border-radius: 4px;
    margin-bottom: 0; /* 移除底部间距，因为不再有字体名称 */
    display: block; /* 确保图片正确显示 */
    object-fit: contain; /* 保持图片比例 */
}

.typo-name {
    display: none !important; /* 隐藏字体名称 */
}

/* 图片加载失败时的样式 */
.typo-fallback {
    background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%) !important;
    border: 1px solid #ddd !important;
    color: #666 !important;
    font-weight: 500 !important;
    text-shadow: 0 1px 2px rgba(255,255,255,0.8) !important;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1) !important;
}

.typo-fallback span {
    background: rgba(255,255,255,0.8) !important;
    padding: 4px 8px !important;
    border-radius: 3px !important;
    font-size: 11px !important;
    letter-spacing: 0.5px !important;
}

/* 加载动画样式 */
.loading-photo {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.circular {
    width: 40px;
    height: 40px;
    animation: rotate 2s linear infinite;
}

.path {
    stroke: #007cba;
    stroke-linecap: round;
    animation: dash 1.5s ease-in-out infinite;
}

@keyframes rotate {
    100% {
        transform: rotate(360deg);
    }
}

@keyframes dash {
    0% {
        stroke-dasharray: 1, 150;
        stroke-dashoffset: 0;
    }
    50% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -35;
    }
    100% {
        stroke-dasharray: 90, 150;
        stroke-dashoffset: -124;
    }
}

/* 调试样式 */
.typo-debug {
    background: rgba(255,255,255,0.9);
    padding: 2px 4px;
    border-radius: 2px;
    font-family: monospace;
}

.typography-item:hover .typo-debug {
    display: block;
}

.typography-item .typo-debug {
    display: none;
}
</style>

<!-- 调试脚本 -->
<script>
// 监听字体加载事件
document.addEventListener('DOMContentLoaded', function() {
    // 监听字体加载失败
    if (typeof FontFaceObserver !== 'undefined') {
        console.log('FontFaceObserver 已加载');
    } else {
        console.error('FontFaceObserver 未加载');
    }
    
    // 监听 AJAX 请求
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxError(function(event, xhr, settings, error) {
            if (settings.url && settings.url.indexOf('get_typo') > -1) {
                console.error('字体资源获取失败:', settings.url, error);
            }
        });
    }
});
</script>