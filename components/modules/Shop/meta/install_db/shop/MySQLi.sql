CREATE TABLE IF NOT EXISTS `[prefix]shop_attributes` (
	`id` int(11) NOT NULL,
	`type` tinyint(4) NOT NULL,
	`title` varchar(1024) NOT NULL,
	`title_internal` varchar(1024) NOT NULL,
	`value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_categories` (
	`id` int(11) NOT NULL,
	`parent` int(11) NOT NULL,
	`title` varchar(1024) NOT NULL,
	`description` text NOT NULL,
	`title_attribute` int(11) NOT NULL,
	`visible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_categories_attributes` (
	`id` int(11) NOT NULL COMMENT 'Category id',
	`attribute` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_items` (
	`id` int(11) NOT NULL,
	`date` bigint(20) NOT NULL,
	`category` int(11) NOT NULL,
	`price` float NOT NULL DEFAULT '0',
	`in_stock` int(11) NOT NULL DEFAULT '0' COMMENT 'How much items are in in stock',
	`soon` tinyint(1) NOT NULL,
	`listed` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_items_attributes` (
	`id` int(11) NOT NULL COMMENT 'Item id',
	`attribute` int(11) NOT NULL,
	`numeric_value` float NOT NULL,
	`string_value` varchar(1024) NOT NULL,
	`text_value` text NOT NULL,
	`lang` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_items_images` (
	`id` int(11) NOT NULL COMMENT 'Item id',
	`image` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_items_tags` (
	`id` int(11) NOT NULL COMMENT 'Item id',
	`tag` int(11) NOT NULL,
	`lang` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_orders` (
	`id` bigint(20) NOT NULL,
	`user` int(11) NOT NULL,
	`date` bigint(20) NOT NULL,
	`shipping_type` tinyint(4) NOT NULL,
	`shipping_phone` varchar(255) NOT NULL,
	`shipping_address` text NOT NULL,
	`status` tinyint(4) NOT NULL,
	`comment` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_orders_history` (
	`id` bigint(20) NOT NULL,
	`date` bigint(20) NOT NULL,
	`status` tinyint(4) NOT NULL,
	`comment` text NOT NULL COMMENT 'Can be used for emails'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_orders_items` (
	`id` bigint(20) NOT NULL COMMENT 'Order id',
	`item` int(11) NOT NULL,
	`units` int(11) NOT NULL,
	`price` float NOT NULL COMMENT 'Total price for all units (may include discount)',
	`unit_price` float NOT NULL COMMENT 'Original price of one unit'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_order_statuses` (
	`id` int(11) NOT NULL,
	`title` varchar(1024) NOT NULL,
	`type` tinyint(4) NOT NULL,
	`color` varchar(255) NOT NULL,
	`send_update_status_email` tinyint(1) NOT NULL,
	`comment` text NOT NULL COMMENT 'Can be used for emails'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_shipping_types` (
	`id` tinyint(4) NOT NULL,
	`price` int(11) NOT NULL,
	`phone_needed` tinyint(1) NOT NULL,
	`address_needed` tinyint(1) NOT NULL,
	`title` varchar(1024) NOT NULL,
	`description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `[prefix]shop_tags` (
	`id` int(11) NOT NULL,
	`text` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `[prefix]shop_attributes`
ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`), ADD KEY `title_internal` (`title_internal`(255));

ALTER TABLE `[prefix]shop_categories`
ADD PRIMARY KEY (`id`), ADD KEY `parent` (`parent`), ADD KEY `visible` (`visible`);

ALTER TABLE `[prefix]shop_categories_attributes`
ADD PRIMARY KEY (`id`,`attribute`);

ALTER TABLE `[prefix]shop_items`
ADD PRIMARY KEY (`id`), ADD KEY `date` (`date`), ADD KEY `category` (`category`), ADD KEY `in_stock` (`in_stock`), ADD KEY `listed` (`listed`);

ALTER TABLE `[prefix]shop_items_attributes`
ADD PRIMARY KEY (`id`,`attribute`,`lang`), ADD KEY `numeric_value` (`numeric_value`), ADD KEY `string_value` (`string_value`(255)), ADD KEY `lang` (`lang`), ADD FULLTEXT KEY `text_value` (`text_value`);

ALTER TABLE `[prefix]shop_items_images`
ADD KEY `id` (`id`);

ALTER TABLE `[prefix]shop_items_tags`
ADD PRIMARY KEY (`id`,`tag`,`lang`), ADD KEY `tag` (`tag`), ADD KEY `lang` (`lang`);

ALTER TABLE `[prefix]shop_orders`
ADD PRIMARY KEY (`id`), ADD KEY `user` (`user`), ADD KEY `date` (`date`), ADD KEY `status` (`status`);

ALTER TABLE `[prefix]shop_orders_history`
ADD PRIMARY KEY (`id`,`date`), ADD KEY `date` (`date`);

ALTER TABLE `[prefix]shop_orders_items`
ADD PRIMARY KEY (`id`,`item`), ADD KEY `item` (`item`);

ALTER TABLE `[prefix]shop_order_statuses`
ADD PRIMARY KEY (`id`);

ALTER TABLE `[prefix]shop_shipping_types`
ADD PRIMARY KEY (`id`);

ALTER TABLE `[prefix]shop_tags`
ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `text` (`text`(255));

ALTER TABLE `[prefix]shop_attributes`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `[prefix]shop_categories`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `[prefix]shop_items`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `[prefix]shop_orders`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `[prefix]shop_order_statuses`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `[prefix]shop_shipping_types`
MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT;

ALTER TABLE `[prefix]shop_tags`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
