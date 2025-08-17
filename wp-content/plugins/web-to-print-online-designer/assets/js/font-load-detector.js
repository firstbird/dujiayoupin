/**
 * 增强字体加载检测脚本
 * 提供详细的错误信息和诊断
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupFontLoadDetector($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupFontLoadDetector($scope) {
        console.log('=== 增强字体加载检测器已加载 ===');
        
        // 添加全局检测命令
        window.fontLoadDetector = {
            // 增强的字体加载检测
            detectFontLoad: function(fontName, options) {
                options = options || {};
                var timeout = options.timeout || 3000;
                var previewText = options.previewText || 'Sample Text';
                
                console.log('=== 开始字体加载检测 ===');
                console.log('字体名称:', fontName);
                console.log('预览文本:', previewText);
                console.log('超时时间:', timeout + 'ms');
                
                var detectionResult = {
                    fontName: fontName,
                    previewText: previewText,
                    startTime: new Date(),
                    steps: []
                };
                
                // 步骤1: 检查FontFaceObserver可用性
                if (typeof FontFaceObserver === 'undefined') {
                    detectionResult.steps.push({
                        step: 1,
                        name: 'FontFaceObserver检查',
                        status: '❌ 失败',
                        error: 'FontFaceObserver库未加载'
                    });
                    console.error('FontFaceObserver库未加载');
                    return detectionResult;
                }
                
                detectionResult.steps.push({
                    step: 1,
                    name: 'FontFaceObserver检查',
                    status: '✅ 通过',
                    data: 'FontFaceObserver可用'
                });
                
                // 步骤2: 检查字体CSS是否已加载
                var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                var existingCSS = jQuery('#' + font_id);
                var cssLoaded = existingCSS.length > 0;
                
                detectionResult.steps.push({
                    step: 2,
                    name: '字体CSS检查',
                    status: cssLoaded ? '✅ 已加载' : '⚠️ 未加载',
                    data: {
                        fontId: font_id,
                        cssExists: cssLoaded,
                        cssElement: existingCSS[0] || null
                    }
                });
                
                // 步骤3: 检查字体是否在系统字体列表中
                var systemFonts = this.checkSystemFonts(fontName);
                detectionResult.steps.push({
                    step: 3,
                    name: '系统字体检查',
                    status: systemFonts.exists ? '✅ 存在' : '❌ 不存在',
                    data: systemFonts
                });
                
                // 步骤4: 创建FontFaceObserver实例
                try {
                    var font = new FontFaceObserver(fontName);
                    detectionResult.steps.push({
                        step: 4,
                        name: 'FontFaceObserver实例创建',
                        status: '✅ 成功',
                        data: font
                    });
                } catch (error) {
                    detectionResult.steps.push({
                        step: 4,
                        name: 'FontFaceObserver实例创建',
                        status: '❌ 失败',
                        error: error.message || error
                    });
                    console.error('创建FontFaceObserver实例失败:', error);
                    return detectionResult;
                }
                
                // 步骤5: 尝试加载字体
                var loadStartTime = new Date();
                
                font.load(previewText, timeout).then(function() {
                    var loadEndTime = new Date();
                    var loadDuration = loadEndTime - loadStartTime;
                    
                    detectionResult.steps.push({
                        step: 5,
                        name: '字体加载',
                        status: '✅ 成功',
                        data: {
                            loadDuration: loadDuration + 'ms',
                            loadStartTime: loadStartTime,
                            loadEndTime: loadEndTime
                        }
                    });
                    
                    detectionResult.endTime = new Date();
                    detectionResult.totalDuration = detectionResult.endTime - detectionResult.startTime;
                    detectionResult.success = true;
                    
                    console.log('✅ 字体加载成功:', fontName);
                    console.log('加载耗时:', loadDuration + 'ms');
                    console.log('完整检测结果:', detectionResult);
                    
                }).catch(function(error) {
                    var loadEndTime = new Date();
                    var loadDuration = loadEndTime - loadStartTime;
                    
                    // 分析错误原因
                    var errorAnalysis = analyzeFontLoadError(error, fontName, previewText, cssLoaded);
                    
                    detectionResult.steps.push({
                        step: 5,
                        name: '字体加载',
                        status: '❌ 失败',
                        error: error.message || error,
                        analysis: errorAnalysis,
                        data: {
                            loadDuration: loadDuration + 'ms',
                            loadStartTime: loadStartTime,
                            loadEndTime: loadEndTime
                        }
                    });
                    
                    detectionResult.endTime = new Date();
                    detectionResult.totalDuration = detectionResult.endTime - detectionResult.startTime;
                    detectionResult.success = false;
                    detectionResult.errorAnalysis = errorAnalysis;
                    
                    console.error('❌ 字体加载失败:', fontName);
                    console.error('错误详情:', error);
                    console.error('错误分析:', errorAnalysis);
                    console.log('完整检测结果:', detectionResult);
                });
                
                return detectionResult;
            },
            
            // 检查系统字体
            checkSystemFonts: function(fontName) {
                var testString = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                var testFont = 'monospace';
                var testSize = '72px';
                
                var canvas = document.createElement('canvas');
                var context = canvas.getContext('2d');
                
                // 使用默认字体测量
                context.font = testSize + ' ' + testFont;
                var defaultWidth = context.measureText(testString).width;
                
                // 使用目标字体测量
                context.font = testSize + ' ' + fontName + ', ' + testFont;
                var targetWidth = context.measureText(testString).width;
                
                return {
                    fontName: fontName,
                    exists: targetWidth !== defaultWidth,
                    defaultWidth: defaultWidth,
                    targetWidth: targetWidth,
                    difference: targetWidth - defaultWidth
                };
            },
            
            // 分析字体加载错误
            analyzeFontLoadError: function(error, fontName, previewText, cssLoaded) {
                var analysis = {
                    possibleCauses: [],
                    recommendations: []
                };
                
                // 检查错误类型
                if (error.name === 'TimeoutError') {
                    analysis.possibleCauses.push('字体加载超时');
                    analysis.recommendations.push('增加超时时间');
                    analysis.recommendations.push('检查网络连接');
                } else if (error.name === 'NetworkError') {
                    analysis.possibleCauses.push('网络错误');
                    analysis.recommendations.push('检查字体文件URL');
                    analysis.recommendations.push('检查网络连接');
                } else {
                    analysis.possibleCauses.push('未知错误');
                }
                
                // 检查CSS状态
                if (!cssLoaded) {
                    analysis.possibleCauses.push('字体CSS未加载');
                    analysis.recommendations.push('确保字体CSS已注入到页面');
                }
                
                // 检查预览文本
                if (!previewText || previewText.trim() === '') {
                    analysis.possibleCauses.push('预览文本为空');
                    analysis.recommendations.push('提供有效的预览文本');
                }
                
                // 检查字体名称
                if (fontName.includes(' ') && !fontName.startsWith('"') && !fontName.endsWith('"')) {
                    analysis.possibleCauses.push('字体名称包含空格但未加引号');
                    analysis.recommendations.push('为包含空格的字体名称添加引号');
                }
                
                return analysis;
            },
            
            // 测试字体渲染
            testFontRendering: function(fontName, text) {
                text = text || 'Sample Text';
                console.log('=== 测试字体渲染 ===');
                console.log('字体名称:', fontName);
                console.log('测试文本:', text);
                
                var canvas = document.createElement('canvas');
                canvas.width = 800;
                canvas.height = 200;
                var context = canvas.getContext('2d');
                
                // 设置背景
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);
                
                // 测试不同字体大小
                var sizes = [16, 24, 32, 48];
                var y = 30;
                
                sizes.forEach(function(size) {
                    context.font = size + 'px ' + fontName + ', Arial';
                    context.fillStyle = '#000000';
                    context.fillText(size + 'px: ' + text, 10, y);
                    y += size + 10;
                });
                
                // 显示结果
                var img = new Image();
                img.src = canvas.toDataURL();
                img.style.cssText = 'border: 2px solid #333; margin: 10px; max-width: 100%;';
                document.body.appendChild(img);
                
                console.log('✅ 字体渲染测试完成');
                return canvas.toDataURL();
            },
            
            // 批量检测字体
            batchDetectFonts: function(fontNames) {
                console.log('=== 批量字体检测 ===');
                console.log('字体列表:', fontNames);
                
                var results = [];
                var promises = [];
                
                fontNames.forEach(function(fontName, index) {
                    var promise = new Promise(function(resolve) {
                        setTimeout(function() {
                            var result = window.fontLoadDetector.detectFontLoad(fontName);
                            resolve(result);
                        }, index * 1000); // 间隔1秒
                    });
                    promises.push(promise);
                });
                
                Promise.all(promises).then(function(allResults) {
                    console.log('批量检测完成:', allResults);
                    
                    var summary = {
                        total: allResults.length,
                        success: allResults.filter(r => r.success).length,
                        failed: allResults.filter(r => !r.success).length,
                        results: allResults
                    };
                    
                    console.log('检测摘要:', summary);
                    return summary;
                });
            }
        };
        
        // 全局错误分析函数
        window.analyzeFontLoadError = function(error, fontName, previewText, cssLoaded) {
            return window.fontLoadDetector.analyzeFontLoadError(error, fontName, previewText, cssLoaded);
        };
        
        console.log('增强字体加载检测器已加载');
        console.log('使用方法:');
        console.log('fontLoadDetector.detectFontLoad("字体名称") - 检测字体加载');
        console.log('fontLoadDetector.testFontRendering("字体名称") - 测试字体渲染');
        console.log('fontLoadDetector.batchDetectFonts(["字体1", "字体2"]) - 批量检测');
    }
    
    // 启动检测器
    waitForAngular();
    
})(); 