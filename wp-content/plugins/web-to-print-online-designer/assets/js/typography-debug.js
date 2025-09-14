// 字体插入功能调试脚本
console.log('=== 🐛 字体插入功能调试脚本已加载 ===');

// 监控字体插入事件
angular.element(document).ready(function() {
    var scope = angular.element(document.querySelector('[ng-controller]')).scope();
    if (scope) {
        scope.$on('typographyInserted', function(event, typo) {
            console.log('🎯 字体插入事件被触发:', typo);
            console.log('📊 字体信息:', {
                id: typo.id,
                name: typo.name,
                folder: typo.folder,
                language: typo.language
            });
        });
        
        console.log('✅ 字体插入事件监听器已设置');
    } else {
        console.log('❌ 无法获取Angular作用域');
    }
});

// 监控控制台错误
window.addEventListener('error', function(event) {
    if (event.error && event.error.message && event.error.message.includes('Maximum call stack size exceeded')) {
        console.error('🚨 检测到调用栈溢出错误:', event.error);
        console.error('📍 错误位置:', event.filename, ':', event.lineno);
        console.error('📋 错误堆栈:', event.error.stack);
    }
});

console.log('=== 🐛 字体插入功能调试脚本加载完成 ===');











