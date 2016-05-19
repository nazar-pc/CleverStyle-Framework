CREATE TABLE IF NOT EXISTS `[prefix]crud_test_advanced` (
	`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(1024) NOT NULL,
	`description` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]crud_test_advanced_joined_table1` (
	`id` smallint(5) unsigned NOT NULL,
	`value` tinyint(1) unsigned NOT NULL,
	`lang` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]crud_test_advanced_joined_table2` (
	`id` smallint(5) unsigned NOT NULL,
	`points` tinyint(1) unsigned NOT NULL,
	PRIMARY KEY (`id`, `points`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
