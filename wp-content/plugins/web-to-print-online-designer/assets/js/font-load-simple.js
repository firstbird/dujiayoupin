// 简化的字体加载脚本
(function() {
    'use strict';
    
    console.log('=== 简化字体加载脚本开始 ===');
    
    // 等待Angular应用初始化
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document.body).scope()) {
            initSimpleFontLoad();
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function initSimpleFontLoad() {
        var scope = angular.element(document.body).scope();
        if (!scope) {
            console.error('无法获取Angular scope');
            return;
        }
        
        console.log('Angular scope已获取，开始简化字体加载');
        
        // 新增的本地字体配置
        var newLocalFonts = [
            {
                alias: 'greatvibes',
                name: 'Great Vibes',
                url: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/greatvibes.ttf'
            },
            {
                alias: 'hindsiliguri',
                name: 'Hind Siliguri',
                url: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/hindsiliguri.ttf'
            },
            {
                alias: 'lustria',
                name: 'Lustria',
                url: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/lustria.ttf'
            }
        ];
        
        // 1. 确保字体资源数据中包含新增字体
        if (scope.resource && scope.resource.font && scope.resource.font.data) {
            newLocalFonts.forEach(function(font) {
                var existingFont = scope.resource.font.data.filter(function(f) {
                    return f.alias === font.alias;
                })[0];
                
                if (!existingFont) {
                    scope.resource.font.data.push({
                        alias: font.alias,
                        name: font.name,
                        url: font.url,
                        type: 'local',
                        cat: ['0'],
                        subset: 'latin'
                    });
                    console.log('✅ 字体已添加到资源数据:', font.alias);
                } else {
                    console.log('字体已存在于资源数据:', font.alias);
                }
            });
        }
        
        // 2. 简化版insertTemplateFont函数
        if (scope.insertTemplateFont) {
            var originalInsertTemplateFont = scope.insertTemplateFont;
            
            scope.insertTemplateFont = function(font_name, callback) {
                console.log('简化版insertTemplateFont被调用:', font_name);
                
                // 检查是否为新增的本地字体
                var isNewLocalFont = newLocalFonts.some(function(font) {
                    return font.alias === font_name;
                });
                
                if (isNewLocalFont) {
                    console.log('检测到新增本地字体:', font_name);
                    
                    // 注入字体CSS（如果还没有注入）
                    var font_id = font_name.replace(/\s/gi, '').toLowerCase();
                    var existingCSS = jQuery('#' + font_id);
                    
                    if (!existingCSS.length) {
                        var fontConfig = newLocalFonts.find(function(font) {
                            return font.alias === font_name;
                        });
                        
                        if (fontConfig) {
                            var css = "<style type='text/css' id='" + font_id + "'>";
                            css += "@font-face {";
                            css += "font-family: '" + font_name + "';";
                            css += "src: url('" + fontConfig.url + "') format('truetype');";
                            css += "font-weight: normal;";
                            css += "font-style: normal;";
                            css += "font-display: swap;";
                            css += "}";
                            css += "</style>";
                            
                            jQuery("head").append(css);
                            console.log('✅ 字体CSS已注入:', font_name);
                        }
                    }
                    
                    // 直接调用回调，不等待FontFaceObserver
                    console.log('✅ 字体准备完成，直接使用:', font_name);
                    
                    // 清理Fabric.js字体缓存
                    if (typeof fabric !== 'undefined' && fabric.util && fabric.util.clearFabricFontCache) {
                        fabric.util.clearFabricFontCache();
                        console.log('Fabric.js字体缓存已清理');
                    }
                    
                    // 强制重新渲染所有画布
                    if (scope.stages) {
                        scope.stages.forEach(function(stage, index) {
                            if (stage && stage.canvas) {
                                stage.canvas.requestRenderAll();
                                console.log('画布已重新渲染:', index);
                            }
                        });
                    }
                    
                    // 确保关闭loading状态
                    if (scope.toggleStageLoading) {
                        scope.toggleStageLoading();
                        console.log('Loading状态已关闭');
                    }
                    
                    if (callback) callback(font_name);
                } else {
                    // 对于其他字体，使用原始函数
                    console.log('使用原始insertTemplateFont函数:', font_name);
                    originalInsertTemplateFont.call(this, font_name, callback);
                }
            };
            
            console.log('✅ insertTemplateFont函数已简化');
        }
        
        // 3. 简化版addText函数
        if (scope.addText) {
            var originalAddText = scope.addText;
            
            scope.addText = function(content, type, additionalObj) {
                console.log('简化版addText被调用:', content, type);
                
                // 确保字体已加载
                var fontName = additionalObj && additionalObj.fontFamily ? additionalObj.fontFamily : NBDESIGNCONFIG.default_font.alias;
                
                var isNewLocalFont = newLocalFonts.some(function(font) {
                    return font.alias === fontName;
                });
                
                if (isNewLocalFont) {
                    console.log('检测到新增本地字体，确保加载:', fontName);
                    
                    // 先加载字体，再添加文本
                    scope.insertTemplateFont(fontName, function(loadedFont) {
                        console.log('字体加载完成，添加文本:', loadedFont);
                        originalAddText.call(this, content, type, additionalObj);
                    });
                } else {
                    // 对于其他字体，使用原始函数
                    originalAddText.call(this, content, type, additionalObj);
                }
            };
            
            console.log('✅ addText函数已简化');
        }
        
        // 4. 创建测试函数
        scope.testSimpleFonts = function() {
            console.log('开始测试简化字体加载');
            
            newLocalFonts.forEach(function(font) {
                console.log('测试字体:', font.alias);
                
                // 测试字体加载
                scope.insertTemplateFont(font.alias, function(result) {
                    console.log('字体加载结果:', font.alias, result);
                    
                    // 测试添加文本
                    if (result === font.alias) {
                        scope.addText('测试文本 - ' + font.name, 'bodytext', {
                            fontFamily: font.alias,
                            fontSize: 24,
                            top: 100 + Math.random() * 200,
                            left: 100 + Math.random() * 200
                        });
                    }
                });
            });
        };
        
        // 5. 自动测试字体
        setTimeout(function() {
            console.log('开始自动测试简化字体加载');
            scope.testSimpleFonts();
        }, 1000);
        
        console.log('=== 简化字体加载脚本完成 ===');
    }
    
    // 开始等待Angular
    waitForAngular();
    
})(); 