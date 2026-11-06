#!/usr/bin/env bash
# ==========================================================
# 批量补日线（sync kline 只支持单只，这里循环调用，供定时任务使用）
#
# 用法（项目根目录）：
#   ./tools/sync_kline_batch.sh                 # 默认：首页热门标的（is_hot=1）
#   ./tools/sync_kline_batch.sh -n 20           # 按 weigh 取前 20 只
#   ./tools/sync_kline_batch.sh 7203 6758 9984  # 指定代码
#
# 环境变量：
#   PHP_BIN=/www/server/php/81/bin/php    PHP 绝对路径（宝塔必填）
#   DB_PASS=123456                        数据库密码
#   SLEEP=2                               每只之间的间隔秒数（免费源限速）
# ==========================================================
set -u

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_BIN="${PHP_BIN:-$(command -v php)}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-123456}"
DB_NAME="${DB_NAME:-stock}"
SLEEP="${SLEEP:-2}"
LOG="$ROOT/runtime/sync_kline.log"

mkdir -p "$ROOT/runtime"
export DB_HOST DB_USER DB_PASS DB_NAME

query_codes() {
  # $1 = 传入给 PHP 的 SQL 片段控制参数：空=热门，数字=取前 N 只
  "$PHP_BIN" -r '
    $m = @new mysqli(getenv("DB_HOST"), getenv("DB_USER"), getenv("DB_PASS"), getenv("DB_NAME"));
    if ($m->connect_error) { fwrite(STDERR, "db error\n"); exit(1); }
    $m->set_charset("utf8mb4");
    $n = isset($argv[1]) ? (int)$argv[1] : 0;
    if ($n > 0) {
      $sql = "select code from fa_stock where type=\"stock\" and status=\"normal\" order by weigh desc, id asc limit $n";
    } else {
      $sql = "select code from fa_stock where is_hot=1 and status=\"normal\" order by weigh desc, id asc";
    }
    $r = $m->query($sql);
    $out = [];
    while ($x = $r->fetch_row()) { $out[] = $x[0]; }
    echo implode(" ", $out);
  ' -- "${1:-0}"
}

if [ "${1:-}" = "-n" ]; then
  CODES=$(query_codes "${2:-20}")
elif [ "$#" -gt 0 ]; then
  CODES="$*"
else
  CODES=$(query_codes 0)
fi

CODES=$(echo "$CODES" | tr -s ' \n' ' ' | sed 's/^ *//;s/ *$//')
if [ -z "$CODES" ]; then
  echo "[kline] 没有取到标的代码，检查数据库连接或 is_hot 配置" | tee -a "$LOG"
  exit 1
fi

COUNT=$(echo "$CODES" | wc -w | tr -d ' ')
echo "[kline] $(date '+%F %T') 开始同步 $COUNT 只日线" | tee -a "$LOG"

i=0
for code in $CODES; do
  i=$((i + 1))
  echo "[kline] ($i/$COUNT) $code" | tee -a "$LOG"
  (cd "$ROOT/fastadmin" && "$PHP_BIN" think sync kline --code="$code" >> "$LOG" 2>&1)
  sleep "$SLEEP"
done

echo "[kline] $(date '+%F %T') 完成，共 $COUNT 只" | tee -a "$LOG"
