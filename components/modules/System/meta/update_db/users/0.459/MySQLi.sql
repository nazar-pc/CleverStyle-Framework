ALTER TABLE `[prefix]groups_permissions` DROP INDEX `id`;
ALTER TABLE `[prefix]groups_permissions` DROP INDEX `permission`;
ALTER TABLE `[prefix]groups_permissions` ADD PRIMARY KEY ( `id` , `permission` ) ;
ALTER TABLE `[prefix]users_permissions` DROP INDEX `id`;
ALTER TABLE `[prefix]users_permissions` DROP INDEX `permission`;
ALTER TABLE `[prefix]users_permissions` ADD PRIMARY KEY ( `id` , `permission` ) ;
