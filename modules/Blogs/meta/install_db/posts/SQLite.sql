CREATE TABLE IF NOT EXISTS `[prefix]blogs_posts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `user` bigint(20) NOT NULL,
  `date` bigint(20) NOT NULL,
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `draft` tinyint(1) NOT NULL
);

CREATE TABLE IF NOT EXISTS `[prefix]blogs_posts_sections` (
  `id` int(11) NOT NULL,
  `section` int(11) NOT NULL
);

CREATE TABLE IF NOT EXISTS `[prefix]blogs_posts_tags` (
  `id` bigint(20) NOT NULL,
  `tag` bigint(20) NOT NULL,
  `lang` varchar(2) NOT NULL
);

CREATE TABLE IF NOT EXISTS `[prefix]blogs_sections` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `parent` smallint(4) NOT NULL DEFAULT '0',
  `title` varchar(1024) NOT NULL,
  `path` varchar(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS `[prefix]blogs_tags` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `text` varchar(1024) NOT NULL
);

CREATE TABLE IF NOT EXISTS `[prefix]texts` (
  `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  `label` varchar(1024) NOT NULL,
  `group` varchar(1024) NOT NULL
);

CREATE TABLE IF NOT EXISTS `[prefix]texts_data` (
  `id` bigint(20) NOT NULL,
  `id_` varchar(25) NOT NULL,
  `lang` varchar(2) NOT NULL,
  `text` mediumtext NOT NULL,
  PRIMARY KEY (`id`,`lang`)
);

CREATE INDEX `[prefix]blogs_posts_user` ON `[prefix]blogs_posts` (`user`);
CREATE INDEX `[prefix]blogs_posts_date` ON `[prefix]blogs_posts` (`date`);
CREATE INDEX `[prefix]blogs_posts_path` ON `[prefix]blogs_posts` (`path`);
CREATE INDEX `[prefix]blogs_posts_draft` ON `[prefix]blogs_posts` (`draft`);

CREATE INDEX `[prefix]blogs_posts_sections_id` ON `[prefix]blogs_posts_sections` (`id`);
CREATE INDEX `[prefix]blogs_posts_sections_section` ON `[prefix]blogs_posts_sections` (`section`);

CREATE INDEX `[prefix]blogs_posts_tags_id` ON `[prefix]blogs_posts_tags` (`id`);
CREATE INDEX `[prefix]blogs_posts_tags_tag` ON `[prefix]blogs_posts_tags` (`tag`);

CREATE INDEX `[prefix]blogs_sections_parent` ON `[prefix]blogs_sections` (`parent`);
CREATE INDEX `[prefix]blogs_sections_path` ON `[prefix]blogs_sections` (`path`);

CREATE UNIQUE INDEX `[prefix]blogs_tags_text` ON `[prefix]blogs_tags` (`text`);

CREATE INDEX IF NOT EXISTS `[prefix]texts_label` ON `[prefix]texts` (`label`, `group`);

CREATE INDEX IF NOT EXISTS `[prefix]texts_data_id_` ON `[prefix]texts_data` (`id_`);
