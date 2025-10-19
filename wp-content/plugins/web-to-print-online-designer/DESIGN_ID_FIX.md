# Design ID 问题修复说明

## 问题描述
用户在设计页面保存设计后，`NBDESIGNCONFIG['design_id']` 为空（undefined）。

## 问题原因分析

### 1. 服务器端问题
在 `includes/class.nbdesigner.php` 文件的 `nbd_save_customer_design()` 方法中：
- 服务器端正确生成了 `design_id`（通过 `generate_design_id()` 方法）
- 将 `design_id` 保存到了 Session 和数据库
- **但是在返回给前端的 `$result` 数组中，没有包含 `design_id` 字段**

```php
// 只返回了这些字段
$result['flag']     = 'success';
$result['folder']   = $nbd_item_key;  // folder就是design_id，但前端代码期望一个单独的design_id字段
$result['image']    = array(...);
// 缺少: $result['design_id'] = $design_id;
```

### 2. 前端问题
在 `assets/js/app-modern.min.js` 文件中：
- 保存设计成功后接收到服务器响应
- **没有将响应中的 `design_id` 设置到 `NBDESIGNCONFIG` 对象中**
- 导致后续代码打印 `NBDESIGNCONFIG['design_id']` 时为空

## 解决方案

### 修改1: 服务器端添加 design_id 到返回结果
**文件**: `includes/class.nbdesigner.php`  
**位置**: 第 4368 行

```php
$result['flag']     = 'success';
$result['folder']   = $nbd_item_key;
$result['design_id'] = $design_id;  // ← 新增这一行
```

### 修改2: 前端接收并设置 design_id
**文件**: `assets/js/app-modern.min.js`  
**位置**: 第 4316-4321 行

```javascript
NBDDataFactory.get(action, dataObj, function(data){
    console.log('saveData NBDDataFactory data: ', data, ' type: ', type);
    data = JSON.parse(data);
    if(data.flag == 'success'){
        // 保存design_id到NBDESIGNCONFIG
        if(data.design_id){
            NBDESIGNCONFIG['design_id'] = data.design_id;  // ← 新增这段代码
            console.log('设置 NBDESIGNCONFIG design_id:', data.design_id);
        }
        // ... 其他代码
```

### 修改3: 改进日志输出和 postMessage
**文件**: `assets/js/app-modern.min.js`  
**位置**: 第 4429-4452 行

```javascript
// 改进日志输出
console.log('NBDESIGNCONFIG design_id: ', NBDESIGNCONFIG['design_id']);  // ← 更清晰的日志
console.log('NBDESIGNCONFIG design: ', NBDESIGNCONFIG['design']);

// 在 postMessage 中包含 design_id
if (window.parent && window.parent.postMessage) {
    window.parent.postMessage({
        type: 'nbdesigner_design_saved',
        design: data,                          // ← 添加完整的设计数据
        designId: NBDESIGNCONFIG['design_id']  // ← 添加 design_id
    }, '*');
}
```

## 数据流程

```
1. 用户保存设计
   ↓
2. 前端调用 nbd_save_customer_design AJAX
   ↓
3. 服务器端 generate_design_id() 生成唯一ID
   例如: 123_a1b2c45671234567890
   ↓
4. 保存设计文件和数据
   ↓
5. 返回结果包含 design_id
   {
     "flag": "success",
     "folder": "123_a1b2c45671234567890",
     "design_id": "123_a1b2c45671234567890",  ← 新增
     "image": {...}
   }
   ↓
6. 前端接收后设置到 NBDESIGNCONFIG
   NBDESIGNCONFIG['design_id'] = data.design_id
   ↓
7. 后续代码可以正常使用 design_id
```

## Design ID 生成规则

```php
function generate_design_id() {
    $product_id = $_POST['product_id'];       // 产品ID
    $random_part = substr(md5(uniqid()), 0, 5); // 随机5位MD5
    $random_number = rand(1, 100);             // 1-100随机数
    $timestamp = time();                       // 时间戳
    
    return $product_id . '_' . $random_part . $random_number . $timestamp;
}
```

示例: `123_a1b2c45671234567890`

## 测试步骤

1. **清除浏览器缓存**
2. **打开产品页面**，选择一个可设计的产品
3. **进入设计器**
4. **添加一些设计元素**（文字、图片等）
5. **点击保存设计**
6. **打开浏览器控制台**，查看输出：
   ```
   设置 NBDESIGNCONFIG design_id: 123_a1b2c45671234567890
   NBDESIGNCONFIG design_id: 123_a1b2c45671234567890
   ```
7. **验证** `NBDESIGNCONFIG['design_id']` 不再为空

## 相关文件

- `includes/class.nbdesigner.php` - 服务器端保存设计逻辑
- `assets/js/app-modern.min.js` - 前端设计器主逻辑
- `views/editor_components/js_config.php` - NBDESIGNCONFIG 初始化

## 修复日期
2025-10-19

## 注意事项

1. 修改 JavaScript 文件后，可能需要清除浏览器缓存才能看到效果
2. 如果使用了 CDN 或缓存插件，需要清除服务器端缓存
3. `design_id` 和 `folder` 字段的值是相同的，都是设计的唯一标识符
4. 这个 `design_id` 会被保存到 Session 和购物车数据中，用于后续的订单处理


