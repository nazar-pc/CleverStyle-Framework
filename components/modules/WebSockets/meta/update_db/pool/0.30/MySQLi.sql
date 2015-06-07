ALTER TABLE `[prefix]websockets_pool` DROP PRIMARY KEY, ADD PRIMARY KEY (`address`(191));
ALTER TABLE `[prefix]websockets_pool` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
