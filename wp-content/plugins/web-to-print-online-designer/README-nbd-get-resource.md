# nbd_get_resource AJAX 实现说明

## 概述

本文档说明了如何在 `NBDDataFactory` 中实现 `nbd_get_resource` 函数的 AJAX 调用，参照 `loadOSSFiles` 函数中 `nbd_oss_list_files` 的调用和实现方式。

## 实现方式对比

### 原有方式 (FormData + $http.post)
```javascript
// 使用 Angular 的 $http.post 和 FormData
var formData = new FormData();
formData.append("action", "nbd_get_resource");
formData.append("nonce", NBDESIGNCONFIG['nonce_get']);
formData.append("type", "background");

$http.post(url, formData, config).then(
    function(response) {
        callback(response.data);
    },
    function(response) {
        console.log('AJAX Error');
    }
);
```

### 新方式 (jQuery.ajax + 普通对象)
```javascript
// 使用 jQuery.ajax 和普通对象，与 nbd_oss_list_files 保持一致
jQuery.ajax({
    url: NBDESIGNCONFIG['ajax_url'],
    method: "POST",
    data: {
        action: 'nbd_get_resource',
        type: 'background',
        nonce: NBDESIGNCONFIG['nonce_get']
    },
    success: function(response) {
        callback(response);
    },
    error: function(xhr, status, error) {
        callback({ flag: 0, error: error });
    }
});
```

## 主要改进

### 1. 统一请求方式
- 将 `nbd_get_resource` 的请求方式改为与 `nbd_oss_list_files` 一致
- 使用 `jQuery.ajax` 而不是 Angular 的 `$http.post`
- 使用普通对象而不是 `FormData`

### 2. 特殊处理逻辑
```javascript
get: function(action, data, callback, progressCallback) {
    // 特殊处理 nbd_get_resource 请求
    if (action === 'nbd_get_resource') {
        return this.getResource(data, callback);
    }
    
    // 原有的 FormData 处理方式
    // ... 其他代码
}
```

### 3. 新增 getResource 方法
```javascript
getResource: function(data, callback) {
    // 构建请求参数
    var requestData = {
        action: 'nbd_get_resource',
        nonce: NBDESIGNCONFIG['nonce_get']
    };
    
    // 添加其他参数
    angular.forEach(data, function(value, key) {
        requestData[key] = value;
    });
    
    // 使用 jQuery.ajax 发送请求
    jQuery.ajax({
        url: NBDESIGNCONFIG['ajax_url'],
        method: "POST",
        data: requestData,
        success: function(response) {
            callback(response);
        },
        error: function(xhr, status, error) {
            callback({ flag: 0, error: error });
        }
    });
}
```

## 使用方式

### 方式 1: 直接调用 getResource
```javascript
NBDDataFactory.getResource({ 
    type: 'background' 
}, function(response) {
    if (response.flag === 1) {
        console.log('成功:', response.data);
    } else {
        console.log('失败:', response.message);
    }
});
```

### 方式 2: 通过 get 方法调用
```javascript
NBDDataFactory.get('nbd_get_resource', { 
    type: 'background' 
}, function(response) {
    if (response.flag === 1) {
        console.log('成功:', response.data);
    } else {
        console.log('失败:', response.message);
    }
});
```

## 响应格式处理

### 标准响应格式
```javascript
{
    flag: 1,
    data: [...]
}
```

### 新响应格式 (如 background 类型)
```javascript
{
    status: 'success',
    message: '获取背景图片成功',
    data: {
        bgs: [...],
        length: 20
    }
}
```

### 错误响应格式
```javascript
{
    flag: 0,
    error: '错误信息',
    message: '请求失败：错误信息'
}
```

## 错误处理

### 网络错误
- 自动显示错误信息
- 调用回调函数传递错误信息
- 支持特定类型的加载指示器

### 验证错误
- nonce 验证失败
- 参数缺失或无效
- 服务器内部错误

## 测试

### 测试页面
- `test-nbd-get-resource.html` - 完整的测试页面
- 包含所有功能的测试用例
- 模拟真实环境进行测试

### 测试项目
1. 配置检查
2. getResource 方法测试
3. get 方法测试
4. 直接 AJAX 测试
5. 错误处理测试

## 兼容性

### 向后兼容
- 原有的 `NBDDataFactory.get()` 调用方式保持不变
- 自动识别 `nbd_get_resource` 请求并使用新方式处理
- 其他请求类型继续使用原有方式

### 前端兼容
- 支持 Angular 1.x
- 支持 jQuery 3.x
- 支持现代浏览器

## 调试信息

### 控制台输出
- 请求开始和结束日志
- 请求数据详情
- 响应数据详情
- 错误信息详情

### 加载指示器
- 支持特定类型的加载提示
- 错误信息显示
- 成功状态反馈

## 注意事项

1. **jQuery 依赖**: 确保页面已加载 jQuery
2. **Nonce 验证**: 使用正确的 nonce 值
3. **错误处理**: 始终检查响应状态
4. **回调函数**: 确保回调函数正确处理各种响应格式

## 问题修复

### JSON.parse 错误修复

在实现过程中发现了一个重要问题：原有的代码期望 `data` 是一个 JSON 字符串，但新的实现直接返回了对象，导致 `JSON.parse(data)` 失败。

**错误信息：**
```
Uncaught SyntaxError: "[object Object]" is not valid JSON
```

**修复方案：**
```javascript
// 修复前
var _data = JSON.parse(data);

// 修复后
var _data;
if (typeof data === 'string') {
    try {
        _data = JSON.parse(data);
    } catch (e) {
        console.error('JSON.parse 失败:', e, '原始数据:', data);
        return;
    }
} else {
    _data = data; // data 已经是对象，直接使用
}
```

**修复位置：**
- `app-modern.min.js` 第 1454 行附近 - 主要的数据处理逻辑
- `app-modern.min.js` 第 1601 行附近 - 模板分类加载
- `app-modern.min.js` 第 1678 行附近 - 全局模板加载
- `app-modern.min.js` 第 1892 行附近 - Flaticon token 获取
- `app-modern.min.js` 第 9473 行附近 - Typography 模板加载
- `app-modern.min.js` 第 2833 行附近 - 产品信息获取

### 响应数据标准化

为了确保数据格式的一致性，新增了响应标准化处理：

```javascript
// 检查响应格式并标准化
var standardizedResponse;
if (response && response.flag !== undefined) {
    // 标准 nbd_get_resource 响应格式
    standardizedResponse = response;
} else if (response && response.status !== undefined) {
    // 新的响应格式（如 background 类型），转换为标准格式
    standardizedResponse = {
        flag: response.status === 'success' ? 1 : 0,
        data: response.data || response.bgs || [],
        message: response.message || '请求成功',
        status: response.status
    };
} else {
    // 其他格式，尝试标准化
    standardizedResponse = {
        flag: 1,
        data: response || [],
        message: '请求成功'
    };
}
```

## 总结

通过这种方式实现，`nbd_get_resource` 的 AJAX 调用现在与 `nbd_oss_list_files` 保持一致的风格和结构，提高了代码的一致性和可维护性。同时保持了向后兼容性，不会影响现有的功能。

**主要改进：**
1. ✅ 统一了 AJAX 请求方式
2. ✅ 修复了 JSON.parse 错误
3. ✅ 标准化了响应数据格式
4. ✅ 保持了向后兼容性
5. ✅ 增强了错误处理能力
