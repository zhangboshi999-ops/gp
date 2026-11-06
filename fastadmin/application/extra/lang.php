<?php

// API 多语言配置
//
// 新增一门语言的步骤：
//   1. 在 application/api/lang/ 下建语言包目录（如 en-us、ko-kr），文件名与 zh-cn 保持一致
//   2. 在下面的 languages 中加一行：code=客户端标识（X-Lang 请求头 / index/langs 返回）
//      pack=语言包目录名，enable=true 才会生效
//   3. 前端 web/src/locales/messages/ 下放同名 code 的语言文件即可自动出现在语言列表
return [
    // 未匹配到任何语言时使用的语言包
    'default' => 'zh-cn',
    'languages' => [
        ['code' => 'zh', 'pack' => 'zh-cn', 'name' => '简体中文', 'enable' => true],
        ['code' => 'ja', 'pack' => 'ja-jp', 'name' => '日本語', 'enable' => true],
        ['code' => 'en', 'pack' => 'en-us', 'name' => 'English', 'enable' => true],
    ],
];
