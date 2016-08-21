CREATE TABLE `[prefix]config` (
  `domain` varchar(255) NOT NULL,
  `core` mediumtext NOT NULL,
  `db` mediumtext NOT NULL,
  `storage` mediumtext NOT NULL,
  `components` mediumtext NOT NULL,
  PRIMARY KEY (`domain`)
);

CREATE TABLE `[prefix]groups` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `title` varchar(1024) NOT NULL,
  `description` TEXT NOT NULL
);

INSERT INTO `[prefix]groups` (`title`, `description`) VALUES ('Administrators', 'Administrators'), ('Users', 'Users');

CREATE TABLE `[prefix]groups_permissions` (
  `id` smallint(5) NOT NULL,
  `permission` smallint(5) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`, `permission`)
);

INSERT INTO `[prefix]groups_permissions` (`id`, `permission`, `value`) VALUES (1, 2, 1), (2, 2, 0);

CREATE TABLE `[prefix]keys` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `key` varbinary(56) NOT NULL,
  `expire` bigint(20) NOT NULL DEFAULT '0',
  `data` mediumtext NOT NULL
);

CREATE TABLE `[prefix]permissions` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `label` varchar(1024) NOT NULL,
  `group` varchar(1024) NOT NULL
);

INSERT INTO `[prefix]permissions` (`label`, `group`) VALUES ('index', 'admin/System'), ('index', 'api/System');

CREATE TABLE `[prefix]sessions` (
  `id` varchar(32) NOT NULL,
  `user` int(11) NOT NULL,
  `created` bigint(20) NOT NULL,
  `expire` bigint(20) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `remote_addr` varchar(32) NOT NULL,
  `ip` varchar(32) NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `[prefix]sign_ins` (
  `id` bigint(20) NOT NULL,
  `expire` bigint(20) NOT NULL,
  `login_hash` varchar(56) NOT NULL,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY (`expire`,`login_hash`,`ip`)
);

CREATE TABLE `[prefix]texts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `label` varchar(1024) NOT NULL,
  `group` varchar(1024) NOT NULL
);

CREATE TABLE `[prefix]texts_data` (
  `id` bigint(20) NOT NULL,
  `id_` varchar(25) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `text` mediumtext NOT NULL,
  `text_md5` varchar(32) NOT NULL,
  PRIMARY KEY (`id`,`lang`)
);

CREATE TABLE `[prefix]users` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `login` varchar(1024) NOT NULL,
  `login_hash` varchar(56) NOT NULL,
  `username` varchar(1024) NOT NULL DEFAULT '',
  `password_hash` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(1024) NOT NULL DEFAULT '',
  `email_hash` varchar(56) NOT NULL DEFAULT '',
  `language` varchar(255) NOT NULL DEFAULT '',
  `timezone` varchar(255) NOT NULL DEFAULT '',
  `reg_date` bigint(20) NOT NULL DEFAULT '0',
  `reg_ip` varchar(32) NOT NULL DEFAULT '',
  `reg_key` varchar(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '-1',
  `block_until` bigint(20) NOT NULL DEFAULT '0',
  `avatar` varchar(255) NOT NULL DEFAULT ''
);

INSERT INTO `[prefix]users` (`login`, `login_hash`, `status`) VALUES ('guest', '5cf371cef0648f2656ddc13b773aa642251267dbd150597506e96c3a', '1');

CREATE TABLE `[prefix]users_data` (
  `id` int(10) NOT NULL,
  `item` varchar(1024) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`,`item`)
);

CREATE TABLE `[prefix]users_groups` (
  `id` int(10) NOT NULL,
  `group` smallint(5) NOT NULL,
  `priority` smallint(5) NOT NULL,
  PRIMARY KEY (`id`,`group`)
);

INSERT INTO `[prefix]users_groups` (`id`, `group`, `priority`) VALUES (2, 1, 0), (2, 2, 1);

CREATE TABLE `[prefix]users_permissions` (
  `id` int(10) NOT NULL,
  `permission` smallint(5) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`, `permission`)
);

CREATE INDEX `[prefix]texts_label` ON `[prefix]texts` (`label`,`group`);
CREATE INDEX `[prefix]users_login` ON `[prefix]users` (`login`,`username`,`email`);
CREATE INDEX `[prefix]users_login_hash` ON `[prefix]users` (`login_hash`);
CREATE INDEX `[prefix]users_email_hash` ON `[prefix]users` (`email_hash`);
CREATE INDEX `[prefix]users_language` ON `[prefix]users` (`language`);
CREATE INDEX `[prefix]users_status` ON `[prefix]users` (`status`);
CREATE UNIQUE INDEX `[prefix]sign_ins_id` ON `[prefix]sign_ins` (`id`);
CREATE INDEX `[prefix]permissions_label` ON `[prefix]permissions` (`label`);
CREATE INDEX `[prefix]permissions_group` ON `[prefix]permissions` (`group`);
CREATE INDEX `[prefix]users_groups_group` ON `[prefix]users_groups` (`group`);
CREATE INDEX `[prefix]users_groups_priority` ON `[prefix]users_groups` (`priority`);
CREATE INDEX `[prefix]users_data_item` ON `[prefix]users_data` (`item`);
CREATE INDEX `[prefix]sessions_expire` ON `[prefix]sessions` (`expire`);
CREATE INDEX `[prefix]sessions_user` ON `[prefix]sessions` (`user`);
CREATE INDEX `[prefix]keys_key` ON `[prefix]keys` (`key`);
CREATE INDEX `[prefix]keys_expire` ON `[prefix]keys` (`expire`);
CREATE INDEX `[prefix]texts_data_id_` ON `[prefix]texts_data` (`id_`);
CREATE INDEX `[prefix]texts_data_text_md5` ON `[prefix]texts_data` (`text_md5`);
