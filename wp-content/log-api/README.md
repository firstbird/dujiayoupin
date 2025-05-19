# WordPress 日志 API 服务

这是一个用于处理WordPress日志信息的Node.js API服务。

## 功能特点

- 接收和存储日志信息
- 支持不同级别的日志记录
- RESTful API接口
- 日志文件自动轮转

## 安装

```bash
npm install
```

## 运行

开发环境：
```bash
npm run dev
```

生产环境：
```bash
npm start
```

## API 接口

### 记录日志
POST /api/logs

请求体示例：
```json
{
  "level": "info",
  "message": "用户登录成功",
  "meta": {
    "userId": 123,
    "ip": "192.168.1.1"
  }
}
```

### 获取日志
GET /api/logs?level=info&limit=100

## 环境变量

- PORT: 服务端口号（默认：3000）
- NODE_ENV: 运行环境（development/production） 