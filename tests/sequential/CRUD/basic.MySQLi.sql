CREATE TABLE IF NOT EXISTS `[prefix]crud_test_basic` (
	`id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`max` smallint(5) unsigned NOT NULL,
	`set` varchar(1) NOT NULL,
	`number` smallint(5) unsigned NOT NULL,
	`title` varchar(1024) NOT NULL,
	`description` text NOT NULL,
	`data` text NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]crud_test_basic_joined_table` (
	`id` smallint(5) unsigned NOT NULL,
	`value` tinyint(1) unsigned NOT NULL,
	PRIMARY KEY (`id`, `value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
