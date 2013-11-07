CREATE TABLE IF NOT EXISTS `[prefix]deferred_tasks_tasks` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`begin` bigint(20) unsigned NOT NULL COMMENT 'Time, after which task execution should begin',
	`started` bigint(20) unsigned NOT NULL COMMENT 'Time, when execution started',
	`started_hash` varchar(32) NOT NULL,
	`expected` bigint(20) NOT NULL COMMENT 'Max time in seconds, during which task is expected to be finished. If it exists longer than this time - it will be executed again.',
	`priority` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - low priority, 1 - normal, 2 - high',
	`module` varchar(255) NOT NULL,
	`data` text NOT NULL,
	PRIMARY KEY (`id`),
	KEY `begin` (`begin`),
	KEY `started` (`started`),
	KEY `expected` (`expected`),
	KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;