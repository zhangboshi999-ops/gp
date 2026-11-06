<?php
/**
 * 导出基础数据到 db/business_seed.sql
 *
 * 用法（项目根目录）：
 *   php tools/export_seed.php
 *
 * 导出内容：fa_stock（标的信息）、fa_ipo（新股）、fa_trader（交易员）、fa_support（客服问答）
 * 不含用户/订单/持仓等业务数据，K 线请用 php think sync 生成。
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '123456';
$name = getenv('DB_NAME') ?: 'stock';
$port = (int)(getenv('DB_PORT') ?: 3306);

$m = new mysqli($host, $user, $pass, $name, $port);
if ($m->connect_error) {
    die('db error: ' . $m->connect_error . "\n");
}
$m->set_charset('utf8mb4');

$root = getcwd();
$dir = $root . '/db';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$tables = ['fa_stock', 'fa_ipo', 'fa_trader', 'fa_support'];
$total = 0;
$out = "-- ============================================================\n"
    . "-- 基础数据：股票标的、新股、交易员、客服问答\n"
    . "-- 生成时间：" . date('Y-m-d H:i:s') . "\n"
    . "-- 用法：mysql -uroot -p stock < db/business_seed.sql\n"
    . "-- 注：K 线数据请用 php think sync 同步生成\n"
    . "-- ============================================================\n\n"
    . "SET NAMES utf8mb4;\n\n";

foreach ($tables as $table) {
    $res = $m->query('select * from `' . $table . '`');
    if (!$res || !$res->num_rows) {
        continue;
    }
    $out .= "-- ----------------------------\n-- " . $table . '（' . $res->num_rows . " 行）\n-- ----------------------------\n";
    while ($row = $res->fetch_assoc()) {
        $cols = [];
        $vals = [];
        foreach ($row as $key => $value) {
            $cols[] = '`' . $key . '`';
            if ($value === null) {
                $vals[] = 'NULL';
            } elseif (is_numeric($value)) {
                $vals[] = $value;
            } else {
                $vals[] = "'" . $m->real_escape_string((string)$value) . "'";
            }
        }
        $out .= 'INSERT INTO `' . $table . '` (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ");\n";
        $total++;
    }
    $out .= "\n";
}

file_put_contents($dir . '/business_seed.sql', $out);
echo '已写入 db/business_seed.sql，共 ' . $total . " 行\n";
