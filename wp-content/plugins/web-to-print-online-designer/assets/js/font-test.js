/**
 * 字体测试脚本
 * 验证字体加载参数修复是否有效
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupFontTest($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupFontTest($scope) {
        console.log('=== 字体测试脚本已加载 ===');
        
        // 添加全局测试命令
        window.fontTest = {
            // 测试字体加载参数
            testFontLoadParams: function(fontName) {
                console.log('=== 测试字体加载参数 ===');
                console.log('字体名称:', fontName);
                
                var _font = $scope.getFontInfo(fontName);
                console.log('字体信息:', _font);
                
                if (_font && _font.subset) {
                    var previewText = $scope.settings.subsets[_font.subset]['preview_text'];
                    console.log('预览文本:', previewText);
                    
                    if (typeof FontFaceObserver !== 'undefined') {
                        var font = new FontFaceObserver(fontName);
                        console.log('开始加载字体:', fontName);
                        
                        font.load(previewText).then(function() {
                            console.log('✅ 字体加载成功:', fontName);
                            console.log('使用的预览文本:', previewText);
                        }).catch(function() {
                            console.error('❌ 字体加载失败:', fontName);
                            console.log('尝试的预览文本:', previewText);
                        });
                    } else {
                        console.warn('FontFaceObserver不可用');
                    }
                } else {
                    console.error('无法获取字体信息或字体子集');
                }
            },
            
            // 测试字体CSS注入
            testFontCSSInjection: function(fontName) {
                console.log('=== 测试字体CSS注入 ===');
                console.log('字体名称:', fontName);
                
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (existingFont) {
                    console.log('字体资源:', existingFont);
                    
                    var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                    console.log('字体ID:', font_id);
                    
                    var existingCSS = jQuery('#' + font_id);
                    console.log('现有CSS:', existingCSS.length > 0 ? existingCSS[0] : '不存在');
                    
                    if (!existingCSS.length) {
                        console.log('CSS不存在，准备注入...');
                        
                        if (existingFont.type === 'google') {
                            var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + fontName.replace(/\s/gi, '+') + ':400,400i,700,700i';
                            jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                            console.log('✅ Google字体CSS已注入:', googleFontUrl);
                        } else {
                            var font_url = existingFont.url;
                            if (!(font_url.indexOf("http") > -1)) {
                                font_url = NBDESIGNCONFIG['font_url'] + font_url;
                            }
                            
                            var css = "<style type='text/css' id='" + font_id + "'>";
                            css += "@font-face {font-family: '" + fontName + "';";
                            css += "src: url('" + font_url + "') format('truetype');";
                            css += "font-weight: normal;font-style: normal;";
                            css += "font-display: swap;";
                            css += "}";
                            css += "</style>";
                            
                            jQuery("head").append(css);
                            console.log('✅ 自定义字体CSS已注入:', font_url);
                        }
                    } else {
                        console.log('CSS已存在，跳过注入');
                    }
                } else {
                    console.error('字体不存在于资源中');
                }
            },
            
            // 测试完整的字体加载流程
            testCompleteFontFlow: function(fontName) {
                console.log('=== 测试完整字体加载流程 ===');
                console.log('字体名称:', fontName);
                
                // 1. 检查字体资源
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('❌ 字体不存在于资源中');
                    return;
                }
                
                console.log('✅ 字体资源存在:', existingFont);
                
                // 2. 注入CSS
                this.testFontCSSInjection(fontName);
                
                // 3. 等待CSS加载
                setTimeout(function() {
                    // 4. 测试字体加载
                    window.fontTest.testFontLoadParams(fontName);
                }, 100);
            },
            
            // 列出所有可用字体
            listAllFonts: function() {
                console.log('=== 所有可用字体 ===');
                var fonts = $scope.resource.font.data;
                
                fonts.forEach(function(font, index) {
                    console.log((index + 1) + '.', {
                        alias: font.alias,
                        type: font.type,
                        url: font.url,
                        subset: font.subset
                    });
                });
                
                return fonts;
            },
            
            // 测试字体渲染
            testFontRendering: function(fontName) {
                console.log('=== 测试字体渲染 ===');
                console.log('字体名称:', fontName);
                
                var canvas = document.createElement('canvas');
                canvas.width = 600;
                canvas.height = 150;
                var context = canvas.getContext('2d');
                
                // 设置背景
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                
                // 测试不同大小的字体
                var sizes = [16, 24, 32, 48];
                var y = 30;
                
                sizes.forEach(function(size) {
                    context.font = size + 'px ' + fontName + ', Arial';
                    context.fillStyle = '#000000';
                    context.fillText('Test ' + size + 'px: ' + fontName, 10, y);
                    y += size + 10;
                });
                
                // 显示结果
                var img = new Image();
                img.src = canvas.toDataURL();
                img.style.cssText = 'border: 2px solid #333; margin: 10px; max-width: 100%;';
                document.body.appendChild(img);
                
                console.log('✅ 字体渲染测试完成');
                return canvas.toDataURL();
            }
        };
        
        console.log('字体测试工具已加载');
        console.log('使用方法:');
        console.log('fontTest.testFontLoadParams("字体名称") - 测试字体加载参数');
        console.log('fontTest.testFontCSSInjection("字体名称") - 测试字体CSS注入');
        console.log('fontTest.testCompleteFontFlow("字体名称") - 测试完整字体加载流程');
        console.log('fontTest.listAllFonts() - 列出所有可用字体');
        console.log('fontTest.testFontRendering("字体名称") - 测试字体渲染');
    }
    
    // 启动测试
    waitForAngular();
    
})(); 