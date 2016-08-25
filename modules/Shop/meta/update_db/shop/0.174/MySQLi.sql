ALTER TABLE `[prefix]shop_attributes` DROP INDEX `internal_title`, ADD INDEX `internal_title` (`title_internal`(191));
ALTER TABLE `[prefix]shop_items_attributes` DROP INDEX `string_value`, ADD INDEX `string_value` (`string_value`(191));
