# 立即修复两列布局和字体类型按钮

## 问题
现在还是一行显示一个字体元素，需要改成显示2个，并且需要显示字体类型切换按钮

## 已知问题
- 切换语言时可能出现"$digest already in progress"错误
- 已修复：移除不必要的`$apply()`调用，让Angular自动处理数据绑定

## 立即解决方案

### 方案1：在浏览器控制台执行（推荐）
```javascript
// 立即修复字体语言属性和布局问题
console.log('=== 🚀 开始立即修复 ===');

// 1. 手动检查和更新字体数据
if (typeof forceUpdateTypographyData === 'function') {
    forceUpdateTypographyData();
}

// 2. 修复字体语言属性
var app = angular.element(document.getElementById("designer-controller")).scope();
if (app && app.resource && app.resource.typography && app.resource.typography.data) {
    console.log('🔧 修复字体语言属性...');
    app.resource.typography.data.forEach(function(typo) {
        if (!typo.language) {
            if (typo.folder && typo.folder.includes('sample')) {
                var sampleNum = parseInt(typo.folder.replace('sample', ''));
                typo.language = sampleNum % 2 === 1 ? 'chinese' : 'english';
            } else if (typo.name) {
                var hasChinese = /[\u4e00-\u9fff]/.test(typo.name);
                typo.language = hasChinese ? 'chinese' : 'english';
            } else {
                typo.language = 'chinese';
            }
        }
    });
    console.log('✅ 字体语言属性修复完成');
    
    // 设置默认语言为中文
    app.currentLanguage = 'chinese';
    console.log('✅ 默认语言设置为中文');
    
    // 3. 更新过滤后的数据
    if (typeof updateFilteredTypographyData === 'function') {
        updateFilteredTypographyData();
        console.log('✅ 过滤后的数据已更新');
    }
} else {
    console.log('⚠️ 字体数据未加载，请等待数据加载完成后再试');
}

// 2. 应用两列布局
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
    
    // 强制应用字体类型按钮
    var fontTypeButtons = document.querySelector('.font-type-buttons');
    if (fontTypeButtons) {
        fontTypeButtons.style.cssText = `
            display: flex !important;
            justify-content: center !important;
            gap: 10px !important;
            padding: 15px 10px !important;
            background: #f8f9fa !important;
            border-bottom: 1px solid #e0e0e0 !important;
            margin-bottom: 15px !important;
        `;
        
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
    }
    
    console.log('✅ 两列布局和字体类型按钮已立即应用，字体项目数量:', items.length);
} else {
    console.log('❌ 未找到字体列表容器');
}

console.log('=== ✅ 立即修复完成 ===');
```
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
- ✅ 字体类型按钮正常显示和切换

## 调试函数

如果按钮点击没有反应，请在控制台执行以下函数进行调试：

```javascript
// 1. 手动检查和更新字体数据
forceUpdateTypographyData();

// 2. 检查字体数据语言属性
checkTypographyLanguage();

// 3. 如果语言属性都是undefined，手动设置语言属性
setTypographyLanguage();

// 4. 检查过滤函数缓存状态
checkFilterCache();

// 5. 如果缓存有问题，清除缓存
clearFilterCache();

// 6. 检查语言切换状态
checkLanguageSwitchStatus();

// 7. 检查按钮事件绑定
checkButtonEvents();

// 8. 检查语言切换效果
checkLanguageSwitchEffect();

// 9. 手动触发按钮点击
triggerButtonClick('chinese');  // 测试中文字体按钮
triggerButtonClick('english');  // 测试英文字体按钮

// 10. 测试语言切换功能
testLanguageSwitch();
```

## 如果仍然不工作

1. 检查浏览器是否支持CSS Grid
2. 检查是否有其他CSS覆盖了样式
3. 尝试刷新页面后重新执行修复代码
4. 使用开发者工具检查元素样式
