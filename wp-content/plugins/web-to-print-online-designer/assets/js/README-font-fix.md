# 字体修复说明文档

## 问题描述
在 `app-modern.min.js` 中新增了三个本地字体：`greatvibes.ttf`、`hindsiliguri.ttf`、`lustria.ttf`，但是在 `insertTemplateFont` 中虽然显示"自定义字体CSS已添加"，字体效果没有显示。

## 修复内容

### 1. 修改了 `app-modern.min.js` 文件

#### 1.1 在本地字体映射中添加了新增字体
```javascript
var localFontMap = {
    'glyphicons-regular': 'glyphicons-regular.woff',
    'glyphicons-halflings-regular': 'glyphicons-halflings-regular.woff',
    'fontawesome': 'fontawesome-webfont.woff',
    'FontAwesome': 'fontawesome-webfont.woff',
    'online-design': 'online-design.woff',
    'nbd-vista': 'nbd-vista.woff',
    'FontNBD': 'FontNBD.woff',
    // 新增的本地字体映射
    'greatvibes': 'greatvibes.ttf',
    'hindsiliguri': 'hindsiliguri.ttf',
    'lustria': 'lustria.ttf'
};
```

#### 1.2 在字体资源数据初始化中添加了新增字体定义
```javascript
// 添加新增的本地字体到资源数据中
var newLocalFonts = [
    {
        alias: 'greatvibes',
        name: 'Great Vibes',
        url: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/greatvibes.ttf',
        type: 'local',
        cat: ['0'],
        subset: 'latin'
    },
    {
        alias: 'hindsiliguri',
        name: 'Hind Siliguri',
        url: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/hindsiliguri.ttf',
        type: 'local',
        cat: ['0'],
        subset: 'latin'
    },
    {
        alias: 'lustria',
        name: 'Lustria',
        url: '/wp-content/plugins/web-to-print-online-designer/assets/fonts/lustria.ttf',
        type: 'local',
        cat: ['0'],
        subset: 'latin'
    }
];
```

#### 1.3 增强了字体加载后的处理
- 清理 Fabric.js 字体缓存
- 强制重新渲染所有画布
- 添加了详细的日志输出

### 2. 创建了辅助脚本

#### 2.1 `font-test-local.js` - 字体测试脚本
- 创建可视化的字体测试界面
- 检查字体CSS是否已注入
- 检查字体文件是否存在
- 使用 FontFaceObserver 验证字体加载状态

#### 2.2 `font-fix-complete.js` - 完整字体修复脚本
- 重写 `insertTemplateFont` 函数以支持本地字体
- 重写 `addText` 函数以确保字体正确应用
- 自动测试新增字体
- 提供字体测试函数

## 使用方法

### 方法1：使用修复后的 app-modern.min.js
直接使用已修复的 `app-modern.min.js` 文件，新增字体应该能够正常工作。

### 方法2：使用辅助脚本
在页面中引入辅助脚本：

```html
<!-- 引入字体测试脚本 -->
<script src="/wp-content/plugins/web-to-print-online-designer/assets/js/font-test-local.js"></script>

<!-- 引入完整字体修复脚本 -->
<script src="/wp-content/plugins/web-to-print-online-designer/assets/js/font-fix-complete.js"></script>
```

### 方法3：手动测试
在浏览器控制台中运行：

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

## 验证方法

1. 打开浏览器开发者工具的控制台
2. 查看是否有字体相关的日志输出
3. 使用字体测试脚本创建可视化测试界面
4. 在在线设计器中尝试使用新增字体

## 注意事项

1. 确保字体文件存在于正确的目录中
2. 确保服务器支持 .ttf 文件的访问
3. 如果字体仍然不显示，检查浏览器控制台是否有错误信息
4. 某些浏览器可能需要刷新页面才能正确加载字体

## 故障排除

### 字体文件不存在
检查 `/wp-content/plugins/web-to-print-online-designer/assets/fonts/` 目录中是否包含：
- `greatvibes.ttf`
- `hindsiliguri.ttf`
- `lustria.ttf`

### CSS未注入
检查页面 `<head>` 中是否包含字体CSS：
```css
@font-face {
    font-family: 'greatvibes';
    src: url('/wp-content/plugins/web-to-print-online-designer/assets/fonts/greatvibes.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
    font-display: swap;
}
```

### 字体加载失败
检查浏览器控制台是否有网络错误或字体加载错误。

## 更新日志

- 2024-01-XX: 初始版本，修复字体加载和显示问题
- 添加了本地字体映射支持
- 增强了字体资源数据初始化
- 创建了字体测试和修复脚本 