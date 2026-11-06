-- MySQL dump 10.13  Distrib 9.7.2, for macos15 (arm64)
--
-- Host: 127.0.0.1    Database: fastadmin
-- ------------------------------------------------------
-- Server version	9.7.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fa_admin`
--

DROP TABLE IF EXISTS `fa_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_admin` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '昵称',
  `password` varchar(32) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '密码盐',
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '头像',
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '手机号码',
  `loginfailure` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `logintime` bigint DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '登录IP',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `token` varchar(59) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'Session标识',
  `status` varchar(30) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_admin`
--

LOCK TABLES `fa_admin` WRITE;
/*!40000 ALTER TABLE `fa_admin` DISABLE KEYS */;
INSERT INTO `fa_admin` VALUES (1,'admin','Admin','f68a193093f54d26cf7ed87a6a878ee1','de168b','/assets/img/avatar.png','admin@admin.com','',0,1791526878,'127.0.0.1',1491635035,1791530264,'','normal'),(2,'admin1','hello','6e282c85a9cc2739a95fc6fa4623f2e1','XuoYRc','','1111@qq.com','13898988989',0,1791530278,'127.0.0.1',NULL,1791530278,'b05acb87-a5d5-4e73-89bf-c2f7dd9c3d53','normal');
/*!40000 ALTER TABLE `fa_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_admin_log`
--

DROP TABLE IF EXISTS `fa_admin_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_admin_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `username` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '管理员名字',
  `url` varchar(1500) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '操作页面',
  `title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '日志标题',
  `content` longtext COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `ip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'User-Agent',
  `createtime` bigint DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`),
  KEY `name` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=160 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理员日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_admin_log`
--

LOCK TABLES `fa_admin_log` WRITE;
/*!40000 ALTER TABLE `fa_admin_log` DISABLE KEYS */;
INSERT INTO `fa_admin_log` VALUES (1,0,'Unknown','/ujWLHBNEfM.php/index/login','登录','{\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"phjl\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343269),(2,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"phjl\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343269),(3,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"phjl\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343286),(4,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"nmkr\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343295),(5,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"4ea2\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343302),(6,0,'Unknown','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/general/config?ref=addtabs','','{\"url\":\"\\/ujWLHBNEfM.php\\/general\\/config?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"hlgn\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343667),(7,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/general/config?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/general\\/config?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"8cig\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791343676),(8,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"account\":\"fa_test\",\"password\":\"***\",\"keeplogin\":\"1\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791344869),(9,7,'fa_test','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"fa_test\",\"password\":\"***\",\"keeplogin\":\"1\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791345250),(10,7,'fa_test','/ujWLHBNEfM.php/loan/apply/pass?ids=1&dialog=1','财政支持 / 财政支持申请','{\"ids\":\"1\",\"dialog\":\"1\",\"row\":{\"amount\":\"100000\",\"checkremark\":\"资料齐全，通过\"}}','127.0.0.1','curl/8.7.1',1791345264),(11,7,'fa_test','/ujWLHBNEfM.php/loan/apply/pay?ids=1&dialog=1','财政支持 / 财政支持申请','{\"ids\":\"1\",\"dialog\":\"1\",\"row\":{\"checkremark\":\"已放款\"}}','127.0.0.1','curl/8.7.1',1791345286),(12,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"email\":\"\",\"mobile\":\"\",\"avatar\":\"\",\"level\":\"1\",\"gender\":\"0\",\"birthday\":\"\",\"bio\":\"\",\"money\":\"100000.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791346053),(13,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"email\":\"\",\"mobile\":\"\",\"avatar\":\"\",\"level\":\"1\",\"gender\":\"0\",\"birthday\":\"\",\"bio\":\"\",\"money\":\"100000.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791346054),(14,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"email\":\"\",\"mobile\":\"\",\"avatar\":\"\",\"level\":\"1\",\"gender\":\"0\",\"birthday\":\"\",\"bio\":\"\",\"money\":\"100000.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791346055),(15,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"email\":\"\",\"mobile\":\"\",\"avatar\":\"\",\"level\":\"1\",\"gender\":\"0\",\"birthday\":\"\",\"bio\":\"\",\"money\":\"100000.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791346055),(16,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"email\":\"\",\"mobile\":\"\",\"avatar\":\"\",\"level\":\"1\",\"gender\":\"0\",\"birthday\":\"\",\"bio\":\"\",\"money\":\"100000.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791346055),(17,1,'admin','/ujWLHBNEfM.php/loan/apply/pass?ids=2&dialog=1','财政支持 / 财政支持申请','{\"ids\":\"2\",\"dialog\":\"1\",\"row\":{\"amount\":\"10000.00\",\"checkremark\":\"jkjk\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791346625),(18,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\",\"keeplogin\":\"1\"}','127.0.0.1','curl/8.7.1',1791346737),(19,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\",\"keeplogin\":\"1\"}','127.0.0.1','curl/8.7.1',1791346833),(20,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791347236),(21,1,'admin','/ujWLHBNEfM.php/loan/apply/edit/ids/2?dialog=1','财政支持 / 财政支持申请 / 编辑','{\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"name\":\"11111\",\"mobile\":\"11111\",\"email\":\"abc@qq.com\",\"amount\":\"10000.00\",\"purpose\":\"1111\",\"status\":\"4\",\"term\":\"0\",\"interest_rate\":\"5.00\",\"payment\":\"0.00\",\"interest\":\"0.00\",\"total\":\"0.00\",\"withdraw_amount\":\"0.00\",\"bank_id\":\"0\",\"checkremark\":\"jkjk\",\"checktime\":\"2026-10-07 12:17:05\",\"withdrawtime\":\"2026-11-05 12:28:57\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791347342),(22,1,'admin','/ujWLHBNEfM.php/loan/apply/pass?ids=3&dialog=1','财政支持 / 财政支持申请','{\"ids\":\"3\",\"dialog\":\"1\",\"row\":{\"amount\":\"111.00\",\"checkremark\":\"房贷首付水淀粉\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791347528),(23,1,'admin','/ujWLHBNEfM.php/loan/apply/pay?ids=3&dialog=1','财政支持 / 财政支持申请','{\"ids\":\"3\",\"dialog\":\"1\",\"row\":{\"checkremark\":\"房贷首付的身份\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791347553),(24,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"email\":\"\",\"mobile\":\"\",\"avatar\":\"\",\"level\":\"1\",\"gender\":\"0\",\"birthday\":\"\",\"bio\":\"\",\"money\":\"111110.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791347713),(25,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348119),(26,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348121),(27,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348121),(28,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348121),(29,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348121),(30,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348121),(31,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348122),(32,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348122),(33,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348122),(34,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348122),(35,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"0.00\",\"score\":\"1000000\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348122),(36,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348154),(37,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348548),(38,1,'admin','/ujWLHBNEfM.php/user/user/edit?ids=2','会员管理 / 会员管理 / 编辑','{\"ids\":\"2\",\"row\":{\"username\":\"admin4\",\"nickname\":\"管理员4\",\"group_id\":\"1\",\"status\":\"normal\",\"money\":\"0\",\"score\":\"0\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348548),(39,1,'admin','/ujWLHBNEfM.php/user/user/edit?ids=2','会员管理 / 会员管理 / 编辑','{\"ids\":\"2\",\"row\":{\"id\":\"2\",\"username\":\"admin4\",\"nickname\":\"管理员4\",\"group_id\":\"1\",\"status\":\"normal\",\"money\":\"0\",\"score\":\"0\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348597),(40,1,'admin','/ujWLHBNEfM.php/user/user/edit?ids=2','会员管理 / 会员管理 / 编辑','{\"ids\":\"2\",\"row\":{\"id\":\"2\",\"username\":\"admin4\",\"nickname\":\"管理员4\",\"group_id\":\"1\",\"status\":\"normal\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348597),(41,1,'admin','/ujWLHBNEfM.php/user/user/edit/ids/5?dialog=1','会员管理 / 会员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"id\":\"5\",\"group_id\":\"1\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"password\":\"***\",\"money\":\"111110.00\",\"score\":\"0\",\"successions\":\"1\",\"maxsuccessions\":\"1\",\"prevtime\":\"2026-10-07 12:04:33\",\"logintime\":\"2026-10-07 12:04:33\",\"loginip\":\"127.0.0.1\",\"loginfailure\":\"0\",\"joinip\":\"127.0.0.1\",\"jointime\":\"2026-10-07 12:04:33\",\"status\":\"normal\"},\"ids\":\"5\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791348614),(42,1,'admin','/ujWLHBNEfM.php/user/user/edit?ids=2','会员管理 / 会员管理 / 编辑','{\"ids\":\"2\",\"row\":{\"id\":\"2\",\"username\":\"admin4\",\"nickname\":\"管理员4\",\"email\":\"notanemail\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348651),(43,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348962),(44,1,'admin','/ujWLHBNEfM.php/loan/apply/edit?ids=4','财政支持 / 财政支持申请 / 编辑','{\"ids\":\"4\",\"row\":{\"user_id\":\"5\",\"name\":\"11111\",\"mobile\":\"13800000005\",\"email\":\"a@b.com\",\"amount\":\"1000\",\"purpose\":\"测试\",\"status\":\"0\",\"term\":\"12\",\"interest_rate\":\"5\",\"payment\":\"85\",\"interest\":\"20\",\"total\":\"1020\",\"withdraw_amount\":\"0\",\"checkremark\":\"\",\"checktime\":\"\",\"withdrawtime\":\"\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791348974),(45,1,'admin','/ujWLHBNEfM.php/loan/apply/edit?ids=4','财政支持 / 财政支持申请 / 编辑','{\"ids\":\"4\",\"row\":{\"user_id\":\"5\",\"name\":\"11111\",\"mobile\":\"13800000005\",\"email\":\"a@b.com\",\"amount\":\"1000\",\"purpose\":\"测试\",\"status\":\"0\",\"term\":\"12\",\"interest_rate\":\"5\",\"payment\":\"85\",\"interest\":\"20\",\"total\":\"1020\",\"withdraw_amount\":\"0\",\"checkremark\":\"\",\"checktime\":\"\",\"withdrawtime\":\"\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791349035),(46,1,'admin','/ujWLHBNEfM.php/loan/apply/edit?ids=4','财政支持 / 财政支持申请 / 编辑','{\"ids\":\"4\",\"row\":{\"user_id\":\"2\",\"name\":\"11111\",\"checktime\":\"2026-10-07 10:00:00\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791349035),(47,1,'admin','/ujWLHBNEfM.php/loan/apply/edit?ids=4','财政支持 / 财政支持申请 / 编辑','{\"ids\":\"4\",\"row\":{\"user_id\":\"5\",\"status\":\"0\",\"checktime\":\"\",\"withdrawtime\":\"\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791349135),(48,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791349785),(49,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791350693),(50,1,'admin','/ujWLHBNEfM.php/loan/repayment/add','财政支持 / 还款记录 / 添加','{\"row\":{\"apply_id\":\"1\",\"period_no\":\"1\",\"amount\":\"8561\",\"due_time\":\"2026-11-10\",\"payee_bank_name\":\"三菱UFJ銀行\",\"payee_branch_name\":\"東京営業部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"サンプル株式会社\",\"remark\":\"第一期还款\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791350711),(51,1,'admin','/ujWLHBNEfM.php/loan/repayment/add','财政支持 / 还款记录 / 添加','{\"row\":{\"apply_id\":\"1\",\"period_no\":\"1\",\"amount\":\"1\",\"due_time\":\"2026-11-10\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791350711),(52,1,'admin','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','财政支持 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"4\",\"period_no\":\"1\",\"amount\":\"1000\",\"due_time\":\"2026-10-02\",\"payee_bank_name\":\"三菱UFJ銀行\",\"payee_branch_name\":\"東京営業部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"サンプル株式会社\",\"remark\":\"规划规划\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791350742),(53,1,'admin','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','财政支持 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"2\",\"period_no\":\"1\",\"amount\":\"10000\",\"due_time\":\"2026-10-07\",\"payee_bank_name\":\"三菱UFJ銀行\",\"payee_branch_name\":\"東京営業部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"サンプル株式会社\",\"remark\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791350845),(54,1,'admin','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','财政支持 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"3\",\"period_no\":\"1\",\"amount\":\"\",\"due_time\":\"2026-10-24\",\"payee_bank_name\":\"三菱UFJ銀行\",\"payee_branch_name\":\"東京営業部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"サンプル株式会社\",\"remark\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791350880),(55,1,'admin','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','财政支持 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"3\",\"period_no\":\"1\",\"amount\":\"10000\",\"due_time\":\"2026-10-24\",\"payee_bank_name\":\"三菱UFJ銀行\",\"payee_branch_name\":\"東京営業部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"サンプル株式会社\",\"remark\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791350886),(56,1,'admin','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','财政支持 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"3\",\"period_no\":\"2\",\"amount\":\"1000\",\"due_time\":\"2026-10-09\",\"payee_bank_name\":\"三菱UFJ銀行\",\"payee_branch_name\":\"東京営業部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"サンプル株式会社\",\"remark\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791350924),(57,1,'admin','/ujWLHBNEfM.php/loan/repayment/check?ids=1','财政支持 / 还款记录 / 批复还款凭证','{\"ids\":\"1\",\"row\":{\"checkremark\":\"已确认到账\"},\"action\":\"pass\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791351113),(58,1,'admin','/ujWLHBNEfM.php/loan/repayment/check?ids=3','财政支持 / 还款记录 / 批复还款凭证','{\"ids\":\"3\",\"row\":{\"checkremark\":\"凭证不清\"},\"action\":\"pass\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791351113),(59,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791351361),(60,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791351428),(61,1,'admin','/ujWLHBNEfM.php/loan/repayment/check?ids=3','财政支持 / 还款记录 / 批复还款凭证','{\"ids\":\"3\",\"row\":{\"checkremark\":\"\"},\"action\":\"pass\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791351742),(62,1,'admin','/ujWLHBNEfM.php/loan/repayment/check?ids=2&dialog=1','财政支持 / 还款记录 / 批复还款凭证','{\"ids\":\"2\",\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"checkremark\":\"\"},\"action\":\"pass\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791351940),(63,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352899),(64,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"100\",\"memo\":\"测试充值\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352926),(65,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"dec\",\"money\":\"50\",\"memo\":\"测试扣款\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352926),(66,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"dec\",\"money\":\"999999\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352926),(67,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"0\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352926),(68,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"10000\",\"memo\":\"11\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791352956),(69,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"10000\",\"memo\":\"11\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791352957),(70,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"10000\",\"memo\":\"11\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791352958),(71,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"10000\",\"memo\":\"11\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791352958),(72,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"10000\",\"memo\":\"11\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791352958),(73,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"10000\",\"memo\":\"11\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791352958),(74,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"100\",\"memo\":\"测试充值\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352990),(75,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"dec\",\"money\":\"50\",\"memo\":\"测试扣款\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791352990),(76,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"100\",\"memo\":\"测试充值\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791353052),(77,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"row\":{\"user_id\":\"5\",\"type\":\"dec\",\"money\":\"50\",\"memo\":\"测试扣款\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791353052),(78,1,'admin','/ujWLHBNEfM.php/user/moneylog/del','会员管理 / 会员余额记录 / 删除','{\"ids\":\"2,3\"}','127.0.0.1','curl/8.7.1',1791353088),(79,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"22\",\"memo\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353239),(80,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=2&ids=2&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"2\",\"ids\":\"2\",\"dialog\":\"1\",\"row\":{\"user_id\":\"2\",\"type\":\"inc\",\"money\":\"1000\",\"memo\":\"1000\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353309),(81,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791353392),(82,1,'admin','/ujWLHBNEfM.php/user/user/edit?ids=5','会员管理 / 会员管理 / 编辑','{\"ids\":\"5\",\"row\":{\"id\":\"5\",\"username\":\"admin1\",\"nickname\":\"admin1\",\"group_id\":\"1\",\"status\":\"normal\"},\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791353407),(83,1,'admin','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"5555\",\"memo\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353423),(84,1,'admin','/ujWLHBNEfM.php/auth/admin/edit/ids/2?dialog=1','权限管理 / 管理员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"group\":[\"4\"],\"row\":{\"username\":\"admin\",\"email\":\"1111@qq.com\",\"mobile\":\"13898988989\",\"nickname\":\"hello\",\"password\":\"***\",\"loginfailure\":\"0\",\"status\":\"normal\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353551),(85,1,'admin','/ujWLHBNEfM.php/auth/admin/edit/ids/2?dialog=1','权限管理 / 管理员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"group\":[\"4\"],\"row\":{\"username\":\"admin\",\"email\":\"1111@qq.com\",\"mobile\":\"13898988989\",\"nickname\":\"hello\",\"password\":\"***\",\"loginfailure\":\"0\",\"status\":\"normal\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353552),(86,1,'admin','/ujWLHBNEfM.php/auth/admin/edit/ids/2?dialog=1','权限管理 / 管理员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"group\":[\"4\"],\"row\":{\"username\":\"admin\",\"email\":\"1111@qq.com\",\"mobile\":\"13898988989\",\"nickname\":\"hello\",\"password\":\"***\",\"loginfailure\":\"0\",\"status\":\"normal\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353552),(87,1,'admin','/ujWLHBNEfM.php/auth/admin/edit/ids/2?dialog=1','权限管理 / 管理员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"group\":[\"4\"],\"row\":{\"username\":\"admin\",\"email\":\"1111@qq.com\",\"mobile\":\"13898988989\",\"nickname\":\"hello\",\"password\":\"***\",\"loginfailure\":\"0\",\"status\":\"normal\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353552),(88,1,'admin','/ujWLHBNEfM.php/auth/admin/edit/ids/2?dialog=1','权限管理 / 管理员管理 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"group\":[\"4\"],\"row\":{\"username\":\"admin1\",\"email\":\"1111@qq.com\",\"mobile\":\"13898988989\",\"nickname\":\"hello\",\"password\":\"***\",\"loginfailure\":\"0\",\"status\":\"normal\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353555),(89,1,'admin','/ujWLHBNEfM.php/auth/group/roletree','权限管理 / 角色组','{\"id\":\"4\",\"pid\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353565),(90,1,'admin','/ujWLHBNEfM.php/auth/group/edit/ids/4?dialog=1','权限管理 / 角色组 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"rules\":\"67,68,69,70,71,72,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,66\",\"pid\":\"1\",\"name\":\"二级管理组2\",\"status\":\"normal\"},\"ids\":\"4\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353586),(91,1,'admin','/ujWLHBNEfM.php/loan/apply/pass?ids=4&dialog=1','财政支持 / 财政支持申请','{\"ids\":\"4\",\"dialog\":\"1\",\"row\":{\"amount\":\"1000.00\",\"checkremark\":\"hjhjh\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353689),(92,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353721),(93,2,'admin1','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/loan/repayment?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/loan\\/repayment?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"yuxu\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353738),(94,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791353755),(95,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791354069),(96,0,'Unknown','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"pubf\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354237),(97,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"bptj\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354246),(98,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354248),(99,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354265),(100,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354268),(101,0,'Unknown','/ujWLHBNEfM.php/index/user/login','','{\"account\":\"tmpflow001\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791354280),(102,0,'Unknown','/ujWLHBNEfM.php/index/user/login','','{\"account\":\"tmpflow001\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791354312),(103,0,'Unknown','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354628),(104,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354631),(105,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354637),(106,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354643),(107,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354671),(108,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354681),(109,1,'admin','/ujWLHBNEfM.php/loan/repayment/del','财政支持 / 还款记录 / 删除','{\"action\":\"del\",\"ids\":\"5\",\"params\":\"\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354865),(110,1,'admin','/ujWLHBNEfM.php/loan/repayment/del','财政支持 / 还款记录 / 删除','{\"action\":\"del\",\"ids\":\"4\",\"params\":\"\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354867),(111,1,'admin','/ujWLHBNEfM.php/loan/repayment/del','财政支持 / 还款记录 / 删除','{\"action\":\"del\",\"ids\":\"3\",\"params\":\"\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354871),(112,1,'admin','/ujWLHBNEfM.php/loan/repayment/edit/ids/2?dialog=1','财政支持 / 还款记录 / 编辑','{\"dialog\":\"1\",\"row\":{\"period_no\":\"1\",\"amount\":\"1000.00\",\"due_time\":\"2026-10-02\",\"payee_bank_name\":\"111銀行\",\"payee_branch_name\":\"111111\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"1111111\",\"images\":\"\\/uploads\\/20261007\\/4804588043445744a494b40386ca609d.png\",\"remark\":\"规划规划发反反复复\",\"checkremark\":\"\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354918),(113,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354961),(114,2,'admin1','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/loan/apply?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/loan\\/apply?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"u3za\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791354993),(115,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791355272),(116,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355324),(117,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"4t8a\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355338),(118,1,'admin','/ujWLHBNEfM.php/auth/group/roletree','权限管理 / 角色组','{\"id\":\"4\",\"pid\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355362),(119,1,'admin','/ujWLHBNEfM.php/auth/group/edit/ids/4?dialog=1','权限管理 / 角色组 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"rules\":\"8,29,30,31,32,33,34,67,68,69,70,71,72,85,86,87,88,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,107,108,109,110,2,66\",\"pid\":\"1\",\"name\":\"二级管理组2\",\"status\":\"normal\"},\"ids\":\"4\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355374),(120,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355381),(121,0,'Unknown','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/auth/group?ref=addtabs','','{\"url\":\"\\/ujWLHBNEfM.php\\/auth\\/group?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"fwls\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355393),(122,2,'admin1','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/auth/group?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/auth\\/group?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"6cmy\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791355402),(123,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791371698),(124,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791371801),(125,2,'admin1','/ujWLHBNEfM.php/loan/apply/edit/ids/4?dialog=1','资金管理 / 资金申请 / 编辑','{\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"name\":\"张\",\"mobile\":\"13800000005\",\"email\":\"a@b.com\",\"amount\":\"1000.00\",\"purpose\":\"测试\",\"status\":\"3\",\"term\":\"12\",\"interest_rate\":\"5.00\",\"payment\":\"86.00\",\"interest\":\"32.00\",\"total\":\"1032.00\",\"withdraw_amount\":\"0.00\",\"checkremark\":\"hjhjh\",\"checktime\":\"2026-10-07 14:14:49\",\"withdrawtime\":\"2026-10-07 19:36:19\"},\"ids\":\"4\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791372980),(126,2,'admin1','/ujWLHBNEfM.php/loan/apply/edit/ids/3?dialog=1','资金管理 / 资金申请 / 编辑','{\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"name\":\"王\",\"mobile\":\"11112311897\",\"email\":\"111@gmail.com\",\"amount\":\"111.00\",\"purpose\":\"111\",\"status\":\"4\",\"term\":\"12\",\"interest_rate\":\"5.00\",\"payment\":\"10.00\",\"interest\":\"9.00\",\"total\":\"120.00\",\"withdraw_amount\":\"111.00\",\"checkremark\":\"房贷首付的身份\",\"checktime\":\"2026-10-07 12:32:08\",\"withdrawtime\":\"2026-10-07 12:32:33\"},\"ids\":\"3\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791372986),(127,2,'admin1','/ujWLHBNEfM.php/loan/apply/edit/ids/2?dialog=1','资金管理 / 资金申请 / 编辑','{\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"name\":\"李\",\"mobile\":\"11111\",\"email\":\"abc@qq.com\",\"amount\":\"10000.00\",\"purpose\":\"1111\",\"status\":\"4\",\"term\":\"0\",\"interest_rate\":\"5.00\",\"payment\":\"0.00\",\"interest\":\"0.00\",\"total\":\"0.00\",\"withdraw_amount\":\"0.00\",\"checkremark\":\"jkjk\",\"checktime\":\"2026-10-07 12:17:05\",\"withdrawtime\":\"2026-11-05 12:28:57\"},\"ids\":\"2\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791372995),(128,2,'admin1','/ujWLHBNEfM.php/loan/apply/edit/ids/1?dialog=1','资金管理 / 资金申请 / 编辑','{\"dialog\":\"1\",\"row\":{\"user_id\":\"2\",\"name\":\"赵\",\"mobile\":\"1234567\",\"email\":\"123@gmail.com\",\"amount\":\"100000.00\",\"purpose\":\"hhhh\",\"status\":\"4\",\"term\":\"12\",\"interest_rate\":\"5.00\",\"payment\":\"8561.00\",\"interest\":\"2732.00\",\"total\":\"102732.00\",\"withdraw_amount\":\"100000.00\",\"checkremark\":\"已放款\",\"checktime\":\"2026-10-07 11:54:24\",\"withdrawtime\":\"2026-10-07 11:54:46\"},\"ids\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791373005),(129,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791373294),(130,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374056),(131,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/user/user?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/user\\/user?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"wd6z\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374068),(132,1,'admin','/ujWLHBNEfM.php/auth/group/roletree','权限管理 / 角色组','{\"id\":\"4\",\"pid\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374075),(133,1,'admin','/ujWLHBNEfM.php/auth/group/roletree','权限管理 / 角色组','{\"id\":\"4\",\"pid\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374122),(134,1,'admin','/ujWLHBNEfM.php/auth/group/edit/ids/4?dialog=1','权限管理 / 角色组 / 编辑','{\"dialog\":\"1\",\"__token__\":\"***\",\"row\":{\"rules\":\"29,30,31,32,33,34,68,69,70,71,72,108,109,110,102,103,104,105,106,87,88,89,90,91,98,99,100,93,94,95,96,97,8,67,107,101,86,92,85,2,66\",\"pid\":\"1\",\"name\":\"二级管理组2\",\"status\":\"normal\"},\"ids\":\"4\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374137),(135,1,'admin','/ujWLHBNEfM.php/auth/group/roletree','权限管理 / 角色组','{\"id\":\"4\",\"pid\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374141),(136,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374165),(137,2,'admin1','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/general/config?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/general\\/config?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"kwpp\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374177),(138,2,'admin1','/ujWLHBNEfM.php/user/moneylog/add?user_id=5&ids=5&dialog=1','会员管理 / 会员余额记录 / 添加','{\"user_id\":\"5\",\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"user_id\":\"5\",\"type\":\"inc\",\"money\":\"111\",\"memo\":\"\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374273),(139,2,'admin1','/ujWLHBNEfM.php/loan/apply/pay?ids=4&dialog=1','资金管理 / 资金申请 / 确认放款','{\"ids\":\"4\",\"dialog\":\"1\",\"row\":{\"checkremark\":\"11111\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791374409),(140,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791374557),(141,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791374577),(142,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791374585),(143,0,'Unknown','/ujWLHBNEfM.php/index/login','','{\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"5sk1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791510926),(144,2,'admin1','/ujWLHBNEfM.php/index/login','登录','{\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"agaa\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791510933),(145,2,'admin1','/ujWLHBNEfM.php/user/user/del','会员管理 / 会员管理 / 删除','{\"action\":\"del\",\"ids\":\"9\",\"params\":\"\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791513670),(146,2,'admin1','/ujWLHBNEfM.php/loan/apply/pass?ids=5&dialog=1','资金管理 / 资金申请 / 审核通过','{\"ids\":\"5\",\"dialog\":\"1\",\"row\":{\"amount\":\"1111.00\",\"checkremark\":\"1111\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791526437),(147,1,'admin','/ujWLHBNEfM.php/index/login','登录','{\"username\":\"admin\",\"password\":\"***\",\"__token__\":\"***\"}','127.0.0.1','curl/8.7.1',1791526488),(148,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791526556),(149,2,'admin1','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/loan/bank?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/loan\\/bank?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"vix4\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791526569),(150,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791526863),(151,1,'admin','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/loan/bank?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/loan\\/bank?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin\",\"password\":\"***\",\"captcha\":\"zucn\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791526878),(152,1,'admin','/ujWLHBNEfM.php/general.config/edit','常规管理 / 系统配置 / 编辑','{\"__token__\":\"***\",\"row\":{\"name\":\"My Website\",\"beian\":\"\",\"version\":\"1.9.10\",\"timezone\":\"Asia\\/Shanghai\",\"forbiddenip\":\"\",\"languages\":\"{&quot;backend&quot;:&quot;zh-cn&quot;,&quot;frontend&quot;:&quot;zh-cn&quot;}\",\"fixedpage\":\"dashboard\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791526893),(153,1,'admin','/ujWLHBNEfM.php/general.config/edit','常规管理 / 系统配置 / 编辑','{\"__token__\":\"***\",\"row\":{\"name\":\"My Website\",\"beian\":\"\",\"version\":\"1.9210\",\"timezone\":\"Asia\\/Shanghai\",\"forbiddenip\":\"\",\"languages\":\"{&quot;backend&quot;:&quot;zh-cn&quot;,&quot;frontend&quot;:&quot;zh-cn&quot;}\",\"fixedpage\":\"dashboard\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791529027),(154,1,'admin','/ujWLHBNEfM.php/general.config/edit','常规管理 / 系统配置 / 编辑','{\"__token__\":\"***\",\"row\":{\"name\":\"My Website\",\"beian\":\"\",\"version\":\"1.9212\",\"timezone\":\"Asia\\/Shanghai\",\"forbiddenip\":\"\",\"languages\":\"{&quot;backend&quot;:&quot;zh-cn&quot;,&quot;frontend&quot;:&quot;zh-cn&quot;}\",\"fixedpage\":\"dashboard\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791529252),(155,1,'admin','/ujWLHBNEfM.php/general.config/edit','常规管理 / 系统配置 / 编辑','{\"__token__\":\"***\",\"row\":{\"name\":\"My Website\",\"beian\":\"\",\"version\":\"1.9212\",\"timezone\":\"Asia\\/Shanghai\",\"forbiddenip\":\"\",\"languages\":\"{&quot;backend&quot;:&quot;zh-cn&quot;,&quot;frontend&quot;:&quot;zh-cn&quot;}\",\"fixedpage\":\"dashboard\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791529260),(156,0,'Unknown','/ujWLHBNEfM.php/index/logout','','{\"__token__\":\"***\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791530264),(157,2,'admin1','/ujWLHBNEfM.php/index/login?url=/ujWLHBNEfM.php/auth/adminlog?ref=addtabs','登录','{\"url\":\"\\/ujWLHBNEfM.php\\/auth\\/adminlog?ref=addtabs\",\"__token__\":\"***\",\"username\":\"admin1\",\"password\":\"***\",\"captcha\":\"c6ec\",\"keeplogin\":\"1\"}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791530278),(158,2,'admin1','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','资金管理 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"2\",\"period_no\":\"放松的方式大\",\"amount\":\"1000\",\"due_time\":\"2026-10-16\",\"payee_bank_name\":\"测试銀行\",\"payee_branch_name\":\"测试部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"测试公司\",\"remark\":\"房贷首付的身份\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791548228),(159,2,'admin1','/ujWLHBNEfM.php/loan/repayment/add?dialog=1','资金管理 / 还款记录 / 添加','{\"dialog\":\"1\",\"row\":{\"apply_id\":\"1\",\"period_no\":\"1\",\"amount\":\"2222\",\"due_time\":\"2026-10-09\",\"payee_bank_name\":\"测试銀行\",\"payee_branch_name\":\"测试部\",\"payee_account_type\":\"普通\",\"payee_account_no\":\"1234567\",\"payee_account_name\":\"测试公司\",\"remark\":\"个梵蒂冈\"}}','127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',1791548451);
/*!40000 ALTER TABLE `fa_admin_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_area`
--

DROP TABLE IF EXISTS `fa_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_area` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `pid` int DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '简称',
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '名称',
  `mergename` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '全称',
  `level` tinyint DEFAULT NULL COMMENT '层级:1=省,2=市,3=区/县',
  `pinyin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '长途区号',
  `zip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '首字母',
  `lng` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '经度',
  `lat` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='地区表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_area`
--

LOCK TABLES `fa_area` WRITE;
/*!40000 ALTER TABLE `fa_area` DISABLE KEYS */;
/*!40000 ALTER TABLE `fa_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_attachment`
--

DROP TABLE IF EXISTS `fa_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_attachment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '类别',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '物理路径',
  `imagewidth` int unsigned DEFAULT '0' COMMENT '宽度',
  `imageheight` int unsigned DEFAULT '0' COMMENT '高度',
  `imagetype` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '图片类型',
  `imageframes` int unsigned NOT NULL DEFAULT '0' COMMENT '图片帧数',
  `filename` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '文件名称',
  `filesize` int unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `mimetype` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'mime类型',
  `extparam` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '透传数据',
  `createtime` bigint DEFAULT NULL COMMENT '创建日期',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `uploadtime` bigint DEFAULT NULL COMMENT '上传时间',
  `storage` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `sha1` varchar(40) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '文件 sha1编码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='附件表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_attachment`
--

LOCK TABLES `fa_attachment` WRITE;
/*!40000 ALTER TABLE `fa_attachment` DISABLE KEYS */;
INSERT INTO `fa_attachment` VALUES (1,'',1,0,'/assets/img/qrcode.png',150,150,'png',0,'qrcode.png',21859,'image/png','',1491635035,1491635035,1491635035,'local','17163603d0263e4838b9387ff2cd4877e8b018f6'),(2,'',1,5,'/uploads/20261007/4804588043445744a494b40386ca609d.png',800,2904,'png',0,'1.png',358417,'image/png','',1791350971,1791350971,1791350971,'local','9a209036699f8047397cf49168b1846328aee8c5'),(3,'',0,2,'/uploads/20261007/c5d45b83bf60f1fb144b0a5f32b06eef.png',1,1,'png',0,'voucher.png',69,'image/png','',1791351100,1791351100,1791351100,'local','1d3546e89b22ee137ab0b34973a52035051e17d2');
/*!40000 ALTER TABLE `fa_attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_auth_group`
--

DROP TABLE IF EXISTS `fa_auth_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_auth_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '父组别',
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '组名',
  `rules` text COLLATE utf8mb4_general_ci NOT NULL COMMENT '规则ID',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='分组表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_auth_group`
--

LOCK TABLES `fa_auth_group` WRITE;
/*!40000 ALTER TABLE `fa_auth_group` DISABLE KEYS */;
INSERT INTO `fa_auth_group` VALUES (1,0,'Admin group','*',1491635035,1491635035,'normal'),(2,1,'Second group','13,14,16,15,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,1,9,10,11,7,6,8,2,4,5',1491635035,1491635035,'normal'),(3,2,'Third group','1,4,9,10,11,13,14,15,16,17,40,41,42,43,44,45,46,47,48,49,50,55,56,57,58,59,60,61,62,63,64,65,5',1491635035,1491635035,'normal'),(4,1,'二级管理组2','29,30,31,32,33,34,68,69,70,71,72,108,109,110,102,103,104,105,106,87,88,89,90,91,98,99,100,93,94,95,96,97,8,67,107,101,86,92,85,2,66',1491635035,1791374137,'normal'),(5,2,'Third group 2','1,2,6,7,8,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34',1491635035,1491635035,'normal');
/*!40000 ALTER TABLE `fa_auth_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_auth_group_access`
--

DROP TABLE IF EXISTS `fa_auth_group_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_auth_group_access` (
  `uid` int unsigned NOT NULL COMMENT '会员ID',
  `group_id` int unsigned NOT NULL COMMENT '级别ID',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='权限分组表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_auth_group_access`
--

LOCK TABLES `fa_auth_group_access` WRITE;
/*!40000 ALTER TABLE `fa_auth_group_access` DISABLE KEYS */;
INSERT INTO `fa_auth_group_access` VALUES (1,1),(2,4);
/*!40000 ALTER TABLE `fa_auth_group_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_auth_rule`
--

DROP TABLE IF EXISTS `fa_auth_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_auth_rule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('menu','file') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'file' COMMENT 'menu为菜单,file为权限节点',
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '规则名称',
  `title` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '规则名称',
  `icon` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '图标',
  `url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '规则URL',
  `condition` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '条件',
  `remark` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '备注',
  `ismenu` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '是否为菜单',
  `menutype` enum('addtabs','blank','dialog','ajax') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '菜单类型',
  `extend` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '扩展属性',
  `py` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '拼音首字母',
  `pinyin` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '拼音',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `pid` (`pid`),
  KEY `weigh` (`weigh`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='节点表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_auth_rule`
--

LOCK TABLES `fa_auth_rule` WRITE;
/*!40000 ALTER TABLE `fa_auth_rule` DISABLE KEYS */;
INSERT INTO `fa_auth_rule` VALUES (1,'file',0,'dashboard','Dashboard','fa fa-dashboard','','','Dashboard tips',1,NULL,'','kzt','kongzhitai',1491635035,1491635035,143,'normal'),(2,'file',0,'general','General','fa fa-cogs','','','',1,NULL,'','cggl','changguiguanli',1491635035,1491635035,137,'normal'),(3,'file',0,'category','Category','fa fa-leaf','','','Category tips',0,NULL,'','flgl','fenleiguanli',1491635035,1491635035,119,'normal'),(4,'file',0,'addon','Addon','fa fa-rocket','','','Addon tips',1,NULL,'','cjgl','chajianguanli',1491635035,1491635035,0,'normal'),(5,'file',0,'auth','Auth','fa fa-group','','','',1,NULL,'','qxgl','quanxianguanli',1491635035,1491635035,99,'normal'),(6,'file',2,'general/config','Config','fa fa-cog','','','Config tips',1,NULL,'','xtpz','xitongpeizhi',1491635035,1491635035,60,'normal'),(7,'file',2,'general/attachment','Attachment','fa fa-file-image-o','','','Attachment tips',1,NULL,'','fjgl','fujianguanli',1491635035,1491635035,53,'normal'),(8,'file',2,'general/profile','Profile','fa fa-user','','','',1,NULL,'','grzl','gerenziliao',1491635035,1491635035,34,'normal'),(9,'file',5,'auth/admin','Admin','fa fa-user','','','Admin tips',1,NULL,'','glygl','guanliyuanguanli',1491635035,1491635035,118,'normal'),(10,'file',5,'auth/adminlog','Admin log','fa fa-list-alt','','','Admin log tips',1,NULL,'','glyrz','guanliyuanrizhi',1491635035,1491635035,113,'normal'),(11,'file',5,'auth/group','Group','fa fa-group','','','Group tips',1,NULL,'','jsz','juesezu',1491635035,1491635035,109,'normal'),(12,'file',5,'auth/rule','Rule','fa fa-bars','','','Rule tips',1,NULL,'','cdgz','caidanguize',1491635035,1491635035,104,'normal'),(13,'file',1,'dashboard/index','View','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,136,'normal'),(14,'file',1,'dashboard/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,135,'normal'),(15,'file',1,'dashboard/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,133,'normal'),(16,'file',1,'dashboard/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,134,'normal'),(17,'file',1,'dashboard/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,132,'normal'),(18,'file',6,'general/config/index','View','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,52,'normal'),(19,'file',6,'general/config/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,51,'normal'),(20,'file',6,'general/config/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,50,'normal'),(21,'file',6,'general/config/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,49,'normal'),(22,'file',6,'general/config/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,48,'normal'),(23,'file',7,'general/attachment/index','View','fa fa-circle-o','','','Attachment tips',0,NULL,'','','',1491635035,1491635035,59,'normal'),(24,'file',7,'general/attachment/select','Select attachment','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,58,'normal'),(25,'file',7,'general/attachment/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,57,'normal'),(26,'file',7,'general/attachment/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,56,'normal'),(27,'file',7,'general/attachment/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,55,'normal'),(28,'file',7,'general/attachment/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,54,'normal'),(29,'file',8,'general/profile/index','View','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,33,'normal'),(30,'file',8,'general/profile/update','Update profile','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,32,'normal'),(31,'file',8,'general/profile/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,31,'normal'),(32,'file',8,'general/profile/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,30,'normal'),(33,'file',8,'general/profile/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,29,'normal'),(34,'file',8,'general/profile/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,28,'normal'),(35,'file',3,'category/index','View','fa fa-circle-o','','','Category tips',0,NULL,'','','',1491635035,1491635035,142,'normal'),(36,'file',3,'category/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,141,'normal'),(37,'file',3,'category/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,140,'normal'),(38,'file',3,'category/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,139,'normal'),(39,'file',3,'category/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,138,'normal'),(40,'file',9,'auth/admin/index','View','fa fa-circle-o','','','Admin tips',0,NULL,'','','',1491635035,1491635035,117,'normal'),(41,'file',9,'auth/admin/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,116,'normal'),(42,'file',9,'auth/admin/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,115,'normal'),(43,'file',9,'auth/admin/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,114,'normal'),(44,'file',10,'auth/adminlog/index','View','fa fa-circle-o','','','Admin log tips',0,NULL,'','','',1491635035,1491635035,112,'normal'),(45,'file',10,'auth/adminlog/detail','Detail','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,111,'normal'),(46,'file',10,'auth/adminlog/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,110,'normal'),(47,'file',11,'auth/group/index','View','fa fa-circle-o','','','Group tips',0,NULL,'','','',1491635035,1491635035,108,'normal'),(48,'file',11,'auth/group/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,107,'normal'),(49,'file',11,'auth/group/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,106,'normal'),(50,'file',11,'auth/group/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,105,'normal'),(51,'file',12,'auth/rule/index','View','fa fa-circle-o','','','Rule tips',0,NULL,'','','',1491635035,1491635035,103,'normal'),(52,'file',12,'auth/rule/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,102,'normal'),(53,'file',12,'auth/rule/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,101,'normal'),(54,'file',12,'auth/rule/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,100,'normal'),(55,'file',4,'addon/index','View','fa fa-circle-o','','','Addon tips',0,NULL,'','','',1491635035,1491635035,0,'normal'),(56,'file',4,'addon/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(57,'file',4,'addon/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(58,'file',4,'addon/del','Delete','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(59,'file',4,'addon/downloaded','Local addon','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(60,'file',4,'addon/state','Update state','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(63,'file',4,'addon/config','Setting','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(64,'file',4,'addon/refresh','Refresh','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(65,'file',4,'addon/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(66,'file',0,'user','User','fa fa-user-circle','','','',1,NULL,'','hygl','huiyuanguanli',1491635035,1491635035,0,'normal'),(67,'file',66,'user/user','User','fa fa-user','','','',1,NULL,'','hygl','huiyuanguanli',1491635035,1491635035,0,'normal'),(68,'file',67,'user/user/index','View','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(69,'file',67,'user/user/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(70,'file',67,'user/user/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(71,'file',67,'user/user/del','Del','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(72,'file',67,'user/user/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(73,'file',66,'user/group','User group','fa fa-users','','','',1,NULL,'','hyfz','huiyuanfenzu',1491635035,1491635035,0,'normal'),(74,'file',73,'user/group/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(75,'file',73,'user/group/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(76,'file',73,'user/group/index','View','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(77,'file',73,'user/group/del','Del','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(78,'file',73,'user/group/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(79,'file',66,'user/rule','User rule','fa fa-circle-o','','','',1,NULL,'','hygz','huiyuanguize',1491635035,1491635035,0,'normal'),(80,'file',79,'user/rule/index','View','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(81,'file',79,'user/rule/del','Del','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(82,'file',79,'user/rule/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(83,'file',79,'user/rule/edit','Edit','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(84,'file',79,'user/rule/multi','Multi','fa fa-circle-o','','','',0,NULL,'','','',1491635035,1491635035,0,'normal'),(85,'file',0,'loan','资金管理','fa fa-list','','','',1,NULL,'','l','loan',1791344540,1791344540,0,'normal'),(86,'file',85,'loan/apply','资金申请','fa fa-circle-o','','','',1,NULL,'','czzcsq','caizhengzhichishenqing',1791344540,1791344540,0,'normal'),(87,'file',86,'loan/apply/index','查看','fa fa-circle-o','','','',0,NULL,'','zk','zhakan',1791344540,1791344540,0,'normal'),(88,'file',86,'loan/apply/add','添加','fa fa-circle-o','','','',0,NULL,'','tj','tianjia',1791344540,1791344540,0,'normal'),(89,'file',86,'loan/apply/edit','编辑','fa fa-circle-o','','','',0,NULL,'','bj','bianji',1791344540,1791344540,0,'normal'),(90,'file',86,'loan/apply/del','删除','fa fa-circle-o','','','',0,NULL,'','sc','shanchu',1791344540,1791344540,0,'normal'),(91,'file',86,'loan/apply/multi','批量更新','fa fa-circle-o','','','',0,NULL,'','plgx','pilianggengxin',1791344540,1791344540,0,'normal'),(92,'file',85,'loan/bank','银行卡','fa fa-circle-o','','','',1,NULL,'','yhk','yinhangka',1791344547,1791344547,0,'normal'),(93,'file',92,'loan/bank/index','查看','fa fa-circle-o','','','',0,NULL,'','zk','zhakan',1791344547,1791344547,0,'normal'),(94,'file',92,'loan/bank/add','添加','fa fa-circle-o','','','',0,NULL,'','tj','tianjia',1791344547,1791344547,0,'normal'),(95,'file',92,'loan/bank/edit','编辑','fa fa-circle-o','','','',0,NULL,'','bj','bianji',1791344547,1791344547,0,'normal'),(96,'file',92,'loan/bank/del','删除','fa fa-circle-o','','','',0,NULL,'','sc','shanchu',1791344547,1791344547,0,'normal'),(97,'file',92,'loan/bank/multi','批量更新','fa fa-circle-o','','','',0,NULL,'','plgx','pilianggengxin',1791344547,1791344547,0,'normal'),(98,'file',86,'loan/apply/pass','审核通过','','','','',0,NULL,'','','',1791344663,1791344663,0,'normal'),(99,'file',86,'loan/apply/reject','驳回','','','','',0,NULL,'','','',1791344663,1791344663,0,'normal'),(100,'file',86,'loan/apply/pay','确认放款','','','','',0,NULL,'','','',1791344663,1791344663,0,'normal'),(101,'file',85,'loan/repayment','还款记录','fa fa-jpy','','','',1,NULL,'','','',1791350408,1791350408,1,'normal'),(102,'file',101,'loan/repayment/index','查看','fa fa-circle-o','','','',0,NULL,'','','',1791350408,1791350408,1,'normal'),(103,'file',101,'loan/repayment/add','添加','fa fa-circle-o','','','',0,NULL,'','','',1791350408,1791350408,1,'normal'),(104,'file',101,'loan/repayment/edit','编辑','fa fa-circle-o','','','',0,NULL,'','','',1791350408,1791350408,1,'normal'),(105,'file',101,'loan/repayment/del','删除','fa fa-circle-o','','','',0,NULL,'','','',1791350408,1791350408,1,'normal'),(106,'file',101,'loan/repayment/check','批复还款凭证','fa fa-circle-o','','','',0,NULL,'','','',1791350408,1791350408,1,'normal'),(107,'file',66,'user/moneylog','会员余额记录','fa fa-money','','','',1,'addtabs','','','',1791352702,1791352702,0,'normal'),(108,'file',107,'user/moneylog/index','View','fa fa-circle-o','','','',0,NULL,'','','',1791352702,1791352702,0,'normal'),(109,'file',107,'user/moneylog/add','Add','fa fa-circle-o','','','',0,NULL,'','','',1791352702,1791352702,0,'normal'),(110,'file',107,'user/moneylog/del','Del','fa fa-circle-o','','','',0,NULL,'','','',1791352702,1791352702,0,'normal');
/*!40000 ALTER TABLE `fa_auth_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_category`
--

DROP TABLE IF EXISTS `fa_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_category` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pid` int unsigned NOT NULL DEFAULT '0' COMMENT '父ID',
  `type` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '栏目类型',
  `name` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '',
  `nickname` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '',
  `flag` set('hot','index','recommend') COLLATE utf8mb4_general_ci DEFAULT '',
  `image` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '图片',
  `keywords` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '关键字',
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '描述',
  `diyname` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '自定义名称',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `weigh` (`weigh`,`id`),
  KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='分类表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_category`
--

LOCK TABLES `fa_category` WRITE;
/*!40000 ALTER TABLE `fa_category` DISABLE KEYS */;
INSERT INTO `fa_category` VALUES (1,0,'page','官方新闻','news','recommend','/assets/img/qrcode.png','','','news',1491635035,1491635035,1,'normal'),(2,0,'page','移动应用','mobileapp','hot','/assets/img/qrcode.png','','','mobileapp',1491635035,1491635035,2,'normal'),(3,2,'page','微信公众号','wechatpublic','index','/assets/img/qrcode.png','','','wechatpublic',1491635035,1491635035,3,'normal'),(4,2,'page','Android开发','android','recommend','/assets/img/qrcode.png','','','android',1491635035,1491635035,4,'normal'),(5,0,'page','软件产品','software','recommend','/assets/img/qrcode.png','','','software',1491635035,1491635035,5,'normal'),(6,5,'page','网站建站','website','recommend','/assets/img/qrcode.png','','','website',1491635035,1491635035,6,'normal'),(7,5,'page','企业管理软件','company','index','/assets/img/qrcode.png','','','company',1491635035,1491635035,7,'normal'),(8,6,'page','PC端','website-pc','recommend','/assets/img/qrcode.png','','','website-pc',1491635035,1491635035,8,'normal'),(9,6,'page','移动端','website-mobile','recommend','/assets/img/qrcode.png','','','website-mobile',1491635035,1491635035,9,'normal'),(10,7,'page','CRM系统 ','company-crm','recommend','/assets/img/qrcode.png','','','company-crm',1491635035,1491635035,10,'normal'),(11,7,'page','SASS平台软件','company-sass','recommend','/assets/img/qrcode.png','','','company-sass',1491635035,1491635035,11,'normal'),(12,0,'test','测试1','test1','recommend','/assets/img/qrcode.png','','','test1',1491635035,1491635035,12,'normal'),(13,0,'test','测试2','test2','recommend','/assets/img/qrcode.png','','','test2',1491635035,1491635035,13,'normal');
/*!40000 ALTER TABLE `fa_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_config`
--

DROP TABLE IF EXISTS `fa_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_config` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '变量名',
  `group` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '分组',
  `title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '变量标题',
  `tip` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '变量描述',
  `type` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
  `visible` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '可见条件',
  `value` text COLLATE utf8mb4_general_ci COMMENT '变量值',
  `content` text COLLATE utf8mb4_general_ci COMMENT '变量字典数据',
  `rule` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '验证规则',
  `extend` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '扩展属性',
  `setting` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '配置',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='系统配置';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_config`
--

LOCK TABLES `fa_config` WRITE;
/*!40000 ALTER TABLE `fa_config` DISABLE KEYS */;
INSERT INTO `fa_config` VALUES (1,'name','basic','Site name','请填写站点名称','string','','My Website','','required','',NULL),(2,'beian','basic','Beian','粤ICP备15000000号-1','string','','','','','',NULL),(3,'cdnurl','basic','Cdn url','如果全站静态资源使用第三方云储存请配置该值','string','','','','','',''),(4,'version','basic','Version','如果静态资源有变动请重新配置该值','string','','1.9213','','required','',NULL),(5,'timezone','basic','Timezone','','string','','Asia/Shanghai','','required','',NULL),(6,'forbiddenip','basic','Forbidden ip','一行一条记录','text','','','','','',NULL),(7,'languages','basic','Languages','','array','','{\"backend\":\"zh-cn\",\"frontend\":\"zh-cn\"}','','required','',NULL),(8,'fixedpage','basic','Fixed page','请输入左侧菜单栏存在的链接','string','','dashboard','','required','',NULL),(9,'categorytype','dictionary','Category type','','array','','{\"default\":\"Default\",\"page\":\"Page\",\"article\":\"Article\",\"test\":\"Test\"}','','','',''),(10,'configgroup','dictionary','Config group','','array','','{\"basic\":\"Basic\",\"email\":\"Email\",\"dictionary\":\"Dictionary\",\"user\":\"User\",\"example\":\"Example\"}','','','',''),(11,'mail_type','email','Mail type','选择邮件发送方式','select','','1','[\"请选择\",\"SMTP\"]','','',''),(12,'mail_smtp_host','email','Mail smtp host','错误的配置发送邮件会导致服务器超时','string','','smtp.qq.com','','','',''),(13,'mail_smtp_port','email','Mail smtp port','(不加密默认25,SSL默认465,TLS默认587)','string','','465','','','',''),(14,'mail_smtp_user','email','Mail smtp user','（填写完整用户名）','string','','','','','',''),(15,'mail_smtp_pass','email','Mail smtp password','（填写您的密码或授权码）','password','','','','','',''),(16,'mail_verify_type','email','Mail vertify type','（SMTP验证方式[推荐SSL]）','select','','2','[\"无\",\"TLS\",\"SSL\"]','','',''),(17,'mail_from','email','Mail from','','string','','','','','',''),(18,'attachmentcategory','dictionary','Attachment category','','array','','{\"category1\":\"Category1\",\"category2\":\"Category2\",\"custom\":\"Custom\"}','','','','');
/*!40000 ALTER TABLE `fa_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_ems`
--

DROP TABLE IF EXISTS `fa_ems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_ems` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '事件',
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '邮箱',
  `code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '验证码',
  `times` int unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'IP',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='邮箱验证码表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_ems`
--

LOCK TABLES `fa_ems` WRITE;
/*!40000 ALTER TABLE `fa_ems` DISABLE KEYS */;
/*!40000 ALTER TABLE `fa_ems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_loan_apply`
--

DROP TABLE IF EXISTS `fa_loan_apply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_loan_apply` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '你的名字',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '电话号码',
  `email` varchar(100) NOT NULL DEFAULT '' COMMENT '电子邮件',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '所需金额',
  `purpose` varchar(500) NOT NULL DEFAULT '' COMMENT '目的',
  `status` enum('0','1','2','3','4') NOT NULL DEFAULT '0' COMMENT '状态:0=申请中,1=申请已通过,2=已拒绝,3=提款处理中,4=提款完成',
  `term` int unsigned NOT NULL DEFAULT '0' COMMENT '还款期数',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '5.00' COMMENT '年利率(%)',
  `payment` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '每次付款',
  `interest` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '利息',
  `total` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '应还共计',
  `withdraw_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '已提款金额',
  `bank_id` int unsigned NOT NULL DEFAULT '0' COMMENT '银行卡ID',
  `checkremark` varchar(500) NOT NULL DEFAULT '' COMMENT '审核备注',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '审核管理员',
  `checktime` int unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `withdrawtime` int unsigned NOT NULL DEFAULT '0' COMMENT '提款时间',
  `createtime` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='财政支持申请';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_loan_apply`
--

LOCK TABLES `fa_loan_apply` WRITE;
/*!40000 ALTER TABLE `fa_loan_apply` DISABLE KEYS */;
INSERT INTO `fa_loan_apply` VALUES (1,2,'赵','1234567','123@gmail.com',100000.00,'hhhh','4',12,5.00,8561.00,2732.00,102732.00,100000.00,1,'已放款',7,1791345264,1791345286,1791344845,1791373005),(2,5,'李','11111','abc@qq.com',10000.00,'1111','4',0,5.00,0.00,0.00,0.00,0.00,2,'jkjk',1,1791346625,1793852937,1791345922,1791372995),(3,5,'王','11112311897','111@gmail.com',111.00,'111','4',12,5.00,10.00,9.00,120.00,111.00,2,'房贷首付的身份',1,1791347528,1791347553,1791347392,1791372986),(4,5,'张','13800000005','a@b.com',1000.00,'测试','4',12,5.00,86.00,32.00,1032.00,1000.00,2,'11111',1,1791353689,1791374409,1791347662,1791374409),(5,11,'111','11111','111@gmail.com',1111.00,'111','1',0,5.00,0.00,0.00,0.00,0.00,0,'1111',2,1791526437,0,1791526421,1791526437);
/*!40000 ALTER TABLE `fa_loan_apply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_loan_bank`
--

DROP TABLE IF EXISTS `fa_loan_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_loan_bank` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `account_name` varchar(50) NOT NULL DEFAULT '' COMMENT '账户名称',
  `account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '账号',
  `bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称',
  `branch_code` varchar(30) DEFAULT NULL,
  `branch_name` varchar(100) DEFAULT NULL,
  `account_type` varchar(20) NOT NULL DEFAULT '通常' COMMENT '账户类型',
  `createtime` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='银行卡';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_loan_bank`
--

LOCK TABLES `fa_loan_bank` WRITE;
/*!40000 ALTER TABLE `fa_loan_bank` DISABLE KEYS */;
INSERT INTO `fa_loan_bank` VALUES (1,2,'11','1234567','11','1111','11','通常',1791345272,1791345272),(2,5,'1212','1212','1212','212','2121','当座',1791346650,1791346650);
/*!40000 ALTER TABLE `fa_loan_bank` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_loan_repayment`
--

DROP TABLE IF EXISTS `fa_loan_repayment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_loan_repayment` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `apply_id` int unsigned NOT NULL DEFAULT '0' COMMENT '申请ID',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `period_no` varchar(50) NOT NULL DEFAULT '1',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '本期应还金额',
  `due_time` int unsigned NOT NULL DEFAULT '0' COMMENT '本期还款日期',
  `payee_bank_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款银行名称',
  `payee_branch_name` varchar(100) NOT NULL DEFAULT '' COMMENT '收款支行名称',
  `payee_account_type` varchar(20) NOT NULL DEFAULT '' COMMENT '收款账户类型',
  `payee_account_no` varchar(50) NOT NULL DEFAULT '' COMMENT '收款账号',
  `payee_account_name` varchar(50) NOT NULL DEFAULT '' COMMENT '收款户名',
  `images` varchar(1000) NOT NULL DEFAULT '' COMMENT '转账凭证',
  `status` enum('0','1','2','3') NOT NULL DEFAULT '0' COMMENT '状态:0=待还款,1=已汇款待确认,2=已确认收款,3=已驳回',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '客户备注',
  `checkremark` varchar(500) NOT NULL DEFAULT '' COMMENT '批复备注',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '批复管理员',
  `paytime` int unsigned NOT NULL DEFAULT '0' COMMENT '客户确认还款时间',
  `checktime` int unsigned NOT NULL DEFAULT '0' COMMENT '批复时间',
  `createtime` int unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `apply_id` (`apply_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='还款记录';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_loan_repayment`
--

LOCK TABLES `fa_loan_repayment` WRITE;
/*!40000 ALTER TABLE `fa_loan_repayment` DISABLE KEYS */;
INSERT INTO `fa_loan_repayment` VALUES (2,4,5,'1',1000.00,1790870400,'111銀行','111111','普通','1234567','1111111','/uploads/20261007/4804588043445744a494b40386ca609d.png','2','规划规划发反反复复','',1,1791350975,1791351940,1791350742,1791354918),(6,2,5,'放松的方式大',1000.00,1792080000,'测试銀行','测试部','普通','1234567','测试公司','','0','房贷首付的身份','',0,0,0,1791548228,1791548228),(7,1,2,'1',2222.00,1791475200,'测试銀行','测试部','普通','1234567','测试公司','','0','个梵蒂冈','',0,0,0,1791548451,1791548451);
/*!40000 ALTER TABLE `fa_loan_repayment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_sms`
--

DROP TABLE IF EXISTS `fa_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_sms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `event` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '事件',
  `mobile` varchar(20) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '手机号',
  `code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '验证码',
  `times` int unsigned NOT NULL DEFAULT '0' COMMENT '验证次数',
  `ip` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'IP',
  `createtime` bigint unsigned DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='短信验证码表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_sms`
--

LOCK TABLES `fa_sms` WRITE;
/*!40000 ALTER TABLE `fa_sms` DISABLE KEYS */;
/*!40000 ALTER TABLE `fa_sms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_test`
--

DROP TABLE IF EXISTS `fa_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_test` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int DEFAULT '0' COMMENT '会员ID',
  `admin_id` int DEFAULT '0' COMMENT '管理员ID',
  `category_id` int unsigned DEFAULT '0' COMMENT '分类ID(单选)',
  `category_ids` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '分类ID(多选)',
  `tags` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标签',
  `week` enum('monday','tuesday','wednesday') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '星期(单选):monday=星期一,tuesday=星期二,wednesday=星期三',
  `flag` set('hot','index','recommend') COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标志(多选):hot=热门,index=首页,recommend=推荐',
  `genderdata` enum('male','female') COLLATE utf8mb4_general_ci DEFAULT 'male' COMMENT '性别(单选):male=男,female=女',
  `hobbydata` set('music','reading','swimming') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '爱好(多选):music=音乐,reading=读书,swimming=游泳',
  `title` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标题',
  `content` text COLLATE utf8mb4_general_ci COMMENT '内容',
  `image` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '图片',
  `images` varchar(1500) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '图片组',
  `attachfile` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '附件',
  `keywords` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '关键字',
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '描述',
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '省市',
  `array` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '数组:value=值',
  `json` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '配置:key=名称,value=值',
  `multiplejson` varchar(1500) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '二维数组:title=标题,intro=介绍,author=作者,age=年龄',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '价格',
  `views` int unsigned DEFAULT '0' COMMENT '点击',
  `workrange` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '时间区间',
  `startdate` date DEFAULT NULL COMMENT '开始日期',
  `activitytime` datetime DEFAULT NULL COMMENT '活动时间(datetime)',
  `year` year DEFAULT NULL COMMENT '年',
  `times` time DEFAULT NULL COMMENT '时间',
  `refreshtime` bigint DEFAULT NULL COMMENT '刷新时间',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `deletetime` bigint DEFAULT NULL COMMENT '删除时间',
  `weigh` int DEFAULT '0' COMMENT '权重',
  `switch` tinyint(1) DEFAULT '0' COMMENT '开关',
  `status` enum('normal','hidden') COLLATE utf8mb4_general_ci DEFAULT 'normal' COMMENT '状态',
  `state` enum('0','1','2') COLLATE utf8mb4_general_ci DEFAULT '1' COMMENT '状态值:0=禁用,1=正常,2=推荐',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='测试表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_test`
--

LOCK TABLES `fa_test` WRITE;
/*!40000 ALTER TABLE `fa_test` DISABLE KEYS */;
INSERT INTO `fa_test` VALUES (1,1,1,12,'12,13','互联网,计算机','monday','hot,index','male','music,reading','我是一篇测试文章','<p>我是测试内容</p>','/assets/img/avatar.png','/assets/img/avatar.png,/assets/img/qrcode.png','/assets/img/avatar.png','关键字','我是一篇测试文章描述，内容过多时将自动隐藏','广西壮族自治区/百色市/平果县','[\"a\",\"b\"]','{\"a\":\"1\",\"b\":\"2\"}','[{\"title\":\"标题一\",\"intro\":\"介绍一\",\"author\":\"小明\",\"age\":\"21\"}]',0.00,0,'2020-10-01 00:00:00 - 2021-10-31 23:59:59','2017-07-10','2017-07-10 18:24:45',2017,'18:24:45',1491635035,1491635035,1491635035,NULL,0,1,'normal','1');
/*!40000 ALTER TABLE `fa_test` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_user`
--

DROP TABLE IF EXISTS `fa_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `group_id` int unsigned NOT NULL DEFAULT '0' COMMENT '组别ID',
  `username` varchar(32) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '用户名',
  `nickname` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '昵称',
  `password` varchar(32) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '密码',
  `salt` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '密码盐',
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '电子邮箱',
  `mobile` varchar(11) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '手机号',
  `avatar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '头像',
  `level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '等级',
  `gender` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `bio` varchar(100) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '格言',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `score` int NOT NULL DEFAULT '0' COMMENT '积分',
  `successions` int unsigned NOT NULL DEFAULT '1' COMMENT '连续登录天数',
  `maxsuccessions` int unsigned NOT NULL DEFAULT '1' COMMENT '最大连续登录天数',
  `prevtime` bigint DEFAULT NULL COMMENT '上次登录时间',
  `logintime` bigint DEFAULT NULL COMMENT '登录时间',
  `loginip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '登录IP',
  `loginfailure` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '失败次数',
  `loginfailuretime` bigint DEFAULT NULL COMMENT '最后登录失败时间',
  `joinip` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '加入IP',
  `jointime` bigint DEFAULT NULL COMMENT '加入时间',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `token` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT 'Token',
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '状态',
  `verification` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '验证',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `mobile` (`mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_user`
--

LOCK TABLES `fa_user` WRITE;
/*!40000 ALTER TABLE `fa_user` DISABLE KEYS */;
INSERT INTO `fa_user` VALUES (1,1,'admin','admin','e081a08a02e745f3a6b0150ad29caacb','635530','admin@163.com','13000000000','/assets/img/avatar.png',0,0,'2017-04-08','',0.00,0,1,1,1491635035,1491635035,'127.0.0.1',10,1791527540,'127.0.0.1',1491635035,0,1791527540,'','normal',''),(2,1,'admin4','管理员4','f17c5399e1021a35ba69bba302e3da53','abc123','admin4@example.com','13800000004','',1,1,NULL,'',1000.00,0,1,1,1791349634,1791351047,'127.0.0.1',0,NULL,'',1791344536,1791344536,1791353309,'','normal',''),(5,1,'admin1','admin1','c3d76153a838b2fca4f4c4bf8a42f347','tcFWYl','','','',1,0,NULL,'',116798.00,0,3,3,1791530024,1791548235,'127.0.0.1',0,NULL,'127.0.0.1',1791345873,1791345873,1791548235,'','normal',''),(11,0,'admin3','admin3','7dfb69703c39c1c3912024089cf653f9','GznBPT','','','',1,0,NULL,'',0.00,0,1,1,1791526395,1791526395,'127.0.0.1',0,NULL,'127.0.0.1',1791526395,1791526395,1791526395,'','normal',''),(12,0,'admin111','admin111','964515e018f15395a78e84c62f2384c4','e6wp5z','','','',1,0,NULL,'',0.00,0,1,1,1791527527,1791527527,'127.0.0.1',0,NULL,'127.0.0.1',1791527527,1791527527,1791527527,'','normal',''),(13,0,'111111','111111','8c0ece4a1f697253fc5083e9437b11c3','eqQTXD','','','',1,0,NULL,'',0.00,0,1,1,1791528297,1791528297,'127.0.0.1',0,NULL,'127.0.0.1',1791528297,1791528297,1791528297,'','normal','');
/*!40000 ALTER TABLE `fa_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_user_group`
--

DROP TABLE IF EXISTS `fa_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_user_group` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '组名',
  `rules` text COLLATE utf8mb4_general_ci COMMENT '权限节点',
  `createtime` bigint DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员组表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_user_group`
--

LOCK TABLES `fa_user_group` WRITE;
/*!40000 ALTER TABLE `fa_user_group` DISABLE KEYS */;
INSERT INTO `fa_user_group` VALUES (1,'默认组','1,2,3,4,5,6,7,8,9,10,11,12',1491635035,1491635035,'normal');
/*!40000 ALTER TABLE `fa_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_user_money_log`
--

DROP TABLE IF EXISTS `fa_user_money_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_user_money_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int unsigned NOT NULL DEFAULT '0' COMMENT '操作管理员ID',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更余额',
  `before` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更前余额',
  `after` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '变更后余额',
  `memo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '备注',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员余额变动表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_user_money_log`
--

LOCK TABLES `fa_user_money_log` WRITE;
/*!40000 ALTER TABLE `fa_user_money_log` DISABLE KEYS */;
INSERT INTO `fa_user_money_log` VALUES (1,5,0,111110.00,0.00,111110.00,'管理员变更金额',1791348614),(4,5,1,22.00,111110.00,111132.00,'后台增加余额',1791353239),(5,2,1,1000.00,0.00,1000.00,'1000',1791353309),(6,5,1,5555.00,111132.00,116687.00,'后台增加余额',1791353423),(7,5,2,111.00,116687.00,116798.00,'后台增加余额',1791374273);
/*!40000 ALTER TABLE `fa_user_money_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_user_rule`
--

DROP TABLE IF EXISTS `fa_user_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_user_rule` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pid` int DEFAULT NULL COMMENT '父ID',
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '名称',
  `title` varchar(50) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标题',
  `remark` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '备注',
  `ismenu` tinyint(1) DEFAULT NULL COMMENT '是否菜单',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `weigh` int DEFAULT '0' COMMENT '权重',
  `status` enum('normal','hidden') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员规则表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_user_rule`
--

LOCK TABLES `fa_user_rule` WRITE;
/*!40000 ALTER TABLE `fa_user_rule` DISABLE KEYS */;
INSERT INTO `fa_user_rule` VALUES (1,0,'index','Frontend','',1,1491635035,1491635035,1,'normal'),(2,0,'api','API Interface','',1,1491635035,1491635035,2,'normal'),(3,1,'user','User Module','',1,1491635035,1491635035,12,'normal'),(4,2,'user','User Module','',1,1491635035,1491635035,11,'normal'),(5,3,'index/user/login','Login','',0,1491635035,1491635035,5,'normal'),(6,3,'index/user/register','Register','',0,1491635035,1491635035,7,'normal'),(7,3,'index/user/index','User Center','',0,1491635035,1491635035,9,'normal'),(8,3,'index/user/profile','Profile','',0,1491635035,1491635035,4,'normal'),(9,4,'api/user/login','Login','',0,1491635035,1491635035,6,'normal'),(10,4,'api/user/register','Register','',0,1491635035,1491635035,8,'normal'),(11,4,'api/user/index','User Center','',0,1491635035,1491635035,10,'normal'),(12,4,'api/user/profile','Profile','',0,1491635035,1491635035,3,'normal');
/*!40000 ALTER TABLE `fa_user_rule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_user_score_log`
--

DROP TABLE IF EXISTS `fa_user_score_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_user_score_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `score` int NOT NULL DEFAULT '0' COMMENT '变更积分',
  `before` int NOT NULL DEFAULT '0' COMMENT '变更前积分',
  `after` int NOT NULL DEFAULT '0' COMMENT '变更后积分',
  `memo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '备注',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员积分变动表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_user_score_log`
--

LOCK TABLES `fa_user_score_log` WRITE;
/*!40000 ALTER TABLE `fa_user_score_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `fa_user_score_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_user_token`
--

DROP TABLE IF EXISTS `fa_user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_user_token` (
  `token` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Token',
  `user_id` int unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `expiretime` bigint DEFAULT NULL COMMENT '过期时间',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员Token表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_user_token`
--

LOCK TABLES `fa_user_token` WRITE;
/*!40000 ALTER TABLE `fa_user_token` DISABLE KEYS */;
INSERT INTO `fa_user_token` VALUES ('04961c341151b61f9f9ec2b15325afed7df1443e',5,1791548235,1794140235),('160eb2cb6f3b4ce54bbea149502f7438763f70b9',7,1791349721,1793941721),('1d31198f054e7f5542647b9d73809d0208e2cc3e',5,1791526290,1794118290),('2b87a45e75b9dd4939c41c11c9cfdfcfb6c0880b',2,1791344838,1793936838),('32f1a9c5515d7cbcb57a273f08a948ebeac4840b',8,1791354449,1793946449),('3a2b171b33092951d0bb62515575e41e89e85a9c',5,1791461413,1794053413),('3d1cbdab7e6c3b5fba04200fa71cde3b035f95a1',3,1791345707,1793937707),('40884dcccf57d4f621167db0006caff01742fa32',4,1791345765,1793937765),('4211d1150741c91c00f1afd7743968fa0fbccb6c',3,1791345669,1793937669),('4e1480af39513924e03516719489d0269fb58cdb',6,1791348321,1793940321),('80686ee77bc822414dc3382c9e27f108fa7ce584',5,1791460870,1794052870),('84bc64398e290cf6570ef66389992eff1a3f443b',5,1791461235,1794053235),('9bb68ed00102a31d741cef58cfb2d0ef1ab39c2b',8,1791354448,1793946448),('cd691d48577b43e3987d5048058b23a99c2f6b33',5,1791460895,1794052895),('efc4e03ecd3fabc4d721da6409d2beb205e9f75f',5,1791502457,1794094457),('f264825618ccba3ee2217389667eb3aac6a11c1c',10,1791461480,1794053480),('f2a61f28c55963e4a2f527db1aad23b50fdc58d9',2,1791351047,1793943047),('ff511e94f00a0ba655ce8e625ec9fbcc977082ad',2,1791349634,1793941634);
/*!40000 ALTER TABLE `fa_user_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fa_version`
--

DROP TABLE IF EXISTS `fa_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fa_version` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `oldversion` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '旧版本号',
  `newversion` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '新版本号',
  `packagesize` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '包大小',
  `content` varchar(500) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '升级内容',
  `downloadurl` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '下载地址',
  `enforce` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '强制更新',
  `createtime` bigint DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint DEFAULT NULL COMMENT '更新时间',
  `weigh` int NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='版本表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fa_version`
--

LOCK TABLES `fa_version` WRITE;
/*!40000 ALTER TABLE `fa_version` DISABLE KEYS */;
/*!40000 ALTER TABLE `fa_version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'fastadmin'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-09 20:49:41
