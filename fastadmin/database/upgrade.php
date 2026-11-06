<?php
/**
 * 表结构增量升级脚本（幂等，可重复执行）
 *
 * 用途：database/stock.sql 变更后在现有库上补齐字段，
 *      不需要重跑 install.php（重跑会清空业务数据）。
 *
 * 用法：php database/upgrade.php
 */

$config = [
    'socket'   => '/tmp/mysql_3306.sock',
    'user'     => 'root',
    'password' => '123456',
    'database' => 'stock',
];

$pdo = new PDO(
    "mysql:unix_socket={$config['socket']};dbname={$config['database']};charset=utf8mb4",
    $config['user'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

/**
 * 字段不存在则新增
 */
function addColumn(PDO $pdo, $table, $column, $definition)
{
    $exists = $pdo->prepare("SHOW COLUMNS FROM `{$table}` LIKE ?");
    $exists->execute([$column]);
    if ($exists->fetch()) {
        echo "  - {$table}.{$column} 已存在，跳过\n";
        return;
    }
    $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
    echo "  + {$table}.{$column} 已新增\n";
}

echo "升级 fa_stock：\n";
addColumn($pdo, 'fa_stock', 'sector', "varchar(50) NOT NULL DEFAULT '' COMMENT '所属行业/板块'");
addColumn($pdo, 'fa_stock', 'source', "varchar(20) NOT NULL DEFAULT '' COMMENT '数据来源:twelvedata/coingecko/manual'");
addColumn($pdo, 'fa_stock', 'symbol', "varchar(50) NOT NULL DEFAULT '' COMMENT '数据源代码'");
addColumn($pdo, 'fa_stock', 'market_cap', "decimal(20,2) NOT NULL DEFAULT 0 COMMENT '总市值'");
addColumn($pdo, 'fa_stock', 'pe_ratio', "decimal(12,4) NOT NULL DEFAULT 0 COMMENT '市盈率PE'");
addColumn($pdo, 'fa_stock', 'pb_ratio', "decimal(12,4) NOT NULL DEFAULT 0 COMMENT '市净率PB'");
addColumn($pdo, 'fa_stock', 'dividend_yield', "decimal(10,4) NOT NULL DEFAULT 0 COMMENT '股息率(%)'");
addColumn($pdo, 'fa_stock', 'turnover_rate', "decimal(10,4) NOT NULL DEFAULT 0 COMMENT '换手率(%)'");
addColumn($pdo, 'fa_stock', 'amplitude', "decimal(10,4) NOT NULL DEFAULT 0 COMMENT '振幅(%)'");
addColumn($pdo, 'fa_stock', 'high_52w', "decimal(20,4) NOT NULL DEFAULT 0 COMMENT '52周最高'");
addColumn($pdo, 'fa_stock', 'low_52w', "decimal(20,4) NOT NULL DEFAULT 0 COMMENT '52周最低'");

echo "升级 fa_stock_kline：\n";
addColumn($pdo, 'fa_stock_kline', 'date', "varchar(10) NOT NULL DEFAULT '' COMMENT '交易日期'");
addColumn($pdo, 'fa_stock_kline', 'createtime', "int(10) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间'");
// 回填历史数据
$pdo->exec("UPDATE `fa_stock_kline` SET `date` = FROM_UNIXTIME(`time`, '%Y-%m-%d') WHERE `date` = '' AND `time` > 0");
$pdo->exec("UPDATE `fa_stock_kline` SET `createtime` = `time` WHERE `createtime` = 0 AND `time` > 0");
echo "  + fa_stock_kline 历史数据已回填\n";

// 类型枚举扩展：增加板块
$type = $pdo->query("SHOW COLUMNS FROM `fa_stock` LIKE 'type'")->fetch(PDO::FETCH_ASSOC);
if ($type && strpos($type['Type'], 'sector') === false) {
    $pdo->exec("ALTER TABLE `fa_stock` MODIFY COLUMN `type` enum('stock','crypto','index','sector') NOT NULL DEFAULT 'stock' COMMENT '类型:stock=股票,crypto=加密货币,index=指数,sector=板块'");
    echo "  + fa_stock.type 已扩展\n";
}

echo "升级 fa_user：\n";
addColumn($pdo, 'fa_user', 'pay_password', "varchar(32) DEFAULT '' COMMENT '资金密码' AFTER `verification`");
addColumn($pdo, 'fa_user', 'pay_salt', "varchar(30) DEFAULT '' COMMENT '资金密码盐' AFTER `pay_password`");
addColumn($pdo, 'fa_user', 'kyc_status', "tinyint(1) NOT NULL DEFAULT 0 COMMENT '实名状态' AFTER `pay_salt`");

echo "升级完成\n";
