/**
 * 简化字体检查脚本
 */

(function() {
    'use strict';
    
    function waitForAngular() {
        if (typeof angular !== 'undefined' && angular.element(document).scope()) {
            var $scope = angular.element(document).scope();
            if ($scope.resource && $scope.resource.font) {
                setupSimpleFontCheck($scope);
            } else {
                setTimeout(waitForAngular, 100);
            }
        } else {
            setTimeout(waitForAngular, 100);
        }
    }
    
    function setupSimpleFontCheck($scope) {
        console.log('=== 简化字体检查工具已加载 ===');
        
        window.simpleFontCheck = {
            // 检查字体文件
            checkFont: function(fontName) {
                var existingFont = $scope.resource.font.data.filter(function(font) {
                    return font.alias === fontName;
                })[0];
                
                if (!existingFont) {
                    console.error('字体不存在:', fontName);
                    return;
                }
                
                var originalUrl = existingFont.url;
                var fileName = originalUrl.split('/').pop();
                var localPath = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fileName;
                
                console.log('字体信息:', {
                    fontName: fontName,
                    originalUrl: originalUrl,
                    fileName: fileName,
                    localPath: localPath
                });
                
                // 检查文件是否存在
                fetch(localPath, { method: 'HEAD' })
                    .then(function(response) {
                        console.log('文件检查结果:', {
                            exists: response.ok,
                            status: response.status,
                            path: localPath
                        });
                    })
                    .catch(function(error) {
                        console.error('文件检查失败:', error);
                    });
            },
            
            // 列出所有字体
            listFonts: function() {
                var fonts = $scope.resource.font.data;
                console.log('所有字体:', fonts.map(f => f.alias));
                return fonts;
            }
        };
        
        console.log('简化字体检查工具已加载');
        console.log('使用方法:');
        console.log('simpleFontCheck.checkFont("字体名称") - 检查字体文件');
        console.log('simpleFontCheck.listFonts() - 列出所有字体');
    }
    
    waitForAngular();
    
})(); 