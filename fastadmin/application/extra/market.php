<?php

/**
 * 外部行情数据源配置
 *
 * 股票（東京/美股）与指数：Twelve Data  https://twelvedata.com  （免费 800 credits/天，8 credits/分钟）
 * 加密货币 24h/市值/排名：CoinGecko    https://www.coingecko.com（免费，无需 key）
 */
return [
    // Twelve Data：免费申请 https://twelvedata.com/pricing ，拿到后填到这里
    'twelvedata' => [
        'apikey'   => '',
        'endpoint' => 'https://api.twelvedata.com',
        // 免费计划 8 credits/分钟，批量请求之间需要限速（秒）
        'interval' => 8,
    ],

    // CoinGecko：免费无需 key
    'coingecko' => [
        'endpoint' => 'https://api.coingecko.com/api/v3',
    ],

    /**
     * 日本股票数据源
     *
     * Twelve Data 免费计划(Basic)只含美股/外汇/加密，日本 TSE 需要 Grow($79/月) 以上，
     * 因此东京股票改用下面的免费源：
     *   fmp          = Financial Modeling Prep，免费 250 次/天，代码 7203.TSE（推荐）
     *   alphavantage = Alpha Vantage，免费 25 次/天，代码 7203.TYO
     *   twelvedata   = 已购买 Grow/Pro 计划时改回此项
     * 申请地址：https://site.financialmodelingprep.com/register
     *          https://www.alphavantage.co/support/#api-key
     */
    'japan' => [
        // fmp = Financial Modeling Prep(250/天) | hayinsights = HayInsights(J-Quants 数据，推荐) | alphavantage(25/天) | twelvedata(Grow 计划以上)
        'source'        => 'hayinsights',
        'fmp'           => ['apikey' => '', 'endpoint' => 'https://financialmodelingprep.com/api/v3'],
        'alphavantage'  => ['apikey' => '', 'endpoint' => 'https://www.alphavantage.co/query'],
        'hayinsights'   => ['apikey' => '', 'endpoint' => 'https://api-v2.hayinsights.com/openapi/v1'],
    ],

    // 免 key 兜底：纳斯达克官方行情接口（仅限 NASDAQ 上市的美股，实时价 + 基本面）
    // 仅在未配置 twelvedata.apikey 时自动启用
    'nasdaq' => [
        'endpoint' => 'https://api.nasdaq.com/api/quote',
    ],

    /**
     * iTick（https://itick.org）：日本 + 美国股票统一数据源
     *
     * - 单个 API 同时覆盖东京(JP)与美股(US)，含 OHLC、前收、涨跌、成交量、成交额
     * - 配置了 token 后，股票行情与日线都优先走它，其余源自动成为备选
     * - 免费体验版有速率限制（约 1 次/秒，批量接口不可用），interval 为请求间隔(微秒)
     * - 付费后把 endpoint 换成 https://api.itick.org 即可（速率与批量权限提升）
     */
    'itick' => [
        'token'    => '10790d7dd6f1425ab29a998df659f0852e4114835f4d4e6da147e00d0fb44ae0',
        'endpoint' => 'https://api-free.itick.org',
        'interval' => 1500000,
        'retry'    => 2,
        // fa_stock.market => iTick region
        'region' => [
            'TSE'    => 'JP',
            'NASDAQ' => 'US',
            'NYSE'   => 'US',
            'NY'     => 'US',
        ],
    ],

    // fa_stock.market => Twelve Data 交易所代码（用于拼 exchange 参数）
    // TSE=東京証券取引所（日本）、NASDAQ / NYSE = 美国
    'exchange' => [
        'TSE'    => 'TSE',
        'NASDAQ' => 'NASDAQ',
        'NYSE'   => 'NYSE',
        'NY'     => 'NYSE',
    ],

    // 行情同步的标的范围：日本 + 美国股票（加密货币由 sync crypto 单独同步）
    'sync_markets' => ['TSE', 'NASDAQ', 'NYSE'],

    // 首页指数（Twelve Data 指数符号）
    'indexes' => [
        ['code' => 'N225', 'name' => '日経平均', 'symbol' => '^N225', 'market' => 'JPX', 'currency' => 'JPY'],
        ['code' => 'TPX',  'name' => 'TOPIX',    'symbol' => '^TOPX', 'market' => 'JPX', 'currency' => 'JPY'],
        ['code' => 'SPX',  'name' => 'S&P500',   'symbol' => '^SPX',  'market' => 'US',  'currency' => 'USD'],
        ['code' => 'NDX',  'name' => 'NASDAQ100', 'symbol' => '^NDX', 'market' => 'US',  'currency' => 'USD'],
        ['code' => 'DJI',  'name' => 'NYダウ',    'symbol' => '^DJI', 'market' => 'US',  'currency' => 'USD'],
    ],

    // 加密货币：td = Twelve Data 代码（主用），cg = CoinGecko 币种 id（备用，用于市值/排名/24h高低）
    'crypto' => [
        'BTC'  => ['td' => 'BTC/USD', 'cg' => 'bitcoin'],
        'ETH'  => ['td' => 'ETH/USD', 'cg' => 'ethereum'],
        'USDT' => ['td' => 'USDT/USD', 'cg' => 'tether'],
    ],

    // 单次同步拉取的日线根数
    'kline_limit' => 90,

    // HTTP 超时（秒）
    'timeout' => 20,
];
