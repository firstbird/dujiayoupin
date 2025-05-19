const express = require('express');
const cors = require('cors');
const winston = require('winston');
const path = require('path');

const app = express();
const port = process.env.PORT || 3000;

// 配置日志记录器
const logger = winston.createLogger({
  level: 'info',
  format: winston.format.combine(
    winston.format.timestamp(),
    winston.format.json()
  ),
  transports: [
    new winston.transports.File({ 
      filename: path.join(__dirname, '../logs/error.log'), 
      level: 'error' 
    }),
    new winston.transports.File({ 
      filename: path.join(__dirname, '../logs/combined.log') 
    })
  ]
});

// 开发环境下同时输出到控制台
if (process.env.NODE_ENV !== 'production') {
  logger.add(new winston.transports.Console({
    format: winston.format.simple()
  }));
}

// 中间件
app.use(cors());
app.use(express.json());

// API路由
app.post('/api/logs', (req, res) => {
  const { level, message, meta } = req.body;
  
  if (!level || !message) {
    return res.status(400).json({ error: '缺少必要参数' });
  }

  logger.log(level, message, meta);
  res.status(200).json({ success: true });
});

// 获取日志接口
app.get('/api/logs', (req, res) => {
  const { level, limit = 100 } = req.query;
  // 这里可以添加获取日志的逻辑
  res.status(200).json({ message: '获取日志功能待实现' });
});

// 错误处理中间件
app.use((err, req, res, next) => {
  logger.error('服务器错误', { error: err.message });
  res.status(500).json({ error: '服务器内部错误' });
});

app.listen(port, () => {
  console.log(`日志API服务运行在端口 ${port}`);
}); 