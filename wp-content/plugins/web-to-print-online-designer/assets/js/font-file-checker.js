/**
 * 字体文件检查工具
 * 验证本地字体文件是否存在
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.resource && $scope.resource.font) {
                setupFontFileChecker($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupFontFileChecker($scope) {
        console.log('=== 字体文件检查工具已加载 ===');
        
        // 本地字体目录配置
        var LOCAL_FONT_DIRS = [
            '/wp-content/plugins/web-to-print-online-designer/assets/fonts/',
            '/wp-content/plugins/web-to-print-online-designer/assets/css/fonts/'
        ];
        
        // 添加全局检查工具
        window.fontFileChecker = {
            // 检查单个字体文件
            checkFontFile: function(fontName) {
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('字体不存在于资源中:', fontName);
                    return null;
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                
                console.log('检查字体文件:', {
                    fontName: fontName,
                    originalUrl: originalUrl,
                    fileName: fileName
                });
                
                // 检查所有本地目录
                var checkPromises = LOCAL_FONT_DIRS.map(function(dir) {
                    var filePath = dir + fileName;
                    return fetch(filePath, { method: 'HEAD' })
                        .then(function(response) {
                            return {
                                directory: dir,
                                filePath: filePath,
                                exists: response.ok,
                                status: response.status,
                                statusText: response.statusText
                            };
                        })
                        .catch(function(error) {
                            return {
                                directory: dir,
                                filePath: filePath,
                                exists: false,
                                error: error.message || error
                            };
                        });
                });
                
                Promise.all(checkPromises).then(function(results) {
                    var foundFiles = results.filter(r => r.exists);
                    
                    console.log('字体文件检查结果:', {
                        fontName: fontName,
                        fileName: fileName,
                        allResults: results,
                        foundFiles: foundFiles,
                        exists: foundFiles.length > 0
                    });
                    
                    if (foundFiles.length > 0) {
                        console.log('✅ 找到字体文件:', foundFiles[0].filePath);
                    } else {
                        console.error('❌ 未找到字体文件:', fileName);
                        console.log('检查的路径:', results.map(r => r.filePath));
                    }
                    
                    return {
                        fontName: fontName,
                        fileName: fileName,
                        results: results,
                        foundFiles: foundFiles,
                        exists: foundFiles.length > 0
                    };
                });
            },
            
            // 检查所有字体文件
            checkAllFontFiles: function() {
                var fonts = $scope.resource.font.data;
                console.log('开始检查所有字体文件，共', fonts.length, '个字体');
                
                var checkPromises = fonts.map(function(font) {
                    return window.fontFileChecker.checkFontFile(font.alias);
                });
                
                Promise.all(checkPromises).then(function(allResults) {
                    var summary = {
                        total: allResults.length,
                        found: allResults.filter(r => r && r.exists).length,
                        missing: allResults.filter(r => r && !r.exists).length,
                        results: allResults
                    };
                    
                    console.log('字体文件检查摘要:', summary);
                    
                    // 显示缺失的字体文件
                    var missingFonts = allResults.filter(r => r && !r.exists);
                    if (missingFonts.length > 0) {
                        console.warn('缺失的字体文件:');
                        missingFonts.forEach(function(result) {
                            console.warn('-', result.fontName, ':', result.fileName);
                        });
                    }
                    
                    return summary;
                });
            },
            
            // 获取字体文件的本地路径
            getLocalFontPath: function(fontName) {
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    return null;
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                
                // 返回所有可能的本地路径
                return LOCAL_FONT_DIRS.map(function(dir) {
                    return dir + fileName;
                });
            },
            
            // 测试字体加载
            testFontLoad: function(fontName) {
                console.log('测试字体加载:', fontName);
                
                var fontPaths = this.getLocalFontPath(fontName);
                if (!fontPaths) {
                    console.error('无法获取字体路径:', fontName);
                    return;
                }
                
                console.log('字体路径:', fontPaths);
                
                // 检查第一个路径
                fetch(fontPaths[0], { method: 'HEAD' })
                    .then(function(response) {
                        if (response.ok) {
                            console.log('✅ 字体文件存在:', fontPaths[0]);
                            
                            // 尝试加载字体
                            if (typeof FontFaceObserver !== 'undefined') {
                                var font = new FontFaceObserver(fontName);
                                font.load('Sample Text', 5000).then(function() {
                                    console.log('✅ 字体加载成功:', fontName);
                                }).catch(function(error) {
                                    console.error('❌ 字体加载失败:', fontName, error);
                                });
                            } else {
                                console.warn('FontFaceObserver不可用');
                            }
                        } else {
                            console.error('❌ 字体文件不存在:', fontPaths[0]);
                        }
                    })
                    .catch(function(error) {
                        console.error('❌ 字体文件检查失败:', error);
                    });
            },
            
            // 列出所有本地字体文件
            listLocalFontFiles: function() {
                console.log('=== 本地字体文件列表 ===');
                
                // 已知的字体文件列表
                var knownFonts = [
                    'fontawesome-webfont.ttf',
                    'fontawesome-webfont.woff',
                    'fontawesome-webfont.woff2',
                    'glyphicons-halflings-regular.woff',
                    'glyphicons-regular.woff',
                    'FontAwesome.otf',
                    'online-design.woff',
                    'online-design.ttf',
                    'nbd-vista.woff',
                    'nbd-vista.ttf',
                    'FontNBD.ttf',
                    'FontNBD.woff'
                ];
                
                var allChecks = [];
                
                LOCAL_FONT_DIRS.forEach(function(dir) {
                    knownFonts.forEach(function(fileName) {
                        var filePath = dir + fileName;
                        var check = fetch(filePath, { method: 'HEAD' })
                            .then(function(response) {
                                return {
                                    directory: dir,
                                    fileName: fileName,
                                    filePath: filePath,
                                    exists: response.ok,
                                    size: response.headers.get('content-length')
                                };
                            })
                            .catch(function() {
                                return {
                                    directory: dir,
                                    fileName: fileName,
                                    filePath: filePath,
                                    exists: false
                                };
                            });
                        allChecks.push(check);
                    });
                });
                
                Promise.all(allChecks).then(function(results) {
                    var existingFiles = results.filter(r => r.exists);
                    var missingFiles = results.filter(r => !r.exists);
                    
                    console.log('存在的字体文件:', existingFiles);
                    console.log('缺失的字体文件:', missingFiles);
                    
                    return {
                        existing: existingFiles,
                        missing: missingFiles,
                        total: results.length
                    };
                });
            }
        };
        
        console.log('字体文件检查工具已加载');
        console.log('使用方法:');
        console.log('fontFileChecker.checkFontFile("字体名称") - 检查单个字体文件');
        console.log('fontFileChecker.checkAllFontFiles() - 检查所有字体文件');
        console.log('fontFileChecker.getLocalFontPath("字体名称") - 获取字体本地路径');
        console.log('fontFileChecker.testFontLoad("字体名称") - 测试字体加载');
        console.log('fontFileChecker.listLocalFontFiles() - 列出本地字体文件');
    }
    
    // 启动检查工具
    waitForAngular();
    
})(); 