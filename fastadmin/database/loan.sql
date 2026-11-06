-- ----------------------------
-- 财政支持（请款）模块数据表
-- ----------------------------

-- ----------------------------
-- Table structure for fa_loan_apply
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fa_loan_apply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '你的名字',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '电话号码',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮件',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '所需金额',
  `purpose` varchar(500) NOT NULL DEFAULT '' COMMENT '目的',
  `status` enum('0','1','2','3','4') NOT NULL DEFAULT '0' COMMENT '状态:0=申请中,1=申请已通过,2=已拒绝,3=提款处理中,4=提款完成',
  `term` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '还款期数',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '5.00' COMMENT '年利率(%)',
  `payment` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '每次付款',
  `interest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '利息',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '应还共计',
  `withdraw_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已提款金额',
  `bank_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '银行卡ID',
  `checkremark` varchar(500) NOT NULL DEFAULT '' COMMENT '审核备注',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核管理员',
  `checktime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `withdrawtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提款时间',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='财政支持申请';

-- ----------------------------
-- Table structure for fa_loan_bank
-- ----------------------------
CREATE TABLE IF NOT EXISTS `fa_loan_bank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `account_name` varchar(50) NOT NULL DEFAULT '' COMMENT '账户名称',
  `account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称',
  `branch_code` varchar(30) NOT NULL DEFAULT '' COMMENT '分行号',
  `branch_name` varchar(100) NOT NULL DEFAULT '' COMMENT '分支机构名称',
  `account_type` varchar(20) NOT NULL DEFAULT '通常' COMMENT '账户类型',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='银行卡';
