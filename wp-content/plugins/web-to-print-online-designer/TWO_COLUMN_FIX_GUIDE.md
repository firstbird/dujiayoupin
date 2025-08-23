# 字体两列布局修复指南

## 问题描述
字体没有实现一行排布两个字体

## 快速修复步骤

### 步骤1：检查CSS应用状态
在浏览器控制台执行：
```javascript
checkTypographyCSS()
```

### 步骤2：如果CSS未正确应用，强制应用样式
在浏览器控制台执行：
```javascript
forceApplyTypographyCSS()
```

### 步骤3：验证修复效果
再次检查CSS状态：
```javascript
checkTypographyCSS()
```

### 步骤4：如果仍然不工作，使用调试页面
访问调试页面：
```
/wp-content/plugins/web-to-print-online-designer/debug-typography-layout.html
```

## 调试命令

### 基础调试
```javascript
// 检查CSS状态
checkTypographyCSS()

// 强制应用CSS
forceApplyTypographyCSS()

// 检查字体数据
checkTypographyStatus()
```

### 高级调试
```javascript
// 手动检查DOM元素
var typographyList = document.querySelector('.typography-items');
if (typographyList) {
    console.log('字体列表容器:', typographyList);
    console.log('子元素数量:', typographyList.children.length);
    console.log('计算样式:', window.getComputedStyle(typographyList));
} else {
    console.log('未找到字体列表容器');
}

// 手动应用样式
if (typographyList) {
    typographyList.style.display = 'grid';
    typographyList.style.gridTemplateColumns = '1fr 1fr';
    typographyList.style.gap = '15px';
    console.log('手动应用样式完成');
}
```

## 常见问题解决方案

### 问题1：CSS未正确加载
**症状**: `checkTypographyCSS()` 显示 display 不是 'grid'
**解决**: 执行 `forceApplyTypographyCSS()`

### 问题2：字体数据为空
**症状**: 没有字体项目显示
**解决**: 执行 `addTestTypographyData()`

### 问题3：CSS被其他样式覆盖
**症状**: CSS已应用但布局不正确
**解决**: 检查是否有其他CSS文件覆盖了样式

### 问题4：浏览器不支持Grid
**症状**: 现代浏览器应该都支持Grid
**解决**: 检查浏览器版本，建议使用Chrome、Firefox、Safari最新版本

## 手动修复方法

如果自动修复不工作，可以手动执行以下步骤：

### 方法1：直接修改DOM样式
```javascript
// 在控制台执行
var list = document.querySelector('.typography-items');
if (list) {
    list.style.cssText = `
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 15px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
        list-style: none !important;
        padding: 0 !important;
    `;
    console.log('手动修复完成');
}
```

### 方法2：创建新的样式标签
```javascript
// 在控制台执行
var style = document.createElement('style');
style.textContent = `
    .typography-items {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 15px !important;
        max-width: 400px !important;
        margin: 0 auto !important;
        list-style: none !important;
        padding: 0 !important;
    }
`;
document.head.appendChild(style);
console.log('新样式标签已添加');
```

## 验证修复效果

修复后应该看到：
- ✅ 字体项目一行显示两个
- ✅ 字体项目之间有15px间距
- ✅ 字体列表容器最大宽度400px
- ✅ 字体列表容器水平居中

## 联系支持

如果以上步骤都无法解决问题，请提供：

1. `checkTypographyCSS()` 的完整输出
2. 浏览器控制台的错误信息
3. 调试页面的结果
4. 浏览器版本信息
