// custom-console-log.js
(function() {
    console.log('[DUJIA] 自定义日志脚本初始化');
    
    const originalConsoleLog = console.log;
    console.log = function() {
      // 调用原始的 console.log，添加[DUJIA]前缀
      const args = Array.from(arguments);
      args.unshift('[DUJIA]');
      originalConsoleLog.apply(console, args);
      
      // 准备日志数据
      const logData = {
        level: 'info',
        message: '[DUJIA] ' + args.slice(1).join(' '),
        meta: {
          url: window.location.href,
          timestamp: new Date().toISOString(),
          userAgent: navigator.userAgent,
          platform: navigator.platform,
          language: navigator.language
        }
      };
      
      // 将日志保存到本地文件
      saveLogToFile(logData);
    };

    // 将日志保存到本地文件
    function saveLogToFile(logData) {
      const logMessage = JSON.stringify(logData) + '\n';
      
      // 使用fetch发送日志到本地文件
      fetch('/wp-content/plugins/web-to-print-online-designer/assets/js/save-log.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: logMessage
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('保存日志失败');
        }
      })
      .catch(error => {
        originalConsoleLog.call(console, '[DUJIA] 保存日志失败:', error);
      });
    }

    console.log('[DUJIA] 自定义日志脚本初始化完成');
})();