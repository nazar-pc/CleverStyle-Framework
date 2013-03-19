CREATE TABLE IF NOT EXISTS `[prefix]oauth2_clients` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`secret` varchar(32) NOT NULL,
	`name` varchar(255) NOT NULL,
	`domain` varchar(255) NOT NULL,
	`active` tinyint(1) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `[prefix]oauth2_clients_sessions` (
	`client` int(10) unsigned NOT NULL COMMENT 'Client id',
	`user` int(10) unsigned NOT NULL COMMENT 'User id',
	`created` bigint(20) unsigned NOT NULL,
	`expire` bigint(20) unsigned NOT NULL,
	`access_token` varchar(32) NOT NULL,
	`refresh_token` varchar(32) NOT NULL,
	UNIQUE KEY `access_token` (`access_token`),
	UNIQUE KEY `refresh_token` (`refresh_token`),
	KEY `client` (`client`),
	KEY `user` (`user`),
	KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;