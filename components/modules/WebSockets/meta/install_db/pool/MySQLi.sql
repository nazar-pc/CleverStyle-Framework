CREATE TABLE IF NOT EXISTS `[prefix]websockets_pool` (
	`address` varchar(1024) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4_unicode_ci;

ALTER TABLE `[prefix]websockets_pool`
ADD PRIMARY KEY (`address`(191));
