/**
 * 本地字体加载测试脚本
 */

(function() {
    'use strict';
    
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupTest($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupTest($scope) {
        console.log('=== 本地字体加载测试 ===');
        
        // 测试字体列表
        var testFonts = [
            'glyphicons-regular',
            'glyphicons-halflings-regular',
            'fontawesome',
            'FontAwesome'
        ];
        
        // 添加测试函数到全局
        window.testLocalFonts = {
            // 测试单个字体
            testFont: function(fontName) {
                console.log('测试字体:', fontName);
                
                return new Promise(function(resolve, reject) {
                    $scope.insertTemplateFont(fontName, function(result) {
                        console.log('字体测试结果:', {
                            fontName: fontName,
                            result: result,
                            success: result === fontName
                        });
                        
                        if (result === fontName) {
                            resolve(result);
                        } else {
                            reject(new Error('字体加载失败: ' + result));
                        }
                    });
                });
            },
            
            // 测试所有字体
            testAllFonts: function() {
                console.log('开始测试所有字体...');
                
                var promises = testFonts.map(function(fontName) {
                    return window.testLocalFonts.testFont(fontName)
                        .then(function(result) {
                            return { fontName: fontName, success: true, result: result };
                        })
                        .catch(function(error) {
                            return { fontName: fontName, success: false, error: error.message };
                        });
                });
                
                Promise.all(promises).then(function(results) {
                    var successCount = results.filter(r => r.success).length;
                    var failCount = results.filter(r => !r.success).length;
                    
                    console.log('测试完成:', {
                        total: results.length,
                        success: successCount,
                        failed: failCount,
                        results: results
                    });
                    
                    // 显示失败的结果
                    var failedFonts = results.filter(r => !r.success);
                    if (failedFonts.length > 0) {
                        console.warn('失败的字体:');
                        failedFonts.forEach(function(result) {
                            console.warn('-', result.fontName, ':', result.error);
                        });
                    }
                    
                    return results;
                });
            },
            
            // 检查字体资源
            checkFontResources: function() {
                console.log('检查字体资源...');
                
                if ($scope.resource && $scope.resource.font && $scope.resource.font.data) {
                    console.log('字体资源数据:', {
                        total: $scope.resource.font.data.length,
                        fonts: $scope.resource.font.data.map(f => f.alias)
                    });
                } else {
                    console.warn('字体资源数据不存在');
                }
            },
            
            // 检查本地字体文件
            checkLocalFontFiles: function() {
                console.log('检查本地字体文件...');
                
                var localFonts = [
                    '/wp-content/plugins/web-to-print-online-designer/assets/fonts/glyphicons-regular.woff',
                    '/wp-content/plugins/web-to-print-online-designer/assets/fonts/glyphicons-halflings-regular.woff',
                    '/wp-content/plugins/web-to-print-online-designer/assets/fonts/fontawesome-webfont.woff'
                ];
                
                var promises = localFonts.map(function(fontPath) {
                    return fetch(fontPath, { method: 'HEAD' })
                        .then(function(response) {
                            return {
                                path: fontPath,
                                exists: response.ok,
                                status: response.status
                            };
                        })
                        .catch(function(error) {
                            return {
                                path: fontPath,
                                exists: false,
                                error: error.message
                            };
                        });
                });
                
                Promise.all(promises).then(function(results) {
                    console.log('本地字体文件检查结果:', results);
                    
                    var existingFiles = results.filter(r => r.exists);
                    var missingFiles = results.filter(r => !r.exists);
                    
                    console.log('存在的文件:', existingFiles.length);
                    console.log('缺失的文件:', missingFiles.length);
                    
                    if (missingFiles.length > 0) {
                        console.warn('缺失的字体文件:');
                        missingFiles.forEach(function(result) {
                            console.warn('-', result.path);
                        });
                    }
                    
                    return results;
                });
            },
            
            // 运行完整测试
            runFullTest: function() {
                console.log('=== 运行完整测试 ===');
                
                // 1. 检查字体资源
                this.checkFontResources();
                
                // 2. 检查本地字体文件
                this.checkLocalFontFiles();
                
                // 3. 测试字体加载
                setTimeout(function() {
                    window.testLocalFonts.testAllFonts();
                }, 1000);
            }
        };
        
        console.log('本地字体测试工具已加载');
        console.log('使用方法:');
        console.log('testLocalFonts.testFont("字体名") - 测试单个字体');
        console.log('testLocalFonts.testAllFonts() - 测试所有字体');
        console.log('testLocalFonts.checkFontResources() - 检查字体资源');
        console.log('testLocalFonts.checkLocalFontFiles() - 检查本地字体文件');
        console.log('testLocalFonts.runFullTest() - 运行完整测试');
        
        // 自动运行完整测试
        setTimeout(function() {
            window.testLocalFonts.runFullTest();
        }, 2000);
    }
    
    waitForAngular();
    
})(); 