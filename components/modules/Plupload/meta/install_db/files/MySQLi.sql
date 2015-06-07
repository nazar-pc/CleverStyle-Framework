CREATE TABLE IF NOT EXISTS `[prefix]plupload_files` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`user` int(10) unsigned NOT NULL,
	`uploaded` bigint(20) unsigned NOT NULL,
	`source` varchar(255) NOT NULL COMMENT 'Path locally on storage',
	`url` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `url` (`url`(191)),
	KEY `user` (`user`),
	KEY `uploaded` (`uploaded`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]plupload_files_tags` (
	`id` bigint(20) unsigned NOT NULL,
	`tag` varchar(255) NOT NULL,
	PRIMARY KEY (`id`,`tag`(191)),
	KEY `tag` (`tag`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
