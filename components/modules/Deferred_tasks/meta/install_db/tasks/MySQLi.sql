CREATE TABLE IF NOT EXISTS `[prefix]deferred_tasks_tasks` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`begin` bigint(20) unsigned NOT NULL COMMENT 'Time, after which task execution should begin',
	`started` bigint(20) unsigned NOT NULL COMMENT 'Time, when execution started',
	`expected` bigint(20) NOT NULL COMMENT 'Maximum time, when task is expected to be done. If it exists after this time - it will be executed again.',
	`priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - low priority, 1 - normal, 2 - high',
	PRIMARY KEY (`id`),
	KEY `begin` (`begin`),
	KEY `started` (`started`),
	KEY `expected` (`expected`),
	KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;