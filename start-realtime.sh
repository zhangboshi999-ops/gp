#!/usr/bin/env bash
# 实时行情链路一键启停：Redis → WebSocket 服务 → PHP tick 推送
#   ./start-realtime.sh          启动全部
#   ./start-realtime.sh stop     停止全部
#   ./start-realtime.sh status   查看状态
set -u

ROOT="$(cd "$(dirname "$0")" && pwd)"
REDIS_CLI="$ROOT/bin/redis/redis-cli"
REDIS_SERVER="$ROOT/bin/redis/redis-server"
LOG_DIR="$ROOT/runtime"
WS_PORT=9502

mkdir -p "$LOG_DIR/redis"

status() {
  if "$REDIS_CLI" -p 6379 ping >/dev/null 2>&1; then
    echo "[redis ] running  $( "$REDIS_CLI" -p 6379 ping )"
  else
    echo "[redis ] stopped"
  fi
  if curl -s "http://127.0.0.1:$WS_PORT/health" >/dev/null 2>&1; then
    echo "[socket] running  $(curl -s "http://127.0.0.1:$WS_PORT/health")"
  else
    echo "[socket] stopped"
  fi
  if pgrep -f "think quote:tick" >/dev/null 2>&1; then
    echo "[tick  ] running  pid=$(pgrep -f 'think quote:tick' | tr '\n' ' ')"
  else
    echo "[tick  ] stopped"
  fi
}

start() {
  if "$REDIS_CLI" -p 6379 ping >/dev/null 2>&1; then
    echo "[redis ] already running"
  else
    "$REDIS_SERVER" --port 6379 --daemonize yes \
      --dir "$LOG_DIR/redis" --logfile "$LOG_DIR/redis/redis.log" \
      --save "" --appendonly no
    sleep 1
    echo "[redis ] started"
  fi

  if curl -s "http://127.0.0.1:$WS_PORT/health" >/dev/null 2>&1; then
    echo "[socket] already running"
  else
    (cd "$ROOT/socket" && nohup node server.js >> "$LOG_DIR/socket.log" 2>&1 &)
    sleep 1
    echo "[socket] started ws://127.0.0.1:$WS_PORT/ws"
  fi

  if pgrep -f "think quote:tick" >/dev/null 2>&1; then
    echo "[tick  ] already running"
  else
    (cd "$ROOT/fastadmin" && nohup php think quote:tick >> "$LOG_DIR/tick.log" 2>&1 &)
    echo "[tick  ] started"
  fi
}

stop() {
  pkill -f "think quote:tick" 2>/dev/null && echo "[tick  ] stopped"
  pkill -f "socket/server.js" 2>/dev/null && echo "[socket] stopped"
  if "$REDIS_CLI" -p 6379 ping >/dev/null 2>&1; then
    "$REDIS_CLI" -p 6379 shutdown nosave >/dev/null 2>&1 && echo "[redis ] stopped"
  fi
}

case "${1:-start}" in
  start) start ;;
  stop) stop ;;
  restart) stop; sleep 1; start ;;
  status) status ;;
  *) echo "用法: $0 [start|stop|restart|status]"; exit 1 ;;
esac
