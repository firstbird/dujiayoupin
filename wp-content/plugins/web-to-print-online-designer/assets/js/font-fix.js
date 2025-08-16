/**
 * 字体加载修复脚本
 * 解决字体艺术效果不显示的问题
 */

(function() {
    'use strict';
    
    // 字体加载计数器
    var fontLoadCounter = {};
    
    // 等待 Angular 应用加载完成
    // function waitForAngular() {
    //     if (typeof angular !== 'undefined' && angular.element(document).scope()) {
    //         var $scope = angular.element(document).scope();
            
    //         console.log('字体修复脚本已加载，开始修复字体加载问题');
            
    //         // 重写 insertTypography 函数
    //         if ($scope.insertTypography) {
    //             var originalInsertTypography = $scope.insertTypography;
    //             $scope.insertTypography = function(typo) {
    //                 console.log('=== 插入字体开始 ===');
    //                 console.log('字体对象:', typo);
                    
    //                 // 重置字体加载计数器
    //                 fontLoadCounter = {};
                    
    //                 // 确保字体资源已加载
    //                 if (!$scope.resource.font.data) {
    //                     $scope.resource.font.data = [];
    //                 }
                    
    //                 // 调用原始函数
    //                 originalInsertTypography.call(this, typo);
    //             };
    //         }
            
    //         // 重写 insertTemplateFont 函数
    //         if ($scope.insertTemplateFont) {
    //             var originalInsertTemplateFont = $scope.insertTemplateFont;
    //             $scope.insertTemplateFont = function(font_name, callback) {
    //                 // 增加调用计数器
    //                 fontLoadCounter[font_name] = (fontLoadCounter[font_name] || 0) + 1;
                    
    //                 console.log('=== insertTemplateFont 调用 #' + fontLoadCounter[font_name] + ' ===');
    //                 console.log('字体名称:', font_name);
    //                 console.log('调用堆栈:', new Error().stack);
                    
    //                 // 检查字体是否已存在
    //                 var existingFont = $scope.resource.font.data.filter(function(font) {
    //                     return font.alias === font_name;
    //                 })[0];
                    
    //                 if (existingFont) {
    //                     console.log('字体已存在于资源中:', existingFont);
                        
    //                     // 确保字体CSS已加载
    //                     var font_id = font_name.replace(/\s/gi, '').toLowerCase();
    //                     if (!jQuery('#' + font_id).length) {
    //                         if (existingFont.type === 'google') {
    //                             // Google字体处理
    //                             console.log('加载Google字体:', font_name);
    //                         } else {
    //                             // 自定义字体处理
    //                             var font_url = existingFont.url;
    //                             if (!(font_url.indexOf("http") > -1)) {
    //                                 font_url = NBDESIGNCONFIG['font_url'] + font_url;
    //                             }
                                
    //                             var css = "<style type='text/css' id='" + font_id + "'>";
    //                             css += "@font-face {font-family: '" + font_name + "';";
    //                             css += "src: url('" + font_url + "') format('truetype');";
    //                             css += "font-weight: normal;font-style: normal;";
    //                             css += "}";
    //                             css += "</style>";
                                
    //                             jQuery("head").append(css);
    //                             console.log('已添加字体CSS:', font_url);
    //                         }
    //                     } else {
    //                         console.log('字体CSS已存在，跳过添加');
    //                     }
                        
    //                     // 使用FontFaceObserver确保字体加载
    //                     if (typeof FontFaceObserver !== 'undefined') {
    //                         var font = new FontFaceObserver(font_name);
    //                         font.load('Sample Text').then(function() {
    //                             console.log('字体加载成功:', font_name);
    //                             fabric.util.clearFabricFontCache();
    //                             if (callback) callback(font_name);
    //                         }).catch(function() {
    //                             console.error('字体加载失败:', font_name);
    //                             if (callback) callback('Arial');
    //                         });
    //                     } else {
    //                         console.log('FontFaceObserver未加载，直接调用回调');
    //                         if (callback) callback(font_name);
    //                     }
    //                 } else {
    //                     console.log('字体不存在于资源中，调用原始函数');
    //                     // 调用原始函数
    //                     originalInsertTemplateFont.call(this, font_name, callback);
    //                 }
    //             };
    //         }
            
    //         // 重写 renderTextAfterLoadFont 函数
    //         if ($scope.renderTextAfterLoadFont) {
    //             var originalRenderTextAfterLoadFont = $scope.renderTextAfterLoadFont;
    //             $scope.renderTextAfterLoadFont = function(layers, callback) {
    //                 console.log('=== renderTextAfterLoadFont 开始 ===');
    //                 console.log('图层数量:', layers.length);
                    
    //                 if (layers.length === 0) {
    //                     console.log('没有图层需要渲染');
    //                     if (callback) callback();
    //                     return;
    //                 }
                    
    //                 var loadedCount = 0;
    //                 var totalLayers = layers.length;
                    
    //                 layers.forEach(function(item, index) {
    //                     if (['text', 'i-text', 'curvedText', 'textbox'].indexOf(item.type) > -1) {
    //                         var fontFamily = item.get('fontFamily');
    //                         var fontWeight = item.get('fontWeight') || '';
    //                         var fontStyle = item.get('fontStyle') || '';
                            
    //                         console.log('处理文本图层 #' + (index + 1) + ':', {
    //                             type: item.type,
    //                             fontFamily: fontFamily,
    //                             fontWeight: fontWeight,
    //                             fontStyle: fontStyle,
    //                             text: item.get('text')
    //                         });
                            
    //                         // 确保字体已加载
    //                         var _font = $scope.getFontInfo(fontFamily);
    //                         if (_font) {
    //                             console.log('找到字体信息:', _font);
    //                             item.set({objectCaching: false});
    //                             fabric.util.clearFabricFontCache();
                                
    //                             if (typeof FontFaceObserver !== 'undefined') {
    //                                 var font = new FontFaceObserver(fontFamily, {
    //                                     weight: fontWeight,
    //                                     style: fontStyle
    //                                 });
                                    
    //                                 font.load('Sample Text').then(function() {
    //                                     console.log('文本图层字体加载成功:', fontFamily);
    //                                     fabric.util.clearFabricFontCache();
    //                                     item.initDimensions();
    //                                     item.setCoords();
    //                                     loadedCount++;
                                        
    //                                     if (loadedCount === totalLayers && callback) {
    //                                         console.log('所有图层处理完成');
    //                                         callback();
    //                                     }
    //                                 }).catch(function() {
    //                                     console.error('文本图层字体加载失败:', fontFamily);
    //                                     loadedCount++;
                                        
    //                                     if (loadedCount === totalLayers && callback) {
    //                                         console.log('所有图层处理完成（包含失败）');
    //                                         callback();
    //                                     }
    //                                 });
    //                             } else {
    //                                 console.log('FontFaceObserver未加载，跳过字体检测');
    //                                 loadedCount++;
    //                                 if (loadedCount === totalLayers && callback) {
    //                                     callback();
    //                                 }
    //                             }
    //                         } else {
    //                             console.error('字体信息未找到:', fontFamily);
    //                             loadedCount++;
    //                             if (loadedCount === totalLayers && callback) {
    //                                 callback();
    //                             }
    //                         }
    //                     } else {
    //                         console.log('跳过非文本图层:', item.type);
    //                         loadedCount++;
    //                         if (loadedCount === totalLayers && callback) {
    //                             callback();
    //                         }
    //                     }
    //                 });
    //             };
    //         }
            
    //         // 添加字体预加载功能
    //         $scope.preloadFont = function(fontName, fontUrl) {
    //             var font_id = fontName.replace(/\s/gi, '').toLowerCase();
    //             if (!jQuery('#' + font_id).length) {
    //                 var css = "<style type='text/css' id='" + font_id + "'>";
    //                 css += "@font-face {font-family: '" + fontName + "';";
    //                 css += "src: url('" + fontUrl + "') format('truetype');";
    //                 css += "font-weight: normal;font-style: normal;";
    //                 css += "}";
    //                 css += "</style>";
                    
    //                 jQuery("head").append(css);
    //                 console.log('预加载字体:', fontName, fontUrl);
    //             }
    //         };
            
    //         // 监听字体资源加载
    //         if ($scope.resource && $scope.resource.font && $scope.resource.font.data) {
    //             $scope.resource.font.data.forEach(function(font) {
    //                 if (font.type === 'ttf' && font.url) {
    //                     var fontUrl = font.url;
    //                     if (!(fontUrl.indexOf("http") > -1)) {
    //                         fontUrl = NBDESIGNCONFIG['font_url'] + fontUrl;
    //                     }
    //                     $scope.preloadFont(font.alias, fontUrl);
    //                 }
    //             });
    //         }
            
    //         console.log('字体修复脚本加载完成');
    //     } else {
    //         setTimeout(waitForAngular, 100);
    //     }
    // }
    
    // // 启动修复脚本
    // waitForAngular();
    
})(); 