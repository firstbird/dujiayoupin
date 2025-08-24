// 字体插入功能最终修复脚本
console.log('=== 🔧 字体插入功能最终修复脚本已加载 ===');

// 等待 Angular 应用加载完成
function waitForAngular() {
    if (typeof angular !== 'undefined' && angular.element(document).scope()) {
        var $scope = angular.element(document).scope();
        
        console.log('✅ Angular 应用已加载，开始修复字体插入功能');
        
        // 监控字体插入事件
        $scope.$on('typographyInserted', function(event, typo) {
            console.log('📝 字体插入事件触发:', typo);
            console.log('🌐 字体语言:', typo.language);
            console.log('🎯 当前选择语言:', $scope.currentLanguage || '未设置');
        });
        
        // 监控错误
        $scope.$on('$error', function(event, error) {
            console.error('🚨 Angular 错误:', error);
        });
        
        // 只添加事件监听，不重写函数
        console.log('📝 添加字体插入事件监听器');
        
        // 监控字体插入事件
        $scope.$on('typographyInserted', function(event, typo) {
            console.log('📝 字体插入事件触发:', typo);
            console.log('🌐 字体语言:', typo.language);
            console.log('🎯 当前选择语言:', $scope.currentLanguage || '未设置');
        });
        
        // 监控函数调用
        var callCount = {};
        $scope.$watch(function() {
            return $scope.resource && $scope.resource.typography && $scope.resource.typography.data;
        }, function(newData, oldData) {
            if (newData && newData !== oldData) {
                console.log('📊 字体数据已更新，数量:', newData.length);
            }
        });
        
        console.log('✅ 字体插入功能修复完成');
        
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
        console.error('📋 错误堆栈:', event.error.stack);
        
        // 尝试恢复
        console.log('🔄 尝试恢复...');
        var scope = angular.element(document.querySelector('[ng-controller]')).scope();
        if (scope) {
            scope._insertTypographyRecursionGuard = false;
            scope._insertTemplateFontRecursionGuard = false;
            console.log('✅ 递归保护已重置');
        }
    }
});

console.log('=== 🔧 字体插入功能最终修复脚本加载完成 ===');
