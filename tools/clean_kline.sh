#!/usr/bin/env bash
# 清理 K 线历史数据，避免 fa_stock_kline 无限增长
#
# 为什么需要：
#   1d 日线由 `php think sync kline` 整体覆盖（先删后写），每只恒定 90 根，不会涨；
#   1m 分钟线由 `quote:tick` 实时进程每分钟写一行，只增不减 —— 本脚本清理的就是它。
#
# 用法：
#   ./tools/clean_kline.sh                 # 默认：删除 30 天前的 1m 分钟线
#   ./tools/clean_kline.sh -d 7            # 只保留最近 7 天
#   ./tools/clean_kline.sh -d 90 -p 5m     # 清理指定周期
#   ./tools/clean_kline.sh --dry           # 只打印将要删除多少行，不真删
#
# 优先用 mysql 客户端；找不到则自动用 PHP 的 mysqli 执行（服务器一定有 PHP）
# 数据库信息自动从 fastadmin/.env 读取
#
# 建议挂定时任务：每天 04:00 执行一次，保留 30 天
#   0 4 * * * cd /path/to/project && ./tools/clean_kline.sh -d 30 >> runtime/clean_kline.log 2>&1

set -uo pipefail

DAYS=30
PERIOD=1m
DRY=0
LOG_DIR="runtime"

while [[ $# -gt 0 ]]; do
  case "$1" in
    -d|--days)   DAYS="${2:?-d 需要天数}"; shift 2 ;;
    -p|--period) PERIOD="${2:?-p 需要周期}"; shift 2 ;;
    --dry)       DRY=1; shift ;;
    -h|--help)   sed -n '2,26p' "$0"; exit 0 ;;
    *) echo "未知参数: $1（用 -h 查看用法）"; exit 1 ;;
  esac
done

# ---- 定位 mysql / php ----
MYSQL="${MYSQL_BIN:-}"
for c in "${MYSQL_BIN:-}" mysql /www/server/mysql/bin/mysql \
         /Applications/Herd.app/Contents/Resources/bin/mysql/bin/mysql /usr/local/mysql/bin/mysql; do
  [[ -n "$c" ]] && command -v "$c" >/dev/null 2>&1 && MYSQL="$c" && break
done

PHP="${PHP_BIN:-}"
for c in "${PHP_BIN:-}" php /www/server/php/81/bin/php /www/server/php/74/bin/php \
         /opt/homebrew/bin/php /usr/local/bin/php; do
  [[ -n "$c" ]] && command -v "$c" >/dev/null 2>&1 && PHP="$c" && break
done

if [[ -z "$MYSQL" && -z "$PHP" ]]; then
  echo "找不到 mysql 客户端也没有 php，无法执行"
  exit 1
fi

# ---- 数据库配置（默认从 .env 读）----
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-stock}"

ENV_FILE="fastadmin/.env"
if [[ -f "$ENV_FILE" ]]; then
  while IFS='=' read -r k v; do
    k="$(echo "$k" | tr -d '[:space:]')"
    v="$(echo "$v" | sed -e 's/^[[:space:]]*//' -e 's/[[:space:]]*$//' -e 's/^"//' -e 's/"$//')"
    [[ -z "$k" || "$k" == \#* ]] && continue
    case "$(echo "$k" | tr 'A-Z' 'a-z')" in
      hostname) DB_HOST="$v" ;;
      database) DB_NAME="$v" ;;
      username) DB_USER="$v" ;;
      password) DB_PASS="$v" ;;
      hostport) DB_PORT="$v" ;;
    esac
  done < "$ENV_FILE"
fi

# 执行 SQL 并回显单值
db_scalar() {
  local sql="$1"
  if [[ -n "$MYSQL" ]]; then
    MYSQL_PWD="$DB_PASS" "$MYSQL" -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} \
      -D "$DB_NAME" -N -B -e "$sql" 2>/dev/null
  else
    DB_HOST="$DB_HOST" DB_PORT="$DB_PORT" DB_USER="$DB_USER" DB_PASS="$DB_PASS" DB_NAME="$DB_NAME" SQL="$sql" \
    "$PHP" -r '
      try {
        $m = @new mysqli(getenv("DB_HOST"), getenv("DB_USER"), getenv("DB_PASS"), getenv("DB_NAME"), (int)getenv("DB_PORT"));
        if ($m->connect_error) { echo "0"; exit(0); }
        $r = $m->query(getenv("SQL"));
        echo ($r && ($row = $r->fetch_row())) ? $row[0] : "0";
      } catch (Throwable $e) { echo "0"; }
    ' 2>/dev/null
  fi
}

# 执行 SQL（不关心返回值）
db_exec() {
  local sql="$1"
  if [[ -n "$MYSQL" ]]; then
    MYSQL_PWD="$DB_PASS" "$MYSQL" -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} \
      -D "$DB_NAME" -e "$sql" >/dev/null 2>&1
  else
    DB_HOST="$DB_HOST" DB_PORT="$DB_PORT" DB_USER="$DB_USER" DB_PASS="$DB_PASS" DB_NAME="$DB_NAME" SQL="$sql" \
    "$PHP" -r '
      try {
        $m = @new mysqli(getenv("DB_HOST"), getenv("DB_USER"), getenv("DB_PASS"), getenv("DB_NAME"), (int)getenv("DB_PORT"));
        if (!$m->connect_error) { $m->query(getenv("SQL")); }
      } catch (Throwable $e) {}
    ' >/dev/null 2>&1
  fi
}

CUTOFF=$(($(date +%s) - DAYS * 86400))
mkdir -p "$LOG_DIR"

COUNT=$(db_scalar "SELECT COUNT(*) FROM fa_stock_kline WHERE period='${PERIOD}' AND time < ${CUTOFF}")
[[ "$COUNT" =~ ^[0-9]+$ ]] || COUNT=0

echo "[clean_kline] $(date '+%F %T') period=${PERIOD} 保留最近 ${DAYS} 天 → 待清理 ${COUNT} 行"

if [[ "$DRY" == "1" ]]; then
  echo "[clean_kline] --dry 模式，未执行删除"
  exit 0
fi

if [[ "$COUNT" -gt 0 ]]; then
  DELETED=0
  # 分批删除（每批 5000 行，避免长事务锁表）；每批后重新统计剩余，用差值算删除数
  while true; do
    db_exec "DELETE FROM fa_stock_kline WHERE period='${PERIOD}' AND time < ${CUTOFF} LIMIT 5000"
    LEFT=$(db_scalar "SELECT COUNT(*) FROM fa_stock_kline WHERE period='${PERIOD}' AND time < ${CUTOFF}")
    [[ "$LEFT" =~ ^[0-9]+$ ]] || LEFT=0
    [[ "$LEFT" -ge "$COUNT" ]] && break          # 本批没删掉任何行，退出防死循环
    DELETED=$((DELETED + COUNT - LEFT))
    COUNT=$LEFT
    [[ "$COUNT" -le 0 ]] && break
    sleep 0.2
  done
  echo "[clean_kline] 已删除 ${DELETED} 行"
else
  echo "[clean_kline] 无需清理"
fi

# 日线是"先删后写"的覆盖模式，长期会产生碎片，每月 1 号整理一次
if [[ "$(date +%d)" == "01" ]]; then
  db_exec "OPTIMIZE TABLE fa_stock_kline"
  echo "[clean_kline] 已执行 OPTIMIZE TABLE（每月 1 号）"
fi

TOTAL=$(db_scalar "SELECT COUNT(*) FROM fa_stock_kline")
echo "[clean_kline] 当前总行数：${TOTAL:-?}"
