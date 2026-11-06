# 证券交易移动端 H5 —— 部署与开发指南

面向零基础接手的同学：跟着本文一步步操作，即可把**后台管理 + PHP 接口 + Vue 移动端 + 实时行情链路**完整跑起来。

> 用**宝塔面板**的同学请直接看：[`docs/宝塔运行文档.md`](docs/宝塔运行文档.md)（含面板操作路径、伪静态、反代与常驻进程模板，全套在 `deploy/`）。

---

## 1. 项目是什么

一个模仿证券 App 的移动端 H5，含行情、交易、资金、新股、跟单、多语言、WebSocket 实时 K 线等模块。

| 组成 | 技术 | 目录 | 默认地址 |
| --- | --- | --- | --- |
| 后端接口 | PHP + ThinkPHP 5（FastAdmin） | `fastadmin/` | http://stock.test |
| 后台管理 | FastAdmin 后台 | `fastadmin/public/ujWLHBNEfM.php` | http://stock.test/ujWLHBNEfM.php |
| 移动端 | Vue 3 + Vant 4 + Vite | `web/` | http://localhost:5173 |
| 实时推送 | Node + ws + Redis 订阅 | `socket/` | ws://127.0.0.1:9502/ws |
| 行情缓存/消息总线 | Redis | `bin/redis/`（自带） | 127.0.0.1:6379 |

**数据链路**

```
外部行情源(iTick/Twelve Data/CoinGecko)
        ↓ php think sync（历史 K 线）
      MySQL(fa_stock / fa_stock_kline)
        ↓ php think quote:tick（每秒报价，本地聚合成 1 分钟 K 线）
      Redis(stock:quote / stock:bar / stock:stream)
        ↓ SUBSCRIBE
      socket/server.js  ──WS──▶  浏览器（Vue 详情页实时跳动）
```

