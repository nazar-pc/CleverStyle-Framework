CREATE TABLE IF NOT EXISTS `[prefix]content` (
	`key` varchar(255) NOT NULL,
	`title` varchar(1024) NOT NULL,
	`content` text NOT NULL,
	`type` set('text','html') NOT NULL,
	PRIMARY KEY (`key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `[prefix]texts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(1024) NOT NULL,
  `group` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`(191),`group`(191))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]texts_data` (
  `id` bigint(20) NOT NULL COMMENT 'id from texts table',
  `id_` varchar(25) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`id`,`lang`),
  KEY `id_` (`id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;
