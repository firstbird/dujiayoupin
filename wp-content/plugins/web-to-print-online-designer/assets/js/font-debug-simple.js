// 简化的字体调试脚本
(function() {
    'use strict';
    
    console.log('=== 字体调试脚本开始 ===');
    
    // 测试字体列表
    var testFonts = ['greatvibes', 'hindsiliguri', 'lustria'];
    
    // 1. 检查字体文件是否存在
    console.log('1. 检查字体文件:');
    testFonts.forEach(function(fontName) {
        var fontUrl = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fontName + '.ttf';
        
        // 使用fetch检查文件是否存在
        fetch(fontUrl, {method: 'HEAD'})
            .then(function(response) {
                if (response.ok) {
                    console.log('✅ 字体文件存在:', fontName);
                } else {
                    console.log('❌ 字体文件不存在:', fontName, response.status);
                }
            })
            .catch(function(error) {
                console.log('❌ 字体文件检查失败:', fontName, error.message);
            });
    });
    
    // 2. 检查CSS是否已注入
    console.log('2. 检查CSS注入:');
    testFonts.forEach(function(fontName) {
        var fontId = fontName.replace(/\s/gi, '').toLowerCase();
        var cssElement = document.getElementById(fontId);
        if (cssElement) {
            console.log('✅ CSS已注入:', fontName);
            console.log('CSS内容:', cssElement.textContent);
        } else {
            console.log('❌ CSS未注入:', fontName);
        }
    });
    
    // 3. 手动注入CSS并测试
    console.log('3. 手动注入CSS:');
    testFonts.forEach(function(fontName) {
        var fontId = fontName.replace(/\s/gi, '').toLowerCase();
        var existingCSS = document.getElementById(fontId);
        
        if (!existingCSS) {
            var css = "<style type='text/css' id='" + fontId + "'>";
            css += "@font-face {";
            css += "font-family: '" + fontName + "';";
            css += "src: url('/wp-content/plugins/web-to-print-online-designer/assets/fonts/" + fontName + ".ttf') format('truetype');";
            css += "font-weight: normal;";
            css += "font-style: normal;";
            css += "font-display: swap;";
            css += "}";
            css += "</style>";
            
            document.head.insertAdjacentHTML('beforeend', css);
            console.log('✅ 手动注入CSS:', fontName);
        }
    });
    
    // 4. 创建测试元素
    console.log('4. 创建测试元素:');
    var testContainer = document.createElement('div');
    testContainer.id = 'font-debug-test';
    testContainer.style.cssText = `
        position: fixed;
        top: 50px;
        left: 50px;
        background: white;
        border: 2px solid #333;
        padding: 20px;
        border-radius: 8px;
        z-index: 10000;
        font-family: Arial, sans-serif;
        font-size: 14px;
        max-width: 400px;
    `;
    
    var title = document.createElement('h3');
    title.textContent = '字体调试测试';
    title.style.margin = '0 0 15px 0';
    testContainer.appendChild(title);
    
    testFonts.forEach(function(fontName) {
        var testDiv = document.createElement('div');
        testDiv.style.cssText = `
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        `;
        
        var label = document.createElement('div');
        label.textContent = '字体: ' + fontName;
        label.style.fontWeight = 'bold';
        label.style.marginBottom = '5px';
        testDiv.appendChild(label);
        
        var text = document.createElement('div');
        text.textContent = '测试文本 - Test Text - 123456';
        text.style.fontFamily = fontName + ', Arial, sans-serif';
        text.style.fontSize = '18px';
        text.style.color = '#333';
        testDiv.appendChild(text);
        
        // 添加状态指示器
        var status = document.createElement('div');
        status.style.fontSize = '12px';
        status.style.marginTop = '5px';
        status.textContent = '状态: 检查中...';
        status.style.color = 'orange';
        testDiv.appendChild(status);
        
        testContainer.appendChild(testDiv);
        
        // 测试字体加载
        if (typeof FontFaceObserver !== 'undefined') {
            var font = new FontFaceObserver(fontName);
            font.load('测试文本', 2000).then(function() {
                status.textContent = '状态: ✅ 加载成功';
                status.style.color = 'green';
                text.style.color = '#006600';
                console.log('✅ 字体加载成功:', fontName);
            }).catch(function(error) {
                status.textContent = '状态: ❌ 加载失败 - ' + error.name;
                status.style.color = 'red';
                text.style.color = '#cc0000';
                console.log('❌ 字体加载失败:', fontName, error);
            });
        } else {
            status.textContent = '状态: ⚠️ FontFaceObserver不可用';
            status.style.color = 'orange';
        }
    });
    
    // 添加关闭按钮
    var closeBtn = document.createElement('button');
    closeBtn.textContent = '关闭调试窗口';
    closeBtn.style.cssText = `
        margin-top: 15px;
        padding: 8px 16px;
        background: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
    `;
    closeBtn.onclick = function() {
        document.body.removeChild(testContainer);
    };
    testContainer.appendChild(closeBtn);
    
    document.body.appendChild(testContainer);
    
    // 5. 检查Angular scope
    console.log('5. 检查Angular scope:');
    if (typeof angular !== 'undefined') {
        var scope = angular.element(document.body).scope();
        if (scope) {
            console.log('✅ Angular scope可用');
            console.log('字体资源数据:', scope.resource && scope.resource.font ? scope.resource.font.data.length : '不可用');
            
            // 检查新增字体是否在资源数据中
            testFonts.forEach(function(fontName) {
                if (scope.resource && scope.resource.font && scope.resource.font.data) {
                    var existingFont = scope.resource.font.data.filter(function(font) {
                        return font.alias === fontName;
                    })[0];
                    
                    if (existingFont) {
                        console.log('✅ 字体在资源数据中:', fontName);
                    } else {
                        console.log('❌ 字体不在资源数据中:', fontName);
                    }
                }
            });
        } else {
            console.log('❌ Angular scope不可用');
        }
    } else {
        console.log('❌ Angular不可用');
    }
    
    console.log('=== 字体调试脚本完成 ===');
    
})(); 