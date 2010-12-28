SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `exp_categories` (
  `cat_id` int(10) unsigned NOT NULL auto_increment,
  `cat_name` varchar(250) collate utf8_polish_ci NOT NULL,
  `cat_status` enum('enabled','disabled') collate utf8_polish_ci NOT NULL default 'disabled',
  PRIMARY KEY  (`cat_id`),
  UNIQUE KEY `name` (`cat_name`),
  KEY `name_status` (`cat_name`,`cat_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

CREATE TABLE IF NOT EXISTS `exp_items` (
  `item_id` int(11) NOT NULL auto_increment,
  `cat_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(255) collate utf8_polish_ci NOT NULL,
  `item_note` mediumtext collate utf8_polish_ci NOT NULL,
  `item_quant` decimal(10,2) NOT NULL,
  `item_quant_unit` enum('szt','kg','l','m','h') collate utf8_polish_ci NOT NULL default 'szt',
  `item_value` decimal(10,2) NOT NULL,
  `item_date_buy` date NOT NULL,
  `item_date_add` datetime NOT NULL,
  PRIMARY KEY  (`item_id`),
  KEY `list` (`item_date_buy`,`cat_id`,`item_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

CREATE TABLE IF NOT EXISTS `exp_users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(60) NOT NULL,
  `user_email` varchar(250) NOT NULL,
  `user_login` varchar(60) NOT NULL,
  `user_password` varchar(40) NOT NULL,
  `user_status` enum('enabled','disabled') NOT NULL default 'disabled',
  `user_role` enum('admin','user') NOT NULL default 'user',
  `user_date_add` datetime NOT NULL,
  `user_permissions` mediumtext character set utf8 collate utf8_bin NOT NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_login` (`user_login`),
  KEY `login_password_status` (`user_login`,`user_password`,`user_status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `exp_users` VALUES(1, 'admin', 'admin@example.com', 'admin', '1bb7f06a47ac7b31930e90de8b24910b5fa6d04a', 'enabled', 'admin', NOW(), 0x613a31323a7b733a31303a2243617465676f72696573223b623a313b733a31313a2243617465676f7279416464223b623a313b733a31313a2243617465676f727944656c223b623a313b733a31323a2243617465676f727945646974223b623a313b733a373a224974656d416464223b623a313b733a373a224974656d44656c223b623a313b733a383a224974656d45646974223b623a313b733a353a224974656d73223b623a313b733a373a2255736572416464223b623a313b733a373a225573657244656c223b623a313b733a383a225573657245646974223b623a313b733a353a225573657273223b623a313b7d);

