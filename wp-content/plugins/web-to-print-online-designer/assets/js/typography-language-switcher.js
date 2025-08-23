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
            initLanguageSwitcher(app);
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
    
    function initLanguageSwitcher($scope) {
        console.log('=== 初始化字体语言切换功能 ===');
        
        // 确保获取正确的Angular作用域
        if (!$scope || !$scope.$root) {
            $scope = angular.element(document.getElementById("designer-controller")).scope();
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
        
        // 语言切换函数
        $scope.switchLanguage = function(language) {
            console.log('切换到语言:', language);
            $scope.currentLanguage = language;
            $scope.$apply();
        };
        
        // 自定义字体预览图片URL生成函数
        $scope.generateTypoLink = function(typo) {
            if (!typo || !typo.folder) {
                console.log('❌ 字体对象无效:', typo);
                return '';
            }
            
            // 生成字体预览图片URL
            var baseUrl = window.location.origin;
            var typoUrl = baseUrl + '/wp-content/plugins/web-to-print-online-designer/data/typography/store/' + typo.folder + '/frame_0.png';
            
            console.log('🖼️ 生成字体预览URL:', typoUrl, '字体:', typo);
            return typoUrl;
        };
        
        // 过滤字体数据函数
        $scope.filteredTypographyData = function() {
            // 检查resource对象是否存在
            if (!$scope.resource) {
                if (!hasLoggedDataNotLoaded) {
                    console.log('❌ Resource对象未初始化');
                    hasLoggedDataNotLoaded = true;
                }
                return [];
            }
            
            // 检查typography对象是否存在
            if (!$scope.resource.typography) {
                if (!hasLoggedDataNotLoaded) {
                    console.log('❌ Typography对象未初始化');
                    hasLoggedDataNotLoaded = true;
                }
                return [];
            }
            
            // 检查data数组是否存在
            if (!$scope.resource.typography.data) {
                if (!hasLoggedDataNotLoaded) {
                    console.log('❌ Typography数据数组未初始化');
                    hasLoggedDataNotLoaded = true;
                }
                return [];
            }
            
            // 检查data数组是否为空
            if ($scope.resource.typography.data.length === 0) {
                dataLoadAttempts++;
                if (dataLoadAttempts <= 3) {
                    console.log(`⏳ 字体数据数组为空，等待数据加载... (尝试 ${dataLoadAttempts}/3)`);
                } else if (dataLoadAttempts === 4) {
                    console.log('⚠️ 字体数据仍未加载，可能存在问题');
                    console.log('建议检查：');
                    console.log('1. 字体数据文件是否存在');
                    console.log('2. AJAX请求是否成功');
                    console.log('3. 服务器端字体加载逻辑');
                }
                return [];
            }
            
            // 数据已加载，重置标志
            if (hasLoggedDataNotLoaded || dataLoadAttempts > 0) {
                console.log('✅ 字体数据已加载，开始过滤');
                hasLoggedDataNotLoaded = false;
                dataLoadAttempts = 0;
            }
            
            var filteredData = $scope.resource.typography.data.filter(function(typo) {
                // 如果字体没有language属性，默认显示
                if (!typo.language) {
                    return true;
                }
                // 根据当前选择的语言过滤
                return typo.language === $scope.currentLanguage;
            });
            
            // 只在数据变化时打印日志
            return filteredData;
        };
        
        // 监听字体数据变化
        $scope.$watch('resource.typography.data', function(newData, oldData) {
            if (newData && newData.length > 0) {
                console.log('🎉 字体数据已加载，总数:', newData.length);
                console.log('📋 字体数据详情:');
                newData.forEach(function(font, index) {
                    console.log(`  ${index + 1}. ID: ${font.id}, 文件夹: ${font.folder}, 语言: ${font.language || '未设置'}, 名称: ${font.name || '未设置'}`);
                });
                console.log('🌐 当前语言:', $scope.currentLanguage);
                
                var filteredCount = $scope.filteredTypographyData().length;
                console.log('🔍 过滤后的字体数量:', filteredCount);
                
                // 强制更新视图
                $scope.$apply();
            } else if (newData && newData.length === 0) {
                console.log('⚠️ 字体数据数组为空');
            }
        });
        
        // 监听语言变化
        $scope.$watch('currentLanguage', function(newLanguage, oldLanguage) {
            if (newLanguage !== oldLanguage) {
                console.log('🔄 语言已切换到:', newLanguage);
                // 重新计算过滤后的数据
                $scope.$apply();
            }
        });
        
        // 扩展原有的字体加载逻辑
        var originalGetResource = $scope.getResource;
        $scope.getResource = function(type, container) {
            if (type === 'typography') {
                console.log('🚀 开始加载字体数据...');
                console.log('📡 请求参数: type=' + type + ', container=' + container);
            }
            
            if (originalGetResource) {
                originalGetResource.call(this, type, container);
            }
        };
        
        // 扩展原有的字体插入逻辑
        var originalInsertTypography = $scope.insertTypography;
        $scope.insertTypography = function(typo) {
            console.log('📝 插入字体:', typo);
            console.log('🌐 字体语言:', typo.language);
            console.log('🎯 当前选择语言:', $scope.currentLanguage);
            
            if (originalInsertTypography) {
                originalInsertTypography.call(this, typo);
            }
        };
        
        // 添加调试信息到控制台
        console.log('✅ 字体语言切换功能已初始化');
        console.log('🌐 当前语言:', $scope.currentLanguage);
        console.log('📊 字体数据状态:', $scope.resource ? ($scope.resource.typography ? '已初始化' : 'typography未初始化') : 'resource未初始化');
        
        // 检查初始状态
        if ($scope.resource && $scope.resource.typography) {
            console.log('📋 初始字体数据:', $scope.resource.typography.data);
        }
        
        // 强制应用两列布局
        setTimeout(function() {
            forceApplyTwoColumnLayout();
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
            app.$apply();
        }
    };
    
    window.getFilteredTypographyData = function() {
        var app = angular.element(document.body).scope();
        if (app && app.filteredTypographyData) {
            return app.filteredTypographyData();
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
            console.log('🔍 过滤后的数据:', app.filteredTypographyData ? app.filteredTypographyData() : '函数未定义');
            
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
    window.forceLoadTypography = function() {
        var app = angular.element(document.body).scope();
        if (app && app.getResource) {
            console.log('🔄 手动触发字体数据加载...');
            app.getResource('typography', '#tab-typography');
        } else {
            console.log('❌ 无法手动触发字体加载，getResource函数不存在');
        }
    };
    
    // 添加测试字体预览图片的函数
    window.testTypographyImages = function() {
        var app = angular.element(document.body).scope();
        if (app && app.resource && app.resource.typography && app.resource.typography.data) {
            console.log('🖼️ 测试字体预览图片...');
            app.resource.typography.data.forEach(function(font, index) {
                var imageUrl = app.generateTypoLink(font);
                console.log(`字体 ${index + 1}: ${font.name} - ${imageUrl}`);
                
                // 测试图片是否可以加载
                var img = new Image();
                img.onload = function() {
                    console.log(`✅ 图片加载成功: ${font.name}`);
                };
                img.onerror = function() {
                    console.log(`❌ 图片加载失败: ${font.name} - ${imageUrl}`);
                };
                img.src = imageUrl;
            });
        } else {
            console.log('❌ 字体数据未加载，无法测试图片');
        }
    };
    
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
            app.$apply();
            
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
})();
