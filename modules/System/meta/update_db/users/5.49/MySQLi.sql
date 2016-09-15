ALTER TABLE `[prefix]sign_ins` DROP PRIMARY KEY, ADD INDEX (`expire`, `login_hash`, `ip`);
ALTER TABLE `[prefix]sign_ins` DROP INDEX `id`, ADD PRIMARY KEY (`id`);
