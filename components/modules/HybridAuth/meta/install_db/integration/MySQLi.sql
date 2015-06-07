CREATE TABLE IF NOT EXISTS `[prefix]users_social_integration` (
  `id` int(10) unsigned NOT NULL COMMENT 'User id',
  `provider` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'Identifier of provider (unique for every provider)',
  `profile` varchar(1024) NOT NULL,
  UNIQUE KEY `provider` (`provider`(191),`identifier`(191)),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]users_social_integration_contacts` (
	`id` int(10) unsigned NOT NULL COMMENT 'User id',
	`provider` varchar(255) NOT NULL,
	`identifier` varchar(255) NOT NULL,
	KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
