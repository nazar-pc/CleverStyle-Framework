CREATE TABLE `[prefix]config` (
  `domain` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `core` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `db` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `storage` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `components` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `replace` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `routing` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Settings';

CREATE TABLE `[prefix]groups` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'WARNING: Never delete first 3 groups!',
  `title` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `[prefix]groups` (`title`, `description`) VALUES ('Administrators', 'Administrators'), ('Users', 'Users'), ('Bots', 'Bots');

CREATE TABLE `[prefix]groups_permissions` (
  `id` smallint(5) unsigned NOT NULL COMMENT 'Group id',
  `permission` smallint(5) unsigned NOT NULL COMMENT 'Permission id',
  `value` tinyint(1) unsigned NOT NULL,
  KEY `id` (`id`),
  KEY `permission` (`permission`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]groups_permissions` (`id`, `permission`, `value`) VALUES (1, 2, 1), (2, 2, 0), (3, 2, 0);

CREATE TABLE `[prefix]keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varbinary(56) NOT NULL COMMENT 'Key may be generated by sha224 algorithm',
  `expire` bigint(20) unsigned NOT NULL DEFAULT '0',
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `key` (`key`(32)),
  KEY `expire` (`expire`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Temporary keys';

CREATE TABLE `[prefix]logins` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `expire` bigint(20) NOT NULL,
  `login_hash` varchar(56) NOT NULL,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY (`expire`,`login_hash`,`ip`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]permissions` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`(255)),
  KEY `group` (`group`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `[prefix]permissions` (`label`, `group`) VALUES ('index', 'admin/System'), ('index', 'api/System');

CREATE TABLE `[prefix]sessions` (
  `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `user` int(11) unsigned NOT NULL COMMENT 'User id',
  `created` bigint(20) unsigned NOT NULL,
  `expire` bigint(20) unsigned NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()',
  `forwarded_for` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()',
  `client_ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()',
  PRIMARY KEY (`id`,`expire`,`user_agent`,`ip`,`forwarded_for`,`client_ip`),
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `[prefix]texts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`(255),`group`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `[prefix]texts_data` (
  `id` bigint(20) NOT NULL COMMENT 'id from texts table',
  `id_` varchar(25) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`,`lang`),
  KEY `id_` (`id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'WARNING: Never delete first 2 users!',
  `login` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `login_hash` varchar(56) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hash method - sha224',
  `username` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(128) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hash method - sha512',
  `email` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `email_hash` varchar(56) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hash method - sha224',
  `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `theme` varchar(512) CHARACTER SET utf8 NOT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `reg_date` bigint(20) unsigned NOT NULL DEFAULT '0',
  `reg_ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()',
  `reg_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '''-1'' - not activated (for example after registration), 0 - inactive, 1 - active',
  `block_until` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_login` bigint(20) unsigned NOT NULL DEFAULT '0',
  `last_ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()',
  `last_online` bigint(20) unsigned NOT NULL DEFAULT '0',
  `gender` tinyint(1) NOT NULL DEFAULT '-1' COMMENT '0 - male, 1 - female, -1 - undefined',
  `birthday` bigint(20) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `icq` int(9) unsigned NOT NULL DEFAULT '0',
  `skype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `about` text COLLATE utf8_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Field for addition data in JSON format',
  PRIMARY KEY (`id`),
  KEY `login` (`login`(5),`username`(5),`email`(5)),
  KEY `login_hash` (`login_hash`(5)),
  KEY `password_hash` (`password_hash`(5)),
  KEY `email_hash` (`email_hash`(5)),
  KEY `language` (`language`(3)),
  KEY `status` (`status`),
  KEY `icq` (`icq`),
  KEY `skype` (`skype`),
  KEY `last_login` (`last_login`),
  KEY `last_online` (`last_online`),
  KEY `gender` (`gender`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `[prefix]users` (`login`, `login_hash`, `status`) VALUES ('guest', '5cf371cef0648f2656ddc13b773aa642251267dbd150597506e96c3a', '1');

CREATE TABLE `[prefix]users_groups` (
  `id` int(10) unsigned NOT NULL COMMENT 'User id',
  `group` smallint(5) unsigned NOT NULL COMMENT 'Group id',
  `priority` smallint(5) unsigned NOT NULL COMMENT 'Lower priority is more important',
  KEY `id` (`id`),
  KEY `group` (`group`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `[prefix]users_groups` (`id`, `group`, `priority`) VALUES (2, 1, 0), (2, 2, 1);

CREATE TABLE `[prefix]users_permissions` (
  `id` int(10) unsigned NOT NULL COMMENT 'User id',
  `permission` smallint(5) unsigned NOT NULL COMMENT 'Permission id',
  `value` tinyint(1) unsigned NOT NULL,
  KEY `id` (`id`),
  KEY `permission` (`permission`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;