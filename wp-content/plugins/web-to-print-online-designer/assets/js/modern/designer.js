$scope.recentImages = nbdesigner.recentImages || [];

// 添加日志记录函数
$scope.logToServer = function(message, data) {
    // if (!nbdesigner.debug) {
    //     console.log('调试模式未启用');
    //     return;
    // }
    
    // console.log('发送日志:', message, data);
    
    // if (!nbdesigner.ajax_url) {
    //     console.error('ajax_url未定义');
    //     return;
    // }
    
    // if (!nbdesigner.nonce) {
    //     console.error('nonce未定义');
    //     return;
    // }
    
    // $http.post(nbdesigner.ajax_url, {
    //     action: 'nbdesigner_log_frontend',
    //     message: message,
    //     data: data,
    //     nonce: nbdesigner.nonce
    // }).then(function(response) {
    //     console.log('日志发送成功:', response);
    //     if (!response.data.success) {
    //         console.error('日志发送失败:', response.data);
    //     }
    // }).catch(function(error) {
    //     console.error('日志发送失败:', error);
    //     // 尝试使用备用方法记录错误
    //     if (window.localStorage) {
    //         try {
    //             var logs = JSON.parse(localStorage.getItem('nbdesigner_logs') || '[]');
    //             logs.push({
    //                 time: new Date().toISOString(),
    //                 message: message,
    //                 data: data,
    //                 error: error.toString()
    //             });
    //             localStorage.setItem('nbdesigner_logs', JSON.stringify(logs.slice(-100))); // 只保留最近100条
    //         } catch (e) {
    //             console.error('保存日志到localStorage失败:', e);
    //         }
    //     }
    // });
};

// 添加全局错误处理
window.onerror = function(message, source, lineno, colno, error) {
    console.error('JavaScript错误:', message, 'at', source, lineno, colno);
    $scope.logToServer('JavaScript错误', {
        message: message,
        source: source,
        lineno: lineno,
        colno: colno,
        error: error ? error.toString() : '未知错误'
    });
    return false;
};

// 添加未捕获的Promise错误处理
window.addEventListener('unhandledrejection', function(event) {
    console.error('未处理的Promise错误:', event.reason);
    $scope.logToServer('未处理的Promise错误', {
        reason: event.reason ? event.reason.toString() : '未知错误'
    });
});

// 初始化时记录一条日志
$scope.logToServer('页面初始化', {
    url: window.location.href,
    userAgent: navigator.userAgent
});

$scope.setBackgroundUrl = function(url) {
    if(!url) {
        $scope.logToServer('设置背景图片失败', {error: 'URL为空'});
        return;
    }
    
    $scope.logToServer('开始设置背景图片', {url: url});
    
    var img = new Image();
    img.onload = function() {
        try {
            var width = img.width;
            var height = img.height;
            var stage = $scope.stage;
            var stageWidth = stage.width();
            var stageHeight = stage.height();
            var scale = Math.max(stageWidth / width, stageHeight / height);
            var newWidth = width * scale;
            var newHeight = height * scale;
            var x = (stageWidth - newWidth) / 2;
            var y = (stageHeight - newHeight) / 2;
            
            $scope.logToServer('图片加载成功', {
                originalSize: {width: width, height: height},
                newSize: {width: newWidth, height: newHeight},
                position: {x: x, y: y}
            });
            
            var background = new fabric.Image(img, {
                left: x,
                top: y,
                width: newWidth,
                height: newHeight,
                selectable: false,
                evented: false
            });
            stage.setBackgroundImage(background, stage.renderAll.bind(stage));
            
            // 保存到最近图片
            $http.post(nbdesigner.ajax_url, {
                action: 'nbdesigner_save_recent_image',
                image_url: url,
                nonce: nbdesigner.nonce
            }).then(function(response) {
                if(response.data.success) {
                    $scope.logToServer('保存最近图片成功', {url: url});
                    // 更新最近图片列表
                    var index = $scope.recentImages.findIndex(function(img) {
                        return img.url === url;
                    });
                    if(index > -1) {
                        $scope.recentImages.splice(index, 1);
                    }
                    $scope.recentImages.unshift({url: url});
                    if($scope.recentImages.length > 9) {
                        $scope.recentImages.pop();
                    }
                    $scope.$apply();
                } else {
                    $scope.logToServer('保存最近图片失败', {url: url, response: response.data});
                }
            }).catch(function(error) {
                $scope.logToServer('保存最近图片出错', {url: url, error: error});
            });
        } catch (error) {
            $scope.logToServer('设置背景图片时发生错误', {error: error.toString()});
        }
    };
    img.onerror = function(error) {
        $scope.logToServer('加载图片失败', {url: url, error: error});
    };
    img.src = url;
}; 