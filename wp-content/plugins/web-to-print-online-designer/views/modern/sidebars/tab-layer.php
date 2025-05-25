<div class="<?php if( $active_layers ) echo 'active'; ?> tab tab-last" id="tab-layer">
    <div class="tab-main tab-scroll">
        <div class="inner-tab-layer">
            <div class="empty-layer-tip" ng-if="!stages[currentStage].layers || stages[currentStage].layers.length === 0">
                <i class="icon-nbd icon-nbd-layers"></i>
                <p>当前设计未包含任何元素 todo picture</p>
            </div>
            <ul class="menu-layer" nbd-layer="sortLayer(srcIndex, dstIndex)" ng-if="stages[currentStage].layers && stages[currentStage].layers.length > 0">
                <li class="menu-item item-layer-text" data-index="{{layer.index}}" ng-click="activeLayer(layer.index)" 
                    ng-class="[{'lock-active': !layer.selectable, 'nbd-disable-event': !isTemplateMode && layer.forceLock, 'active' : stages[currentStage].states.isLayer && stages[currentStage].states.itemId == layer.itemId}, layer.type]" 
                    data-id="{{layer.itemId}}" ng-repeat="layer in stages[currentStage].layers | reverse">
                    <div class="item-layer-inner" ng-if="layer.type != 'image-layer'">
                        <i ng-if="layer.type != 'image'" class="icon-nbd item-left" ng-class="'icon-nbd-' + layer.icon_class"></i>
                        <img ng-if="layer.type == 'image'" ng-src="{{layer.src}}" />
                        <div ng-if="layer.type == 'text'" class="item-center text-layer"><input ng-class="layer.editable ? '' : 'nbd-disabled'" ng-change="setLayerAttribute('text', layer.text, layer.index, layer.index)" ng-model="layer.text" type="text"/></div>
                        <!-- <span ng-if="layer.type != 'text'" class="item-center">{{settings.nbdlangs[layer.type]}}</span> -->
                        <input ng-if="layer.type != 'text'" ng-model="layer.layerName" ng-change="changeLayerName( layer.index, layer.layerName )"/>
                        <span class="item-right">
                            <i class="icon-nbd icon-nbd-baseline-warning" ng-if="layer.lostChar"></i>
                            <i ng-click="setLayerAttribute('visible', !layer.visible, layer.index, layer.index); $event.stopPropagation();" ng-class="layer.visible ? 'icon-nbd-fomat-visibility' : 'icon-nbd-fomat-visibility-off'" class="icon-nbd icon-visibility" data-active="true" data-act="visibility" title="<?php esc_html_e('Show/Hide', 'web-to-print-online-designer'); ?>"></i>
                            <i ng-click="setLayerAttribute('selectable', !layer.selectable, layer.index, layer.index); $event.stopPropagation();" ng-class="layer.selectable ? 'icon-nbd-fomat-lock-open' : 'icon-nbd-fomat-lock-outline'" class="icon-nbd icon-lock" data-active="true" data-act="lock" title="<?php esc_html_e('Lock/Unlock', 'web-to-print-online-designer'); ?>"></i>
                            <i ng-click="deleteLayers(layer.index); $event.stopPropagation();" class="icon-nbd icon-nbd-fomat-highlight-off icon-close" data-act="close" title="<?php esc_html_e('Delete', 'web-to-print-online-designer'); ?>"></i>
                        </span>
                    </div>
                    <div ng-if="layer.type == 'image-layer'" class="item-layer-image-mask">
                        <div class="item-layer-inner">
                            <span class="item-center"><i class="icon-nbd item-left icon-nbd-images" ></i> {{settings.nbdlangs[layer.type]}}</span>
                            <span class="item-right">
                                <i class="icon-nbd icon-nbd-baseline-warning" ng-if="layer.lostChar"></i>
                                <i ng-click="setLayerAttribute('visible', !layer.visible, layer.index, layer.index); $event.stopPropagation();" ng-class="layer.visible ? 'icon-nbd-fomat-visibility' : 'icon-nbd-fomat-visibility-off'" class="icon-nbd icon-visibility" data-active="true" data-act="visibility" title="<?php esc_html_e('Show/Hide', 'web-to-print-online-designer'); ?>"></i>
                                <i ng-click="setLayerAttribute('selectable', !layer.selectable, layer.index, layer.index); $event.stopPropagation();" ng-class="layer.selectable ? 'icon-nbd-fomat-lock-open' : 'icon-nbd-fomat-lock-outline'" class="icon-nbd icon-lock" data-active="true" data-act="lock" title="<?php esc_html_e('Lock/Unlock', 'web-to-print-online-designer'); ?>"></i>
                                <i ng-click="deleteLayers(layer.index); $event.stopPropagation();" class="icon-nbd icon-nbd-fomat-highlight-off icon-close" data-act="close" title="<?php esc_html_e('Delete', 'web-to-print-online-designer'); ?>"></i>
                            </span>
                        </div>
                        <div class="item-layer-inner">
                            <ul>
                                <li>
                                    <?php esc_html_e('Mask', 'web-to-print-online-designer'); ?>
                                </li>
                                <li>
                                    <img ng-src="{{layer.src}}" />
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
.empty-layer-tip {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
    color: #999;
}

.empty-layer-tip .icon-nbd {
    font-size: 32px;
    margin-bottom: 12px;
    color: #ccc;
}

.empty-layer-tip p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}
</style>