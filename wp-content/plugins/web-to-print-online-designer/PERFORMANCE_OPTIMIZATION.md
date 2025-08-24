# 字体链接生成函数性能优化

## 问题描述

`$scope.generateTypoLink` 函数在正常情况下被频繁调用，导致性能问题。主要原因包括：

1. **AngularJS 脏检查机制**：每次 `$digest` 循环都会重新计算 `{{generateTypoLink(typo)}}` 表达式
2. **ng-repeat 循环**：每个字体项目都会调用一次函数
3. **频繁的数据变化**：字体数据更新、语言切换等操作触发大量函数调用
4. **缺乏缓存机制**：每次调用都重新计算URL

## 优化方案

### 1. 函数级缓存优化

在 `app-modern.min.js` 中的 `generateTypoLink` 函数添加了缓存机制：

```javascript
$scope.generateTypoLink = function(typo){
    // 创建缓存键
    var cacheKey = typo.id + '_' + typo.language + '_' + ($scope.settings.task || 'default');
    
    // 检查缓存
    if (!$scope._typoLinkCache) {
        $scope._typoLinkCache = {};
    }
    
    if ($scope._typoLinkCache[cacheKey]) {
        return $scope._typoLinkCache[cacheKey];
    }
    
    // 计算URL并缓存
    var url = /* URL计算逻辑 */;
    $scope._typoLinkCache[cacheKey] = url;
    return url;
};
```

### 2. 模板级缓存优化

在 `tab-typography.php` 中使用对象属性缓存：

```html
<img ng-src="{{typo._cachedSrc || (typo._cachedSrc = generateTypoLink(typo))}}" />
```

### 3. ng-repeat 性能优化

添加 `track by` 来优化 AngularJS 的 DOM 操作：

```html
<li ng-repeat="typo in filteredTypographyData | limitTo: resource.typography.filter.currentPage * resource.typography.filter.perPage track by typo.id">
```

### 4. 缓存清理机制

在 `typography-language-switcher.js` 中添加缓存清理逻辑：

```javascript
$scope.$watch('resource.typography.data', function(newData, oldData) {
    // 清理函数级缓存
    if ($scope._typoLinkCache) {
        $scope._typoLinkCache = {};
    }
    
    // 清理对象级缓存
    if (newData && newData.length > 0) {
        newData.forEach(function(typo) {
            if (typo._cachedSrc) {
                delete typo._cachedSrc;
            }
        });
    }
});
```

## 性能提升效果

1. **减少函数调用次数**：从每次 `$digest` 循环调用 N 次减少到最多调用 1 次
2. **提高响应速度**：缓存命中时直接返回结果，无需重新计算
3. **降低CPU使用率**：减少不必要的字符串拼接和URL生成操作
4. **改善用户体验**：减少页面卡顿和延迟

## 注意事项

1. 缓存会在字体数据更新时自动清理
2. 缓存键包含字体ID、语言和任务类型，确保唯一性
3. 内存使用量会略有增加，但相比性能提升是值得的
4. 建议在生产环境中监控缓存命中率

## 监控建议

可以通过以下方式监控优化效果：

```javascript
// 在 generateTypoLink 函数中添加统计
if ($scope._typoLinkCache[cacheKey]) {
    console.log('缓存命中:', cacheKey);
    return $scope._typoLinkCache[cacheKey];
}
console.log('缓存未命中，重新计算:', cacheKey);
```
