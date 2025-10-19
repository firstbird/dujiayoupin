# get_list_images 找不到文件的Bug修复

## 问题描述
尽管预览图片文件存在（如 `frame_0.png`），但 `get_list_images()` 函数返回空数组，导致设计列表为空。

## 问题现象
```
目录存在: /www/wwwroot/dujiayoupin/wp-content/uploads/nbdesigner/designs/2996_d0cd5691760884594/preview
文件存在: frame_0.png (48945 bytes)
但是 get_list_images() 返回: Array() - 空数组！
```

日志显示：
```
[nbd_get_user_designs] 检查设计: 2996_d0cd5691760884594
[nbd_get_user_designs] 路径: /www/wwwroot/.../preview
[nbd_get_user_designs] 找到 0 个预览图片  ← 错误！
```

## 根本原因

### Bug 1: 参数顺序错误 🐛

**位置**: `includes/class-util.php` 第 25 行

**错误代码**:
```php
public static function get_list_images($path, $level = 100) {
    $files = array();
    if (is_dir($path)) {
        // ❌ 错误：参数顺序不对
        $files = self::get_list_files_by_type($path, $level, 'image');
    }
    return $files;
}
```

**函数签名**:
```php
public static function get_list_files_by_type($path, $type, $level = 100)
```

**问题分析**:
- 调用时传的参数是: `($path, $level, 'image')` = `($path, 100, 'image')`
- 但函数期望的是: `($path, $type, $level)`
- 结果:
  - `$type` 得到的是 `100` (数字)
  - `$level` 得到的是 `'image'` (字符串)

### Bug 2: 文件类型过滤逻辑错误 🐛

**位置**: `includes/class-util.php` 第 36 行

**错误代码**:
```php
public static function get_list_files_by_type($path, $type, $level = 100){
    $files = array();
    if(is_dir($path)){
        $files = self::get_list_files($path, $level);
        // ❌ 错误：检查文件名中是否包含 "image" 字符串
        $files = array_filter($files, function($file) use ($type) {
            return strpos($file, $type) !== false;
        });
    }
    return $files;
}
```

**问题分析**:
- 由于 Bug 1，`$type` = 100
- 过滤条件变成：检查文件名中是否包含 "100"
- 文件名 `frame_0.png` 不包含 "100"
- 结果：所有图片都被过滤掉！

**即使 Bug 1 修复后**:
- 原逻辑检查文件名是否包含 "image" 字符串
- 但图片文件名通常是 `frame_0.png`，不包含 "image"
- 仍然找不到文件！

### Bug 3: 返回的文件路径不完整 🐛

**错误代码**:
```php
public static function get_list_files($folder = '', $levels = 100) {
    $list = array();
    while (false !== ($file = readdir($dir_handle))) {
        if ($file != '.' && $file != '..') {
            $list[] = $file;  // ❌ 只返回文件名，没有完整路径
        }
    }
    return $list;
}
```

**问题**:
- 返回的是 `['frame_0.png']`
- 不是 `['/full/path/to/preview/frame_0.png']`
- 后续代码可能需要完整路径

## 修复方案

### 修复 1: 纠正参数顺序

```php
public static function get_list_images($path, $level = 100) {
    error_log('[get_list_images] 正在获取图片列表，路径: ' . $path);
    $files = array();
    if (is_dir($path)) {
        // ✅ 修复：正确的顺序是 ($path, $type, $level)
        $files = self::get_list_files_by_type($path, 'image', $level);
    } else {
        error_log('[get_list_images] 路径不是目录: ' . $path);
    }
    error_log('[get_list_images] 找到图片文件: ' . count($files) . ' 个');
    if(count($files) > 0) {
        error_log('[get_list_images] 文件列表: ' . print_r($files, true));
    }
    return $files;
}
```

### 修复 2: 改进文件类型过滤逻辑

```php
public static function get_list_files_by_type($path, $type, $level = 100){
    $files = array();
    if(is_dir($path)){
        $files = self::get_list_files($path, $level);
        
        // ✅ 根据类型定义文件扩展名
        $extensions = array();
        if($type == 'image'){
            $extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg');
        } elseif($type == 'video'){
            $extensions = array('mp4', 'avi', 'mov', 'wmv', 'flv', 'webm');
        } elseif($type == 'pdf'){
            $extensions = array('pdf');
        }
        
        if(!empty($extensions)){
            // ✅ 按文件扩展名过滤
            $files = array_filter($files, function($file) use ($path, $extensions) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                return in_array($ext, $extensions);
            });
        } else {
            // 如果没有定义扩展名，使用原来的逻辑
            $files = array_filter($files, function($file) use ($type) {
                return strpos($file, $type) !== false;
            });
        }
        
        // ✅ 添加完整路径
        $files = array_map(function($file) use ($path) {
            return $path . '/' . $file;
        }, $files);
    }
    return $files;
}
```

## 修复效果

### 修复前:
```php
get_list_images('/path/to/preview')
// 调用 get_list_files_by_type($path, 100, 'image')
// $type = 100, 检查文件名是否包含 "100"
// frame_0.png 不包含 "100"
// 返回: []
```

### 修复后:
```php
get_list_images('/path/to/preview')
// 调用 get_list_files_by_type($path, 'image', 100)
// $type = 'image', 检查扩展名是否在 ['jpg', 'jpeg', 'png', ...]
// frame_0.png 的扩展名是 'png'
// 返回: ['/path/to/preview/frame_0.png']
```

## 日志对比

### 修复前:
```
[nbd_get_user_designs] 找到 0 个预览图片
```

### 修复后:
```
[get_list_images] 正在获取图片列表，路径: /www/.../preview
[get_list_images] 找到图片文件: 1 个
[get_list_images] 文件列表: Array (
    [0] => /www/.../preview/frame_0.png
)
[nbd_get_user_designs] 找到 1 个预览图片
[nbd_get_user_designs] 添加设计到结果: {"id":"2996_d0cd5691760884594","src":"...","created_date":"..."}
[nbd_get_user_designs] 最终返回 1 个设计
```

## 测试验证

### 1. 检查文件是否存在
```bash
ls -la /www/wwwroot/dujiayoupin/wp-content/uploads/nbdesigner/designs/2996_d0cd5691760884594/preview/
# 应该看到: frame_0.png
```

### 2. 测试函数调用
```php
$path = '/www/wwwroot/.../designs/2996_d0cd5691760884594/preview';
$files = Nbdesigner_IO::get_list_images($path);
error_log('Found files: ' . print_r($files, true));
// 修复后应该返回: ['/full/path/preview/frame_0.png']
```

### 3. 测试完整流程
1. 清空 debug.log
2. 登录网站
3. 保存一个设计
4. 刷新产品页面
5. 查看日志和前端响应

**预期结果**:
```javascript
response.data.designs = [{
  id: "2996_d0cd5691760884594",
  src: "https://.../preview/frame_0.png",
  created_date: "2025-10-19 15:52:11"
}]
```

## 影响范围

这个bug影响所有使用 `get_list_images()` 的功能：
- ✅ 设计列表展示
- ✅ 模板预览
- ✅ 我的设计页面
- ✅ 设计缩略图显示
- ✅ 任何需要扫描图片文件的功能

## 修复文件
- `includes/class-util.php` - Nbdesigner_IO 类

## 修复日期
2025-10-19

## 注意事项
⚠️ 这是一个核心函数的bug，影响范围广。修复后需要：
1. 清除浏览器缓存
2. 清空 PHP OPcache (如果启用)
3. 全面测试所有图片相关功能


