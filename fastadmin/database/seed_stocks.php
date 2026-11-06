<?php
/**
 * 补充美股 + 日本股票标的（幂等：已存在的代码跳过）
 *
 * 用法：php database/seed_stocks.php
 * 价格仅为初始值，执行 php think sync quote 后会被真实行情覆盖。
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
 * 日本股票（東京証券取引所）
 * [代码, 名称, 行业, 价格, 总股本(万股)]
 */
$jp = [
    ['6902', 'デンソー', '自動車', 2500, 280000],
    ['7267', '本田技研工業', '自動車', 1550, 190000],
    ['8316', '三井住友フィナンシャルグループ', '金融', 3450, 39000],
    ['8411', 'みずほフィナンシャルグループ', '金融', 2900, 250000],
    ['8766', '東京海上ホールディングス', '保険', 5200, 21000],
    ['6301', '小松製作所', '機械', 4600, 95000],
    ['7201', '日産自動車', '自動車', 420, 400000],
    ['2914', '日本たばこ産業', '食品', 3900, 200000],
    ['4502', '武田薬品工業', '医薬品', 4400, 155000],
    ['6367', 'ダイキン工業', '機械', 19000, 29000],
    ['3382', 'セブン&アイ・ホールディングス', '小売', 2100, 87000],
    ['9202', 'ANAホールディングス', '空運', 2900, 47000],
    ['8058', '三菱商事', '商社', 3100, 150000],
    ['8002', '丸紅', '商社', 2600, 170000],
    ['9433', 'KDDI', '通信', 4800, 220000],
    ['9983', 'ファーストリテイリング', '小売', 45000, 10000],
    ['7751', 'キヤノン', '電機', 4800, 130000],
    ['6702', '富士通', '情報通信', 2600, 200000],
    ['8031', '三井物産', '商社', 2900, 150000],
    ['6867', 'リコー', '電機', 1300, 70000],
];

/**
 * 美国股票
 * [代码, 名称, 交易所(NASDAQ/NYSE), 行业, 价格]
 */
$us = [
    ['GOOGL', 'Alphabet Inc.', 'NASDAQ', '通信サービス', 175],
    ['META',  'Meta Platforms, Inc.', 'NASDAQ', '通信サービス', 590],
    ['NFLX',  'Netflix, Inc.', 'NASDAQ', '通信サービス', 880],
    ['AMD',   'Advanced Micro Devices, Inc.', 'NASDAQ', '半導体', 165],
    ['INTC',  'Intel Corporation', 'NASDAQ', '半導体', 34],
    ['ADBE',  'Adobe Inc.', 'NASDAQ', 'ソフトウェア', 480],
    ['CRM',   'Salesforce, Inc.', 'NASDAQ', 'ソフトウェア', 300],
    ['ORCL',  'Oracle Corporation', 'NASDAQ', 'ソフトウェア', 175],
    ['PYPL',  'PayPal Holdings, Inc.', 'NASDAQ', '金融', 78],
    ['COST',  'Costco Wholesale Corporation', 'NASDAQ', '小売', 900],
    ['JPM',   'JPMorgan Chase & Co.', 'NYSE', '金融', 245],
    ['V',     'Visa Inc.', 'NYSE', '金融', 290],
    ['JNJ',   'Johnson & Johnson', 'NYSE', '医薬品', 155],
    ['XOM',   'Exxon Mobil Corporation', 'NYSE', 'エネルギー', 118],
    ['DIS',   'The Walt Disney Company', 'NYSE', 'メディア', 95],
    ['KO',    'The Coca-Cola Company', 'NYSE', '食品', 68],
    ['PFE',   'Pfizer Inc.', 'NYSE', '医薬品', 28],
    ['WMT',   'Walmart Inc.', 'NYSE', '小売', 78],
    ['MCD',   "McDonald's Corporation", 'NYSE', '外食', 300],
    ['BA',    'The Boeing Company', 'NYSE', '航空機', 165],
];

$now = time();
$added = 0;
$skipped = 0;

/**
 * 插入一只标的
 */
function insertStock(PDO $pdo, $data, $now, &$added, &$skipped)
{
    $check = $pdo->prepare("SELECT id FROM `fa_stock` WHERE `code` = ?");
    $check->execute([$data['code']]);
    if ($check->fetch()) {
        $skipped++;
        return;
    }
    $stmt = $pdo->prepare(
        "INSERT INTO `fa_stock`
        (`code`,`name`,`market`,`type`,`currency`,`price`,`prev_close`,`open`,`high`,`low`,
         `change_amount`,`change_rate`,`volume`,`turnover`,`total_shares`,`lot_size`,`sector`,
         `is_hot`,`weigh`,`status`,`source`,`symbol`,`createtime`,`updatetime`)
        VALUES
        (:code,:name,:market,:type,:currency,:price,:prev_close,:open,:high,:low,
         :change_amount,:change_rate,:volume,:turnover,:total_shares,:lot_size,:sector,
         :is_hot,:weigh,:status,:source,:symbol,:createtime,:updatetime)"
    );
    $stmt->execute($data + [
        'createtime' => $now,
        'updatetime' => $now,
    ]);
    $added++;
}

// 日本股票
foreach ($jp as $index => $item) {
    list($code, $name, $sector, $price, $shares) = $item;
    insertStock($pdo, [
        'code'          => $code,
        'name'          => $name,
        'market'        => 'TSE',
        'type'          => 'stock',
        'currency'      => 'JPY',
        'price'         => $price,
        'prev_close'    => $price,
        'open'          => $price,
        'high'          => $price,
        'low'           => $price,
        'change_amount' => 0,
        'change_rate'   => 0,
        'volume'        => 0,
        'turnover'      => 0,
        'total_shares'  => $shares * 10000,
        'lot_size'      => 100,
        'sector'        => $sector,
        'is_hot'        => $index < 4 ? 1 : 0,
        'weigh'         => 100 - $index,
        'status'        => 'normal',
        'source'        => 'seed',
        'symbol'        => $code . '.TSE',
    ], $now, $added, $skipped);
}

// 美国股票
foreach ($us as $index => $item) {
    list($code, $name, $market, $sector, $price) = $item;
    insertStock($pdo, [
        'code'          => $code,
        'name'          => $name,
        'market'        => $market,
        'type'          => 'stock',
        'currency'      => 'USD',
        'price'         => $price,
        'prev_close'    => $price,
        'open'          => $price,
        'high'          => $price,
        'low'           => $price,
        'change_amount' => 0,
        'change_rate'   => 0,
        'volume'        => 0,
        'turnover'      => 0,
        'total_shares'  => 0,
        'lot_size'      => 1,
        'sector'        => $sector,
        'is_hot'        => $index < 4 ? 1 : 0,
        'weigh'         => 100 - $index,
        'status'        => 'normal',
        'source'        => 'seed',
        'symbol'        => $code,
    ], $now, $added, $skipped);
}

echo "新增 {$added} 只标的，跳过已存在 {$skipped} 只\n";
foreach ($pdo->query("SELECT market, COUNT(*) c FROM `fa_stock` WHERE type='stock' GROUP BY market") as $row) {
    echo "  {$row['market']}: {$row['c']} 只\n";
}
