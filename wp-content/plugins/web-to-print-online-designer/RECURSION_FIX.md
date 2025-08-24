# 字体插入无限递归问题修复

## 问题描述

点击英文字体后出现 `RangeError: Maximum call stack size exceeded` 错误，这是由于函数重写导致的无限递归调用。

## 问题根源

### 1. 函数重写导致的递归
在 `typography-language-switcher.js` 中：
```javascript
var originalInsertTypography = $scope.insertTypography;
$scope.insertTypography = function(typo) {
    // 自定义逻辑
    if (originalInsertTypography) {
        originalInsertTypography.call(this, typo); // 这里可能导致递归
    }
};
```

### 2. 条件调用导致的循环
在 `app-modern.min.js` 的 `insertCanvaTypo` 函数中：
```javascript
if( $scope.settings.task != 'typography' ){
    $scope.insertTypography(typo); // 调用被重写的函数
    return;
};
```

当 `$scope.settings.task` 不等于 `'typography'` 时，会调用重写的 `insertTypography` 函数，而重写的函数又会调用原始函数，形成无限递归。

## 解决方案

### 1. 移除函数重写
不再重写 `insertTypography` 函数，避免递归调用。

### 2. 使用事件监听扩展功能
通过 `$scope.$on('typographyInserted')` 来扩展功能：

```javascript
// 使用事件监听的方式扩展功能，避免重写函数
$scope.$on('typographyInserted', function(event, typo) {
    console.log('📝 字体插入事件触发:', typo);
    console.log('🌐 字体语言:', typo.language);
    console.log('🎯 当前选择语言:', $scope.currentLanguage);
});
```

### 3. 在原始函数中触发事件
在原始的 `insertTypography` 函数中添加事件触发和防递归机制：

```javascript
$scope.insertTypography = function(typo){
    console.log('insertTypography 插入字体:', typo);
    if( !$scope.canAddMoreLayer() ) return;
    
    // 防止无限递归
    if (this._insertTypographyRecursionGuard) {
        console.log('⚠️ 防止无限递归，跳过重复调用');
        return;
    }
    
    this._insertTypographyRecursionGuard = true;
    
    try {
        // 触发字体插入事件
        $scope.$emit('typographyInserted', typo);
        
        // 原有逻辑...
    } finally {
        this._insertTypographyRecursionGuard = false;
    }
};
```

### 4. 简化条件调用
保持 `insertCanvaTypo` 函数的简单性：

```javascript
$scope.insertCanvaTypo = function(typo){
    if( $scope.settings.task != 'typography' ){
        $scope.insertTypography(typo);
        return;
    };
    // 其他逻辑...
};
```

## 修复效果

1. **消除无限递归**：不再有函数重写导致的递归调用
2. **保持功能完整**：通过事件监听保持扩展功能
3. **提高稳定性**：避免调用栈溢出错误
4. **便于维护**：代码结构更清晰，易于理解和维护

## 测试验证

1. 点击英文字体，不再出现调用栈溢出错误
2. 字体插入功能正常工作
3. 事件监听器正确触发
4. 控制台日志正常输出

## 注意事项

1. 确保事件监听器在正确的时机设置
2. 避免在事件处理函数中再次触发相同事件
3. 定期清理不需要的事件监听器
4. 监控控制台错误，及时发现类似问题

## 最新修复

### 问题
之前的修复方案仍然存在递归问题，因为：
1. 保存原始函数引用的时机不对
2. 复杂的条件判断逻辑容易出错

### 解决方案
1. **完全移除函数重写**：不再保存或使用原始函数引用
2. **使用防递归机制**：在 `insertTypography` 函数中添加递归保护
3. **简化调用逻辑**：直接调用函数，不进行复杂的条件判断
4. **事件驱动扩展**：通过事件监听器实现功能扩展

### 优势
- 代码更简单，不容易出错
- 性能更好，没有额外的函数调用开销
- 维护性更强，逻辑清晰
- 扩展性更好，通过事件可以轻松添加新功能
