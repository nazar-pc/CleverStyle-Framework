ALTER TABLE `[prefix]oauth2_clients` CHANGE `id` `id` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `[prefix]oauth2_clients_grant_access` CHANGE `id` `id` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `[prefix]oauth2_clients_sessions` CHANGE `id` `id` VARCHAR( 32 ) NOT NULL COMMENT 'Client id';
