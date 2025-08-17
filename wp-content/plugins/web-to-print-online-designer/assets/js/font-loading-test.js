// 字体加载测试脚本
console.log('=== 字体加载测试开始 ===');

// 测试1: 检查NBDESIGNCONFIG配置
console.log('测试1: 检查NBDESIGNCONFIG配置');
if (typeof NBDESIGNCONFIG !== 'undefined') {
    console.log('NBDESIGNCONFIG存在');
    if (NBDESIGNCONFIG.default_font) {
        console.log('默认字体配置:', NBDESIGNCONFIG.default_font);
        console.log('默认字体别名:', NBDESIGNCONFIG.default_font.alias);
        console.log('默认字体类型:', NBDESIGNCONFIG.default_font.type);
    } else {
        console.log('⚠️ 默认字体配置不存在');
    }
} else {
    console.log('❌ NBDESIGNCONFIG不存在');
}

// 测试2: 测试字体加载函数
console.log('\n测试2: 测试字体加载函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.insertTemplateFont) {
        console.log('insertTemplateFont函数存在');
        
        // 测试加载默认字体
        var defaultFont = NBDESIGNCONFIG.default_font && NBDESIGNCONFIG.default_font.alias ? NBDESIGNCONFIG.default_font.alias : 'Arial';
        console.log('测试加载默认字体:', defaultFont);
        
        try {
            scope.insertTemplateFont(defaultFont, function(result) {
                console.log('默认字体加载结果:', result);
            });
        } catch (error) {
            console.error('默认字体加载错误:', error);
        }
        
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
            console.log('测试加载新增字体:', fontName);
            try {
                scope.insertTemplateFont(fontName, function(result) {
                    console.log('新增字体加载结果:', fontName, result);
                });
            } catch (error) {
                console.error('新增字体加载错误:', fontName, error);
            }
        });
    } else {
        console.log('⚠️ insertTemplateFont函数不存在');
    }
} else {
    console.log('❌ Angular不存在');
}

// 测试3: 测试文本添加函数
console.log('\n测试3: 测试文本添加函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.addText) {
        console.log('addText函数存在');
        
        // 测试添加文本
        try {
            scope.addText('测试文本', 'bodytext');
            console.log('✅ 文本添加成功');
        } catch (error) {
            console.error('文本添加错误:', error);
        }
    } else {
        console.log('⚠️ addText函数不存在');
    }
    
    if (scope && scope.addCurvedText) {
        console.log('addCurvedText函数存在');
        
        // 测试添加曲线文本
        try {
            scope.addCurvedText('测试曲线文本');
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

// 测试4: 检查字体资源数据
console.log('\n测试4: 检查字体资源数据');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.resource && scope.resource.font) {
        console.log('字体资源数据存在');
        console.log('字体数据数量:', scope.resource.font.data.length);
        
        // 检查默认字体
        var defaultFont = NBDESIGNCONFIG.default_font && NBDESIGNCONFIG.default_font.alias ? NBDESIGNCONFIG.default_font.alias : 'Arial';
        var defaultFontInData = scope.resource.font.data.filter(function(font) {
            return font.alias === defaultFont;
        });
        
        if (defaultFontInData.length > 0) {
            console.log('✅ 默认字体在资源数据中:', defaultFontInData[0]);
        } else {
            console.log('❌ 默认字体不在资源数据中:', defaultFont);
        }
        
        // 检查新增字体
        var newFonts = ['greatvibes', 'hindsiliguri', 'lustria'];
        newFonts.forEach(function(fontName) {
            var found = scope.resource.font.data.filter(function(font) {
                return font.alias === fontName;
            });
            if (found.length > 0) {
                console.log('✅ 新增字体在资源数据中:', fontName);
            } else {
                console.log('❌ 新增字体不在资源数据中:', fontName);
            }
        });
    } else {
        console.log('⚠️ 字体资源数据不存在');
    }
}

// 测试5: 检查CSS注入
console.log('\n测试5: 检查CSS注入');
var fontCSS = [];
jQuery('head style').each(function() {
    var cssText = this.textContent || this.innerText;
    if (cssText.indexOf('@font-face') !== -1) {
        fontCSS.push(cssText);
    }
});
console.log('找到的字体CSS数量:', fontCSS.length);
fontCSS.forEach(function(css, index) {
    console.log('CSS ' + (index + 1) + ':', css.substring(0, 200) + '...');
});

// 测试6: 检查FontFaceObserver
console.log('\n测试6: 检查FontFaceObserver');
if (typeof FontFaceObserver !== 'undefined') {
    console.log('✅ FontFaceObserver存在');
    
    // 测试FontFaceObserver加载Arial
    try {
        var arialFont = new FontFaceObserver('Arial');
        arialFont.load('Sample Text', 3000).then(function() {
            console.log('✅ FontFaceObserver成功加载Arial字体');
        }).catch(function(error) {
            console.log('❌ FontFaceObserver加载Arial字体失败:', error);
        });
    } catch (error) {
        console.error('FontFaceObserver测试错误:', error);
    }
} else {
    console.log('❌ FontFaceObserver不存在');
}

// 测试7: 检查错误日志
console.log('\n测试7: 检查错误日志');
console.log('请检查控制台是否有 "Fail to load font: Roboto" 错误');
console.log('如果修复成功，应该看到 "⚠️ 字体加载失败，继续使用:" 而不是 "Fail to load font:"');

console.log('\n=== 字体加载测试完成 ==='); 