CREATE TABLE IF NOT EXISTS `[prefix]comments` (
	`id` bigint(20) NOT NULL AUTO_INCREMENT,
	`parent` bigint(20) NOT NULL DEFAULT '0',
	`module` varchar(255) NOT NULL,
	`item` bigint(20) NOT NULL,
	`user` int(11) NOT NULL,
	`date` bigint(20) NOT NULL,
	`text` text NOT NULL,
	`lang` varchar(2) NOT NULL COMMENT 'Language of original message',
	PRIMARY KEY (`id`),
	KEY `parent` (`parent`),
	KEY `module` (`module`(191),`item`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
