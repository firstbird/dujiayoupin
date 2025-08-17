// Roboto字体加载测试脚本
console.log('=== Roboto字体加载测试开始 ===');

// 测试1: 检查NBDESIGNCONFIG.default_font配置
console.log('测试1: 检查默认字体配置');
if (typeof NBDESIGNCONFIG !== 'undefined' && NBDESIGNCONFIG.default_font) {
    console.log('默认字体配置:', NBDESIGNCONFIG.default_font);
    console.log('默认字体别名:', NBDESIGNCONFIG.default_font.alias);
    console.log('默认字体类型:', NBDESIGNCONFIG.default_font.type);
} else {
    console.log('❌ 默认字体配置不存在');
}

// 测试2: 测试getFontInfo函数
console.log('\n测试2: 测试getFontInfo函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.getFontInfo) {
        console.log('getFontInfo函数存在');
        
        // 测试获取Roboto字体信息
        try {
            var robotoInfo = scope.getFontInfo('Roboto');
            console.log('Roboto字体信息:', robotoInfo);
        } catch (error) {
            console.error('获取Roboto字体信息失败:', error);
        }
        
        // 测试获取Arial字体信息
        try {
            var arialInfo = scope.getFontInfo('Arial');
            console.log('Arial字体信息:', arialInfo);
        } catch (error) {
            console.error('获取Arial字体信息失败:', error);
        }
    } else {
        console.log('⚠️ getFontInfo函数不存在');
    }
} else {
    console.log('❌ Angular不存在');
}

// 测试3: 测试字体加载函数
console.log('\n测试3: 测试字体加载函数');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.insertTemplateFont) {
        console.log('insertTemplateFont函数存在');
        
        // 测试加载Roboto字体
        console.log('测试加载Roboto字体...');
        try {
            scope.insertTemplateFont('Roboto', function(result) {
                console.log('Roboto字体加载结果:', result);
            });
        } catch (error) {
            console.error('Roboto字体加载错误:', error);
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
    } else {
        console.log('⚠️ insertTemplateFont函数不存在');
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
        
        // 查找Roboto字体
        var robotoFont = scope.resource.font.data.filter(function(font) {
            return font.alias === 'Roboto';
        });
        
        if (robotoFont.length > 0) {
            console.log('✅ Roboto字体在资源数据中:', robotoFont[0]);
        } else {
            console.log('❌ Roboto字体不在资源数据中');
        }
        
        // 查找Arial字体
        var arialFont = scope.resource.font.data.filter(function(font) {
            return font.alias === 'Arial';
        });
        
        if (arialFont.length > 0) {
            console.log('✅ Arial字体在资源数据中:', arialFont[0]);
        } else {
            console.log('❌ Arial字体不在资源数据中');
        }
    } else {
        console.log('⚠️ 字体资源数据不存在');
    }
}

// 测试5: 检查Google字体CSS
console.log('\n测试5: 检查Google字体CSS');
var googleFontCSS = [];
jQuery('head link[href*="fonts.googleapis.com"]').each(function() {
    googleFontCSS.push(this.href);
});
console.log('找到的Google字体CSS数量:', googleFontCSS.length);
googleFontCSS.forEach(function(css, index) {
    console.log('Google字体CSS ' + (index + 1) + ':', css);
});

// 测试6: 检查FontFaceObserver
console.log('\n测试6: 检查FontFaceObserver');
if (typeof FontFaceObserver !== 'undefined') {
    console.log('✅ FontFaceObserver存在');
    
    // 测试FontFaceObserver加载Roboto
    try {
        var robotoFont = new FontFaceObserver('Roboto');
        robotoFont.load('Sample Text', 3000).then(function() {
            console.log('✅ FontFaceObserver成功加载Roboto字体');
        }).catch(function(error) {
            console.log('❌ FontFaceObserver加载Roboto字体失败:', error);
        });
    } catch (error) {
        console.error('FontFaceObserver测试错误:', error);
    }
} else {
    console.log('❌ FontFaceObserver不存在');
}

// 测试7: 检查页面中的字体CSS
console.log('\n测试7: 检查页面中的字体CSS');
var allFontCSS = [];
jQuery('head style').each(function() {
    var cssText = this.textContent || this.innerText;
    if (cssText.indexOf('@font-face') !== -1) {
        allFontCSS.push(cssText);
    }
});
console.log('找到的字体CSS数量:', allFontCSS.length);
allFontCSS.forEach(function(css, index) {
    if (css.indexOf('Roboto') !== -1) {
        console.log('包含Roboto的CSS ' + (index + 1) + ':', css.substring(0, 300) + '...');
    }
});

console.log('\n=== Roboto字体加载测试完成 ==='); 