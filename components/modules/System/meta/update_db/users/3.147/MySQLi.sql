ALTER TABLE `[prefix]sessions` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`);
ALTER TABLE `[prefix]sessions` ADD INDEX (`expire`);
