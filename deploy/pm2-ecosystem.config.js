/**
 * PM2 进程编排（宝塔 → 软件商店 → PM2管理器 / Node版本管理器）
 *
 * 用法：
 *   cd /www/wwwroot/stock
 *   pm2 start deploy/pm2-ecosystem.config.js
 *   pm2 save && pm2 startup      # 开机自启
 */
const path = require('path')

const ROOT = path.resolve(__dirname, '..')
const PHP = process.env.PHP_BIN || '/www/server/php/81/bin/php'

module.exports = {
  apps: [
    {
      name: 'stock-socket',
      cwd: path.join(ROOT, 'socket'),
      script: 'server.js',
      interpreter: 'node',
      watch: false,
      env: {
        WS_PORT: 9502,
        REDIS_URL: 'redis://127.0.0.1:6379'
      },
      out_file: path.join(ROOT, 'runtime/socket.log'),
      error_file: path.join(ROOT, 'runtime/socket.log'),
      merge_logs: true,
      autorestart: true,
      max_memory_restart: '300M'
    },
    {
      name: 'stock-tick',
      cwd: path.join(ROOT, 'fastadmin'),
      script: 'think',
      args: 'quote:tick',
      interpreter: PHP,
      watch: false,
      out_file: path.join(ROOT, 'runtime/tick.log'),
      error_file: path.join(ROOT, 'runtime/tick.log'),
      merge_logs: true,
      autorestart: true,
      max_memory_restart: '300M'
    }
  ]
}
