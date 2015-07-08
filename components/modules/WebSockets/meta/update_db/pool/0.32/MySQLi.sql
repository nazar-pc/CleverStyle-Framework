ALTER TABLE `[prefix]websockets_pool` ADD `date` BIGINT NOT NULL AFTER `address`, ADD INDEX (`date`);
