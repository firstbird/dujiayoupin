// 测试简单修复脚本
console.log('=== 🧪 测试简单修复脚本已加载 ===');

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
        
        // 测试函数是否存在
        if (scope.insertTypography) {
            console.log('✅ insertTypography 函数存在');
        } else {
            console.log('❌ insertTypography 函数不存在');
        }
        
        if (scope.insertTemplateFont) {
            console.log('✅ insertTemplateFont 函数存在');
        } else {
            console.log('❌ insertTemplateFont 函数不存在');
        }
        
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

// 定期检查状态
setInterval(function() {
    var scope = angular.element(document.querySelector('[ng-controller]')).scope();
    if (scope) {
        // 检查递归保护状态
        if (scope._insertTypographyRecursionGuard) {
            console.log('⚠️ insertTypography 递归保护已激活');
        }
        if (scope._insertTemplateFontRecursionGuard) {
            console.log('⚠️ insertTemplateFont 递归保护已激活');
        }
    }
}, 5000);

console.log('=== 🧪 测试简单修复脚本加载完成 ===');
