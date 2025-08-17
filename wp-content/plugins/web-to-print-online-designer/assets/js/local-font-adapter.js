/**
 * 本地字体适配器
 * 适配本地字体加载，解决字体数据查找问题
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupLocalFontAdapter($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupLocalFontAdapter($scope) {
        console.log('=== 本地字体适配器已加载 ===');
        
        // 本地字体映射表
        var localFontMap = {
            'glyphicons-regular': {
                fileName: 'glyphicons-regular.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/glyphicons-regular.woff'
            },
            'glyphicons-halflings-regular': {
                fileName: 'glyphicons-halflings-regular.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/glyphicons-halflings-regular.woff'
            },
            'fontawesome': {
                fileName: 'fontawesome-webfont.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/fontawesome-webfont.woff'
            },
            'FontAwesome': {
                fileName: 'fontawesome-webfont.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/fontawesome-webfont.woff'
            },
            'online-design': {
                fileName: 'online-design.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/css/fonts/online-design.woff'
            },
            'nbd-vista': {
                fileName: 'nbd-vista.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/css/fonts/nbd-vista.woff'
            },
            'FontNBD': {
                fileName: 'FontNBD.woff',
                format: 'woff',
                localPath: '/wp-content/plugins/web-to-print-online-designer/assets/css/fonts/FontNBD.woff'
            }
        };
        
        // 保存原始函数
        var originalInsertTemplateFont = $scope.insertTemplateFont;
        
        // 重写insertTemplateFont函数
        $scope.insertTemplateFont = function(font_name, callback) {
            console.log('本地字体适配器 - 插入字体:', font_name);
            
            // 检查字体映射表
            var localFontInfo = localFontMap[font_name];
            if (localFontInfo) {
                console.log('找到本地字体映射:', localFontInfo);
                loadLocalFont(font_name, localFontInfo, callback);
                return;
            }
            
            // 检查字体是否已存在于资源中
            var existingFont = null;
            if ($scope.resource && $scope.resource.font && $scope.resource.font.data) {
                existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === font_name;
                })[0];
            }
            
            if (existingFont) {
                console.log('字体存在于资源中:', existingFont);
                loadFontFromResource(font_name, existingFont, callback);
            } else {
                console.log('字体不存在于资源中，尝试创建本地字体信息');
                createLocalFontInfo(font_name, callback);
            }
        };
        
        // 加载本地字体
        function loadLocalFont(fontName, fontInfo, callback) {
            console.log('加载本地字体:', fontName, fontInfo);
            
            var font_id = fontName.replace(/\s/gi, '').toLowerCase();
            var existingCSS = jQuery('#' + font_id);
            
            if (!existingCSS.length) {
                console.log('注入本地字体CSS:', fontInfo.localPath);
                
                var css = "<style type='text/css' id='" + font_id + "'>";
                css += "@font-face {font-family: '" + fontName + "';";
                css += "src: url('" + fontInfo.localPath + "') format('" + fontInfo.format + "');";
                css += "font-weight: normal;font-style: normal;";
                css += "font-display: swap;";
                css += "}";
                css += "</style>";
                
                jQuery("head").append(css);
                console.log('本地字体CSS已注入');
            } else {
                console.log('本地字体CSS已存在');
            }
            
            // 使用FontFaceObserver确保字体加载
            if (typeof FontFaceObserver !== 'undefined') {
                var font = new FontFaceObserver(fontName);
                font.load('Sample Text', 10000).then(function() {
                    console.log('✅ 本地字体加载成功:', fontName);
                    fabric.util.clearFabricFontCache();
                    if (callback) callback(fontName);
                }).catch(function(error) {
                    console.error('❌ 本地字体加载失败:', fontName, error);
                    if (callback) callback('Arial');
                });
            } else {
                console.warn('FontFaceObserver不可用');
                if (callback) callback(fontName);
            }
        }
        
        // 从资源加载字体
        function loadFontFromResource(fontName, existingFont, callback) {
            console.log('从资源加载字体:', fontName, existingFont);
            
            var font_id = fontName.replace(/\s/gi, '').toLowerCase();
            var existingCSS = jQuery('#' + font_id);
            
            if (!existingCSS.length) {
                if (existingFont.type === 'google') {
                    // Google字体处理
                    console.log('加载Google字体:', fontName);
                    var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + fontName.replace(/\s/gi, '+') + ':400,400i,700,700i';
                    jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                    console.log('Google字体CSS已添加');
                } else {
                    // 自定义字体处理 - 转换为本地路径
                    console.log('转换自定义字体为本地路径:', fontName);
                    
                    var originalUrl = existingFont.url;
                    var fileName = originalUrl.split('/').pop();
                    
                    // 尝试多个本地字体目录
                    var localFontPaths = [
                        '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName,
                        '/wp-content/plugins/web-to-print-online-designer/assets/css/fonts/' + fileName
                    ];
                    
                    var font_url = localFontPaths[0];
                    console.log('转换后的本地路径:', font_url);
                    
                    var css = "<style type='text/css' id='" + font_id + "'>";
                    css += "@font-face {font-family: '" + fontName + "';";
                    css += "src: url('" + font_url + "') format('truetype');";
                    css += "font-weight: normal;font-style: normal;";
                    css += "font-display: swap;";
                    css += "}";
                    css += "</style>";
                    
                    jQuery("head").append(css);
                    console.log('自定义字体CSS已添加');
                }
            } else {
                console.log('字体CSS已存在');
            }
            
            // 使用FontFaceObserver确保字体加载
            if (typeof FontFaceObserver !== 'undefined') {
                var font = new FontFaceObserver(fontName);
                font.load('Sample Text', 10000).then(function() {
                    console.log('✅ 字体加载成功:', fontName);
                    fabric.util.clearFabricFontCache();
                    if (callback) callback(fontName);
                }).catch(function(error) {
                    console.error('❌ 字体加载失败:', fontName, error);
                    if (callback) callback('Arial');
                });
            } else {
                console.warn('FontFaceObserver不可用');
                if (callback) callback(fontName);
            }
        }
        
        // 创建本地字体信息
        function createLocalFontInfo(fontName, callback) {
            console.log('创建本地字体信息:', fontName);
            
            // 尝试从字体名称推断文件名
            var fileName = fontName.toLowerCase().replace(/[^a-z0-9]/g, '') + '.woff';
            var localPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
            
            console.log('推断的字体文件:', {
                fontName: fontName,
                fileName: fileName,
                localPath: localPath
            });
            
            // 检查文件是否存在
            fetch(localPath, { method: 'HEAD' })
                .then(function(response) {
                    if (response.ok) {
                        console.log('找到本地字体文件:', localPath);
                        
                        // 创建字体信息
                        var fontInfo = {
                            fileName: fileName,
                            format: 'woff',
                            localPath: localPath
                        };
                        
                        // 添加到映射表
                        localFontMap[fontName] = fontInfo;
                        
                        // 加载字体
                        loadLocalFont(fontName, fontInfo, callback);
                    } else {
                        console.error('本地字体文件不存在:', localPath);
                        if (callback) callback('Arial');
                    }
                })
                .catch(function(error) {
                    console.error('检查本地字体文件失败:', error);
                    if (callback) callback('Arial');
                });
        }
        
        // 添加全局工具函数
        window.localFontAdapter = {
            // 添加字体映射
            addFontMapping: function(fontName, fontInfo) {
                localFontMap[fontName] = fontInfo;
                console.log('添加字体映射:', fontName, fontInfo);
            },
            
            // 获取字体映射
            getFontMapping: function(fontName) {
                return localFontMap[fontName];
            },
            
            // 列出所有字体映射
            listFontMappings: function() {
                console.log('所有字体映射:', localFontMap);
                return localFontMap;
            },
            
            // 检查字体资源
            checkFontResource: function(fontName) {
                var existingFont = null;
                if ($scope.resource && $scope.resource.font && $scope.resource.font.data) {
                    existingFont = $scope.resource.font.data.filter(function(font) {
                        return font.alias === fontName;
                    })[0];
                }
                
                console.log('字体资源检查:', {
                    fontName: fontName,
                    exists: !!existingFont,
                    fontData: existingFont
                });
                
                return existingFont;
            },
            
            // 测试字体加载
            testFontLoad: function(fontName) {
                console.log('测试字体加载:', fontName);
                $scope.insertTemplateFont(fontName, function(loadedFont) {
                    console.log('字体加载测试完成:', loadedFont);
                });
            }
        };
        
        console.log('本地字体适配器已加载');
        console.log('使用方法:');
        console.log('localFontAdapter.addFontMapping("字体名", {fileName: "文件.woff", format: "woff", localPath: "路径"}) - 添加字体映射');
        console.log('localFontAdapter.getFontMapping("字体名") - 获取字体映射');
        console.log('localFontAdapter.listFontMappings() - 列出所有字体映射');
        console.log('localFontAdapter.checkFontResource("字体名") - 检查字体资源');
        console.log('localFontAdapter.testFontLoad("字体名") - 测试字体加载');
    }
    
    // 启动适配器
    waitForAngular();
    
})(); 