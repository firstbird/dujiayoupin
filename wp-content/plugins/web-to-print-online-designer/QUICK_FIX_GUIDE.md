# 字体显示问题快速修复指南

## 问题描述
修改后字体看不到了

## 快速修复步骤

### 步骤1：检查字体数据
在浏览器控制台执行：
```javascript
checkTypographyStatus()
```

### 步骤2：如果字体数据为空，手动添加测试数据
在浏览器控制台执行：
```javascript
addTestTypographyData()
```

### 步骤3：如果仍然看不到字体，使用临时修复
在浏览器控制台执行：
```javascript
// 临时禁用语言切换，显示所有字体
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
        },
        {
            id: 3,
            folder: "sample3",
            language: "chinese",
            name: "中文字体2"
        },
        {
            id: 4,
            folder: "sample4",
            language: "english",
            name: "English Font 2"
        }
    ];
    app.$apply();
    console.log('✅ 临时字体数据已添加');
}
```

### 步骤4：测试字体图片
访问测试页面：
```
/wp-content/plugins/web-to-print-online-designer/test-typography-simple.html
```

### 步骤5：检查文件权限
确保字体数据文件可读：
```bash
ls -la wp-content/plugins/web-to-print-online-designer/data/typography/
ls -la wp-content/plugins/web-to-print-online-designer/data/typography/store/sample*/frame_0.png
```

## 常见问题解决方案

### 问题1：字体数据未加载
**症状**: 控制台显示"字体数据未加载"
**解决**: 执行 `addTestTypographyData()`

### 问题2：字体图片无法显示
**症状**: 字体项目显示但图片为空白
**解决**: 检查图片文件是否存在，重新生成预览图片

### 问题3：Angular作用域问题
**症状**: 控制台显示"无法获取Angular作用域"
**解决**: 刷新页面，等待Angular完全加载

### 问题4：CSS样式问题
**症状**: 字体项目存在但不可见
**解决**: 检查CSS样式，确保没有`display: none`

## 调试命令

### 基础调试
```javascript
// 检查字体状态
checkTypographyStatus()

// 添加测试数据
addTestTypographyData()

// 检查Angular应用
var app = angular.element(document.body).scope();
console.log('App:', app);
console.log('Resource:', app ? app.resource : 'No app');
```

### 高级调试
```javascript
// 检查DOM元素
var items = document.querySelectorAll('.typography-item');
console.log('字体项目数量:', items.length);

// 检查字体数据
var app = angular.element(document.body).scope();
if (app && app.resource && app.resource.typography) {
    console.log('字体数据:', app.resource.typography.data);
    console.log('数据长度:', app.resource.typography.data ? app.resource.typography.data.length : 0);
}
```

## 临时解决方案

如果问题无法立即解决，可以使用以下临时方案：

### 方案1：显示所有字体
```javascript
// 修改模板，显示所有字体而不是过滤后的字体
// 在tab-typography.php中，将：
// ng-repeat="typo in filteredTypographyData()"
// 改为：
// ng-repeat="typo in resource.typography.data"
```

### 方案2：手动渲染字体
```javascript
// 手动创建字体项目
function createTypographyItems() {
    var container = document.querySelector('.typography-items');
    if (container) {
        var fonts = [
            {id: 1, folder: 'sample1', name: '中文字体1'},
            {id: 2, folder: 'sample2', name: 'English Font 1'},
            {id: 3, folder: 'sample3', name: '中文字体2'},
            {id: 4, folder: 'sample4', name: 'English Font 2'}
        ];
        
        container.innerHTML = '';
        fonts.forEach(function(font) {
            var li = document.createElement('li');
            li.className = 'typography-item';
            li.innerHTML = `
                <div class="typo-item-content">
                    <img src="/wp-content/plugins/web-to-print-online-designer/data/typography/store/${font.folder}/frame_0.png" alt="${font.name}" class="typo-preview" />
                    <div class="typo-name">${font.name}</div>
                </div>
            `;
            container.appendChild(li);
        });
    }
}
```

## 联系支持

如果以上步骤都无法解决问题，请提供：

1. 浏览器控制台的完整日志
2. `checkTypographyStatus()` 的输出结果
3. 测试页面的结果
4. 服务器错误日志
