<?php

// 充值收款信息（前台充值页展示，改这里即可，不需要动代码）
return [
    // 银行转账收款账户
    'bank' => [
        'bank_name'    => 'サンプル銀行',
        'branch_name'  => '東京支店',
        'account_no'   => '1234567',
        'account_name' => 'カ）サンプル証券',
        'tips'         => '振込人名義はご本人名義でお願いします（名義が異なると入金反映が遅れます）',
    ],

    // 加密货币收款地址：key 与前端 method 对应
    'crypto' => [
        'btc' => [
            'rate'    => 9800000.0,
            'name'    => 'Bitcoin',
            'network' => 'Bitcoin',
            'address' => 'bc1qsample0bitcoin0address0demo000000000',
            'confirm' => 1,
        ],
        'eth' => [
            'rate'    => 340000.0,
            'name'    => 'Ethereum',
            'network' => 'ERC20',
            'address' => '0x1a2b3c4d5e6fSampleEthereumAddressDemo0000',
            'confirm' => 12,
        ],
        'usdt' => [
            'rate'    => 157.44,
            'name'    => 'USDT',
            'network' => 'TRC20',
            'address' => 'TQSampleUsdtTrc20AddressDemo000000000000',
            'confirm' => 20,
        ],
    ],

    // 单笔充值限额（JPY）
    'min_amount' => 1000,
    'max_amount' => 5000000,
];
