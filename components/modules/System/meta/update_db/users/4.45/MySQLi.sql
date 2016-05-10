ALTER TABLE `[prefix]groups_permissions` DROP INDEX `permission`;
ALTER TABLE `[prefix]groups_permissions` DROP INDEX `id`, ADD PRIMARY KEY (`id`, `permission`);
ALTER TABLE `[prefix]users_permissions` DROP INDEX `permission`;
ALTER TABLE `[prefix]users_permissions` DROP INDEX `id`, ADD PRIMARY KEY (`id`, `permission`);
