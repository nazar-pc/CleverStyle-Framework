ALTER TABLE `[prefix]blockchain_payment_transactions` DROP INDEX `secret`, ADD INDEX `secret` (`secret`(191));
