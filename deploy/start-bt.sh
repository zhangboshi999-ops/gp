#!/usr/bin/env bash
# ==========================================================
# 宝塔环境实时行情一键启停（Redis → WebSocket → tick）
# 与根目录 start-realtime.sh 的区别：
#   - Redis 用宝塔软件商店安装的系统服务（systemctl / service），不用自带的 bin/redis
#   - Node / PHP 走宝塔的实际安装路径，可用环境变量覆盖
#
#   ./start-bt.sh start | stop | status | restart
# ==========================================================
set -u

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_BIN="${PHP_BIN:-/www/server/php/81/bin/php}"
NODE_BIN="${NODE_BIN:-$(command -v node)}"
WS_PORT="${WS_PORT:-9502}"
LOG_DIR="$ROOT/runtime"
mkdir -p "$LOG_DIR"

REDIS_CLI="$(command -v redis-cli || echo /www/server/redis/redis-cli)"

start_redis() {
  if "$REDIS_CLI" -p 6379 ping >/dev/null 2>&1; then
    echo "[redis ] running"
  elif command -v systemctl >/dev/null 2>&1; then
    systemctl start redis >/dev/null 2>&1 && echo "[redis ] started(systemd)" || echo "[redis ] start failed，请在宝塔面板启动 Redis"
  else
    echo "[redis ] 请用宝塔面板「软件商店 → Redis → 启动」"
  fi
}

stop_redis() {
  # 宝塔的 Redis 常被其他站点共用，默认不停，避免误伤
  echo "[redis ] skipped（宝塔面板中手动停止，避免影响其他站点）"
}

is_port_up() { curl -s "http://127.0.0.1:$1/health" >/dev/null 2>&1; }

start() {
  echo "[redis ] 检查..."
  start_redis

  if is_port_up "$WS_PORT"; then
    echo "[socket] already running"
  else
    (cd "$ROOT/socket" && nohup "$NODE_BIN" server.js >> "$LOG_DIR/socket.log" 2>&1 &)
    sleep 1
    echo "[socket] started ws://127.0.0.1:$WS_PORT/ws"
  fi

  if pgrep -f "think quote:tick" >/dev/null 2>&1; then
    echo "[tick  ] already running"
  else
    (cd "$ROOT/fastadmin" && nohup "$PHP_BIN" think quote:tick >> "$LOG_DIR/tick.log" 2>&1 &)
    echo "[tick  ] started"
  fi
}

stop() {
  pkill -f "server.js" 2>/dev/null && echo "[socket] stopped"
  pkill -f "think quote:tick" 2>/dev/null && echo "[tick  ] stopped"
  stop_redis
}

status() {
  "$REDIS_CLI" -p 6379 ping >/dev/null 2>&1 && echo "[redis ] running" || echo "[redis ] stopped"
  is_port_up "$WS_PORT" && echo "[socket] running $(curl -s "http://127.0.0.1:$WS_PORT/health")" || echo "[socket] stopped"
  pgrep -f "think quote:tick" >/dev/null 2>&1 && echo "[tick  ] running pid=$(pgrep -f 'think quote:tick' | tr '\n' ' ')" || echo "[tick  ] stopped"
}

case "${1:-start}" in
  start)   start ;;
  stop)    stop ;;
  restart) stop; sleep 1; start ;;
  status)  status ;;
  *)       echo "用法: $0 start|stop|restart|status" ;;
esac
