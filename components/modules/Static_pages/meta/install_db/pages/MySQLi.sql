CREATE TABLE IF NOT EXISTS `[prefix]static_pages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `category` smallint(4) unsigned NOT NULL,
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `interface` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `path` (`path`),
  KEY `category` (`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `[prefix]static_pages_categories` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `parent` smallint(4) unsigned NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`),
  KEY `parent` (`parent`(191))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

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
