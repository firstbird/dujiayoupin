// 简单修复脚本 - 完全避免函数重写
console.log('=== 🔧 简单修复脚本已加载 ===');

// 等待 Angular 应用加载完成
function waitForAngular() {
    if (typeof angular !== 'undefined' && angular.element(document).scope()) {
        var $scope = angular.element(document).scope();
        
        console.log('✅ Angular 应用已加载');
        
        // 只添加事件监听，不重写任何函数
        $scope.$on('typographyInserted', function(event, typo) {
            console.log('📝 字体插入事件触发:', typo);
            console.log('🌐 字体语言:', typo.language);
            console.log('🎯 当前选择语言:', $scope.currentLanguage || '未设置');
        });
        
        // 监控错误
        $scope.$on('$error', function(event, error) {
            console.error('🚨 Angular 错误:', error);
        });
        
        console.log('✅ 简单修复完成 - 只添加事件监听，不重写函数');
        
    } else {
        setTimeout(waitForAngular, 100);
    }
}

// 启动修复脚本
waitForAngular();

// 全局错误监控
window.addEventListener('error', function(event) {
    if (event.error && event.error.message && event.error.message.includes('Maximum call stack size exceeded')) {
        console.error('🚨 检测到调用栈溢出错误:', event.error);
        console.error('📍 错误位置:', event.filename, ':', event.lineno);
        
        // 尝试恢复
        console.log('🔄 尝试恢复...');
        var scope = angular.element(document.querySelector('[ng-controller]')).scope();
        if (scope) {
            // 重置所有可能的递归保护
            scope._insertTypographyRecursionGuard = false;
            scope._insertTemplateFontRecursionGuard = false;
            console.log('✅ 递归保护已重置');
        }
        
        // 阻止错误传播
        event.preventDefault();
        return false;
    }
});

console.log('=== �� 简单修复脚本加载完成 ===');
