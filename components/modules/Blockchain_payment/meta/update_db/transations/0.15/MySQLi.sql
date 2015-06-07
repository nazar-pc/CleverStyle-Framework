ALTER TABLE `[prefix]blockchain_payment_transactions` DROP INDEX `module`, ADD UNIQUE `module` (`module`(191), `purpose`(191));
ALTER TABLE `[prefix]blockchain_payment_transactions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]blockchain_payment_transactions`;
OPTIMIZE TABLE `[prefix]blockchain_payment_transactions`;
