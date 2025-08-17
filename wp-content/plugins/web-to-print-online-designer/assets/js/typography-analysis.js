// insertTypography函数文字内容字段分析脚本
console.log('=== insertTypography函数文字内容字段分析 ===');

// 分析insertTypography函数中的文字内容字段
function analyzeTypographyFields() {
    console.log('\n1. insertTypography函数主要流程:');
    console.log('- 接收typo参数（包含folder等信息）');
    console.log('- 调用NBDDataFactory.get获取字体和设计数据');
    console.log('- 处理字体加载');
    console.log('- 加载模板图层');
    console.log('- 渲染文字');
    
    console.log('\n2. 文字内容相关字段分析:');
    
    // 从NBDDataFactory.get返回的数据结构
    console.log('\n2.1 NBDDataFactory.get返回的数据结构:');
    console.log('data.data.font - 字体数组');
    console.log('data.data.design.frame_0.objects - 设计对象数组');
    
    // 字体对象字段
    console.log('\n2.2 字体对象(font)字段:');
    console.log('- font.alias: 字体别名');
    console.log('- font.name: 字体名称');
    console.log('- font.url: 字体文件URL');
    console.log('- font.type: 字体类型（local/google等）');
    console.log('- font.cat: 字体分类');
    console.log('- font.subset: 字体子集');
    
    // 设计对象字段
    console.log('\n2.3 设计对象(design.frame_0.objects)字段:');
    console.log('- object.type: 对象类型（text/i-text/textbox/curvedText等）');
    console.log('- object.text: 文字内容（主要字段）');
    console.log('- object.fontFamily: 字体族');
    console.log('- object.fontSize: 字体大小');
    console.log('- object.fontWeight: 字体粗细');
    console.log('- object.fontStyle: 字体样式');
    console.log('- object.fill: 文字颜色');
    console.log('- object.left: 左边距');
    console.log('- object.top: 上边距');
    console.log('- object.width: 宽度');
    console.log('- object.height: 高度');
    console.log('- object.angle: 旋转角度');
    console.log('- object.scaleX: X轴缩放');
    console.log('- object.scaleY: Y轴缩放');
    console.log('- object.opacity: 透明度');
    
    // insertCanvaTypo函数中的文字字段
    console.log('\n2.4 insertCanvaTypo函数中的文字字段:');
    console.log('- path.text: SVG路径中的文字内容');
    console.log('- path.fontFamily: SVG路径中的字体族');
    console.log('- path.fill: SVG路径中的填充颜色');
    console.log('- el.text: 提取的文字内容');
    console.log('- el.font: 提取的字体名称');
    console.log('- el.fill: 提取的颜色');
    
    // 文字状态字段
    console.log('\n2.5 文字状态字段(states.text):');
    console.log('- text.text: 文字内容');
    console.log('- text.fontFamily: 字体族');
    console.log('- text.fontSize: 字体大小');
    console.log('- text.ptFontSize: 点字体大小');
    console.log('- text.fontWeight: 字体粗细');
    console.log('- text.fontStyle: 字体样式');
    console.log('- text.fill: 文字颜色');
    console.log('- text.stroke: 描边颜色');
    console.log('- text.textBackgroundColor: 文字背景色');
    console.log('- text.textAlign: 文字对齐');
    console.log('- text.spacing: 字符间距');
    console.log('- text.lineHeight: 行高');
    console.log('- text.is_uppercase: 是否大写');
    console.log('- text.rtl: 是否从右到左');
    
    // 文字对象创建时的字段
    console.log('\n2.6 文字对象创建时的字段:');
    console.log('- textObj.fontFamily: 字体族');
    console.log('- textObj.fontSize: 字体大小');
    console.log('- textObj.ptFontSize: 点字体大小');
    console.log('- textObj.fontWeight: 字体粗细');
    console.log('- textObj.fontStyle: 字体样式');
    console.log('- textObj.fill: 文字颜色');
    console.log('- textObj.stroke: 描边颜色');
    console.log('- textObj.textAlign: 文字对齐');
    console.log('- textObj.spacing: 字符间距');
    console.log('- textObj.lineHeight: 行高');
    console.log('- textObj.radius: 圆角半径');
    console.log('- textObj.objectCaching: 对象缓存');
    console.log('- textObj.lockUniScaling: 锁定统一缩放');
    console.log('- textObj.lockRotation: 锁定旋转');
    
    // 字段映射关系
    console.log('\n3. 字段映射关系:');
    console.log('原始数据 -> 处理后的对象:');
    console.log('object.text -> item.text (文字内容)');
    console.log('object.fontFamily -> item.fontFamily (字体族)');
    console.log('object.fontSize -> item.fontSize (字体大小)');
    console.log('object.fill -> item.fill (文字颜色)');
    console.log('object.left -> item.left (位置X)');
    console.log('object.top -> item.top (位置Y)');
    
    // 特殊处理
    console.log('\n4. 特殊处理:');
    console.log('- 字体加载: 通过insertTemplateFont函数加载字体');
    console.log('- 文字渲染: 通过renderTextAfterLoadFont函数渲染文字');
    console.log('- 图层加载: 通过loadTemplateLayer函数加载图层');
    console.log('- 文字替换: 支持用户信息字段替换');
    console.log('- 大小写转换: 支持is_uppercase字段控制');
    console.log('- RTL支持: 支持从右到左文字显示');
    
    // 用户信息字段替换
    console.log('\n5. 用户信息字段替换:');
    console.log('- 支持动态替换文字内容');
    console.log('- 字段格式: {{field_name}}');
    console.log('- 替换逻辑: 根据用户信息自动替换');
    console.log('- 大小写控制: 根据is_uppercase字段决定');
    
    return {
        textFields: [
            'text', 'fontFamily', 'fontSize', 'ptFontSize', 'fontWeight', 
            'fontStyle', 'fill', 'stroke', 'textBackgroundColor', 'textAlign',
            'spacing', 'lineHeight', 'is_uppercase', 'rtl'
        ],
        positionFields: [
            'left', 'top', 'width', 'height', 'angle', 'scaleX', 'scaleY', 'opacity'
        ],
        styleFields: [
            'fontFamily', 'fontSize', 'fontWeight', 'fontStyle', 'fill', 'stroke'
        ]
    };
}

// 执行分析
var analysis = analyzeTypographyFields();

console.log('\n=== 分析完成 ===');
console.log('主要文字内容字段:', analysis.textFields);
console.log('位置相关字段:', analysis.positionFields);
console.log('样式相关字段:', analysis.styleFields); 