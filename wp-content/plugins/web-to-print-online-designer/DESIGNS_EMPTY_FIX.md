# Response.data.designs 为空问题修复说明

## 问题描述
用户保存设计后，前端调用 `nbd_get_user_designs` 时，返回的 `response.data.designs` 数组为空。

## 问题原因分析

### 可能的原因

1. **数据库中没有设计记录**
   - 保存设计时没有成功插入到 `wp_nbdesigner_mydesigns` 表

2. **设计文件夹没有预览图片**
   - 预览图片生成失败
   - 文件路径不正确

3. **所有设计都在购物车中**
   - 代码逻辑会过滤掉已在购物车中的设计
   - 导致返回空数组

4. **用户未登录**
   - `user_id` 为 0

5. **查询参数问题**
   - `product_id` 或 `variation_id` 不匹配

6. **未初始化 designs 数组**
   - 原代码可能在某些情况下不初始化 `designs` 键

## 修复内容

### 1️⃣ 改进 nbd_get_user_designs 函数
**文件**: `includes/class.nbdesigner.php`  
**位置**: 第 449-537 行

#### 主要改进：

1. **初始化 designs 数组**
```php
$result = array(
    'flag'   =>  1,
    'designs' => array()  // 确保总是有这个键
);
```

2. **使用预处理语句防止SQL注入**
```php
$designs = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}nbdesigner_mydesigns WHERE user_id = %d AND product_id = %d AND variation_id = %d ORDER BY created_date DESC",
    $user_id, $product_id, $variation_id
) );
```

3. **添加详细日志**
- 记录查询参数
- 记录查询结果数量
- 记录每个设计的处理过程
- 记录购物车中的设计ID
- 记录最终返回的设计数量

4. **检查 WC()->cart 是否存在**
```php
if(WC()->cart){
    foreach(WC()->cart->cart_contents as $cart_item_key => $cart_item) {
        // ...
    }
}
```

### 2️⃣ 改进 nbdesigner_insert_table_my_design 函数
**文件**: `includes/class.my.design.php`  
**位置**: 第 542-565 行

#### 主要改进：

1. **添加插入前日志**
```php
error_log('[nbdesigner_insert_table_my_design] 插入设计记录 - product_id: ' . $product_id . ', variation_id: ' . $variation_id . ', folder: ' . $folder . ', user_id: ' . $user_id);
```

2. **检查插入结果**
```php
$result = $wpdb->insert($table_name, array(...));

if($result === false){
    error_log('[nbdesigner_insert_table_my_design] 插入失败: ' . $wpdb->last_error);
    return false;
}

error_log('[nbdesigner_insert_table_my_design] 插入成功，插入ID: ' . $wpdb->insert_id);
return true;
```

## 调试流程

### 查看日志
日志会记录完整的处理流程，查看 `wp-content/debug.log`：

```
[nbd_get_user_designs] 开始执行
[nbd_get_user_designs] user_id: 1
[nbd_get_user_designs] 查询参数 - product_id: 2996, variation_id: 0
[nbd_get_user_designs] 从数据库查询到 2 个设计
[nbd_get_user_designs] 购物车中的设计ID: Array ( [0] => 2996_d0cd5691760884594 )
[nbd_get_user_designs] 检查设计: 2996_d0cd5691760884594, 路径: /path/to/designs/2996_d0cd5691760884594/preview
[nbd_get_user_designs] 找到 1 个预览图片
[nbd_get_user_designs] 设计 2996_d0cd5691760884594 在购物车中: 是
[nbd_get_user_designs] 检查设计: 2996_abc12345678901234, 路径: /path/to/designs/2996_abc12345678901234/preview
[nbd_get_user_designs] 找到 1 个预览图片
[nbd_get_user_designs] 设计 2996_abc12345678901234 在购物车中: 否
[nbd_get_user_designs] 添加设计到结果: {"id":"2996_abc12345678901234","src":"...","created_date":"2025-10-19 12:00:00"}
[nbd_get_user_designs] 最终返回 1 个设计
```

### 检查点

1. **检查用户是否登录**
   - 日志会显示 `user_id`
   - 如果为 0，说明用户未登录

2. **检查数据库查询**
   - 日志会显示查询参数和结果数量
   - 如果为 0，说明数据库中没有记录

3. **检查设计文件**
   - 日志会显示每个设计的预览图片数量
   - 如果为 0，说明预览图片不存在

4. **检查购物车过滤**
   - 日志会显示哪些设计在购物车中
   - 如果所有设计都在购物车，结果为空是正常的

## 数据表结构

```sql
CREATE TABLE `wp_nbdesigner_mydesigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `variation_id` int(11) DEFAULT 0,
  `folder` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`,`variation_id`)
);
```

## 数据流程

```
1. 用户保存设计
   ↓
2. nbd_save_customer_design() 处理保存请求
   ↓
3. generate_design_id() 生成唯一 ID
   ↓
4. store_design_data() 保存设计文件
   ↓
5. create_preview_design() 生成预览图
   ↓
6. nbd_update_design_data() 更新设计数据
   ↓
7. nbdesigner_insert_table_my_design() 插入数据库
   folder = design_id (例如: 2996_d0cd5691760884594)
   ↓
8. 前端加载页面
   ↓
9. 调用 nbd_get_user_designs 获取设计列表
   ↓
10. 从数据库查询设计记录
   ↓
11. 过滤购物车中的设计
   ↓
12. 返回可用设计列表
```

## 常见问题

### Q1: designs 数组为空，但数据库有记录
**原因**: 所有设计都在购物车中
**解决**: 这是正常行为，清空购物车后再试

### Q2: designs 数组为空，数据库也没有记录
**原因**: 
- 保存设计时插入失败
- 用户未登录
- 数据库表不存在

**解决**: 
1. 查看日志中的插入错误
2. 确认用户已登录
3. 检查数据库表是否存在

### Q3: 有记录但没有预览图
**原因**: 预览图生成失败
**解决**: 
1. 检查文件夹权限
2. 检查 GD 或 Imagick 扩展
3. 查看预览图生成日志

## 测试步骤

1. **清空 debug.log**
```bash
> /www/wwwroot/dujiayoupin/wp-content/debug.log
```

2. **登录网站**

3. **打开产品页面**

4. **进入设计器并保存设计**

5. **查看日志**
```bash
tail -f /www/wwwroot/dujiayoupin/wp-content/debug.log | grep "nbd_get_user_designs\|nbdesigner_insert_table_my_design"
```

6. **刷新产品页面**

7. **打开浏览器控制台**，查看输出：
```javascript
product page onready response {success: true, data: {flag: 1, designs: Array(1)}}
```

8. **检查设计预览是否显示**

## 修复日期
2025-10-19

## 相关文件
- `includes/class.nbdesigner.php` - nbd_get_user_designs 函数
- `includes/class.my.design.php` - nbdesigner_insert_table_my_design 函数
- `assets/js/nbdesigner.js` - 前端调用代码


