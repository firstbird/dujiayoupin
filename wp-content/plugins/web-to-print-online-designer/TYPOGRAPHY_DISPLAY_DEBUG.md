# 字体显示问题调试指南

## 问题描述
控制台不再无限打印错误，但是字体没有显示。

## 可能的原因

### 1. 字体数据未加载
- AJAX请求失败
- 字体数据文件不存在或格式错误
- 服务器端字体加载逻辑问题

### 2. 字体预览图片问题
- 预览图片文件不存在
- 图片URL生成错误
- 图片访问权限问题

### 3. Angular应用问题
- 字体数据加载时机问题
- 视图更新问题
- 过滤函数问题

## 调试步骤

### 步骤1：检查控制台输出
打开浏览器开发者工具，查看控制台输出：

```javascript
// 在控制台执行以下命令
debugTypographyData()
```

### 步骤2：检查字体数据加载
在控制台执行：
```javascript
forceLoadTypography()
```

### 步骤3：检查字体预览图片
在控制台执行：
```javascript
testTypographyImages()
```

### 步骤4：使用测试页面
访问以下测试页面：

1. **字体数据加载测试**：
   ```
   /wp-content/plugins/web-to-print-online-designer/test-font-data-loading.html
   ```

2. **字体预览图片测试**：
   ```
   /wp-content/plugins/web-to-print-online-designer/test-typography-images.html
   ```

### 步骤5：检查Network标签页
在浏览器开发者工具的Network标签页中：
1. 刷新页面
2. 查找对`admin-ajax.php`的请求
3. 查找字体预览图片的请求
4. 检查请求状态和响应

## 常见解决方案

### 解决方案1：手动触发字体加载
```javascript
// 在控制台执行
var app = angular.element(document.body).scope();
if (app && app.getResource) {
    app.getResource('typography', '#tab-typography');
}
```

### 解决方案2：检查字体数据
```javascript
// 在控制台执行
var app = angular.element(document.body).scope();
if (app && app.resource && app.resource.typography) {
    console.log('字体数据:', app.resource.typography.data);
    console.log('数据长度:', app.resource.typography.data ? app.resource.typography.data.length : 0);
}
```

### 解决方案3：检查过滤函数
```javascript
// 在控制台执行
var app = angular.element(document.body).scope();
if (app && app.filteredTypographyData) {
    console.log('过滤后的数据:', app.filteredTypographyData());
}
```

### 解决方案4：检查语言设置
```javascript
// 在控制台执行
var app = angular.element(document.body).scope();
if (app) {
    console.log('当前语言:', app.currentLanguage);
    console.log('切换语言函数:', app.switchLanguage);
}
```

## 调试命令

### 基础调试命令
```javascript
// 获取当前语言
getCurrentLanguage()

// 切换语言
switchTypographyLanguage('chinese')
switchTypographyLanguage('english')

// 获取过滤后的字体数据
getFilteredTypographyData()

// 详细调试信息
debugTypographyData()

// 手动触发字体加载
forceLoadTypography()

// 测试字体预览图片
testTypographyImages()
```

### 高级调试命令
```javascript
// 检查Angular应用状态
var app = angular.element(document.body).scope();
console.log('App exists:', !!app);
console.log('Resource exists:', !!(app && app.resource));
console.log('Typography exists:', !!(app && app.resource && app.resource.typography));

// 检查字体数据
if (app && app.resource && app.resource.typography) {
    console.log('Typography data:', app.resource.typography.data);
    console.log('Data length:', app.resource.typography.data ? app.resource.typography.data.length : 0);
}

// 检查过滤函数
if (app && app.filteredTypographyData) {
    console.log('Filtered data:', app.filteredTypographyData());
}

// 检查generateTypoLink函数
if (app && app.generateTypoLink) {
    var testFont = {id: 1, folder: 'sample1', name: '测试字体'};
    console.log('Generated URL:', app.generateTypoLink(testFont));
}
```

## 日志分析

### 正常启动日志
```
📜 字体语言切换脚本已加载
Angular应用已初始化，开始初始化字体语言切换功能
=== 初始化字体语言切换功能 ===
✅ 字体语言切换功能已初始化
🌐 当前语言: chinese
📊 字体数据状态: typography未初始化
🚀 开始加载字体数据...
📡 请求参数: type=typography, container=#tab-typography
🎉 字体数据已加载，总数: 4
✅ 字体数据已加载，开始过滤
🖼️ 生成字体预览URL: http://localhost/wp-content/plugins/web-to-print-online-designer/data/typography/store/sample1/frame_0.png
```

### 问题日志
```
📜 字体语言切换脚本已加载
Angular应用已初始化，开始初始化字体语言切换功能
=== 初始化字体语言切换功能 ===
✅ 字体语言切换功能已初始化
🌐 当前语言: chinese
📊 字体数据状态: resource未初始化
❌ Resource对象未初始化
```

## 检查清单

### 字体数据检查
- [ ] 字体数据文件存在：`/wp-content/plugins/web-to-print-online-designer/data/typography/typo.json`
- [ ] 字体数据格式正确（JSON格式）
- [ ] 字体数据包含必要的字段（id, folder, language, name）
- [ ] AJAX请求成功返回字体数据

### 字体预览图片检查
- [ ] 预览图片文件存在：`/wp-content/plugins/web-to-print-online-designer/data/typography/store/sample*/frame_0.png`
- [ ] 预览图片文件大小大于0
- [ ] 预览图片URL可以正常访问
- [ ] 预览图片格式正确（PNG格式）

### Angular应用检查
- [ ] Angular应用正确初始化
- [ ] Resource对象存在
- [ ] Typography对象存在
- [ ] 字体数据数组不为空
- [ ] 过滤函数正常工作
- [ ] 语言切换功能正常

### 视图更新检查
- [ ] 字体列表正确渲染
- [ ] 语言切换按钮正常工作
- [ ] 字体项目正确显示
- [ ] 预览图片正确加载

## 临时解决方案

如果问题无法立即解决，可以使用以下临时方案：

### 方案1：使用默认字体显示
```javascript
// 临时禁用语言切换功能，显示所有字体
window.showAllTypography = function() {
    var app = angular.element(document.body).scope();
    if (app) {
        app.currentLanguage = 'all';
        app.filteredTypographyData = function() {
            return app.resource && app.resource.typography && app.resource.typography.data ? app.resource.typography.data : [];
        };
        app.$apply();
    }
};
```

### 方案2：手动添加字体数据
```javascript
// 手动添加字体数据到页面
window.addTypographyData = function() {
    var app = angular.element(document.body).scope();
    if (app && app.resource && app.resource.typography) {
        app.resource.typography.data = [
            {
                id: 1,
                folder: "sample1",
                language: "chinese",
                name: "中文字体1"
            },
            {
                id: 2,
                folder: "sample2",
                language: "english",
                name: "English Font 1"
            }
        ];
        app.$apply();
    }
};
```

## 联系支持

如果以上步骤都无法解决问题，请提供以下信息：

1. 浏览器控制台的完整日志
2. Network标签页中的请求详情
3. 测试页面的结果
4. 服务器错误日志
5. WordPress版本和插件版本
6. 服务器环境信息
