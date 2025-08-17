/**
 * 本地字体加载器
 * 用于检查和加载本地字体文件
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupLocalFontLoader($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupLocalFontLoader($scope) {
        console.log('=== 本地字体加载器已加载 ===');
        
        // 本地字体路径配置
        var LOCAL_FONT_PATH = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/';
        
        // 添加全局字体加载器
        window.localFontLoader = {
            // 检查本地字体文件是否存在
            checkLocalFontFile: function(fontName) {
                var $scope = angular.element(document).scope();
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('字体不存在于资源中:', fontName);
                    return { exists: false, error: '字体不存在于资源中' };
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                var localPath = LOCAL_FONT_PATH + fileName;
                
                console.log('检查本地字体文件:', {
                    fontName: fontName,
                    originalUrl: originalUrl,
                    fileName: fileName,
                    localPath: localPath
                });
                
                // 使用fetch检查文件是否存在
                return fetch(localPath, { method: 'HEAD' })
                    .then(function(response) {
                        var result = {
                            fontName: fontName,
                            originalUrl: originalUrl,
                            fileName: fileName,
                            localPath: localPath,
                            exists: response.ok,
                            status: response.status,
                            statusText: response.statusText
                        };
                        
                        console.log('本地字体文件检查结果:', result);
                        return result;
                    })
                    .catch(function(error) {
                        var result = {
                            fontName: fontName,
                            originalUrl: originalUrl,
                            fileName: fileName,
                            localPath: localPath,
                            exists: false,
                            error: error.message || error
                        };
                        
                        console.error('本地字体文件检查失败:', result);
                        return result;
                    });
            },
            
            // 获取本地字体路径
            getLocalFontPath: function(fontName) {
                var $scope = angular.element(document).scope();
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    return null;
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                return LOCAL_FONT_PATH + fileName;
            },
            
            // 注入本地字体CSS
            injectLocalFontCSS: function(fontName) {
                var $scope = angular.element(document).scope();
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('字体不存在于资源中:', fontName);
                    return false;
                }
                
                var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                var existingCSS = jQuery('#' + font_id);
                
                if (existingCSS.length > 0) {
                    console.log('字体CSS已存在:', fontName);
                    return true;
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                var localPath = LOCAL_FONT_PATH + fileName;
                
                console.log('注入本地字体CSS:', {
                    fontName: fontName,
                    localPath: localPath
                });
                
                var css = "<style type='text/css' id='" + font_id + "'>";
                css += "@font-face {font-family: '" + fontName + "';";
                css += "src: url('" + localPath + "') format('truetype');";
                css += "font-weight: normal;font-style: normal;";
                css += "font-display: swap;";
                css += "}";
                css += "</style>";
                
                jQuery("head").append(css);
                console.log('本地字体CSS已注入:', fontName);
                return true;
            },
            
            // 加载本地字体
            loadLocalFont: function(fontName, callback) {
                console.log('开始加载本地字体:', fontName);
                
                // 1. 检查字体文件
                this.checkLocalFontFile(fontName).then(function(result) {
                    if (!result.exists) {
                        console.error('本地字体文件不存在:', result);
                        if (callback) callback(null, result);
                        return;
                    }
                    
                    // 2. 注入CSS
                    window.localFontLoader.injectLocalFontCSS(fontName);
                    
                    // 3. 使用FontFaceObserver加载字体
                    if (typeof FontFaceObserver !== 'undefined') {
                        var font = new FontFaceObserver(fontName);
                        var previewText = 'Sample Text';
                        
                        // 获取预览文本
                        var $scope = angular.element(document).scope();
                        var _font = $scope.getFontInfo(fontName);
                        if (_font && _font.subset && $scope.settings.subsets[_font.subset]) {
                            previewText = $scope.settings.subsets[_font.subset]['preview_text'] || previewText;
                        }
                        
                        console.log('使用预览文本加载字体:', previewText);
                        
                        font.load(previewText, 10000).then(function() {
                            console.log('✅ 本地字体加载成功:', fontName);
                            fabric.util.clearFabricFontCache();
                            if (callback) callback(fontName, null);
                        }).catch(function(error) {
                            console.error('❌ 本地字体加载失败:', fontName, error);
                            if (callback) callback(null, error);
                        });
                    } else {
                        console.warn('FontFaceObserver不可用，直接调用回调');
                        if (callback) callback(fontName, null);
                    }
                });
            },
            
            // 批量检查本地字体文件
            checkAllLocalFonts: function() {
                var $scope = angular.element(document).scope();
                var fonts = $scope.resource.font.data;
                var results = [];
                
                console.log('开始检查所有本地字体文件...');
                
                var promises = fonts.map(function(font) {
                    return window.localFontLoader.checkLocalFontFile(font.alias);
                });
                
                Promise.all(promises).then(function(allResults) {
                    var summary = {
                        total: allResults.length,
                        exists: allResults.filter(r => r.exists).length,
                        missing: allResults.filter(r => !r.exists).length,
                        results: allResults
                    };
                    
                    console.log('本地字体文件检查摘要:', summary);
                    
                    // 显示缺失的字体文件
                    var missingFonts = allResults.filter(r => !r.exists);
                    if (missingFonts.length > 0) {
                        console.warn('缺失的字体文件:');
                        missingFonts.forEach(function(result) {
                            console.warn('-', result.fontName, ':', result.localPath);
                        });
                    }
                    
                    return summary;
                });
            },
            
            // 列出本地字体目录中的文件
            listLocalFontFiles: function() {
                console.log('=== 本地字体目录文件列表 ===');
                
                // 常见的字体文件扩展名
                var fontExtensions = ['.ttf', '.woff', '.woff2', '.otf', '.eot'];
                var fontFiles = [];
                
                // 这里可以添加实际的目录扫描逻辑
                // 由于浏览器限制，我们只能通过已知的字体文件来检查
                var knownFonts = [
                    'fontawesome-webfont.ttf',
                    'fontawesome-webfont.woff',
                    'fontawesome-webfont.woff2',
                    'glyphicons-halflings-regular.woff',
                    'glyphicons-regular.woff'
                ];
                
                knownFonts.forEach(function(fileName) {
                    var filePath = LOCAL_FONT_PATH + fileName;
                    fetch(filePath, { method: 'HEAD' })
                        .then(function(response) {
                            fontFiles.push({
                                name: fileName,
                                path: filePath,
                                exists: response.ok,
                                size: response.headers.get('content-length')
                            });
                            
                            if (fontFiles.length === knownFonts.length) {
                                console.log('本地字体文件列表:', fontFiles);
                            }
                        })
                        .catch(function() {
                            fontFiles.push({
                                name: fileName,
                                path: filePath,
                                exists: false
                            });
                            
                            if (fontFiles.length === knownFonts.length) {
                                console.log('本地字体文件列表:', fontFiles);
                            }
                        });
                });
            }
        };
        
        console.log('本地字体加载器已加载');
        console.log('使用方法:');
        console.log('localFontLoader.checkLocalFontFile("字体名称") - 检查本地字体文件');
        console.log('localFontLoader.injectLocalFontCSS("字体名称") - 注入本地字体CSS');
        console.log('localFontLoader.loadLocalFont("字体名称", callback) - 加载本地字体');
        console.log('localFontLoader.checkAllLocalFonts() - 检查所有本地字体');
        console.log('localFontLoader.listLocalFontFiles() - 列出本地字体文件');
    }
    
    // 启动本地字体加载器
    waitForAngular();
    
})(); 