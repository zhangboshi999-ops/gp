-- ------------------------------------------------------------
-- 证券交易业务表（新项目）
-- 数据库：stock    表前缀：fa_
-- ------------------------------------------------------------

DROP TABLE IF EXISTS `fa_stock`;
CREATE TABLE `fa_stock` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '证券代码',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '证券名称',
  `market` varchar(30) NOT NULL DEFAULT 'TSE' COMMENT '所属市场:TSE=东京证券交易所,NY=纽交所,NASDAQ=纳斯达克,CRYPTO=加密货币',
  `type` enum('stock','crypto','index','sector') NOT NULL DEFAULT 'stock' COMMENT '类型:stock=股票,crypto=加密货币,index=指数,sector=板块',
  `currency` varchar(10) NOT NULL DEFAULT 'JPY' COMMENT '计价币种:JPY=日元,USD=美元',
  `price` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '最新价',
  `prev_close` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '昨收价',
  `open` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '今开',
  `high` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '最高',
  `low` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '最低',
  `change_amount` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '涨跌额',
  `change_rate` decimal(10,4) NOT NULL DEFAULT 0 COMMENT '涨跌幅(%)',
  `volume` bigint NOT NULL DEFAULT 0 COMMENT '成交量(股)',
  `turnover` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '成交额',
  `total_shares` bigint NOT NULL DEFAULT 0 COMMENT '总股本',
  `lot_size` int unsigned NOT NULL DEFAULT 100 COMMENT '每手股数',
  `sector` varchar(50) NOT NULL DEFAULT '' COMMENT '所属行业/板块',
  `source` varchar(20) NOT NULL DEFAULT '' COMMENT '数据来源:twelvedata/coingecko/manual',
  `symbol` varchar(50) NOT NULL DEFAULT '' COMMENT '数据源代码,如 7203.T / BTC/USD / bitcoin',
  `market_cap` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '总市值',
  `pe_ratio` decimal(12,4) NOT NULL DEFAULT 0 COMMENT '市盈率PE',
  `pb_ratio` decimal(12,4) NOT NULL DEFAULT 0 COMMENT '市净率PB',
  `dividend_yield` decimal(10,4) NOT NULL DEFAULT 0 COMMENT '股息率(%)',
  `turnover_rate` decimal(10,4) NOT NULL DEFAULT 0 COMMENT '换手率(%)',
  `amplitude` decimal(10,4) NOT NULL DEFAULT 0 COMMENT '振幅(%)',
  `high_52w` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '52周最高',
  `low_52w` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '52周最低',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否热门:0=否,1=是',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重(排序)',
  `status` enum('normal','suspended','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态:normal=交易中,suspended=停牌,hidden=隐藏',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`),
  KEY `idx_market` (`market`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='证券标的表';

DROP TABLE IF EXISTS `fa_stock_kline`;
CREATE TABLE `fa_stock_kline` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `stock_id` int unsigned NOT NULL DEFAULT 0 COMMENT '证券ID',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '证券代码',
  `period` varchar(10) NOT NULL DEFAULT '1d' COMMENT '周期:1m=1分钟,5m,15m,1h,1d=日线,1w=周线',
  `time` bigint(16) NOT NULL DEFAULT 0 COMMENT 'K线时间',
  `open` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '开盘',
  `high` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '最高',
  `low` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '最低',
  `close` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '收盘',
  `volume` bigint NOT NULL DEFAULT 0 COMMENT '成交量',
  PRIMARY KEY (`id`),
  KEY `idx_stock_period` (`stock_id`,`period`),
  KEY `idx_code_period_time` (`code`,`period`,`time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='K线行情表';

DROP TABLE IF EXISTS `fa_stock_favorite`;
CREATE TABLE `fa_stock_favorite` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `stock_id` int unsigned NOT NULL DEFAULT 0 COMMENT '证券ID',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_stock` (`user_id`,`stock_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='自选(收藏)表';

DROP TABLE IF EXISTS `fa_trader`;
CREATE TABLE `fa_trader` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '交易员名称',
  `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `country` varchar(30) NOT NULL DEFAULT '' COMMENT '国家/地区',
  `roi_rate` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '30日收益率(%)',
  `win_rate` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '胜率(%)',
  `followers` int unsigned NOT NULL DEFAULT 0 COMMENT '跟随人数',
  `total_profit` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '累计收益',
  `min_amount` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '最低跟单金额',
  `intro` varchar(255) NOT NULL DEFAULT '' COMMENT '简介',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='交易员表';

DROP TABLE IF EXISTS `fa_follow`;
CREATE TABLE `fa_follow` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `trader_id` int unsigned NOT NULL DEFAULT 0 COMMENT '交易员ID',
  `amount` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '跟单金额',
  `profit` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '跟单收益',
  `status` enum('following','paused','stopped') NOT NULL DEFAULT 'following' COMMENT '状态:following=跟随中,paused=暂停,stopped=已停止',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_trader` (`user_id`,`trader_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='跟单表';

DROP TABLE IF EXISTS `fa_order`;
CREATE TABLE `fa_order` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_no` varchar(32) NOT NULL DEFAULT '' COMMENT '委托单号',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `stock_id` int unsigned NOT NULL DEFAULT 0 COMMENT '证券ID',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '证券代码',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '证券名称',
  `currency` varchar(10) NOT NULL DEFAULT 'JPY' COMMENT '计价币种',
  `side` enum('buy','sell') NOT NULL DEFAULT 'buy' COMMENT '方向:buy=买入,sell=卖出',
  `type` enum('market','limit') NOT NULL DEFAULT 'limit' COMMENT '类型:market=市价,limit=限价',
  `price` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '委托价',
  `quantity` int unsigned NOT NULL DEFAULT 0 COMMENT '委托数量',
  `amount` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '委托金额',
  `filled_quantity` int unsigned NOT NULL DEFAULT 0 COMMENT '已成交数量',
  `filled_amount` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '已成交金额',
  `fee` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '手续费',
  `status` enum('pending','partial','filled','canceled','rejected') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待成交,partial=部分成交,filled=已成交,canceled=已撤单,rejected=已拒绝',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `finishtime` bigint(16) DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_no` (`order_no`),
  KEY `idx_user` (`user_id`),
  KEY `idx_stock` (`stock_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='委托订单表';

DROP TABLE IF EXISTS `fa_position`;
CREATE TABLE `fa_position` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `stock_id` int unsigned NOT NULL DEFAULT 0 COMMENT '证券ID',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '证券代码',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '证券名称',
  `currency` varchar(10) NOT NULL DEFAULT 'JPY' COMMENT '计价币种',
  `quantity` int unsigned NOT NULL DEFAULT 0 COMMENT '持仓数量',
  `available` int unsigned NOT NULL DEFAULT 0 COMMENT '可卖数量',
  `cost_price` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '成本价',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_stock` (`user_id`,`stock_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='持仓表';

DROP TABLE IF EXISTS `fa_account`;
CREATE TABLE `fa_account` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `currency` varchar(10) NOT NULL DEFAULT 'JPY' COMMENT '币种:JPY=日元,USD=美元,USDT=泰达币',
  `balance` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '可用余额',
  `frozen` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '冻结余额',
  `total_deposit` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '累计充值',
  `total_withdraw` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '累计提现',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_currency` (`user_id`,`currency`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='用户资金账户表';

DROP TABLE IF EXISTS `fa_transaction`;
CREATE TABLE `fa_transaction` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `sn` varchar(32) NOT NULL DEFAULT '' COMMENT '流水单号',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `type` enum('deposit','withdraw','transfer') NOT NULL DEFAULT 'deposit' COMMENT '类型:deposit=充值,withdraw=提现,transfer=内部划转',
  `method` varchar(30) NOT NULL DEFAULT 'bank' COMMENT '渠道:bank=银行卡,btc=比特币,eth=以太坊,usdt=USDT,internal=站内划转',
  `currency` varchar(10) NOT NULL DEFAULT 'JPY' COMMENT '币种',
  `amount` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '金额',
  `fee` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '手续费',
  `bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称',
  `branch_name` varchar(100) NOT NULL DEFAULT '' COMMENT '支行名称',
  `account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '账号/钱包地址',
  `account_name` varchar(50) NOT NULL DEFAULT '' COMMENT '户名',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '链上地址',
  `txid` varchar(128) NOT NULL DEFAULT '' COMMENT '交易哈希',
  `to_user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '收款会员ID(划转)',
  `status` enum('pending','success','rejected') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待审核,success=成功,rejected=已驳回',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `checkremark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `checktime` bigint(16) DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sn` (`sn`),
  KEY `idx_user_type` (`user_id`,`type`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='资金流水表';

DROP TABLE IF EXISTS `fa_bank_card`;
CREATE TABLE `fa_bank_card` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称',
  `branch_name` varchar(100) NOT NULL DEFAULT '' COMMENT '支行名称',
  `account_type` varchar(20) NOT NULL DEFAULT '普通' COMMENT '账户类型:普通/当座',
  `account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `account_name` varchar(50) NOT NULL DEFAULT '' COMMENT '户名',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认:0=否,1=是',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='银行卡表';

DROP TABLE IF EXISTS `fa_crypto_wallet`;
CREATE TABLE `fa_crypto_wallet` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `chain` varchar(20) NOT NULL DEFAULT 'BTC' COMMENT '链:BTC=比特币,ETH=以太坊,USDT=泰达币',
  `address` varchar(128) NOT NULL DEFAULT '' COMMENT '钱包地址',
  `label` varchar(50) NOT NULL DEFAULT '' COMMENT '备注名',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='加密钱包地址表';

DROP TABLE IF EXISTS `fa_ipo`;
CREATE TABLE `fa_ipo` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '证券代码',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '证券名称',
  `market` varchar(30) NOT NULL DEFAULT 'TSE' COMMENT '上市市场',
  `issue_price` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '发行价',
  `total_shares` bigint NOT NULL DEFAULT 0 COMMENT '发行总量',
  `min_quantity` int unsigned NOT NULL DEFAULT 100 COMMENT '最小认购数量',
  `max_quantity` int unsigned NOT NULL DEFAULT 0 COMMENT '最大认购数量',
  `start_time` bigint(16) DEFAULT NULL COMMENT '认购开始时间',
  `end_time` bigint(16) DEFAULT NULL COMMENT '认购结束时间',
  `list_date` bigint(16) DEFAULT NULL COMMENT '上市日期',
  `intro` text COMMENT '公司简介',
  `status` enum('upcoming','ongoing','closed','listed') NOT NULL DEFAULT 'ongoing' COMMENT '状态:upcoming=待开始,ongoing=认购中,closed=已截止,listed=已上市',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新股发行表';

DROP TABLE IF EXISTS `fa_ipo_apply`;
CREATE TABLE `fa_ipo_apply` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `apply_no` varchar(32) NOT NULL DEFAULT '' COMMENT '认购单号',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `ipo_id` int unsigned NOT NULL DEFAULT 0 COMMENT '新股ID',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '证券代码',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '证券名称',
  `quantity` int unsigned NOT NULL DEFAULT 0 COMMENT '认购数量',
  `price` decimal(20,4) NOT NULL DEFAULT 0 COMMENT '发行价',
  `amount` decimal(20,2) NOT NULL DEFAULT 0 COMMENT '认购金额',
  `allotment` int unsigned NOT NULL DEFAULT 0 COMMENT '中签数量',
  `status` enum('pending','won','partial','lost','canceled') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待公布,won=已中签,partial=部分中签,lost=未中签,canceled=已撤销',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `checktime` bigint(16) DEFAULT NULL COMMENT '公布时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_apply_no` (`apply_no`),
  KEY `idx_user` (`user_id`),
  KEY `idx_ipo` (`ipo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='新股认购记录表';

DROP TABLE IF EXISTS `fa_user_verify`;
CREATE TABLE `fa_user_verify` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT 0 COMMENT '会员ID',
  `real_name` varchar(50) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `id_type` varchar(20) NOT NULL DEFAULT 'idcard' COMMENT '证件类型:idcard=身份证,passport=护照,license=驾照',
  `id_number` varchar(50) NOT NULL DEFAULT '' COMMENT '证件号码',
  `country` varchar(50) NOT NULL DEFAULT '日本' COMMENT '国家/地区',
  `front_image` varchar(255) NOT NULL DEFAULT '' COMMENT '证件正面',
  `back_image` varchar(255) NOT NULL DEFAULT '' COMMENT '证件反面',
  `hand_image` varchar(255) NOT NULL DEFAULT '' COMMENT '手持证件',
  `status` enum('pending','pass','reject') NOT NULL DEFAULT 'pending' COMMENT '状态:pending=待审核,pass=已通过,reject=已驳回',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '审核备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `checktime` bigint(16) DEFAULT NULL COMMENT '审核时间',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='实名认证表';

DROP TABLE IF EXISTS `fa_support`;
CREATE TABLE `fa_support` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` enum('faq','notice') NOT NULL DEFAULT 'faq' COMMENT '类型:faq=常见问题,notice=公告',
  `title` varchar(255) NOT NULL DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `lang` varchar(10) NOT NULL DEFAULT 'ja' COMMENT '语言:ja=日文,zh=中文,en=英文',
  `weigh` int NOT NULL DEFAULT 0 COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='客服帮助/公告表';
