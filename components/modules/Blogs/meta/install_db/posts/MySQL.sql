CREATE TABLE `[prefix]blogs_comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `parent` bigint(20) NOT NULL DEFAULT '0',
  `post` bigint(20) NOT NULL,
  `user` bigint(20) NOT NULL,
  `date` bigint(20) NOT NULL,
  `text` text NOT NULL,
  `lang` varchar(2) NOT NULL COMMENT 'Language of original message',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `post` (`post`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]blogs_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user` bigint(20) unsigned NOT NULL,
  `date` bigint(20) unsigned NOT NULL,
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`),
  KEY `date` (`date`),
  KEY `path` (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]blogs_posts_sections` (
  `id` int(11) NOT NULL COMMENT 'Post id',
  `section` int(11) NOT NULL COMMENT 'Category id',
  KEY `id` (`id`),
  KEY `section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]blogs_posts_tags` (
  `id` bigint(20) NOT NULL COMMENT 'Post id',
  `tag` bigint(20) NOT NULL COMMENT 'Tag id',
  KEY `id` (`id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]blogs_sections` (
  `id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `parent` smallint(4) unsigned NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `path` (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE `[prefix]blogs_tags` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `text` (`text`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]texts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `group` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`(255),`group`(255))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]texts_data` (
  `id` bigint(20) NOT NULL COMMENT 'id from texts table',
  `id_` varchar(25) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`id`,`lang`),
  KEY `id_` (`id_`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;