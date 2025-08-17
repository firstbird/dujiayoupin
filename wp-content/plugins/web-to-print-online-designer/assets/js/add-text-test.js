// 添加文本测试脚本
console.log('=== 添加文本测试开始 ===');

// 测试1: 检查基本配置
console.log('测试1: 检查基本配置');
if (typeof NBDESIGNCONFIG !== 'undefined' && NBDESIGNCONFIG.default_font) {
    console.log('默认字体配置:', NBDESIGNCONFIG.default_font);
} else {
    console.log('⚠️ 默认字体配置不存在');
}

// 测试2: 测试添加文本函数
console.log('\n测试2: 测试添加文本函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.addText) {
        console.log('addText函数存在');
        
        // 测试添加Arial字体文本
        console.log('测试添加Arial字体文本...');
        try {
            scope.addText('Arial字体测试文本', 'bodytext', {
                fontFamily: 'Arial',
                fontSize: 24,
                top: 100,
                left: 100
            });
            console.log('✅ Arial字体文本添加成功');
        } catch (error) {
            console.error('Arial字体文本添加错误:', error);
        }
        
        // 测试添加新增字体文本
        var newFonts = ['greatvibes', 'hindsiliguri', 'lustria'];
        newFonts.forEach(function(fontName, index) {
            console.log('测试添加' + fontName + '字体文本...');
            try {
                scope.addText(fontName + '字体测试文本', 'bodytext', {
                    fontFamily: fontName,
                    fontSize: 24,
                    top: 150 + index * 50,
                    left: 100
                });
                console.log('✅ ' + fontName + '字体文本添加成功');
            } catch (error) {
                console.error(fontName + '字体文本添加错误:', error);
            }
        });
        
    } else {
        console.log('⚠️ addText函数不存在');
    }
} else {
    console.log('❌ Angular不存在');
}

// 测试3: 测试曲线文本函数
console.log('\n测试3: 测试曲线文本函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.addCurvedText) {
        console.log('addCurvedText函数存在');
        
        // 测试添加曲线文本
        console.log('测试添加曲线文本...');
        try {
            scope.addCurvedText('曲线文本测试');
            console.log('✅ 曲线文本添加成功');
        } catch (error) {
            console.error('曲线文本添加错误:', error);
        }
    } else {
        console.log('⚠️ addCurvedText函数不存在');
    }
} else {
    console.log('❌ Angular不存在');
}

// 测试4: 检查错误日志
console.log('\n测试4: 检查错误日志');
console.log('请检查控制台是否有 "Fail to load font: Roboto" 错误');
console.log('如果修复成功，应该不会出现Roboto相关的错误');

// 测试5: 测试字体加载函数
console.log('\n测试5: 测试字体加载函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.insertTemplateFont) {
        console.log('insertTemplateFont函数存在');
        
        // 测试加载Arial字体
        console.log('测试加载Arial字体...');
        try {
            scope.insertTemplateFont('Arial', function(result) {
                console.log('Arial字体加载结果:', result);
            });
        } catch (error) {
            console.error('Arial字体加载错误:', error);
        }
        
        // 测试加载新增字体
        var newFonts = ['greatvibes', 'hindsiliguri', 'lustria'];
        newFonts.forEach(function(fontName) {
            console.log('测试加载' + fontName + '字体...');
            try {
                scope.insertTemplateFont(fontName, function(result) {
                    console.log(fontName + '字体加载结果:', result);
                });
            } catch (error) {
                console.error(fontName + '字体加载错误:', error);
            }
        });
    } else {
        console.log('⚠️ insertTemplateFont函数不存在');
    }
} else {
    console.log('❌ Angular不存在');
}

console.log('\n=== 添加文本测试完成 ==='); 