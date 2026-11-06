<?php

// Redis 配置（行情实时推送：报价缓存 + 消息总线）
return [
    'host'     => '127.0.0.1',
    'port'     => 6379,
    'password' => '',
    'select'   => 0,
    'timeout'  => 2,
    // WebSocket 服务端口（Node 端 socket/server.js 使用，需保持一致）
    'ws_port'  => 9502,
];
