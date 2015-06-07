CREATE TABLE IF NOT EXISTS `[prefix]blockchain_payment_transactions` (
	`id` bigint(20) unsigned NOT NULL,
	`amount` float NOT NULL,
	`currency` varchar(255) NOT NULL,
	`user` int(10) unsigned NOT NULL,
	`module` varchar(1024) NOT NULL,
	`purpose` varchar(1024) NOT NULL,
	`description` text NOT NULL,
	`amount_btc` float NOT NULL,
	`destination_address` varchar(255) NOT NULL,
	`input_address` varchar(255) NOT NULL,
	`created` bigint(20) unsigned NOT NULL,
	`paid` bigint(20) unsigned NOT NULL,
	`confirmed` bigint(20) unsigned NOT NULL,
	`secret` varchar(255) NOT NULL,
	`transaction_hash` varchar(255) NOT NULL,
	`input_transaction_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE `[prefix]blockchain_payment_transactions`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `module` (`module`(191),`purpose`(191)), ADD KEY `user` (`user`), ADD KEY `created` (`created`), ADD KEY `paid` (`paid`), ADD KEY `confirmed` (`confirmed`), ADD KEY `secret` (`secret`);


ALTER TABLE `[prefix]blockchain_payment_transactions`
MODIFY `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
