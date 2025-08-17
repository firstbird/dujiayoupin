/**
 * 简化字体适配器
 */

(function() {
    'use strict';
    
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupFontAdapter($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupFontAdapter($scope) {
        console.log('=== 简化字体适配器已加载 ===');
        
        // 字体映射表
        var fontMap = {
            'glyphicons-regular': 'glyphicons-regular.woff',
            'glyphicons-halflings-regular': 'glyphicons-halflings-regular.woff',
            'fontawesome': 'fontawesome-webfont.woff',
            'FontAwesome': 'fontawesome-webfont.woff'
        };
        
        // 保存原始函数
        var originalInsertTemplateFont = $scope.insertTemplateFont;
        
        // 重写函数
        $scope.insertTemplateFont = function(font_name, callback) {
            console.log('适配器 - 插入字体:', font_name);
            
            // 检查映射表
            if (fontMap[font_name]) {
                var fileName = fontMap[font_name];
                var localPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
                
                console.log('使用映射字体:', {
                    fontName: font_name,
                    fileName: fileName,
                    localPath: localPath
                });
                
                loadLocalFont(font_name, localPath, callback);
                return;
            }
            
            // 检查资源数据
            var existingFont = null;
            if ($scope.resource && $scope.resource.font && $scope.resource.font.data) {
                existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === font_name;
                })[0];
            }
            
            if (existingFont) {
                console.log('从资源加载字体:', existingFont);
                loadFontFromResource(font_name, existingFont, callback);
            } else {
                console.log('字体不存在，使用默认字体');
                if (callback) callback('Arial');
            }
        };
        
        function loadLocalFont(fontName, localPath, callback) {
            var font_id = fontName.replace(/\s/gi, '').toLowerCase();
            var existingCSS = jQuery('#' + font_id);
            
            if (!existingCSS.length) {
                var css = "<style type='text/css' id='" + font_id + "'>";
                css += "@font-face {font-family: '" + fontName + "';";
                css += "src: url('" + localPath + "') format('woff');";
                css += "font-weight: normal;font-style: normal;";
                css += "}";
                css += "</style>";
                
                jQuery("head").append(css);
                console.log('本地字体CSS已注入:', localPath);
            }
            
            if (typeof FontFaceObserver !== 'undefined') {
                var font = new FontFaceObserver(fontName);
                font.load('Sample Text', 5000).then(function() {
                    console.log('✅ 字体加载成功:', fontName);
                    fabric.util.clearFabricFontCache();
                    if (callback) callback(fontName);
                }).catch(function(error) {
                    console.error('❌ 字体加载失败:', fontName, error);
                    if (callback) callback('Arial');
                });
            } else {
                if (callback) callback(fontName);
            }
        }
        
        function loadFontFromResource(fontName, existingFont, callback) {
            var font_id = fontName.replace(/\s/gi, '').toLowerCase();
            var existingCSS = jQuery('#' + font_id);
            
            if (!existingCSS.length) {
                if (existingFont.type === 'google') {
                    var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + fontName.replace(/\s/gi, '+') + ':400,400i,700,700i';
                    jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                } else {
                    var originalUrl = existingFont.url;
                    var fileName = originalUrl.split('/').pop();
                    var localPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
                    
                    var css = "<style type='text/css' id='" + font_id + "'>";
                    css += "@font-face {font-family: '" + fontName + "';";
                    css += "src: url('" + localPath + "') format('truetype');";
                    css += "font-weight: normal;font-style: normal;";
                    css += "}";
                    css += "</style>";
                    
                    jQuery("head").append(css);
                }
            }
            
            if (typeof FontFaceObserver !== 'undefined') {
                var font = new FontFaceObserver(fontName);
                font.load('Sample Text', 5000).then(function() {
                    fabric.util.clearFabricFontCache();
                    if (callback) callback(fontName);
                }).catch(function(error) {
                    if (callback) callback('Arial');
                });
            } else {
                if (callback) callback(fontName);
            }
        }
        
        // 添加工具函数
        window.fontAdapter = {
            addFontMapping: function(fontName, fileName) {
                fontMap[fontName] = fileName;
                console.log('添加字体映射:', fontName, fileName);
            },
            
            listFontMappings: function() {
                console.log('字体映射:', fontMap);
                return fontMap;
            },
            
            testFont: function(fontName) {
                $scope.insertTemplateFont(fontName, function(result) {
                    console.log('字体测试结果:', result);
                });
            }
        };
        
        console.log('简化字体适配器已加载');
        console.log('使用方法:');
        console.log('fontAdapter.addFontMapping("字体名", "文件名.woff") - 添加字体映射');
        console.log('fontAdapter.listFontMappings() - 列出字体映射');
        console.log('fontAdapter.testFont("字体名") - 测试字体');
    }
    
    waitForAngular();
    
})(); 