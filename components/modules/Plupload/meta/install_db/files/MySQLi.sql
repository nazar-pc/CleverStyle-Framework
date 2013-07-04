CREATE TABLE IF NOT EXISTS `[prefix]plupload_files` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`user` int(10) unsigned NOT NULL,
	`uploaded` bigint(20) unsigned NOT NULL,
	`source` varchar(255) NOT NULL COMMENT 'Path locally on storage',
	`url` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `url` (`url`),
	KEY `user` (`user`),
	KEY `uploaded` (`uploaded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]plupload_files_tags` (
	`id` bigint(20) unsigned NOT NULL,
	`tag` varchar(255) NOT NULL,
	PRIMARY KEY (`id`,`tag`),
	KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
