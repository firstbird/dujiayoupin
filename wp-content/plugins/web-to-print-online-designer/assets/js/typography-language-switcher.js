/**
 * 字体语言切换功能
 * 用于在中文和英文字体之间切换
 */

(function() {
    'use strict';
    
    // 等待Angular应用初始化
    function waitForAngular() {
        var app = angular.element(document.body).scope();
        
        if (app && app.$root) {
            // Angular应用已初始化
            console.log('Angular应用已初始化，开始初始化字体语言切换功能');
            initLanguageSwitcher();
        } else {
            // 等待Angular应用初始化
            setTimeout(waitForAngular, 100);
        }
    }
    
    // 页面加载完成后开始等待Angular
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForAngular);
    } else {
        waitForAngular();
    }
    
    function initLanguageSwitcher() {
        console.log('=== 初始化字体语言切换功能 ===');
        var $scope = angular.element(document.getElementById("designer-controller")).scope();

        // 确保获取正确的Angular作用域
        if (!$scope || !$scope.$root) {
            if (!$scope) {
                console.log('❌ 无法获取Angular作用域，尝试其他方法...');
                $scope = angular.element(document.body).scope();
            }
        }

        // 检查$scope是否有效
        if (!$scope) {
            console.log('❌ 无法获取有效的Angular作用域');
            return;
        }

        console.log('✅ 成功获取Angular作用域:', $scope);

        // 设置默认语言为中文
        $scope.currentLanguage = 'chinese';
        
        // 添加一个标志来跟踪是否已经打印过数据未加载的信息
        var hasLoggedDataNotLoaded = false;
        var dataLoadAttempts = 0;
        
        // 语言切换函数（简化版）
        $scope.switchLanguage = function(language) {
            // 如果语言没有变化，直接返回
            if ($scope.currentLanguage === language) {
                console.log('⚠️ 语言没有变化，忽略调用:', language);
                return;
            }
            
            console.log('=== 🔄 语言切换函数被调用 ===');
            console.log('📝 参数 language:', language);
            console.log('📝 切换前 currentLanguage:', $scope.currentLanguage);
            
            // 清除缓存，强制重新计算
            cachedFilteredData = [];
            lastLanguage = null;
            lastDataHash = null;
            
            // 更新语言
            $scope.currentLanguage = language;
            console.log('📝 切换后 currentLanguage:', $scope.currentLanguage);
            
            // 更新过滤后的数据
            updateFilteredTypographyData();
            console.log('📝 过滤后的字体数据数量:', $scope.filteredTypographyData.length);
            
            console.log('✅ 语言切换函数执行完成');
        };
        
        // 自定义字体预览图片URL生成函数
        // $scope.generateTypoLink = function(typo) {
        //     if (!typo || !typo.folder) {
        //         console.log('❌ 字体对象无效:', typo);
        //         return '';
        //     }
            
        //     // 生成字体预览图片URL
        //     var baseUrl = window.location.origin;
        //     var typoUrl = baseUrl + '/wp-content/plugins/web-to-print-online-designer/data/typography/store/' + typo.folder + '/frame_0.png';
            
        //     console.log('🖼️ 生成字体预览URL:', typoUrl, '字体:', typo);
        //     return typoUrl;
        // };
        
        // 缓存变量
        var cachedFilteredData = [];
        var lastLanguage = null;
        var lastDataHash = null;
        
        // 生成数据哈希值用于缓存
        function generateDataHash(data) {
            if (!data || !Array.isArray(data)) return '';
            return data.length + '_' + data.map(function(item) {
                return (item.id || '') + '_' + (item.language || '') + '_' + (item.name || '');
            }).join('_');
        }
        
        // 计算过滤后的字体数据
        function updateFilteredTypographyData() {
            console.log('updateFilteredTypographyData +++');
            // 检查resource对象是否存在
            if (!$scope.resource || !$scope.resource.typography || !$scope.resource.typography.data) {
                console.log('updateFilteredTypographyData 111');
                $scope.filteredTypographyData = [];
                return;
            }
            
            // 检查data数组是否为空
            if ($scope.resource.typography.data.length === 0) {
                $scope.filteredTypographyData = [];
                console.log('updateFilteredTypographyData 222');
                return;
            }
            
            // 直接使用当前作用域，避免重复获取
            var currentLanguage = $scope.currentLanguage;
            var currentData = $scope.resource.typography.data;
            var currentDataHash = generateDataHash(currentData);
            
            // 检查缓存是否有效
            if (cachedFilteredData.length > 0 && 
                lastLanguage === currentLanguage && 
                lastDataHash === currentDataHash) {
                // 使用缓存数据，不输出日志
                $scope.filteredTypographyData = cachedFilteredData;
                console.log('updateFilteredTypographyData 333');
                return;
            }
            
            // 缓存无效，重新计算
            console.log('=== 🔍 过滤字体数据函数被调用（重新计算） ===');
            console.log('📝 当前语言:', currentLanguage);
            console.log('📝 缓存状态: 语言变化=' + (lastLanguage !== currentLanguage) + ', 数据变化=' + (lastDataHash !== currentDataHash));
            
            // 如果字体数据没有language属性，尝试根据文件夹名称或名称推断语言
            $scope.resource.typography.data.forEach(function(typo) {
                console.log('TypographyData check forEach typo: ', typo);
                // typo.language = 'english';
                // if (!typo.language) {
                //     // 根据文件夹名称推断语言
                //     if (typo.folder && typo.folder.includes('sample')) {
                //         // 根据sample编号设置语言（奇数中文，偶数英文）
                //         var sampleNum = parseInt(typo.folder.replace('sample', ''));
                //         if (sampleNum % 2 === 1) {
                //             typo.language = 'chinese';
                //         } else {
                //             typo.language = 'english';
                //         }
                //     } else if (typo.name) {
                //         // 根据名称推断语言
                //         var hasChinese = /[\u4e00-\u9fff]/.test(typo.name);
                //         typo.language = hasChinese ? 'chinese' : 'english';
                //     } else {
                //         // 默认设置为中文
                //         typo.language = 'chinese';
                //     }
                // }
            });
            
            // todo8.25
            console.log('updateFilteredTypographyData done typography.data: ', $scope.resource.typography.data);
            var filteredData = $scope.resource.typography.data.filter(function(typo) {
                // 根据当前选择的语言过滤
                return typo.language === currentLanguage;
            });
            
            // 更新缓存
            cachedFilteredData = filteredData;
            lastLanguage = currentLanguage;
            lastDataHash = currentDataHash;
            
            // 更新作用域属性
            $scope.filteredTypographyData = filteredData;
            
            console.log('📝 过滤后的字体数据数量:', filteredData.length);
            console.log('📝 缓存已更新');
            console.log('=== 🔍 过滤函数执行完成 ===');
        }
        
        // 监听字体数据变化
        $scope.$watch('resource.typography.data', function(newData, oldData) {
            console.log('🔄 resource.typography.data 监听器被触发');
            console.log('resource.typography.data: ', $scope.resource.typography.data);
            console.log('📝 新数据:', newData);
            console.log('📝 旧数据:', oldData);
            
            // 清理字体链接缓存
            if ($scope._typoLinkCache) {
                $scope._typoLinkCache = {};
                console.log('🧹 已清理字体链接缓存');
            }
            
            // 清理字体对象上的缓存
            if (newData && newData.length > 0) {
                newData.forEach(function(typo) {
                    if (typo._cachedSrc) {
                        delete typo._cachedSrc;
                    }
                });
                console.log('🧹 已清理字体对象缓存');
            }
            
            if (newData && newData.length > 0) {
                console.log('🎉 字体数据已加载，总数:', newData.length);
                console.log('📋 字体数据详情:');
                newData.forEach(function(font, index) {
                    console.log(`  ${index + 1}. ID: ${font.id}, 文件夹: ${font.folder}, 语言: ${font.language || '未设置'}, 名称: ${font.name || '未设置'}`);
                });
                console.log('🌐 当前语言:', $scope.currentLanguage);
                
                // 更新过滤后的数据
                updateFilteredTypographyData();
                console.log('🔍 过滤后的字体数量:', $scope.filteredTypographyData.length);
                
                // 强制更新视图 - 通常不需要手动调用$apply
                console.log('📊 字体数据已更新，总数:', newData.length);
            } else if (newData && newData.length === 0) {
                console.log('⚠️ 字体数据数组为空');
                $scope.filteredTypographyData = [];
            }
        });
        
        // 监听整个resource对象变化
        // $scope.$watch('resource', function(newResource, oldResource) {
        //     console.log('🔄 resource 监听器被触发');
        //     if (newResource && newResource.typography && newResource.typography.data) {
        //         console.log('🎉 Resource对象中的字体数据已加载');
        //         updateFilteredTypographyData();
        //     }
        // }, true); // 深度监听
        
        // 监听typography对象变化
        $scope.$watch('resource.typography', function(newTypography, oldTypography) {
            console.log('🔄 resource.typography 监听器被触发');
            if (newTypography && newTypography.data) {
                console.log('🎉 Typography对象中的字体数据已加载');
                updateFilteredTypographyData();
            }
        }, true); // 深度监听
        
        // 监听语言变化（简化版）
        $scope.$watch('currentLanguage', function(newLanguage, oldLanguage) {
            if (newLanguage !== oldLanguage) {
                console.log('🔄 语言已切换到:', newLanguage);
                
                // 更新过滤后的数据
                updateFilteredTypographyData();
                
                // 延迟执行以确保DOM更新
                setTimeout(function() {
                    // 强制应用两列布局
                    forceApplyTwoColumnLayout();
                    
                    // 检查更新后的字体项目数量
                    var typographyList = document.querySelector('.typography-items');
                    if (typographyList) {
                        var typographyItems = typographyList.querySelectorAll('.typography-item');
                        console.log('📝 语言切换后显示的字体项目数量:', typographyItems.length);
                    }
                }, 100);
            }
        });
        
        // 扩展原有的字体加载逻辑
        // var originalGetResource = $scope.getResource;
        // $scope.getResource = function(type, container) {
        //     if (type === 'typography') {
        //         console.log('🚀 开始加载字体数据...');
        //         console.log('📡 请求参数: type=' + type + ', container=' + container);
                
        //         // 监听AJAX响应
        //         var originalSuccess = this.success;
        //         this.success = function(response) {
        //             console.log('📡 AJAX响应成功:', response);
        //             if (response && response.typography && response.typography.data) {
        //                 console.log('🎉 字体数据通过AJAX加载成功');
        //                 // 延迟更新，确保数据已经绑定到scope
        //                 setTimeout(function() {
        //                     updateFilteredTypographyData();
        //                 }, 100);
        //             }
        //             if (originalSuccess) {
        //                 originalSuccess.call(this, response);
        //             }
        //         };
        //     }
            
        //     if (originalGetResource) {
        //         originalGetResource.call(this, type, container);
        //     }
        // };
        
        // 使用事件监听的方式扩展功能，避免重写函数
        $scope.$on('typographyInserted', function(event, typo) {
            console.log('📝 字体插入事件触发:', typo);
            console.log('🌐 字体语言:', typo.language);
            console.log('🎯 当前选择语言:', $scope.currentLanguage);
        });
        
        // 添加调试信息到控制台
        console.log('=== 🚀 字体语言切换功能初始化 ===');
        console.log('🌐 当前语言:', $scope.currentLanguage);
        console.log('📊 Resource对象存在:', !!$scope.resource);
        console.log('📊 Typography对象存在:', !!($scope.resource && $scope.resource.typography));
        console.log('📊 字体数据状态:', $scope.resource ? ($scope.resource.typography ? '已初始化' : 'typography未初始化') : 'resource未初始化');
        
        // 检查初始状态
        if ($scope.resource && $scope.resource.typography) {
            console.log('📋 初始字体数据数量:', $scope.resource.typography.data ? $scope.resource.typography.data.length : 0);
            console.log('📋 初始字体数据:', $scope.resource.typography.data);
            
            // 如果初始数据存在，立即更新过滤数据
            if ($scope.resource.typography.data && $scope.resource.typography.data.length > 0) {
                console.log('🎉 发现初始字体数据，立即更新过滤数据');
                updateFilteredTypographyData();
            }
        }
        
        // 定期检查数据状态
        // var checkDataInterval = setInterval(function() {
        //     if ($scope.resource && $scope.resource.typography && $scope.resource.typography.data && $scope.resource.typography.data.length > 0) {
        //         console.log('🔍 定期检查发现字体数据已加载');
        //         updateFilteredTypographyData();
        //         clearInterval(checkDataInterval);
        //     }
        // }, 500);
        
        // 10秒后停止检查
        // setTimeout(function() {
        //     clearInterval(checkDataInterval);
        // }, 10000);
        
        // 检查DOM元素
        setTimeout(function() {
            console.log('=== 🔍 检查DOM元素状态 ===');
            var fontTypeButtons = document.querySelector('.font-type-buttons');
            console.log('📝 字体类型按钮容器存在:', !!fontTypeButtons);
            
            if (fontTypeButtons) {
                var chineseBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="chinese"]');
                var englishBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="english"]');
                console.log('📝 中文字体按钮存在:', !!chineseBtn);
                console.log('📝 英文字体按钮存在:', !!englishBtn);
                
                if (chineseBtn) {
                    console.log('📝 中文字体按钮HTML:', chineseBtn.outerHTML);
                }
                if (englishBtn) {
                    console.log('📝 英文字体按钮HTML:', englishBtn.outerHTML);
                }
            }
            
            var typographyList = document.querySelector('.typography-items');
            console.log('📝 字体列表容器存在:', !!typographyList);
            if (typographyList) {
                var typographyItems = typographyList.querySelectorAll('.typography-item');
                console.log('📝 字体项目数量:', typographyItems.length);
            }
            
            console.log('=== 🔍 DOM元素检查完成 ===');
        }, 1000);
        
        console.log('✅ 字体语言切换功能初始化完成');
        
        // 强制应用字体类型按钮的函数
        function forceApplyFontTypeButtons() {
            console.log('🔄 强制应用字体类型按钮...');
            
            var fontTypeButtons = document.querySelector('.font-type-buttons');
            if (fontTypeButtons) {
                // 强制应用字体类型按钮容器样式
                fontTypeButtons.style.cssText = `
                    display: flex !important;
                    justify-content: center !important;
                    gap: 10px !important;
                    padding: 15px 10px !important;
                    background: #f8f9fa !important;
                    border-bottom: 1px solid #e0e0e0 !important;
                    margin-bottom: 15px !important;
                `;
                
                // 强制应用字体类型按钮样式
                var fontTypeBtns = fontTypeButtons.querySelectorAll('.font-type-btn');
                fontTypeBtns.forEach(function(btn) {
                    btn.style.cssText = `
                        background: white !important;
                        border: 2px solid #007cba !important;
                        border-radius: 6px !important;
                        padding: 10px 20px !important;
                        cursor: pointer !important;
                        transition: all 0.3s ease !important;
                        font-size: 14px !important;
                        font-weight: 500 !important;
                        color: #007cba !important;
                        min-width: 80px !important;
                        text-align: center !important;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                    `;
                });
                
                console.log('✅ 字体类型按钮已强制应用');
                console.log('  字体类型按钮数量:', fontTypeBtns.length);
                
                return true;
            } else {
                console.log('❌ 未找到字体类型按钮容器');
                return false;
            }
        }
        
        // 强制应用两列布局和字体类型按钮
        setTimeout(function() {
            forceApplyTwoColumnLayout();
            forceApplyFontTypeButtons();
        }, 1000);
        
        // 监听字体数据变化，重新应用布局
        $scope.$watch('resource.typography.data', function(newData, oldData) {
            if (newData && newData.length > 0) {
                setTimeout(function() {
                    forceApplyTwoColumnLayout();
                }, 500);
            }
        });
        
        // 强制应用两列布局的函数
        function forceApplyTwoColumnLayout() {
            console.log('🔄 强制应用两列布局...');
            
            var typographyList = document.querySelector('.typography-items');
            if (typographyList) {
                // 强制应用Flex布局
                typographyList.style.cssText = `
                    display: flex !important;
                    flex-wrap: wrap !important;
                    gap: 15px !important;
                    max-width: 400px !important;
                    margin: 0 auto !important;
                    list-style: none !important;
                    padding: 0 !important;
                    width: 100% !important;
                `;
                
                // 强制应用字体项目样式
                var typographyItems = document.querySelectorAll('.typography-item');
                typographyItems.forEach(function(item) {
                    item.style.cssText = `
                        width: calc(50% - 7.5px) !important;
                        height: 120px !important;
                        min-width: 0 !important;
                        display: block !important;
                        flex-shrink: 0 !important;
                    `;
                    
                    // 隐藏字体名称
                    var typoName = item.querySelector('.typo-name');
                    if (typoName) {
                        typoName.style.display = 'none';
                    }
                });
                
                console.log('✅ 两列布局已强制应用');
                
                // 检查应用后的样式
                var computedStyle = window.getComputedStyle(typographyList);
                console.log('📊 应用后的样式:');
                console.log('  display:', computedStyle.display);
                console.log('  flex-wrap:', computedStyle.flexWrap);
                console.log('  gap:', computedStyle.gap);
                console.log('  字体项目数量:', typographyItems.length);
                
                return true;
            } else {
                console.log('❌ 未找到字体列表容器');
                return false;
            }
        }
    }
    
    // 添加全局函数供调试使用
    window.getCurrentLanguage = function() {
        var app = angular.element(document.body).scope();
        return app ? app.currentLanguage : null;
    };
    
    window.switchTypographyLanguage = function(language) {
        var app = angular.element(document.body).scope();
        if (app && app.switchLanguage) {
            app.switchLanguage(language);
            console.log('✅ 全局语言切换完成:', language);
        } else {
            console.log('❌ 无法切换语言，switchLanguage函数不存在');
        }
    };
    
    window.getFilteredTypographyData = function() {
        var app = angular.element(document.body).scope();
        if (app && app.filteredTypographyData) {
            return app.filteredTypographyData;
        }
        return [];
    };
    
    // 添加调试函数
    window.debugTypographyData = function() {
        var app = angular.element(document.body).scope();
        if (app) {
            console.log('=== 🔍 字体数据调试信息 ===');
            console.log('🌐 当前语言:', app.currentLanguage);
            console.log('📦 Resource对象:', app.resource);
            console.log('🔤 Typography数据:', app.resource ? app.resource.typography : '未初始化');
            console.log('📋 字体数据数组:', app.resource && app.resource.typography ? app.resource.typography.data : '未加载');
            console.log('🔍 过滤后的数据:', app.filteredTypographyData ? app.filteredTypographyData : '属性未定义');
            
            // 检查字体数据文件
            console.log('📁 检查字体数据文件...');
            fetch('/wp-content/plugins/web-to-print-online-designer/data/typography/typo.json')
                .then(response => response.json())
                .then(data => {
                    console.log('✅ 字体数据文件加载成功:', data);
                })
                .catch(error => {
                    console.log('❌ 字体数据文件加载失败:', error);
                });
        } else {
            console.log('❌ Angular应用未初始化');
        }
    };
    
    // 添加手动触发字体加载的函数
    // window.forceLoadTypography = function() {
    //     var app = angular.element(document.body).scope();
    //     if (app && app.getResource) {
    //         console.log('🔄 手动触发字体数据加载...');
    //         app.getResource('typography', '#tab-typography');
    //     } else {
    //         console.log('❌ 无法手动触发字体加载，getResource函数不存在');
    //     }
    // };
    
    // 添加测试字体预览图片的函数
    // window.testTypographyImages = function() {
    //     var app = angular.element(document.body).scope();
    //     if (app && app.resource && app.resource.typography && app.resource.typography.data) {
    //         console.log('🖼️ 测试字体预览图片...');
    //         app.resource.typography.data.forEach(function(font, index) {
    //             var imageUrl = app.generateTypoLink(font);
    //             console.log(`字体 ${index + 1}: ${font.name} - ${imageUrl}`);
                
    //             // 测试图片是否可以加载
    //             var img = new Image();
    //             img.onload = function() {
    //                 console.log(`✅ 图片加载成功: ${font.name}`);
    //             };
    //             img.onerror = function() {
    //                 console.log(`❌ 图片加载失败: ${font.name} - ${imageUrl}`);
    //             };
    //             img.src = imageUrl;
    //         });
    //     } else {
    //         console.log('❌ 字体数据未加载，无法测试图片');
    //     }
    // };
    
    console.log('📜 字体语言切换脚本已加载');
    
    // 临时调试函数 - 检查字体数据状态
    window.checkTypographyStatus = function() {
        console.log('=== 🔍 字体状态检查 ===');
        
        // 检查Angular应用
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app) {
            console.log('✅ Angular应用存在');
            console.log('📦 Resource对象:', app.resource);
            
            if (app.resource) {
                console.log('🔤 Typography对象:', app.resource.typography);
                
                if (app.resource.typography) {
                    console.log('📋 字体数据数组:', app.resource.typography.data);
                    console.log('📊 数据长度:', app.resource.typography.data ? app.resource.typography.data.length : 0);
                    
                    if (app.resource.typography.data && app.resource.typography.data.length > 0) {
                        console.log('📝 字体数据详情:');
                        app.resource.typography.data.forEach(function(font, index) {
                            console.log(`  ${index + 1}. ID: ${font.id}, 文件夹: ${font.folder}, 语言: ${font.language || '未设置'}, 名称: ${font.name || '未设置'}`);
                        });
                    }
                } else {
                    console.log('❌ Typography对象不存在');
                }
            } else {
                console.log('❌ Resource对象不存在');
            }
            
            // 检查过滤函数
            if (app.filteredTypographyData) {
                var filteredData = app.filteredTypographyData();
                console.log('🔍 过滤后的数据:', filteredData);
                console.log('🔢 过滤后数量:', filteredData.length);
            } else {
                console.log('❌ filteredTypographyData函数不存在');
            }
            
            // 检查当前语言
            console.log('🌐 当前语言:', app.currentLanguage);
            
        } else {
            console.log('❌ Angular应用不存在');
        }
        
        // 检查DOM元素
        var typographyItems = document.querySelectorAll('.typography-item');
        console.log('🏷️ DOM中的字体项目数量:', typographyItems.length);
        
        var typographyList = document.querySelector('.typography-items');
        if (typographyList) {
            console.log('📋 字体列表容器存在');
            console.log('📋 字体列表HTML:', typographyList.innerHTML);
        } else {
            console.log('❌ 字体列表容器不存在');
        }
    };
    
    // 临时函数 - 手动添加字体数据
    window.addTestTypographyData = function() {
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app && app.resource) {
            if (!app.resource.typography) {
                app.resource.typography = {};
            }
            
            app.resource.typography.data = [
                {
                    id: 1,
                    folder: "sample1",
                    language: "chinese",
                    name: "中文字体1"
                },
                {
                    id: 2,
                    folder: "sample2",
                    language: "english",
                    name: "English Font 1"
                },
                {
                    id: 3,
                    folder: "sample3",
                    language: "chinese",
                    name: "中文字体2"
                },
                {
                    id: 4,
                    folder: "sample4",
                    language: "english",
                    name: "English Font 2"
                }
            ];
            
            app.currentLanguage = 'chinese';
            console.log('✅ 测试字体数据已添加，当前语言设置为中文');
            
            console.log('✅ 测试字体数据已添加');
        } else {
            console.log('❌ 无法添加测试数据，Angular应用或Resource对象不存在');
        }
    };
    
    // 强制应用CSS样式的函数
    window.forceApplyTypographyCSS = function() {
        console.log('🔄 强制应用字体布局CSS样式...');
        
        // 查找字体列表容器
        var typographyList = document.querySelector('.typography-items');
        if (typographyList) {
            // 强制应用Flex布局
            typographyList.style.cssText = `
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 15px !important;
                max-width: 400px !important;
                margin: 0 auto !important;
                list-style: none !important;
                padding: 0 !important;
                width: 100% !important;
            `;
            
            // 强制应用字体项目样式
            var typographyItems = document.querySelectorAll('.typography-item');
            typographyItems.forEach(function(item) {
                item.style.cssText = `
                    width: calc(50% - 7.5px) !important;
                    height: 120px !important;
                    min-width: 0 !important;
                    display: block !important;
                    flex-shrink: 0 !important;
                `;
                
                // 隐藏字体名称
                var typoName = item.querySelector('.typo-name');
                if (typoName) {
                    typoName.style.display = 'none';
                }
            });
            
            console.log('✅ 已强制应用Flex布局样式');
            
            // 检查应用后的样式
            var computedStyle = window.getComputedStyle(typographyList);
            console.log('📊 应用后的样式:');
            console.log('  display:', computedStyle.display);
            console.log('  flex-wrap:', computedStyle.flexWrap);
            console.log('  gap:', computedStyle.gap);
            console.log('  max-width:', computedStyle.maxWidth);
            console.log('  字体项目数量:', typographyItems.length);
            
            return true;
        } else {
            console.log('❌ 未找到字体列表容器');
            return false;
        }
    };
    
    // 检查CSS应用状态的函数
    window.checkTypographyCSS = function() {
        console.log('🔍 检查字体布局CSS状态...');
        
        var typographyList = document.querySelector('.typography-items');
        if (typographyList) {
            var computedStyle = window.getComputedStyle(typographyList);
            
            console.log('📊 当前CSS状态:');
            console.log('  display:', computedStyle.display);
            console.log('  flex-wrap:', computedStyle.flexWrap);
            console.log('  gap:', computedStyle.gap);
            console.log('  max-width:', computedStyle.maxWidth);
            console.log('  width:', computedStyle.width);
            console.log('  margin:', computedStyle.margin);
            
            // 检查是否为Flex布局
            if (computedStyle.display === 'flex') {
                console.log('✅ Flex布局已应用');
            } else {
                console.log('❌ Flex布局未应用，当前display:', computedStyle.display);
            }
            
            // 检查是否为两列布局
            if (computedStyle.flexWrap === 'wrap') {
                console.log('✅ 换行布局已应用');
            } else {
                console.log('❌ 换行布局未应用，当前flex-wrap:', computedStyle.flexWrap);
            }
            
            // 检查字体项目宽度
            var typographyItems = document.querySelectorAll('.typography-item');
            if (typographyItems.length > 0) {
                var firstItemStyle = window.getComputedStyle(typographyItems[0]);
                console.log('  第一个字体项目宽度:', firstItemStyle.width);
            }
            
            return {
                isFlex: computedStyle.display === 'flex',
                isWrap: computedStyle.flexWrap === 'wrap',
                display: computedStyle.display,
                flexWrap: computedStyle.flexWrap,
                itemCount: typographyItems.length
            };
        } else {
            console.log('❌ 未找到字体列表容器');
            return null;
        }
    };
    
    // 强制应用字体类型按钮的全局函数
    window.forceApplyFontTypeButtons = function() {
        console.log('🔄 全局强制应用字体类型按钮...');
        
        var fontTypeButtons = document.querySelector('.font-type-buttons');
        if (fontTypeButtons) {
            // 强制应用字体类型按钮容器样式
            fontTypeButtons.style.cssText = `
                display: flex !important;
                justify-content: center !important;
                gap: 10px !important;
                padding: 15px 10px !important;
                background: #f8f9fa !important;
                border-bottom: 1px solid #e0e0e0 !important;
                margin-bottom: 15px !important;
            `;
            
            // 强制应用字体类型按钮样式
            var fontTypeBtns = fontTypeButtons.querySelectorAll('.font-type-btn');
            fontTypeBtns.forEach(function(btn) {
                btn.style.cssText = `
                    background: white !important;
                    border: 2px solid #007cba !important;
                    border-radius: 6px !important;
                    padding: 10px 20px !important;
                    cursor: pointer !important;
                    transition: all 0.3s ease !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                    color: #007cba !important;
                    min-width: 80px !important;
                    text-align: center !important;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                `;
            });
            
            console.log('✅ 字体类型按钮已强制应用');
            console.log('  字体类型按钮数量:', fontTypeBtns.length);
            
            return true;
        } else {
            console.log('❌ 未找到字体类型按钮容器');
            return false;
        }
    };
    
    // 检查语言切换状态的函数
    window.checkLanguageSwitchStatus = function() {
        console.log('=== 🔍 语言切换状态检查 ===');
        
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app) {
            console.log('✅ Angular应用存在');
            console.log('🌐 当前语言:', app.currentLanguage);
            console.log('🔄 switchLanguage函数存在:', typeof app.switchLanguage === 'function');
            
            // 检查字体类型按钮
            var fontTypeButtons = document.querySelector('.font-type-buttons');
            if (fontTypeButtons) {
                console.log('✅ 字体类型按钮容器存在');
                
                var chineseBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="chinese"]');
                var englishBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="english"]');
                
                console.log('🇨🇳 中文字体按钮存在:', !!chineseBtn);
                console.log('🇺🇸 英文字体按钮存在:', !!englishBtn);
                
                if (chineseBtn) {
                    var chineseActive = chineseBtn.classList.contains('active');
                    console.log('🇨🇳 中文字体按钮激活状态:', chineseActive);
                }
                
                if (englishBtn) {
                    var englishActive = englishBtn.classList.contains('active');
                    console.log('🇺🇸 英文字体按钮激活状态:', englishActive);
                }
            } else {
                console.log('❌ 字体类型按钮容器不存在');
            }
            
            // 检查字体数据过滤
            if (app.filteredTypographyData) {
                var filteredData = app.filteredTypographyData();
                console.log('🔍 过滤后的字体数据数量:', filteredData.length);
                console.log('📋 过滤后的字体数据:', filteredData);
            } else {
                console.log('❌ filteredTypographyData函数不存在');
            }
            
        } else {
            console.log('❌ Angular应用不存在');
        }
    };
    
    // 测试语言切换的函数
    window.testLanguageSwitch = function() {
        console.log('🧪 开始测试语言切换功能...');
        
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app && app.switchLanguage) {
            console.log('🔄 测试切换到中文...');
            app.switchLanguage('chinese');
            
            setTimeout(function() {
                console.log('🔄 测试切换到英文...');
                app.switchLanguage('english');
                
                setTimeout(function() {
                    console.log('🔄 测试切换回中文...');
                    app.switchLanguage('chinese');
                    console.log('✅ 语言切换测试完成');
                }, 1000);
            }, 1000);
        } else {
            console.log('❌ 无法测试语言切换，switchLanguage函数不存在');
        }
    };
    
    // 检查语言切换是否生效的函数
    window.checkLanguageSwitchEffect = function() {
        console.log('=== 🔍 检查语言切换效果 ===');
        
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app) {
            console.log('🌐 当前语言:', app.currentLanguage);
            
            // 检查过滤后的数据
            if (app.filteredTypographyData) {
                var filteredData = app.filteredTypographyData();
                console.log('📝 过滤后的字体数据数量:', filteredData.length);
                console.log('📝 过滤后的字体数据:', filteredData);
            }
            
            // 检查DOM中的字体项目
            var typographyList = document.querySelector('.typography-items');
            if (typographyList) {
                var typographyItems = typographyList.querySelectorAll('.typography-item');
                console.log('📝 DOM中的字体项目数量:', typographyItems.length);
                
                // 检查每个字体项目的详细信息
                typographyItems.forEach(function(item, index) {
                    var typoName = item.querySelector('.typo-name');
                    var typoPreview = item.querySelector('.typo-preview');
                    console.log(`📝 字体项目 ${index + 1}:`);
                    console.log('  名称:', typoName ? typoName.textContent : '未找到');
                    console.log('  预览图片:', typoPreview ? typoPreview.src : '未找到');
                });
            }
            
            // 检查按钮状态
            var fontTypeButtons = document.querySelector('.font-type-buttons');
            if (fontTypeButtons) {
                var chineseBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="chinese"]');
                var englishBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="english"]');
                
                console.log('🇨🇳 中文字体按钮激活:', chineseBtn ? chineseBtn.classList.contains('active') : false);
                console.log('🇺🇸 英文字体按钮激活:', englishBtn ? englishBtn.classList.contains('active') : false);
            }
        }
        
        console.log('=== 🔍 语言切换效果检查完成 ===');
    };
    
    // 手动触发按钮点击的函数
    window.triggerButtonClick = function(language) {
        console.log('🎯 手动触发按钮点击:', language);
        
        var fontTypeButtons = document.querySelector('.font-type-buttons');
        if (!fontTypeButtons) {
            console.log('❌ 未找到字体类型按钮容器');
            return;
        }
        
        var targetBtn;
        if (language === 'chinese') {
            targetBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="chinese"]');
        } else if (language === 'english') {
            targetBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="english"]');
        }
        
        if (targetBtn) {
            console.log('📝 找到目标按钮:', targetBtn.outerHTML);
            console.log('📝 按钮点击事件:', targetBtn.getAttribute('ng-click'));
            
            // 手动触发点击事件
            var clickEvent = new MouseEvent('click', {
                view: window,
                bubbles: true,
                cancelable: true
            });
            targetBtn.dispatchEvent(clickEvent);
            
            console.log('✅ 按钮点击事件已触发');
        } else {
            console.log('❌ 未找到目标按钮:', language);
        }
    };
    
    // 检查按钮事件绑定的函数
    window.checkButtonEvents = function() {
        console.log('=== 🔍 检查按钮事件绑定 ===');
        
        var fontTypeButtons = document.querySelector('.font-type-buttons');
        if (!fontTypeButtons) {
            console.log('❌ 未找到字体类型按钮容器');
            return;
        }
        
        var buttons = fontTypeButtons.querySelectorAll('.font-type-btn');
        console.log('📝 找到按钮数量:', buttons.length);
        
        buttons.forEach(function(btn, index) {
            console.log(`📝 按钮 ${index + 1}:`);
            console.log('  HTML:', btn.outerHTML);
            console.log('  ng-click:', btn.getAttribute('ng-click'));
            console.log('  class:', btn.className);
            console.log('  text:', btn.textContent.trim());
            
            // 检查是否有Angular事件监听器
            var angularElement = angular.element(btn);
            console.log('  Angular元素存在:', !!angularElement);
        });
        
        console.log('=== 🔍 按钮事件检查完成 ===');
    };
    
    // 手动设置字体数据语言属性的函数
    // window.setTypographyLanguage = function() {
    //     console.log('=== 🔧 手动设置字体数据语言属性 ===');
        
    //     var app = angular.element(document.body).scope();
    //     if (!app) {
    //         app = angular.element(document.getElementById("designer-controller")).scope();
    //     }
        
    //     if (app && app.resource && app.resource.typography && app.resource.typography.data) {
    //         console.log('📝 开始设置字体语言属性...');
            
    //         app.resource.typography.data.forEach(function(typo, index) {
    //             console.log(`📝 处理字体 ${index + 1}:`, typo);
                
    //             if (!typo.language) {
    //                 // 根据文件夹名称推断语言
    //                 if (typo.folder && typo.folder.includes('sample')) {
    //                     var sampleNum = parseInt(typo.folder.replace('sample', ''));
    //                     if (sampleNum % 2 === 1) {
    //                         typo.language = 'chinese';
    //                     } else {
    //                         typo.language = 'english';
    //                     }
    //                     console.log('🔧 根据文件夹设置语言:', typo.folder, '->', typo.language);
    //                 } else if (typo.name) {
    //                     // 根据名称推断语言
    //                     var hasChinese = /[\u4e00-\u9fff]/.test(typo.name);
    //                     typo.language = hasChinese ? 'chinese' : 'english';
    //                     console.log('🔧 根据名称设置语言:', typo.name, '->', typo.language);
    //                 } else {
    //                     // 默认设置为中文
    //                     typo.language = 'chinese';
    //                     console.log('🔧 默认设置语言为中文:', typo.id);
    //                 }
    //             } else {
    //                 console.log('✅ 字体已有语言属性:', typo.name || typo.id, '->', typo.language);
    //             }
    //         });
            
    //         console.log('📝 设置完成后的字体数据:', app.resource.typography.data);
    //         console.log('✅ 字体语言属性设置完成');
            
    //         // 强制更新视图
    //         if (!app.$$phase && !app.$root.$$phase) {
    //             app.$apply();
    //         }
            
    //         return true;
    //     } else {
    //         console.log('❌ 无法设置字体语言属性，字体数据不存在');
    //         return false;
    //     }
    // };
    
    // 检查字体数据语言属性的函数
    window.checkTypographyLanguage = function() {
        console.log('=== 🔍 检查字体数据语言属性 ===');
        
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app && app.resource && app.resource.typography && app.resource.typography.data) {
            console.log('📝 字体数据数量:', app.resource.typography.data.length);
            
            app.resource.typography.data.forEach(function(typo, index) {
                console.log(`📝 字体 ${index + 1}:`);
                console.log('  ID:', typo.id);
                console.log('  文件夹:', typo.folder);
                console.log('  名称:', typo.name);
                console.log('  语言:', typo.language || 'undefined');
                console.log('  完整对象:', typo);
            });
            
            // 统计语言分布
            var chineseCount = 0;
            var englishCount = 0;
            var undefinedCount = 0;
            
            app.resource.typography.data.forEach(function(typo) {
                if (typo.language === 'chinese') {
                    chineseCount++;
                } else if (typo.language === 'english') {
                    englishCount++;
                } else {
                    undefinedCount++;
                }
            });
            
            console.log('📊 语言分布统计:');
            console.log('  中文字体:', chineseCount);
            console.log('  英文字体:', englishCount);
            console.log('  未定义语言:', undefinedCount);
            
        } else {
            console.log('❌ 字体数据不存在');
        }
        
        console.log('=== 🔍 字体语言属性检查完成 ===');
    };
    
    // 清除过滤函数缓存的函数
    window.clearFilterCache = function() {
        console.log('=== 🧹 清除过滤函数缓存 ===');
        
        // 清除缓存变量
        if (typeof cachedFilteredData !== 'undefined') {
            cachedFilteredData = [];
        }
        if (typeof lastLanguage !== 'undefined') {
            lastLanguage = null;
        }
        if (typeof lastDataHash !== 'undefined') {
            lastDataHash = null;
        }
        
        console.log('✅ 缓存已清除');
        console.log('📝 下次调用filteredTypographyData()将重新计算');
        
        return true;
    };
    
    // 检查缓存状态的函数
    window.checkFilterCache = function() {
        console.log('=== 🔍 检查过滤函数缓存状态 ===');
        
        console.log('📝 缓存数据:', typeof cachedFilteredData !== 'undefined' ? cachedFilteredData.length : 'undefined');
        console.log('📝 上次语言:', typeof lastLanguage !== 'undefined' ? lastLanguage : 'undefined');
        console.log('📝 上次数据哈希:', typeof lastDataHash !== 'undefined' ? lastDataHash : 'undefined');
        
        // 测试过滤函数调用
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app && app.filteredTypographyData) {
            console.log('🔄 测试调用filteredTypographyData()...');
            var result = app.filteredTypographyData();
            console.log('📝 返回结果数量:', result.length);
        }
        
        console.log('=== 🔍 缓存状态检查完成 ===');
    };
    
    // 检查语言切换状态的函数
    window.checkLanguageSwitchStatus = function() {
        console.log('=== 🔍 检查语言切换状态 ===');
        
        var app = angular.element(document.body).scope();
        if (!app) {
            app = angular.element(document.getElementById("designer-controller")).scope();
        }
        
        if (app) {
            console.log('📝 当前语言:', app.currentLanguage);
            console.log('📝 switchLanguage函数存在:', typeof app.switchLanguage === 'function');
            
            // 检查按钮
            var fontTypeButtons = document.querySelector('.font-type-buttons');
            if (fontTypeButtons) {
                var chineseBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="chinese"]');
                var englishBtn = fontTypeButtons.querySelector('.font-type-btn[ng-click*="english"]');
                
                console.log('📝 中文字体按钮存在:', !!chineseBtn);
                console.log('📝 英文字体按钮存在:', !!englishBtn);
                
                if (chineseBtn) {
                    console.log('📝 中文字体按钮激活状态:', chineseBtn.classList.contains('active'));
                }
                if (englishBtn) {
                    console.log('📝 英文字体按钮激活状态:', englishBtn.classList.contains('active'));
                }
            }
        }
        
        console.log('=== 🔍 语言切换状态检查完成 ===');
    };
    
    // 手动检查和更新字体数据的函数
    // window.forceUpdateTypographyData = function() {
    //     console.log('=== 🔄 手动更新字体数据 ===');
        
    //     var app = angular.element(document.body).scope();
    //     if (!app) {
    //         app = angular.element(document.getElementById("designer-controller")).scope();
    //     }
        
    //     if (app) {
    //         console.log('📝 当前语言:', app.currentLanguage);
    //         console.log('📝 Resource对象存在:', !!app.resource);
    //         console.log('📝 Typography对象存在:', !!(app.resource && app.resource.typography));
    //         console.log('📝 字体数据存在:', !!(app.resource && app.resource.typography && app.resource.typography.data));
            
    //         if (app.resource && app.resource.typography && app.resource.typography.data) {
    //             console.log('📝 字体数据数量:', app.resource.typography.data.length);
    //             console.log('📝 字体数据:', app.resource.typography.data);
                
    //             // 手动调用更新函数
    //             if (typeof updateFilteredTypographyData === 'function') {
    //                 updateFilteredTypographyData();
    //                 console.log('✅ 字体数据已手动更新');
    //                 console.log('📝 过滤后的数据数量:', app.filteredTypographyData ? app.filteredTypographyData.length : 0);
    //             } else {
    //                 console.log('❌ updateFilteredTypographyData函数不存在');
    //             }
    //         } else {
    //             console.log('❌ 字体数据未加载');
    //         }
    //     } else {
    //         console.log('❌ 无法获取应用作用域');
    //     }
        
    //     console.log('=== 🔄 手动更新完成 ===');
    // };
})();
