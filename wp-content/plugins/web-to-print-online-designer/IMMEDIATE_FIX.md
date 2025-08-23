# 立即修复两列布局

## 问题
现在还是一行显示一个字体元素，需要改成显示2个

## 立即解决方案

### 方案1：在浏览器控制台执行（推荐）
```javascript
// 立即强制应用两列布局
var list = document.querySelector('.typography-items');
if (list) {
    // 设置容器为Flex布局
    list.style.cssText = `
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 15px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
        list-style: none !important;
        padding: 0 !important;
        width: 100% !important;
    `;
    
    // 设置每个字体项目为50%宽度和固定高度
    var items = document.querySelectorAll('.typography-item');
    items.forEach(function(item) {
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
    
    console.log('✅ 两列布局已立即应用，字体项目数量:', items.length);
} else {
    console.log('❌ 未找到字体列表容器');
}
```

### 方案2：使用我们提供的函数
```javascript
// 使用我们提供的调试函数
forceApplyTypographyCSS();
```

### 方案3：创建新的样式标签
```javascript
// 创建新的样式标签强制覆盖
var style = document.createElement('style');
style.textContent = `
    .typography-items {
        display: flex !important;
        flex-wrap: wrap !important;
        gap: 15px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
        list-style: none !important;
        padding: 0 !important;
        width: 100% !important;
    }
    
    .typography-item {
        width: calc(50% - 7.5px) !important;
        height: 120px !important;
        min-width: 0 !important;
        flex-shrink: 0 !important;
    }
    
    .typo-item-content {
        height: 100% !important;
        box-sizing: border-box !important;
    }
    
    .typo-preview {
        max-height: 80px !important;
        margin-bottom: 0 !important;
    }
    
    .typo-name {
        display: none !important;
    }
`;
document.head.appendChild(style);
console.log('✅ 新样式标签已添加');
```

## 验证修复效果

执行以下命令检查是否成功：
```javascript
// 检查CSS状态
var list = document.querySelector('.typography-items');
if (list) {
    var style = window.getComputedStyle(list);
    console.log('display:', style.display);
    console.log('flex-wrap:', style.flexWrap);
    console.log('gap:', style.gap);
    
    var items = document.querySelectorAll('.typography-item');
    if (items.length > 0) {
        var itemStyle = window.getComputedStyle(items[0]);
        console.log('第一个字体项目宽度:', itemStyle.width);
        console.log('第一个字体项目高度:', itemStyle.height);
    }
    
    console.log('是否成功:', style.display === 'flex' && style.flexWrap === 'wrap');
}
```

## 测试页面

访问以下测试页面验证效果：
```
/wp-content/plugins/web-to-print-online-designer/test-two-column-simple.html
```

## 预期效果

修复后应该看到：
- ✅ 字体项目一行显示两个
- ✅ 字体项目之间有15px间距
- ✅ 所有字体项目高度一致（120px）
- ✅ 字体名称已隐藏，只显示预览图片
- ✅ 字体列表容器最大宽度400px
- ✅ 字体列表容器水平居中

## 如果仍然不工作

1. 检查浏览器是否支持CSS Grid
2. 检查是否有其他CSS覆盖了样式
3. 尝试刷新页面后重新执行修复代码
4. 使用开发者工具检查元素样式
