<?php
/**
 * 项目初始化脚本
 *
 * 作用：创建全新数据库 stock，复制 FastAdmin 原生表（剔除已删除的贷款表），
 *      建立证券交易业务表、写入演示数据与管理后台菜单，并把 .env 指向新库。
 *
 * 用法：php database/install.php
 */

$config = [
    'socket'   => '/tmp/mysql_3306.sock',
    'host'     => '127.0.0.1',
    'user'     => 'root',
    'password' => '123456',
    'source'   => 'fastadmin', // 原有的 FastAdmin 库，仅用于复制系统表
    'target'   => 'stock',     // 新项目数据库
];

$pdo = new PDO(
    "mysql:unix_socket={$config['socket']};charset=utf8mb4",
    $config['user'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

function out($msg)
{
    echo $msg . PHP_EOL;
}

// 1. 创建数据库
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['target']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
out("[1/7] 数据库 {$config['target']} 已就绪");

// 2. 复制 FastAdmin 原生表（跳过贷款相关表）
$pdo->exec("USE `{$config['target']}`");
$skip = ['fa_loan_apply', 'fa_loan_bank', 'fa_loan_repayment', 'fa_admin_log'];
$tables = $pdo->query("SHOW TABLES FROM `{$config['source']}`")->fetchAll(PDO::FETCH_COLUMN);
$copied = 0;
foreach ($tables as $table) {
    if (in_array($table, $skip)) {
        continue;
    }
    $create = $pdo->query("SHOW CREATE TABLE `{$config['source']}`.`{$table}`")->fetch(PDO::FETCH_ASSOC);
    $pdo->exec($create['Create Table']);
    $rows = $pdo->query("SELECT COUNT(*) FROM `{$config['source']}`.`{$table}`")->fetchColumn();
    if ($rows > 0) {
        $pdo->exec("INSERT INTO `{$config['target']}`.`{$table}` SELECT * FROM `{$config['source']}`.`{$table}`");
    }
    $copied++;
}
out("[2/7] 已复制 {$copied} 张系统表");

$pdo->exec("USE `{$config['target']}`");

// 3. 建立业务表
$sql = file_get_contents(__DIR__ . '/stock.sql');
foreach (explode(';', $sql) as $statement) {
    $statement = trim($statement);
    if ($statement === '' || strpos($statement, '--') === 0) {
        continue;
    }
    $pdo->exec($statement);
}
out("[3/7] 业务表创建完成");

// 4. 会员表扩展字段（资金密码 / 实名状态）
$columns = $pdo->query("SHOW COLUMNS FROM `fa_user`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('pay_password', $columns)) {
    $pdo->exec("ALTER TABLE `fa_user` ADD COLUMN `pay_password` varchar(32) DEFAULT '' COMMENT '资金密码' AFTER `verification`");
}
if (!in_array('pay_salt', $columns)) {
    $pdo->exec("ALTER TABLE `fa_user` ADD COLUMN `pay_salt` varchar(30) DEFAULT '' COMMENT '资金密码盐' AFTER `pay_password`");
}
if (!in_array('kyc_status', $columns)) {
    $pdo->exec("ALTER TABLE `fa_user` ADD COLUMN `kyc_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '实名状态:0=未认证,1=审核中,2=已认证,3=已驳回' AFTER `pay_salt`");
}
out("[4/7] 会员表字段已扩展");

// 5. 演示数据
$now = time();

$stocks = [
    ['7203', 'トヨタ自動車', 'TSE', 'stock', 'JPY', 3850, 100],
    ['6758', 'ソニーグループ', 'TSE', 'stock', 'JPY', 3450, 100],
    ['9984', 'ソフトバンクグループ', 'TSE', 'stock', 'JPY', 11200, 100],
    ['8306', '三菱UFJフィナンシャル・グループ', 'TSE', 'stock', 'JPY', 1980, 100],
    ['6501', '日立製作所', 'TSE', 'stock', 'JPY', 3980, 100],
    ['9432', '日本電信電話', 'TSE', 'stock', 'JPY', 148, 100],
    ['6861', 'キーエンス', 'TSE', 'stock', 'JPY', 68500, 100],
    ['4063', '信越化学工業', 'TSE', 'stock', 'JPY', 4920, 100],
    ['8035', '東京エレクトロン', 'TSE', 'stock', 'JPY', 23500, 100],
    ['6098', 'リクルートホールディングス', 'TSE', 'stock', 'JPY', 9100, 100],
    ['7974', '任天堂', 'TSE', 'stock', 'JPY', 11200, 100],
    ['4568', '第一三共', 'TSE', 'stock', 'JPY', 4380, 100],
    ['AAPL', 'Apple Inc.', 'NASDAQ', 'stock', 'USD', 228.5, 1],
    ['TSLA', 'Tesla, Inc.', 'NASDAQ', 'stock', 'USD', 341.2, 1],
    ['NVDA', 'NVIDIA Corporation', 'NASDAQ', 'stock', 'USD', 178.9, 1],
    ['MSFT', 'Microsoft Corporation', 'NASDAQ', 'stock', 'USD', 428.6, 1],
    ['AMZN', 'Amazon.com, Inc.', 'NASDAQ', 'stock', 'USD', 205.4, 1],
    ['BTC', 'Bitcoin', 'CRYPTO', 'crypto', 'USD', 68420.5, 1],
    ['ETH', 'Ethereum', 'CRYPTO', 'crypto', 'USD', 3245.8, 1],
];

$stmt = $pdo->prepare(
    "INSERT INTO `fa_stock` (`code`,`name`,`market`,`type`,`currency`,`price`,`prev_close`,`open`,`high`,`low`,`change_amount`,`change_rate`,`volume`,`turnover`,`total_shares`,`lot_size`,`is_hot`,`weigh`,`status`,`createtime`,`updatetime`)
     VALUES (:code,:name,:market,:type,:currency,:price,:prev_close,:open,:high,:low,:change_amount,:change_rate,:volume,:turnover,:total_shares,:lot_size,:is_hot,:weigh,'normal',:createtime,:updatetime)
     ON DUPLICATE KEY UPDATE `price`=VALUES(`price`)"
);

mt_srand(20261028);
$inserted = 0;
foreach ($stocks as $index => $item) {
    list($code, $name, $market, $type, $currency, $price, $lot) = $item;
    $isCrypto = $type === 'crypto';
    $volatility = $isCrypto ? 0.03 : 0.012;
    $prevClose = round($price * (1 + (mt_rand(-100, 100) / 100) * $volatility), 4);
    $open = round($prevClose * (1 + (mt_rand(-60, 60) / 100) * $volatility), 4);
    $high = round(max($price, $open) * (1 + mt_rand(10, 90) / 10000), 4);
    $low = round(min($price, $open) * (1 - mt_rand(10, 90) / 10000), 4);
    $change = round($price - $prevClose, 4);
    $rate = $prevClose > 0 ? round($change / $prevClose * 100, 4) : 0;
    $volume = mt_rand(120000, 9800000);
    $stmt->execute([
        ':code'          => $code,
        ':name'          => $name,
        ':market'        => $market,
        ':type'          => $type,
        ':currency'      => $currency,
        ':price'         => $price,
        ':prev_close'    => $prevClose,
        ':open'          => $open,
        ':high'          => $high,
        ':low'           => $low,
        ':change_amount' => $change,
        ':change_rate'   => $rate,
        ':volume'        => $volume,
        ':turnover'      => round($volume * $price, 2),
        ':total_shares'  => mt_rand(60000000, 16000000000),
        ':lot_size'      => $lot,
        ':is_hot'        => $index < 6 ? 1 : 0,
        ':weigh'         => 100 - $index,
        ':createtime'    => $now,
        ':updatetime'    => $now,
    ]);
    $inserted++;
}
out("[5/7] 已写入 {$inserted} 只证券标的");

// K线：每只标的生成 90 根日线（随机游走）
$pdo->exec("DELETE FROM `fa_stock_kline` WHERE `period`='1d'");
$kline = $pdo->prepare(
    "INSERT INTO `fa_stock_kline` (`stock_id`,`code`,`period`,`time`,`open`,`high`,`low`,`close`,`volume`) VALUES (?,?,?,?,?,?,?,?,?)"
);
$list = $pdo->query("SELECT `id`,`code`,`price` FROM `fa_stock`")->fetchAll(PDO::FETCH_ASSOC);
foreach ($list as $row) {
    $price = (float)$row['price'];
    $close = $price * (1 - mt_rand(-800, 300) / 10000);
    for ($i = 90; $i >= 1; $i--) {
        $open = $close;
        $close = $open * (1 + (mt_rand(-250, 260) / 10000));
        $high = max($open, $close) * (1 + mt_rand(0, 120) / 10000);
        $low = min($open, $close) * (1 - mt_rand(0, 120) / 10000);
        $kline->execute([
            $row['id'],
            $row['code'],
            '1d',
            strtotime("-{$i} days"),
            round($open, 4),
            round($high, 4),
            round($low, 4),
            round($close, 4),
            mt_rand(50000, 3200000),
        ]);
    }
    // 最后一根收盘对齐最新价
    $pdo->exec("UPDATE `fa_stock_kline` SET `close`={$price} WHERE `code`='{$row['code']}' AND `period`='1d' ORDER BY `time` DESC LIMIT 1");
}

// 新股认购
$ipos = [
    ['5580', 'クラウドテック株式会社', 'TSE', 980, 1200000, 100, 5000, 'ongoing'],
    ['4071', 'グリーンエナジー株式会社', 'TSE', 2300, 800000, 100, 3000, 'ongoing'],
    ['9212', 'ネクストモビリティ株式会社', 'TSE', 1500, 2000000, 100, 8000, 'upcoming'],
    ['7312', 'スマートロジ株式会社', 'TSE', 1180, 600000, 100, 2000, 'closed'],
];
$ipoStmt = $pdo->prepare(
    "INSERT INTO `fa_ipo` (`code`,`name`,`market`,`issue_price`,`total_shares`,`min_quantity`,`max_quantity`,`start_time`,`end_time`,`list_date`,`intro`,`status`,`weigh`,`createtime`,`updatetime`)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE `status`=VALUES(`status`)"
);
foreach ($ipos as $i => $ipo) {
    $ipoStmt->execute([
        $ipo[0], $ipo[1], $ipo[2], $ipo[3], $ipo[4], $ipo[5], $ipo[6],
        $now - 86400 * 3, $now + 86400 * 7, $now + 86400 * 14,
        $ipo[1] . 'の新規上場に伴う公募・売出しです。', $ipo[7], 100 - $i, $now, $now,
    ]);
}

// 交易员（跟单）
$traders = [
    ['佐藤 健一', '日本', 38.62, 71.5, 12480, 1286500, 50000, '大型株を中心とした長期投資が得意です。'],
    ['鈴木 美咲', '日本', 24.18, 66.2, 8630, 742300, 30000, '半導体セクターに強みがあります。'],
    ['Michael Chen', '美国', 52.74, 74.8, 23170, 3260800, 100000, 'Focus on US tech stocks and index trading.'],
    ['田中 雄大', '日本', 15.36, 61.4, 4120, 286400, 10000, '中小型成長株を発掘します。'],
];
$traderStmt = $pdo->prepare(
    "INSERT INTO `fa_trader` (`name`,`country`,`roi_rate`,`win_rate`,`followers`,`total_profit`,`min_amount`,`intro`,`weigh`,`status`,`createtime`,`updatetime`)
     VALUES (?,?,?,?,?,?,?,?,?,'normal',?,?)"
);
$pdo->exec("DELETE FROM `fa_trader`");
foreach ($traders as $i => $t) {
    $traderStmt->execute([$t[0], $t[1], $t[2], $t[3], $t[4], $t[5], $t[6], $t[7], 100 - $i, $now, $now]);
}

// 客服帮助与公告
$supports = [
    ['faq', '入金はどのくらいで反映されますか？', '銀行振込は営業日30分以内、暗号資産はネットワーク承認後に反映されます。', 'ja'],
    ['faq', 'How long does a withdrawal take?', 'Bank withdrawals are processed within 1 business day. Crypto withdrawals depend on network confirmation.', 'en'],
    ['faq', '充值多久到账？', '银行卡充值一般在 30 分钟内到账；加密货币需等待链上确认。', 'zh'],
    ['notice', 'システムメンテナンスのお知らせ', '毎週日曜 03:00-04:00 はシステムメンテナンスのため取引を停止します。', 'ja'],
    ['notice', '新規上場銘柄の取扱い開始', 'クラウドテック株式会社の新規公開株式の取扱いを開始しました。', 'ja'],
];
$supportStmt = $pdo->prepare(
    "INSERT INTO `fa_support` (`type`,`title`,`content`,`lang`,`weigh`,`status`,`createtime`,`updatetime`) VALUES (?,?,?,?,?,'normal',?,?)"
);
$pdo->exec("DELETE FROM `fa_support`");
foreach ($supports as $i => $s) {
    $supportStmt->execute([$s[0], $s[1], $s[2], $s[3], 100 - $i, $now, $now]);
}

// 给已有会员开通账户（日元/美元/USDT）
$users = $pdo->query("SELECT `id` FROM `fa_user`")->fetchAll(PDO::FETCH_COLUMN);
$accountStmt = $pdo->prepare(
    "INSERT INTO `fa_account` (`user_id`,`currency`,`balance`,`frozen`,`updatetime`) VALUES (?,?,?,?,?)
     ON DUPLICATE KEY UPDATE `user_id`=`user_id`"
);
foreach ($users as $uid) {
    $accountStmt->execute([$uid, 'JPY', 1000000, 0, $now]);
    $accountStmt->execute([$uid, 'USD', 8000, 0, $now]);
    $accountStmt->execute([$uid, 'USDT', 2000, 0, $now]);
}
out("[6/7] 演示数据（K线/新股/交易员/帮助/账户）已写入");

// 7. 管理后台菜单
$menus = [
    ['stock', '证券管理', 'fa fa-line-chart', [
        ['stock/index', '证券列表', 'fa fa-list'],
        ['stock/kline', 'K线数据', 'fa fa-area-chart'],
        ['stock_favorite/index', '自选记录', 'fa fa-star'],
    ]],
    ['trade', '交易管理', 'fa fa-exchange', [
        ['order/index', '委托订单', 'fa fa-file-text'],
        ['position/index', '持仓记录', 'fa fa-briefcase'],
    ]],
    ['fund', '资金管理', 'fa fa-yen', [
        ['transaction/index', '资金流水', 'fa fa-random'],
        ['account/index', '资金账户', 'fa fa-credit-card'],
        ['bank_card/index', '银行卡管理', 'fa fa-bank'],
        ['crypto_wallet/index', '钱包地址', 'fa fa-btc'],
    ]],
    ['ipo', '新股管理', 'fa fa-rocket', [
        ['ipo/index', '新股发行', 'fa fa-list'],
        ['ipo_apply/index', '认购记录', 'fa fa-check-square-o'],
    ]],
    ['member', '会员管理', 'fa fa-users', [
        ['user_verify/index', '实名认证', 'fa fa-id-card'],
        ['follow/index', '跟单记录', 'fa fa-link'],
        ['trader/index', '交易员', 'fa fa-user-secret'],
    ]],
    ['content', '内容管理', 'fa fa-file', [
        ['support/index', '客服帮助', 'fa fa-question-circle'],
    ]],
];

$ruleStmt = $pdo->prepare(
    "INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`ismenu`,`createtime`,`updatetime`,`weigh`,`status`)
     VALUES (?,?,?,?,?,?,?,?,?,'normal')"
);
$findStmt = $pdo->prepare("SELECT `id` FROM `fa_auth_rule` WHERE `name`=?");

$weigh = 100;
foreach ($menus as $group) {
    list($gname, $gtitle, $gicon, $children) = $group;
    $findStmt->execute([$gname]);
    $pid = $findStmt->fetchColumn();
    if (!$pid) {
        $ruleStmt->execute(['file', 0, $gname, $gtitle, $gicon, 1, $now, $now, $weigh]);
        $pid = $pdo->lastInsertId();
    }
    $weigh--;
    foreach ($children as $child) {
        $findStmt->execute([$child[0]]);
        if (!$findStmt->fetchColumn()) {
            $ruleStmt->execute(['file', $pid, $child[0], $child[1], $child[2], 1, $now, $now, $weigh]);
        }
    }
}

// 8. 切换 .env 到新数据库
$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    $env = file_get_contents($envFile);
    $env = preg_replace('/database\s*=\s*\S+/', "database = {$config['target']}", $env, 1);
    file_put_contents($envFile, $env);
}
out("[7/7] 完成：数据库 {$config['target']} 初始化成功，.env 已切换");
