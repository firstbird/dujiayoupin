// 本地字体测试脚本
(function() {
    'use strict';
    
    // 等待页面加载完成
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFontTest);
    } else {
        initFontTest();
    }
    
    function initFontTest() {
        console.log('=== 本地字体测试开始 ===');
        
        // 测试字体列表
        var testFonts = [
            'greatvibes',
            'hindsiliguri', 
            'lustria'
        ];
        
        // 创建测试元素
        var testContainer = document.createElement('div');
        testContainer.id = 'font-test-container';
        testContainer.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            background: white;
            border: 2px solid #333;
            padding: 15px;
            border-radius: 8px;
            z-index: 9999;
            max-width: 300px;
            font-family: Arial, sans-serif;
            font-size: 12px;
        `;
        
        var title = document.createElement('h3');
        title.textContent = '本地字体测试';
        title.style.margin = '0 0 10px 0';
        testContainer.appendChild(title);
        
        // 为每个字体创建测试元素
        testFonts.forEach(function(fontName) {
            var testDiv = document.createElement('div');
            testDiv.style.cssText = `
                margin: 5px 0;
                padding: 5px;
                border: 1px solid #ccc;
                border-radius: 3px;
            `;
            
            var fontLabel = document.createElement('div');
            fontLabel.textContent = '字体: ' + fontName;
            fontLabel.style.fontWeight = 'bold';
            fontLabel.style.marginBottom = '3px';
            testDiv.appendChild(fontLabel);
            
            var fontText = document.createElement('div');
            fontText.textContent = '测试文本 - Test Text - 123456';
            fontText.style.fontFamily = fontName + ', Arial, sans-serif';
            fontText.style.fontSize = '16px';
            fontText.style.color = '#333';
            testDiv.appendChild(fontText);
            
            // 检查字体是否加载
            var statusDiv = document.createElement('div');
            statusDiv.style.fontSize = '10px';
            statusDiv.style.marginTop = '3px';
            
            // 使用FontFaceObserver检查字体加载状态
            if (typeof FontFaceObserver !== 'undefined') {
                var font = new FontFaceObserver(fontName);
                font.load('测试文本', 3000).then(function() {
                    statusDiv.textContent = '✅ 字体加载成功';
                    statusDiv.style.color = 'green';
                    fontText.style.color = '#006600';
                }).catch(function(error) {
                    statusDiv.textContent = '❌ 字体加载失败: ' + error.name;
                    statusDiv.style.color = 'red';
                    fontText.style.color = '#cc0000';
                });
            } else {
                statusDiv.textContent = '⚠️ FontFaceObserver不可用';
                statusDiv.style.color = 'orange';
            }
            
            testDiv.appendChild(statusDiv);
            testContainer.appendChild(testDiv);
        });
        
        // 添加关闭按钮
        var closeBtn = document.createElement('button');
        closeBtn.textContent = '关闭';
        closeBtn.style.cssText = `
            margin-top: 10px;
            padding: 5px 10px;
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 3px;
            cursor: pointer;
        `;
        closeBtn.onclick = function() {
            document.body.removeChild(testContainer);
        };
        testContainer.appendChild(closeBtn);
        
        // 添加到页面
        document.body.appendChild(testContainer);
        
        // 检查CSS是否已注入
        console.log('检查字体CSS状态:');
        testFonts.forEach(function(fontName) {
            var fontId = fontName.replace(/\s/gi, '').toLowerCase();
            var cssElement = document.getElementById(fontId);
            if (cssElement) {
                console.log('✅ CSS已注入:', fontName);
            } else {
                console.log('❌ CSS未注入:', fontName);
            }
        });
        
        // 检查字体文件是否存在
        console.log('检查字体文件状态:');
        testFonts.forEach(function(fontName) {
            var fontUrl = '/wp-content/plugins/web-to-print-online-designer/assets/fonts/' + fontName + '.ttf';
            var img = new Image();
            img.onload = function() {
                console.log('✅ 字体文件存在:', fontName);
            };
            img.onerror = function() {
                console.log('❌ 字体文件不存在:', fontName);
            };
            img.src = fontUrl;
        });
        
        console.log('=== 本地字体测试完成 ===');
    }
    
    // 如果存在Angular scope，也进行测试
    if (typeof angular !== 'undefined') {
        angular.element(document).ready(function() {
            var scope = angular.element(document.body).scope();
            if (scope && scope.insertTemplateFont) {
                console.log('Angular scope可用，测试insertTemplateFont函数');
                
                // 测试字体加载
                ['greatvibes', 'hindsiliguri', 'lustria'].forEach(function(fontName) {
                    scope.insertTemplateFont(fontName, function(result) {
                        console.log('字体加载结果:', fontName, result);
                    });
                });
            }
        });
    }
})(); 