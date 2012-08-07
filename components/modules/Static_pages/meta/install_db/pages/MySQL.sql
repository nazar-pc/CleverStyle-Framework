CREATE TABLE `[prefix]static_pages` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `category` smallint(4) unsigned NOT NULL,
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `interface` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`),
  KEY `category` (`category`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]static_pages_categories` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `parent` smallint(4) unsigned NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
