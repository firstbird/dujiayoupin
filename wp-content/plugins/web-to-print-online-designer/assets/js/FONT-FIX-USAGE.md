# 字体修复使用说明

## 问题描述
修改后加载本地字体一直在loading状态，可能是由于以下原因：
1. CSS格式设置错误（已修复）
2. FontFaceObserver超时时间过长
3. Loading状态没有正确关闭
4. 字体加载逻辑过于复杂

## 修复方案

### 方案1：使用修复后的 app-modern.min.js（推荐）
直接使用已修复的 `app-modern.min.js` 文件，主要修复包括：
- 修正了CSS格式从 `format('woff')` 到 `format('truetype')`
- 减少了FontFaceObserver超时时间从10秒到3秒
- 添加了loading状态的正确关闭逻辑
- 即使字体加载失败也会继续使用字体

### 方案2：使用简化字体加载脚本
如果方案1仍有问题，可以使用 `font-load-simple.js` 脚本：

```html
<!-- 在页面中引入简化字体加载脚本 -->
<script src="/wp-content/plugins/web-to-print-online-designer/assets/js/font-load-simple.js"></script>
```

这个脚本的特点：
- 完全跳过FontFaceObserver，避免超时问题
- 直接注入CSS并立即使用字体
- 自动关闭loading状态
- 提供简化的字体加载逻辑

### 方案3：使用调试脚本
如果仍有问题，可以使用 `font-debug-simple.js` 进行调试：

```html
<!-- 在页面中引入调试脚本 -->
<script src="/wp-content/plugins/web-to-print-online-designer/assets/js/font-debug-simple.js"></script>
```

这个脚本会：
- 检查字体文件是否存在
- 检查CSS是否正确注入
- 创建可视化的字体测试界面
- 提供详细的调试信息

## 使用步骤

### 步骤1：验证字体文件
确保以下字体文件存在于正确位置：
```
/wp-content/plugins/web-to-print-online-designer/assets/fonts/
├── greatvibes.ttf
├── hindsiliguri.ttf
└── lustria.ttf
```

### 步骤2：选择修复方案
根据具体情况选择合适的修复方案：
- 如果问题较轻，使用方案1
- 如果loading问题严重，使用方案2
- 如果需要调试，使用方案3

### 步骤3：测试字体
在浏览器控制台中运行测试：

```javascript
// 获取Angular scope
var scope = angular.element(document.body).scope();

// 测试字体加载
scope.insertTemplateFont('greatvibes', function(result) {
    console.log('字体加载结果:', result);
});

// 测试添加文本
scope.addText('测试文本', 'bodytext', {
    fontFamily: 'greatvibes',
    fontSize: 24,
    top: 100,
    left: 100
});
```

### 步骤4：验证结果
检查以下内容：
1. 浏览器控制台是否有错误信息
2. 字体是否正确显示
3. Loading状态是否正确关闭
4. 字体在在线设计器中是否可用

## 故障排除

### 问题1：字体文件不存在
**症状**：控制台显示404错误
**解决方案**：
- 检查字体文件路径是否正确
- 确保字体文件已上传到服务器
- 检查文件权限

### 问题2：CSS未注入
**症状**：字体CSS在页面中找不到
**解决方案**：
- 使用调试脚本检查CSS注入状态
- 手动检查页面head中是否有字体CSS
- 确保jQuery可用

### 问题3：Loading状态一直显示
**症状**：页面一直显示loading状态
**解决方案**：
- 使用简化字体加载脚本
- 检查toggleStageLoading函数是否被正确调用
- 手动关闭loading状态

### 问题4：字体显示不正确
**症状**：字体加载成功但显示效果不对
**解决方案**：
- 检查字体文件是否损坏
- 验证字体名称是否正确
- 清理浏览器缓存

## 调试命令

在浏览器控制台中可以使用以下命令进行调试：

```javascript
// 检查字体资源数据
var scope = angular.element(document.body).scope();
console.log('字体资源数据:', scope.resource.font.data);

// 检查CSS注入状态
var cssElement = document.getElementById('greatvibes');
console.log('CSS元素:', cssElement);

// 手动注入CSS
var css = "<style type='text/css' id='greatvibes'>@font-face {font-family: 'greatvibes'; src: url('/wp-content/plugins/web-to-print-online-designer/assets/fonts/greatvibes.ttf') format('truetype'); font-weight: normal; font-style: normal; font-display: swap;}</style>";
document.head.insertAdjacentHTML('beforeend', css);

// 手动关闭loading状态
scope.toggleStageLoading();

// 清理Fabric.js缓存
fabric.util.clearFabricFontCache();
```

## 注意事项

1. **文件路径**：确保所有路径都是正确的
2. **浏览器兼容性**：不同浏览器对字体加载的支持可能不同
3. **缓存问题**：修改后可能需要清理浏览器缓存
4. **服务器配置**：确保服务器支持.ttf文件的访问
5. **加载顺序**：确保脚本在Angular应用初始化后加载

## 更新日志

- 2024-01-XX: 初始版本
  - 修复了CSS格式错误
  - 减少了FontFaceObserver超时时间
  - 添加了loading状态关闭逻辑
  - 创建了简化字体加载脚本
  - 创建了调试脚本 