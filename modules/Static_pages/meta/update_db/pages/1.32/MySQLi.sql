ALTER TABLE `[prefix]static_pages_categories` DROP INDEX `path`, ADD UNIQUE `path` (`path`(191));
