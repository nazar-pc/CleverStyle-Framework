CREATE TABLE IF NOT EXISTS `[prefix]polls` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`title` varchar(1024) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]polls_options` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`poll` int(11) NOT NULL,
	`title` varchar(1024) NOT NULL,
	`votes` int(11) NOT NULL,
	PRIMARY KEY (`id`),
	KEY `poll` (`poll`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]polls_options_answers` (
	`id` int(11) NOT NULL,
	`option` int(11) NOT NULL,
	`user` int(11) NOT NULL,
	PRIMARY KEY (`id`,`user`),
	KEY `option` (`option`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
