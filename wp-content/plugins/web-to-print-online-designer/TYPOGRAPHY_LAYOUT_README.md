# 字体布局重新设计说明

## 概述
本次修改重新设计了字体标签页的布局，添加了中英文切换功能，实现了每行一个字体并居中的显示效果。

## 主要功能

### 1. 语言切换功能
- 顶部添加了中文/英文切换标签
- 默认显示中文字体
- 点击标签可以切换显示不同语言的字体

### 2. 布局优化
- 字体列表改为垂直布局（每行一个字体）
- 字体项目居中显示
- 添加了现代化的卡片样式
- 悬停效果和动画

### 3. 字体分类
- 为每个字体添加了语言属性（chinese/english）
- 根据语言属性自动过滤显示

## 修改的文件

### 1. 字体数据文件
- `data/typography/typo.json` - 添加了语言属性和字体名称

### 2. 字体标签页模板
- `views/modern/sidebars/tab-typography.php` - 重新设计布局和样式

### 3. JavaScript功能
- `assets/js/typography-language-switcher.js` - 新增语言切换功能

### 4. 主页面
- `views/nbdesigner-frontend-modern.php` - 引入新的JavaScript文件

### 5. 示例字体数据
- `data/typography/store/sample1/` - 中文字体示例
- `data/typography/store/sample2/` - 英文字体示例
- `data/typography/store/sample3/` - 中文字体示例
- `data/typography/store/sample4/` - 英文字体示例

## 样式特点

### 语言切换标签
- 圆角按钮设计
- 悬停和激活状态效果
- 居中布局

### 字体列表
- 卡片式设计
- 阴影和圆角
- 悬停时上移效果
- 响应式布局

### 字体项目
- 预览图片居中显示
- 字体名称显示
- 统一的间距和对齐

## 技术实现

### Angular集成
- 使用Angular的$scope管理状态
- 监听数据变化自动更新视图
- 扩展原有功能而不破坏现有逻辑

### 数据过滤
- 根据language属性过滤字体
- 支持默认显示（无language属性的字体）
- 实时更新过滤结果

### 样式系统
- 使用CSS3现代特性
- 响应式设计
- 平滑的过渡动画

## 测试

### 测试页面
- `test-typography-layout.html` - 独立测试页面
- 可以验证布局和功能是否正常

### 功能检查清单
- ✓ 语言切换按钮样式正确
- ✓ 字体列表垂直布局
- ✓ 字体项目居中显示
- ✓ 悬停效果正常
- ✓ 响应式设计
- ✓ 默认显示中文

## 使用方法

1. 进入设计器页面
2. 点击字体标签页
3. 使用顶部的语言切换按钮选择中文或英文
4. 字体列表会根据选择的语言自动过滤
5. 点击字体项目可以插入到设计中

## 兼容性

- 支持现代浏览器（Chrome, Firefox, Safari, Edge）
- 保持与原有功能的兼容性
- 不影响其他标签页的功能

## 调试

### 控制台命令
```javascript
// 获取当前语言
getCurrentLanguage()

// 切换语言
switchTypographyLanguage('chinese')
switchTypographyLanguage('english')

// 获取过滤后的字体数据
getFilteredTypographyData()
```

### 日志信息
- 字体数据加载状态
- 语言切换操作
- 过滤结果统计

## 注意事项

1. 确保字体数据文件包含正确的language属性
2. 新添加的字体需要配置相应的语言属性
3. 如果字体没有language属性，将默认显示在所有语言下
4. 样式修改不会影响字体的实际功能

## 未来扩展

- 支持更多语言分类
- 添加字体搜索功能
- 支持字体收藏
- 添加字体预览功能

