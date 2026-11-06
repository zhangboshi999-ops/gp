<?php
/**
 * 数据库导出脚本（无 mysqldump 环境的 PHP 兜底）
 * 用法: php export_db.php [输出文件] [库名]
 * 默认: stock_backup_日期.sql / 读 fastadmin/.env
 */

$outFile = $argv[1] ?? ('stock_backup_' . date('Ymd_His') . '.sql');
$dbName  = $argv[2] ?? null;

// 读 .env
$env = parse_ini_file(__DIR__ . '/../fastadmin/.env', true);
$db  = $env['database'];
$dbName = $dbName ?: $db['database'];

$mysqli = new mysqli($db['hostname'], $db['username'], $db['password'], $dbName, (int)($db['hostport'] ?: 3306));
if ($mysqli->connect_error) {
    fwrite(STDERR, "连接失败: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

$fp = fopen($outFile, 'w');
fwrite($fp, "-- 数据库导出: {$dbName}\n-- 时间: " . date('Y-m-d H:i:s') . "\n");
fwrite($fp, "-- 生成方式: PHP 导出脚本 (tools/export_db.php)\n\n");
fwrite($fp, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS = 0;\n\n");

$tables = [];
$res = $mysqli->query('SHOW TABLES');
while ($row = $res->fetch_row()) {
    $tables[] = $row[0];
}

$totalRows = 0;
foreach ($tables as $table) {
    // 结构
    $res = $mysqli->query("SHOW CREATE TABLE `{$table}`");
    $create = $res->fetch_row()[1];
    fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n");

    // 数据
    $res = $mysqli->query("SELECT * FROM `{$table}`");
    $cols = [];
    $fields = $res->fetch_fields();
    foreach ($fields as $f) {
        $cols[] = '`' . $f->name . '`';
    }
    $colList = implode(', ', $cols);
    $batch = [];
    $rows = 0;
    while ($row = $res->fetch_row()) {
        $vals = [];
        foreach ($row as $v) {
            $vals[] = $v === null ? 'NULL' : "'" . $mysqli->real_escape_string($v) . "'";
        }
        $batch[] = '(' . implode(', ', $vals) . ')';
        $rows++;
        // 每 200 行写一条多值 INSERT，避免单条过长
        if (count($batch) >= 200) {
            fwrite($fp, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
            $batch = [];
        }
    }
    if ($batch) {
        fwrite($fp, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $batch) . ";\n");
    }
    fwrite($fp, "\n");
    $totalRows += $rows;
    printf("  %-28s %6d 行\n", $table, $rows);
}

fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
fclose($fp);

printf("\n完成: %s\n共 %d 张表 / %d 行 / %.1f KB\n", $outFile, count($tables), $totalRows, filesize($outFile) / 1024);
