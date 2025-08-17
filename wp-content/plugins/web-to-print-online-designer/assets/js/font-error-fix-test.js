// 字体错误修复测试脚本
console.log('=== 字体错误修复测试开始 ===');

// 测试1: 检查NBDESIGNCONFIG.default_font是否存在
console.log('测试1: 检查NBDESIGNCONFIG.default_font');
if (typeof NBDESIGNCONFIG !== 'undefined') {
    console.log('NBDESIGNCONFIG存在');
    if (NBDESIGNCONFIG.default_font) {
        console.log('NBDESIGNCONFIG.default_font存在');
        console.log('default_font内容:', NBDESIGNCONFIG.default_font);
        
        if (NBDESIGNCONFIG.default_font.file) {
            console.log('default_font.file存在');
            console.log('file内容:', NBDESIGNCONFIG.default_font.file);
        } else {
            console.log('⚠️ default_font.file不存在');
        }
    } else {
        console.log('⚠️ NBDESIGNCONFIG.default_font不存在');
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
        
        // 测试新增字体
        var testFonts = ['greatvibes', 'hindsiliguri', 'lustria'];
        testFonts.forEach(function(fontName) {
            console.log('测试字体:', fontName);
            try {
                scope.insertTemplateFont(fontName, function(result) {
                    console.log('字体加载结果:', fontName, result);
                });
            } catch (error) {
                console.error('字体加载错误:', fontName, error);
            }
        });
    } else {
        console.log('⚠️ insertTemplateFont函数不存在');
    }
} else {
    console.log('❌ Angular不存在');
}

// 测试3: 检查字体资源数据
console.log('\n测试3: 检查字体资源数据');
if (typeof angular !== 'undefined') {
    var scope = angular.element(document.getElementById("designer-controller")).scope();
    if (scope && scope.resource && scope.resource.font) {
        console.log('字体资源数据存在');
        console.log('字体数据数量:', scope.resource.font.data.length);
        
        // 检查新增字体是否在资源数据中
        var newFonts = ['greatvibes', 'hindsiliguri', 'lustria'];
        newFonts.forEach(function(fontName) {
            var found = scope.resource.font.data.filter(function(font) {
                return font.alias === fontName;
            });
            if (found.length > 0) {
                console.log('✅ 字体在资源数据中:', fontName);
            } else {
                console.log('❌ 字体不在资源数据中:', fontName);
            }
        });
    } else {
        console.log('⚠️ 字体资源数据不存在');
    }
}

// 测试4: 检查CSS注入
console.log('\n测试4: 检查CSS注入');
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

// 测试5: 检查字体文件是否存在
console.log('\n测试5: 检查字体文件');
var fontFiles = [
    '/wp-content/plugins/web-to-print-online-designer/assets/fonts/greatvibes.ttf',
    '/wp-content/plugins/web-to-print-online-designer/assets/fonts/hindsiliguri.ttf',
    '/wp-content/plugins/web-to-print-online-designer/assets/fonts/lustria.ttf'
];

fontFiles.forEach(function(fontFile) {
    fetch(fontFile, {method: 'HEAD'})
        .then(function(response) {
            if (response.ok) {
                console.log('✅ 字体文件存在:', fontFile);
            } else {
                console.log('❌ 字体文件不存在:', fontFile, response.status);
            }
        })
        .catch(function(error) {
            console.log('❌ 字体文件检查失败:', fontFile, error);
        });
});

console.log('\n=== 字体错误修复测试完成 ==='); 