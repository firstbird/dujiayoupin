// 禁用递归脚本
console.log('=== 🚫 禁用递归脚本已加载 ===');

// 监控并阻止函数重写
function preventFunctionOverwrite() {
    var scope = angular.element(document.querySelector('[ng-controller]')).scope();
    if (scope) {
        // 保存原始函数
        var originalInsertTypography = scope.insertTypography;
        var originalInsertTemplateFont = scope.insertTemplateFont;
        
        // 防止 insertTypography 被重写
        Object.defineProperty(scope, 'insertTypography', {
            get: function() {
                return originalInsertTypography;
            },
            set: function(newFunction) {
                console.log('🚫 阻止 insertTypography 函数被重写');
                // 不执行重写
            },
            configurable: true
        });
        
        // 防止 insertTemplateFont 被重写
        Object.defineProperty(scope, 'insertTemplateFont', {
            get: function() {
                return originalInsertTemplateFont;
            },
            set: function(newFunction) {
                console.log('🚫 阻止 insertTemplateFont 函数被重写');
                // 不执行重写
            },
            configurable: true
        });
        
        console.log('✅ 函数重写保护已启用');
    } else {
        setTimeout(preventFunctionOverwrite, 100);
    }
}

// 等待 Angular 加载完成后执行
angular.element(document).ready(function() {
    preventFunctionOverwrite();
});

// 全局错误处理
window.addEventListener('error', function(event) {
    if (event.error && event.error.message && event.error.message.includes('Maximum call stack size exceeded')) {
        console.error('🚨 调用栈溢出错误已捕获');
        event.preventDefault();
        event.stopPropagation();
        
        // 重置递归保护
        var scope = angular.element(document.querySelector('[ng-controller]')).scope();
        if (scope) {
            scope._insertTypographyRecursionGuard = false;
            scope._insertTemplateFontRecursionGuard = false;
            console.log('✅ 递归保护已重置');
        }
        
        return false;
    }
});

console.log('=== �� 禁用递归脚本加载完成 ===');