首个 touchdown 必读：[常见问题](#9-常见问题排查)。

---

## 2. 环境要求

| 软件 | 版本 | 说明 |
| --- | --- | --- |
| PHP | ≥ 7.4（实测 8.4 可用） | **必须带 `redis` 扩展**（`php -m \| grep redis`） |
| MySQL | 5.7 / 8.0 | 库名 `stock`，字符集 `utf8mb4` |
| Node.js | ≥ 18（实测 24） | 前端构建 + socket 服务 |
| Redis | ≥ 6（实测 8.10） | 项目已自带编译好的 `bin/redis/redis-server` |
| Web 服务器 | Nginx / Apache / Laravel Herd | 需要 URL 重写（伪静态）指向 `public/index.php` |

检查命令：

```bash
php -v && php -m | grep -i redis && node -v && mysql --version
```

---

## 3. 目录结构

```
学习股票项目/
├── fastadmin/                  后端（ThinkPHP 5）
│   ├── application/
│   │   ├── api/controller/     对外接口：User / Market / Trade / Fund / Support / Index
│   │   ├── common/library/     业务核心：Fund / Trade / MarketSync / QuoteStream
│   │   ├── admin/command/      Sync（历史行情同步）、QuoteTick（实时推送）
│   │   └── extra/              配置文件（见第 6 节）
│   ├── public/                 Web 根目录（含后台随机入口 *.php、uploads/）
│   ├── think                   命令行入口
│   ├── fastadmin.sql           框架自带表（22 张）
│   └── .env                    数据库等环境变量
├── web/                        移动端 H5（Vue 3）
│   ├── src/api/                接口封装（自动带 token 与 X-Lang）
│   ├── src/views/              页面
│   ├── src/components/         组件（StockRow / KLineChart / TabBar / EmptyState）
│   ├── src/locales/messages/   语言包（ja.js / zh.js / en.js）
│   └── vite.config.js          /api 代理后端、/ws 代理 WebSocket
├── socket/                     WebSocket 服务
│   └── server.js               订阅 Redis → 推送浏览器
├── bin/redis/                  自带的 redis-server / redis-cli
├── db/                         ★ 本项目证券业务表（新建环境必导）
│   ├── business_schema.sql     15 张业务表结构
│   └── business_seed.sql       标的 / 新股 / 交易员 / 客服问答基础数据
├── runtime/                    日志：redis.log / socket.log / tick.log
└── start-realtime.sh           实时链路一键启停
```

---

## 4. 五步跑起来

### 4.1 导入数据库

```bash
mysql -uroot -p -e "CREATE DATABASE stock DEFAULT CHARSET utf8mb4;"

# ① 框架表（后台菜单、管理员、会员）
mysql -uroot -p stock < fastadmin/fastadmin.sql

# ② 项目业务表结构（fastadmin.sql 里没有，必须导！）
mysql -uroot -p stock < db/business_schema.sql

# ③ 基础数据（257 个标的、新股、交易员、客服问答）
mysql -uroot -p stock < db/business_seed.sql
```

> 无 mysql 客户端时可用 Adminer：把 `fastadmin/public/adminer.php` 放到 Web 目录后浏览器打开（本项目已自带，见第 7 节）。

导入后应有 **37 张表**（22 框架 + 15 业务），`fa_stock` 有 **257** 行（东京 230 + 美股 25 + 加密 2）。

### 4.2 配置并访问后端

编辑 `fastadmin/.env`：

```ini
[database]
hostname = 127.0.0.1
database  = stock
username  = root
password  = 123456
hostport  = 3306
prefix    = fa_
```

把 Web 根目录指向 `fastadmin/public`，伪静态用官方规则：

```nginx
# Nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ { fastcgi_pass 127.0.0.1:9000; include fastcgi.conf; }
```

Apache 用 `public/.htaccess`（仓库已有，需开启 `mod_rewrite`）。

macOS + Laravel Herd：在 Herd 里把项目目录 link 到 `stock.test` 即可（仓库含 `public/LocalValetDriver.php`）。

验证：

```bash
curl http://stock.test/api/market/lists?limit=1       # 返回 JSON 且 code=1
```

### 4.3 启动前端 H5

```bash
cd web
npm install
npm run dev          # http://localhost:5173
```

`vite.config.js` 已配置代理，**不要手填后端 IP**：

```js
proxy: {
  '/api': { target: 'http://stock.test', changeOrigin: true },   // 改成你的域名
  '/ws' : { target: 'ws://127.0.0.1:9502', ws: true }
}
```

打包：`npm run build`（产物在 `web/dist`）。

### 4.4 启动实时行情（可选，但推荐）

```bash
cd socket && npm install      # 安装 ws / redis 依赖

cd ..
./start-realtime.sh start     # 依次拉起 Redis → WebSocket → tick 推送
./start-realtime.sh status    # 查看三者状态
./start-realtime.sh stop      # 全部停止
```

成功后移动端任一股票详情页右上角会显示绿色「● 实时」，切「分时 / 1 分 K」可看到数据逐秒跳动。
未启动也不影响使用，页面会自动降级为 HTTP 轮询数据（右上角显示「○ 离线」）。

### 4.5 同步行情数据

```bash
cd fastadmin
php think sync quote --market=TSE        # 刷东京市场报价（230 只约 6 分钟）
php think sync quote --code=7203         # 只刷一只
php think sync kline --code=7203         # 拉该股日线（默认 90 根，必须带 --code，一次一只）
php think sync index                     # 首页五大指数
php think sync crypto --kline=1          # BTC/ETH/USDT 报价 + K线
php think sync all                       # 全量（含逐只日线，最慢，别做定时任务）
```

| 命令 | 写进哪张表 | 用途 |
| --- | --- | --- |
| `sync quote` | `fa_stock.price/change_rate/volume` | 列表上的现价与涨跌 |
| `sync kline` | `fa_stock_kline`（`period=1d`） | 详情页的 K 线图 |
| `sync index` / `sync crypto` | `fa_stock` | 指数、加密币报价 |

> `sync kline` 一次只支持一只，批量用 `./tools/sync_kline_batch.sh -n 50`（见下）。
>
> **数据量提醒**：`1d` 日线不会增长（每只恒定 90 根，覆盖写入）；`1m` 分钟线由 `quote:tick` 实时进程每分钟写一行、**只增不减**。用 `./tools/clean_kline.sh -d 30` 清理 30 天前的分钟线，建议挂每日定时任务（详见 [宝塔文档 8.4](docs/宝塔运行文档.md)）。
> 定时任务与命令速查见 [宝塔运行文档 第 8 节](docs/宝塔运行文档.md)。

历史数据写入 `fa_stock`、`fa_stock_kline`；没有配置外部 Key 时会退化为内置演示数据（可用）。

---

## 5. 默认账号

| 角色 | 入口 | 账号 | 密码 |
| --- | --- | --- | --- |
| 后台管理 | http://stock.test/ujWLHBNEfM.php/index/login | `admin` | `123456` |
| App 会员 | http://localhost:5173 | `13800000000`（或 `demo`） | `123456` |

会员账号已预置资金（JPY 1,000,000 / USD 10,000 / USDT 10,000）；后台入口文件名是随机串，部署后以实际文件名访问。

---

## 6. 配置文件速查

| 文件 | 作用 |
| --- | --- |
| `fastadmin/.env` | 数据库连接 |
| `fastadmin/application/extra/redis.php` | Redis 地址与 WS 端口（需与 socket 一致） |
| `fastadmin/application/extra/market.php` | 行情数据源 Key：iTick / Twelve Data / CoinGecko |
| `fastadmin/application/extra/payee.php` | **充值收款账户、各链收款地址、币种汇率、单笔限额** |
| `fastadmin/application/extra/lang.php` | 语言列表开关（增删改语言） |
| `web/vite.config.js` | 后端域名代理 `/api`、`/ws` |
| `socket/server.js` | 环境变量 `WS_PORT`（默认 9502）、`REDIS_URL` |

---

## 7. 常用命令

```bash
# 实时链路
./start-realtime.sh start | stop | restart | status

# 单独看日志
tail -f runtime/tick.log        # 行情 tick
tail -f runtime/socket.log      # WebSocket 服务
tail -f runtime/redis/redis.log # Redis

# 缓存清理（改动 language/payee 等配置后执行）
cd fastadmin && php think clear --cache --temp

# 网页版数据库管理
open http://stock.test/adminer.php   # 服务器填 127.0.0.1，账号 root/123456
```

---

## 8. 数据表说明

| 分类 | 表 |
| --- | --- |
| 会员 | `fa_user` `fa_user_verify`（实名）`fa_user_token` |
| 资金 | `fa_account`（多币种账户）`fa_transaction`（充值/提现流水）`fa_bank_card` `fa_crypto_wallet` |
| 交易 | `fa_order`（委托）`fa_position`（持仓）`fa_trader`（交易员，跟单用）`fa_follow` |
| 行情 | `fa_stock` `fa_stock_kline`（含 period=1m 实时归档）`fa_stock_favorite` |
| 新股 | `fa_ipo` `fa_ipo_apply` |
| 其他 | `fa_support`（客服问答） |

充值收款账户与各链收款地址**不要写死在代码里**，统一在 `application/extra/payee.php` 配置（含汇率与单笔限额）。


---

## 9. 常见问题排查

**① 接口 500 / 提示"缺少 php redis 扩展"**
给 PHP 装上 redis 扩展，或在 `QuoteStream::redis()` 里改用 predis；`php think quote:tick` 依赖它，不装不影响普通接口。

**② 后端提示语言变成看不懂的乌兹别克语**
语言包来自旧模板，已重写为 `application/api/lang/{zh-cn,ja-jp,en-us}`。若仍出现，执行 `php think clear --cache` 清理 runtime 缓存。

**③ 前端页面白屏 / 401**
- 检查 `vite.config.js` 里 `/api` 代理域名是否可达
- 检查是否登录：`localStorage.stock_token`
- 后端 CORS 由 `Api::_initialize()` 统一处理，通常无需改

**④ WebSocket 连不上（右上角一直「○ 离线」）**
`./start-realtime.sh status` 查看三个组件；确认 6379 / 9502 端口未被占用；`socket/server.js` 的 `WS_PORT` 与 `extra/redis.php` 的 `ws_port` 一致。

**⑤ 充值上传收据失败**
检查 `fastadmin/public/uploads/` 有写权限；PHP 的 `upload_max_filesize ≥ 8M`；接口为 `POST /api/common/upload`（需登录）。

**⑥ 看不到行情数据**
先执行 `php think sync all`；`fa_stock` 应有 257 行；外部数据源 Key 在 `extra/market.php`，留空则使用内置演示数据（页面顶部会提示"当前展示演示数据"）。

**⑦ 后台左侧没有菜单 / 菜单不对**
菜单来自 `fa_auth_rule`（`ismenu=1` 才显示）。`admin` 是超级管理员拥有全部权限；改完记得 `php think clear --cache` 并重新登录。

---

## 10. 新增一门语言（练习用）

1. 后端：`application/api/lang/` 下复制 `zh-cn` 三件套改名为 `ko-kr`
2. `application/extra/lang.php` 的 `languages` 增加一行 `['code'=>'ko','pack'=>'ko-kr','name'=>'한국어','enable'=>true]`
3. 前端：`web/src/locales/messages/ko.js`（导出 `name / pack / order / messages`）

前端用 `import.meta.glob` 自动扫描，放进去即生效；语言列表以 `/api/index/langs` 返回的启用项为准。
