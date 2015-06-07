CREATE TABLE IF NOT EXISTS `[prefix]oauth2_clients` (
	`id` varchar(32) NOT NULL,
	`secret` varchar(32) NOT NULL,
	`name` varchar(255) NOT NULL,
	`domain` varchar(255) NOT NULL,
	`active` tinyint(1) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]oauth2_clients_grant_access` (
	`id` varchar(32) NOT NULL,
	`user` int(10) unsigned NOT NULL COMMENT 'User id',
	PRIMARY KEY (`id`,`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]oauth2_clients_sessions` (
	`id` varchar(32) NOT NULL COMMENT 'Client id',
	`user` int(10) unsigned NOT NULL COMMENT 'User id',
	`session` varchar(32) NOT NULL,
	`created` bigint(20) unsigned NOT NULL,
	`expire` bigint(20) unsigned NOT NULL,
	`access_token` varchar(32) NOT NULL,
	`refresh_token` varchar(32) NOT NULL,
	`code` varchar(32) NOT NULL,
	`type` set('code','token') NOT NULL DEFAULT 'code',
	`redirect_uri` varchar(32) NOT NULL,
	UNIQUE KEY `access_token` (`access_token`),
	UNIQUE KEY `refresh_token` (`refresh_token`),
	KEY `id` (`id`),
	KEY `user` (`user`),
	KEY `expire` (`expire`),
	KEY `session` (`session`),
	KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
