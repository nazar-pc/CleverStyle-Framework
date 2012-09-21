CREATE TABLE IF NOT EXISTS `[prefix]users_social_integration` (
  `id` int(10) unsigned NOT NULL COMMENT 'User id',
  `provider` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'Identifier of provider (unique for every provider)',
  UNIQUE KEY `provider` (`provider`,`identifier`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;