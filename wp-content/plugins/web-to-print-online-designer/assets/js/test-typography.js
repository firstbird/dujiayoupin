// 字体插入功能测试脚本
console.log('=== 🧪 字体插入功能测试脚本已加载 ===');

// 监控错误
window.addEventListener('error', function(event) {
    console.error('🚨 捕获到错误:', event.error);
    console.error('📍 错误位置:', event.filename, ':', event.lineno);
    if (event.error && event.error.stack) {
        console.error('📋 错误堆栈:', event.error.stack);
    }
});

// 监控未处理的 Promise 拒绝
window.addEventListener('unhandledrejection', function(event) {
    console.error('🚨 未处理的 Promise 拒绝:', event.reason);
});

// 监控字体插入事件
angular.element(document).ready(function() {
    var scope = angular.element(document.querySelector('[ng-controller]')).scope();
    if (scope) {
        scope.$on('typographyInserted', function(event, typo) {
            console.log('✅ 字体插入事件正常触发:', typo);
        });
        
        // 测试字体插入函数是否存在
        if (scope.insertTypography) {
            console.log('✅ insertTypography 函数存在');
        } else {
            console.log('❌ insertTypography 函数不存在');
        }
        
        // 测试 insertCanvaTypo 函数是否存在
        if (scope.insertCanvaTypo) {
            console.log('✅ insertCanvaTypo 函数存在');
        } else {
            console.log('❌ insertCanvaTypo 函数不存在');
        }
        
        console.log('✅ 测试脚本初始化完成');
    } else {
        console.log('❌ 无法获取 Angular 作用域');
    }
});

console.log('=== 🧪 字体插入功能测试脚本加载完成 ===');
