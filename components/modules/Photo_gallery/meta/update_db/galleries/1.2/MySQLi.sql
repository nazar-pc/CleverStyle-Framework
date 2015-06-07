ALTER TABLE `[prefix]photo_gallery_galleries` DROP INDEX `path`, ADD UNIQUE `path` (`path`(191));
ALTER TABLE `[prefix]photo_gallery_galleries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]photo_gallery_images` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]photo_gallery_galleries`;
OPTIMIZE TABLE `[prefix]photo_gallery_galleries`;
REPAIR TABLE `[prefix]photo_gallery_images`;
OPTIMIZE TABLE `[prefix]photo_gallery_images`;
