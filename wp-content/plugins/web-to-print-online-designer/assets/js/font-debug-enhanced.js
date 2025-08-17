/**
 * 增强字体调试脚本
 * 帮助诊断字体CSS注入问题
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                applyEnhancedDebug($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function applyEnhancedDebug($scope) {
        console.log('=== 应用增强字体调试 ===');
        
        // 保存原始函数
        var originalInsertTemplateFont = $scope.insertTemplateFont;
        
        // 重写insertTemplateFont函数
        $scope.insertTemplateFont = function(font_name, callback) {
            console.log('=== 增强调试: insertTemplateFont 开始 ===');
            console.log('字体名称:', font_name);
            
            // 检查字体是否已存在
            var existingFont = $scope.resource.font.data.filter(function(font) {
                return font.alias === font_name;
            })[0];
            
            console.log('existingFont:', existingFont);
            
            if (existingFont) {
                console.log('字体已存在:', existingFont);
                console.log('字体类型:', existingFont.type);
                console.log('字体URL:', existingFont.url);
                
                // 确保字体CSS已加载
                var font_id = font_name.replace(/\s/gi, '').toLowerCase();
                console.log('生成的font_id:', font_id);
                
                // 检查CSS是否已存在
                var existingCSS = jQuery('#' + font_id);
                console.log('现有CSS元素数量:', existingCSS.length);
                console.log('现有CSS元素:', existingCSS);
                
                if (!existingCSS.length) {
                    console.log('CSS不存在，准备注入...');
                    
                    if (existingFont.type === 'google') {
                        // Google字体处理
                        console.log('=== 加载Google字体 ===');
                        console.log('字体名称:', font_name);
                        
                        var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + font_name.replace(/\s/gi, '+') + ':400,400i,700,700i';
                        console.log('Google字体URL:', googleFontUrl);
                        
                        jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                        console.log('Google字体CSS已添加');
                        
                    } else {
                        // 自定义字体处理
                        console.log('=== 加载自定义字体 ===');
                        console.log('字体名称:', font_name);
                        
                        var font_url = existingFont.url;
                        console.log('原始字体URL:', font_url);
                        
                        if (!(font_url.indexOf("http") > -1)) {
                            font_url = NBDESIGNCONFIG['font_url'] + font_url;
                            console.log('完整字体URL:', font_url);
                        }
                        
                        var css = "<style type='text/css' id='" + font_id + "'>";
                        css += "@font-face {font-family: '" + font_name + "';";
                        css += "src: url('" + font_url + "') format('truetype');";
                        css += "font-weight: normal;font-style: normal;";
                        css += "font-display: swap;";
                        css += "}";
                        css += "</style>";
                        
                        console.log('生成的CSS:', css);
                        
                        jQuery("head").append(css);
                        console.log('自定义字体CSS已添加');
                    }
                } else {
                    console.log('CSS已存在，跳过注入');
                }
                
                // 使用FontFaceObserver确保字体加载
                if (typeof FontFaceObserver !== 'undefined') {
                    console.log('FontFaceObserver可用，开始检测字体加载...');
                    var _font = $scope.getFontInfo(font_name);
                    var font = new FontFaceObserver(font_name);
                    
                    // 使用正确的预览文本
                    var previewText = $scope.settings.subsets[_font.subset]['preview_text'] || 'Sample Text';
                    console.log('字体加载预览文本:', previewText);
                    
                    font.load(previewText).then(function() {
                        console.log('字体加载成功:', font_name);
                        fabric.util.clearFabricFontCache();
                        if (callback) callback(font_name);
                    }).catch(function() {
                        console.error('字体加载失败:', font_name);
                        if (callback) callback('Arial');
                    });
                } else {
                    console.log('FontFaceObserver不可用，直接调用回调');
                    if (callback) callback(font_name);
                }
            } else {
                console.log('字体不存在于资源中，调用原始函数');
                // 调用原始函数
                originalInsertTemplateFont.call(this, font_name, callback);
            }
            
            console.log('=== 增强调试: insertTemplateFont 结束 ===');
        };
        
        // 添加全局调试命令
        window.fontDebugEnhanced = {
            // 检查字体资源
            checkFontResource: function(fontName) {
                var $scope = angular.element(document).scope();
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                console.log('字体资源检查:', {
                    fontName: fontName,
                    exists: !!existingFont,
                    fontData: existingFont
                });
                
                return existingFont;
            },
            
            // 检查CSS状态
            checkCSSStatus: function(fontName) {
                var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                var existingCSS = jQuery('#' + font_id);
                
                console.log('CSS状态检查:', {
                    fontName: fontName,
                    fontId: font_id,
                    cssExists: existingCSS.length > 0,
                    cssElement: existingCSS[0]
                });
                
                return {
                    fontId: font_id,
                    exists: existingCSS.length > 0,
                    element: existingCSS[0]
                };
            },
            
            // 检查页面中的所有字体CSS
            checkAllFontCSS: function() {
                var fontCSS = [];
                jQuery('head style, head link[rel="stylesheet"]').each(function() {
                    var $element = jQuery(this);
                    var id = $element.attr('id');
                    if (id) {
                        fontCSS.push({
                            id: id,
                            type: $element.prop('tagName').toLowerCase(),
                            href: $element.attr('href'),
                            content: $element.prop('tagName').toLowerCase() === 'style' ? $element.text().substring(0, 100) + '...' : null
                        });
                    }
                });
                
                console.log('所有字体CSS:', fontCSS);
                return fontCSS;
            },
            
            // 强制重新注入字体CSS
            forceInjectCSS: function(fontName) {
                var $scope = angular.element(document).scope();
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('字体不存在:', fontName);
                    return false;
                }
                
                var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                
                // 移除现有CSS
                jQuery('#' + font_id).remove();
                
                if (existingFont.type === 'google') {
                    var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + fontName.replace(/\s/gi, '+') + ':400,400i,700,700i';
                    jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                    console.log('强制注入Google字体CSS:', googleFontUrl);
                } else {
                                            var font_url = existingFont.url;
                        // 修改为使用本地文件路径
                        if (!(font_url.indexOf("http") > -1)) {
                            var localFontPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + font_url;
                            font_url = localFontPath;
                        } else {
                            // 如果是远程URL，也尝试使用本地路径
                            var fileName = font_url.split('/').pop();
                            var localFontPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
                            font_url = localFontPath;
                        }
                    
                    var css = "<style type='text/css' id='" + font_id + "'>";
                    css += "@font-face {font-family: '" + fontName + "';";
                    css += "src: url('" + font_url + "') format('truetype');";
                    css += "font-weight: normal;font-style: normal;";
                    css += "font-display: swap;";
                    css += "}";
                    css += "</style>";
                    
                    jQuery("head").append(css);
                    console.log('强制注入自定义字体CSS:', font_url);
                }
                
                return true;
            },
            
            // 测试字体渲染
            testFontRendering: function(fontName) {
                var canvas = document.createElement('canvas');
                canvas.width = 400;
                canvas.height = 100;
                var context = canvas.getContext('2d');
                
                // 设置背景
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                
                // 测试字体
                context.font = '24px ' + fontName + ', Arial';
                context.fillStyle = '#000000';
                context.fillText('Test: ' + fontName, 10, 30);
                
                // 显示结果
                var img = new Image();
                img.src = canvas.toDataURL();
                img.style.cssText = 'border: 1px solid #ccc; margin: 10px;';
                document.body.appendChild(img);
                
                console.log('字体渲染测试完成:', fontName);
                return canvas.toDataURL();
            }
        };
        
        console.log('增强字体调试已应用');
        console.log('使用方法:');
        console.log('fontDebugEnhanced.checkFontResource("字体名称") - 检查字体资源');
        console.log('fontDebugEnhanced.checkCSSStatus("字体名称") - 检查CSS状态');
        console.log('fontDebugEnhanced.checkAllFontCSS() - 检查所有字体CSS');
        console.log('fontDebugEnhanced.forceInjectCSS("字体名称") - 强制注入CSS');
        console.log('fontDebugEnhanced.testFontRendering("字体名称") - 测试字体渲染');
    }
    
    // 启动调试
    waitForAngular();
    
})(); 