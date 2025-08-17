<div class="tab <?php if( $active_typos ) echo 'active'; ?> " ng-if="settings['nbdesigner_enable_text'] == 'yes'" id="tab-typography" nbd-scroll="scrollLoadMore(container, type)" data-container="#tab-typography" data-type="typography" data-offset="20">
    <div class="tab-main tab-scroll">
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
                <li nbd-drag="typo.folder" type="typo" ng-click="insertTypography(typo)" class="typography-item" ng-repeat="typo in resource.typography.data | limitTo: resource.typography.filter.currentPage * resource.typography.filter.perPage" repeat-end="onEndRepeat('typography')">
                    <img ng-src="{{generateTypoLink(typo)}}" alt="Typography" />
                    <!-- 调试信息 -->
                    <!-- <div class="typo-debug" style="font-size: 10px; color: #666; margin-top: 5px;">
                        <div>ID: {{typo.id}}</div>
                        <div>Folder: {{typo.folder}}</div>
                    </div> -->
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

/* 调试样式 */
.typo-debug {
    background: rgba(255,255,255,0.9);
    padding: 2px 4px;
    border-radius: 2px;
    font-family: monospace;
}

.typography-item {
    position: relative;
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