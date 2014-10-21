CREATE TABLE IF NOT EXISTS `[prefix]photo_gallery_galleries` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`path` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`active` int(11) NOT NULL,
	`preview_image` set('first','last') NOT NULL,
	`order` int(4) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`),
	UNIQUE KEY `path` (`path`),
	KEY `order` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]photo_gallery_images` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`gallery` int(10) unsigned NOT NULL COMMENT 'Gallery id',
	`user` int(10) unsigned NOT NULL,
	`title` varchar(255) NOT NULL,
	`description` text NOT NULL,
	`date` bigint(20) unsigned NOT NULL,
	`original` varchar(255) NOT NULL,
	`preview` varchar(255) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `gallery` (`gallery`),
	KEY `user` (`user`),
	KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
