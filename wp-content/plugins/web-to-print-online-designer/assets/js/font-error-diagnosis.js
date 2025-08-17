/**
 * 字体错误诊断脚本
 * 帮助诊断font.load()错误的具体原因
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化完成
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.insertTemplateFont) {
                setupErrorDiagnosis($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupErrorDiagnosis($scope) {
        console.log('=== 字体错误诊断脚本已加载 ===');
        
        // 添加全局诊断命令
        window.fontErrorDiagnosis = {
            // 诊断字体加载错误
            diagnoseFontLoadError: function(fontName) {
                console.log('=== 诊断字体加载错误 ===');
                console.log('字体名称:', fontName);
                
                try {
                    // 1. 检查FontFaceObserver是否可用
                    if (typeof FontFaceObserver === 'undefined') {
                        console.error('❌ FontFaceObserver不可用');
                        return { error: 'FontFaceObserver不可用' };
                    }
                    console.log('✅ FontFaceObserver可用');
                    
                    // 2. 检查字体信息
                    var _font = $scope.getFontInfo(fontName);
                    console.log('字体信息:', _font);
                    
                    if (!_font) {
                        console.error('❌ 无法获取字体信息');
                        return { error: '无法获取字体信息' };
                    }
                    
                    // 3. 检查字体子集
                    if (!_font.subset) {
                        console.error('❌ 字体缺少子集信息');
                        return { error: '字体缺少子集信息', font: _font };
                    }
                    console.log('✅ 字体子集:', _font.subset);
                    
                    // 4. 检查settings.subsets
                    if (!$scope.settings.subsets) {
                        console.error('❌ settings.subsets不存在');
                        return { error: 'settings.subsets不存在' };
                    }
                    console.log('✅ settings.subsets存在');
                    
                    // 5. 检查特定子集
                    if (!$scope.settings.subsets[_font.subset]) {
                        console.error('❌ 子集不存在:', _font.subset);
                        console.log('可用子集:', Object.keys($scope.settings.subsets));
                        return { error: '子集不存在', subset: _font.subset, availableSubsets: Object.keys($scope.settings.subsets) };
                    }
                    console.log('✅ 子集存在:', $scope.settings.subsets[_font.subset]);
                    
                    // 6. 检查预览文本
                    var previewText = $scope.settings.subsets[_font.subset]['preview_text'];
                    if (!previewText) {
                        console.error('❌ 预览文本不存在');
                        return { error: '预览文本不存在', subset: _font.subset };
                    }
                    console.log('✅ 预览文本:', previewText);
                    
                    // 7. 检查字体CSS是否已加载
                    var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                    var existingCSS = jQuery('#' + font_id);
                    console.log('字体CSS状态:', {
                        fontId: font_id,
                        cssExists: existingCSS.length > 0,
                        cssElement: existingCSS[0]
                    });
                    
                    if (!existingCSS.length) {
                        console.warn('⚠️ 字体CSS未加载，这可能导致字体加载失败');
                    }
                    
                    // 8. 尝试创建FontFaceObserver实例
                    try {
                        var font = new FontFaceObserver(fontName);
                        console.log('✅ FontFaceObserver实例创建成功');
                        
                        // 9. 尝试加载字体
                        console.log('开始加载字体...');
                        font.load(previewText).then(function() {
                            console.log('✅ 字体加载成功:', fontName);
                        }).catch(function(error) {
                            console.error('❌ 字体加载失败:', error);
                            console.log('错误详情:', {
                                fontName: fontName,
                                previewText: previewText,
                                fontInfo: _font,
                                cssLoaded: existingCSS.length > 0
                            });
                        });
                        
                    } catch (error) {
                        console.error('❌ 创建FontFaceObserver实例失败:', error);
                        return { error: '创建FontFaceObserver实例失败', details: error };
                    }
                    
                } catch (error) {
                    console.error('❌ 诊断过程中发生错误:', error);
                    return { error: '诊断过程中发生错误', details: error };
                }
            },
            
            // 检查所有字体配置
            checkAllFontConfigs: function() {
                console.log('=== 检查所有字体配置 ===');
                
                var config = {
                    settings: $scope.settings,
                    resourceFonts: $scope.resource.font.data,
                    subsets: $scope.settings.subsets
                };
                
                console.log('完整配置:', config);
                
                // 检查关键配置项
                var checks = {
                    'settings存在': !!$scope.settings,
                    'resource.font.data存在': !!$scope.resource.font.data,
                    'settings.subsets存在': !!$scope.settings.subsets,
                    '字体数量': $scope.resource.font.data ? $scope.resource.font.data.length : 0,
                    '子集数量': $scope.settings.subsets ? Object.keys($scope.settings.subsets).length : 0
                };
                
                console.log('配置检查结果:', checks);
                
                return config;
            },
            
            // 测试字体加载的各个步骤
            testFontLoadSteps: function(fontName) {
                console.log('=== 测试字体加载步骤 ===');
                console.log('字体名称:', fontName);
                
                var steps = [];
                
                // 步骤1: 检查字体资源
                try {
                    var existingFont = $scope.resource.font.data.filter(function(font) {
                        return font.alias === fontName;
                    })[0];
                    
                    if (existingFont) {
                        steps.push({ step: 1, name: '字体资源检查', status: '✅ 通过', data: existingFont });
                    } else {
                        steps.push({ step: 1, name: '字体资源检查', status: '❌ 失败', error: '字体不存在于资源中' });
                        return steps;
                    }
                } catch (error) {
                    steps.push({ step: 1, name: '字体资源检查', status: '❌ 异常', error: error });
                    return steps;
                }
                
                // 步骤2: 获取字体信息
                try {
                    var _font = $scope.getFontInfo(fontName);
                    if (_font) {
                        steps.push({ step: 2, name: '获取字体信息', status: '✅ 通过', data: _font });
                    } else {
                        steps.push({ step: 2, name: '获取字体信息', status: '❌ 失败', error: '无法获取字体信息' });
                        return steps;
                    }
                } catch (error) {
                    steps.push({ step: 2, name: '获取字体信息', status: '❌ 异常', error: error });
                    return steps;
                }
                
                // 步骤3: 检查子集配置
                try {
                    if (_font.subset && $scope.settings.subsets[_font.subset]) {
                        steps.push({ step: 3, name: '子集配置检查', status: '✅ 通过', data: $scope.settings.subsets[_font.subset] });
                    } else {
                        steps.push({ step: 3, name: '子集配置检查', status: '❌ 失败', error: '子集配置不存在', subset: _font.subset });
                        return steps;
                    }
                } catch (error) {
                    steps.push({ step: 3, name: '子集配置检查', status: '❌ 异常', error: error });
                    return steps;
                }
                
                // 步骤4: 获取预览文本
                try {
                    var previewText = $scope.settings.subsets[_font.subset]['preview_text'];
                    if (previewText) {
                        steps.push({ step: 4, name: '获取预览文本', status: '✅ 通过', data: previewText });
                    } else {
                        steps.push({ step: 4, name: '获取预览文本', status: '❌ 失败', error: '预览文本不存在' });
                        return steps;
                    }
                } catch (error) {
                    steps.push({ step: 4, name: '获取预览文本', status: '❌ 异常', error: error });
                    return steps;
                }
                
                // 步骤5: 创建FontFaceObserver
                try {
                    if (typeof FontFaceObserver !== 'undefined') {
                        var font = new FontFaceObserver(fontName);
                        steps.push({ step: 5, name: '创建FontFaceObserver', status: '✅ 通过', data: font });
                    } else {
                        steps.push({ step: 5, name: '创建FontFaceObserver', status: '❌ 失败', error: 'FontFaceObserver不可用' });
                        return steps;
                    }
                } catch (error) {
                    steps.push({ step: 5, name: '创建FontFaceObserver', status: '❌ 异常', error: error });
                    return steps;
                }
                
                // 步骤6: 尝试加载字体
                try {
                    font.load(previewText).then(function() {
                        steps.push({ step: 6, name: '字体加载', status: '✅ 通过', data: '字体加载成功' });
                        console.log('字体加载步骤结果:', steps);
                    }).catch(function(error) {
                        steps.push({ step: 6, name: '字体加载', status: '❌ 失败', error: error });
                        console.log('字体加载步骤结果:', steps);
                    });
                } catch (error) {
                    steps.push({ step: 6, name: '字体加载', status: '❌ 异常', error: error });
                    console.log('字体加载步骤结果:', steps);
                }
                
                return steps;
            },
            
            // 修复字体加载问题
            fixFontLoadIssue: function(fontName) {
                console.log('=== 尝试修复字体加载问题 ===');
                console.log('字体名称:', fontName);
                
                var fixes = [];
                
                // 修复1: 确保CSS已加载
                try {
                    var existingFont = $scope.resource.font.data.filter(function(font) {
                        return font.alias === fontName;
                    })[0];
                    
                    if (existingFont) {
                        var font_id = fontName.replace(/\s/gi, '').toLowerCase();
                        var existingCSS = jQuery('#' + font_id);
                        
                        if (!existingCSS.length) {
                            console.log('修复1: 注入字体CSS...');
                            
                            if (existingFont.type === 'google') {
                                var googleFontUrl = 'https://fonts.googleapis.com/css?family=' + fontName.replace(/\s/gi, '+') + ':400,400i,700,700i';
                                jQuery('head').append('<link id="' + font_id + '" href="' + googleFontUrl + '" rel="stylesheet" type="text/css">');
                                fixes.push({ fix: 1, name: '注入Google字体CSS', status: '✅ 完成', data: googleFontUrl });
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
                                fixes.push({ fix: 1, name: '注入自定义字体CSS', status: '✅ 完成', data: font_url });
                            }
                        } else {
                            fixes.push({ fix: 1, name: '字体CSS检查', status: '✅ 已存在', data: 'CSS已加载' });
                        }
                    }
                } catch (error) {
                    fixes.push({ fix: 1, name: '字体CSS修复', status: '❌ 失败', error: error });
                }
                
                // 修复2: 使用备用预览文本
                try {
                    var _font = $scope.getFontInfo(fontName);
                    var previewText = 'Sample Text'; // 使用简单的备用文本
                    
                    if (_font && _font.subset && $scope.settings.subsets[_font.subset]) {
                        previewText = $scope.settings.subsets[_font.subset]['preview_text'] || previewText;
                    }
                    
                    fixes.push({ fix: 2, name: '预览文本修复', status: '✅ 完成', data: previewText });
                    
                    // 修复3: 重新尝试加载
                    if (typeof FontFaceObserver !== 'undefined') {
                        var font = new FontFaceObserver(fontName);
                        font.load(previewText).then(function() {
                            fixes.push({ fix: 3, name: '重新加载字体', status: '✅ 成功', data: '字体加载成功' });
                            console.log('字体修复结果:', fixes);
                        }).catch(function(error) {
                            fixes.push({ fix: 3, name: '重新加载字体', status: '❌ 失败', error: error });
                            console.log('字体修复结果:', fixes);
                        });
                    } else {
                        fixes.push({ fix: 3, name: '重新加载字体', status: '❌ 失败', error: 'FontFaceObserver不可用' });
                        console.log('字体修复结果:', fixes);
                    }
                    
                } catch (error) {
                    fixes.push({ fix: 2, name: '预览文本修复', status: '❌ 失败', error: error });
                    console.log('字体修复结果:', fixes);
                }
                
                return fixes;
            }
        };
        
        console.log('字体错误诊断工具已加载');
        console.log('使用方法:');
        console.log('fontErrorDiagnosis.diagnoseFontLoadError("字体名称") - 诊断字体加载错误');
        console.log('fontErrorDiagnosis.checkAllFontConfigs() - 检查所有字体配置');
        console.log('fontErrorDiagnosis.testFontLoadSteps("字体名称") - 测试字体加载步骤');
        console.log('fontErrorDiagnosis.fixFontLoadIssue("字体名称") - 尝试修复字体加载问题');
    }
    
    // 启动诊断
    waitForAngular();
    
})(); 