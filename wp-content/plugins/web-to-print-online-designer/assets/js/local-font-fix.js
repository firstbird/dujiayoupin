/**
 * 本地字体修复脚本
 * 将字体加载改为使用本地文件路径
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                applyLocalFontFix($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function applyLocalFontFix($scope) {
        console.log('=== 应用本地字体修复 ===');
        
        // 保存原始函数
        var originalInsertTemplateFont = $scope.insertTemplateFont;
        
        // 重写insertTemplateFont函数
        $scope.insertTemplateFont = function(font_name, callback) {
            console.log('本地字体修复版 - 插入字体:', font_name);
            
            // 检查字体是否已存在
            var existingFont = $scope.resource.font.data.filter(function(font) {
                return font.alias === font_name;
            })[0];
            
            if (existingFont) {
                console.log('字体已存在:', existingFont);
                
                // 确保字体CSS已加载
                var font_id = font_name.replace(/\s/gi, '').toLowerCase();
                var existingCSS = jQuery('#' + font_id);
                
                if (!existingCSS.length) {
                    console.log('CSS不存在，准备注入本地字体...');
                    
                    if (existingFont.type === 'google') {
                        // Google字体处理
                        console.log('Google字体:', font_name);
                        var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + font_name.replace(/\s/gi, '+') + ':400,400i,700,700i';
                        jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                        console.log('Google字体CSS已添加');
                        
                    } else {
                        // 自定义字体处理 - 使用本地路径
                        console.log('自定义字体:', font_name);
                        
                        var originalUrl = existingFont.url;
                        var fileName = originalUrl.split('/').pop(); // 获取文件名
                        var localFontPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
                        
                        console.log('原始URL:', originalUrl);
                        console.log('文件名:', fileName);
                        console.log('本地路径:', localFontPath);
                        
                        var css = "<style type='text/css' id='" + font_id + "'>";
                        css += "@font-face {font-family: '" + font_name + "';";
                        css += "src: url('" + localFontPath + "') format('truetype');";
                        css += "font-weight: normal;font-style: normal;";
                        css += "font-display: swap;";
                        css += "}";
                        css += "</style>";
                        
                        jQuery("head").append(css);
                        console.log('本地字体CSS已添加:', localFontPath);
                    }
                } else {
                    console.log('CSS已存在，跳过注入');
                }
                
                // 使用FontFaceObserver确保字体加载
                if (typeof FontFaceObserver !== 'undefined') {
                    try {
                        var _font = $scope.getFontInfo(font_name);
                        var font = new FontFaceObserver(font_name);
                        
                        // 安全地获取预览文本
                        var previewText = 'Sample Text';
                        try {
                            if (_font && _font.subset && $scope.settings.subsets && $scope.settings.subsets[_font.subset]) {
                                previewText = $scope.settings.subsets[_font.subset]['preview_text'] || previewText;
                            }
                        } catch (e) {
                            console.warn('获取预览文本失败，使用默认值:', e);
                        }
                        
                        console.log('字体加载预览文本:', previewText);
                        
                        font.load(previewText, 10000).then(function() {
                            console.log('✅ 本地字体加载成功:', font_name);
                            fabric.util.clearFabricFontCache();
                            if (callback) callback(font_name);
                        }).catch(function(error) {
                            console.error('❌ 本地字体加载失败:', font_name, error);
                            if (callback) callback('Arial');
                        });
                    } catch (error) {
                        console.error('FontFaceObserver初始化失败:', error);
                        if (callback) callback('Arial');
                    }
                } else {
                    console.warn('FontFaceObserver不可用');
                    if (callback) callback(font_name);
                }
            } else {
                console.log('字体不存在于资源中，调用原始函数');
                originalInsertTemplateFont.call(this, font_name, callback);
            }
        };
        
        // 添加全局工具函数
        window.localFontTools = {
            // 检查本地字体文件
            checkLocalFont: function(fontName) {
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('字体不存在:', fontName);
                    return null;
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                var localPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
                
                console.log('本地字体信息:', {
                    fontName: fontName,
                    originalUrl: originalUrl,
                    fileName: fileName,
                    localPath: localPath
                });
                
                return {
                    fontName: fontName,
                    originalUrl: originalUrl,
                    fileName: fileName,
                    localPath: localPath
                };
            },
            
            // 测试本地字体加载
            testLocalFont: function(fontName) {
                var fontInfo = this.checkLocalFont(fontName);
                if (!fontInfo) return;
                
                // 检查文件是否存在
                fetch(fontInfo.localPath, { method: 'HEAD' })
                    .then(function(response) {
                        console.log('本地字体文件检查:', {
                            fontName: fontName,
                            exists: response.ok,
                            status: response.status
                        });
                    })
                    .catch(function(error) {
                        console.error('本地字体文件检查失败:', error);
                    });
            }
        };
        
        console.log('本地字体修复已应用');
        console.log('使用方法:');
        console.log('localFontTools.checkLocalFont("字体名称") - 检查本地字体信息');
        console.log('localFontTools.testLocalFont("字体名称") - 测试本地字体加载');
    }
    
    // 启动修复
    waitForAngular();
    
})(); 