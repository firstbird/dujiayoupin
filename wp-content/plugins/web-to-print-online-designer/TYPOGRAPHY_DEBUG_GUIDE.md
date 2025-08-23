# 字体数据加载问题调试指南

## 问题描述
控制台一直无限打印"字体数据未加载"的问题。

## 可能的原因

### 1. 字体数据文件不存在或无法访问
- 检查文件路径：`/wp-content/plugins/web-to-print-online-designer/data/typography/typo.json`
- 检查文件权限
- 检查文件内容格式

### 2. AJAX请求失败
- 检查WordPress AJAX URL配置
- 检查nonce验证
- 检查服务器响应

### 3. Angular应用初始化问题
- 检查Angular应用是否正确初始化
- 检查$scope对象是否正确获取

### 4. 字体数据加载逻辑问题
- 检查getResource函数是否被正确调用
- 检查字体数据是否正确解析

## 调试步骤

### 步骤1：检查控制台输出
打开浏览器开发者工具，查看控制台输出：

```javascript
// 在控制台执行以下命令
debugTypographyData()
```

### 步骤2：检查字体数据文件
访问字体数据文件URL：
```
/wp-content/plugins/web-to-print-online-designer/data/typography/typo.json
```

应该返回类似以下内容：
```json
[
  {
    "id": 1,
    "folder": "sample1",
    "language": "chinese",
    "name": "中文字体1"
  },
  ...
]
```

### 步骤3：手动触发字体加载
在控制台执行：
```javascript
forceLoadTypography()
```

### 步骤4：检查AJAX请求
在浏览器开发者工具的Network标签页中：
1. 刷新页面
2. 查找对`admin-ajax.php`的请求
3. 检查请求参数和响应

### 步骤5：使用测试页面
访问测试页面：
```
/wp-content/plugins/web-to-print-online-designer/test-font-data-loading.html
```

## 常见解决方案

### 解决方案1：检查文件权限
```bash
chmod 644 wp-content/plugins/web-to-print-online-designer/data/typography/typo.json
chmod 755 wp-content/plugins/web-to-print-online-designer/data/typography/
```

### 解决方案2：检查WordPress配置
确保WordPress的AJAX功能正常工作：
```php
// 在主题的functions.php中添加测试
add_action('wp_ajax_test_ajax', 'test_ajax_function');
add_action('wp_ajax_nopriv_test_ajax', 'test_ajax_function');

function test_ajax_function() {
    wp_send_json_success('AJAX working');
}
```

### 解决方案3：检查Angular应用
确保Angular应用正确初始化：
```javascript
// 在控制台检查
var app = angular.element(document.body).scope();
console.log('Angular app:', app);
console.log('Resource:', app ? app.resource : 'No app');
```

### 解决方案4：手动加载字体数据
如果自动加载失败，可以手动加载：
```javascript
// 在控制台执行
var app = angular.element(document.body).scope();
if (app && app.getResource) {
    app.getResource('typography', '#tab-typography');
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

## 联系支持

如果以上步骤都无法解决问题，请提供以下信息：

1. 浏览器控制台的完整日志
2. Network标签页中的AJAX请求详情
3. 服务器错误日志
4. WordPress版本和插件版本
5. 服务器环境信息

## 临时解决方案

如果问题无法立即解决，可以使用以下临时方案：

1. 禁用字体语言切换功能
2. 使用默认的字体显示方式
3. 手动添加字体数据到页面

```javascript
// 临时禁用语言切换功能
window.disableTypographyLanguageSwitcher = function() {
    var app = angular.element(document.body).scope();
    if (app) {
        app.currentLanguage = 'chinese';
        app.filteredTypographyData = function() {
            return app.resource && app.resource.typography && app.resource.typography.data ? app.resource.typography.data : [];
        };
    }
};
```
