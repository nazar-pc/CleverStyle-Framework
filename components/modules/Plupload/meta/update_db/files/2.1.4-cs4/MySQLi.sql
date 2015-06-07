ALTER TABLE `[prefix]plupload_files` DROP INDEX `url`, ADD UNIQUE `url` (`url`(191));
ALTER TABLE `[prefix]plupload_files` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]plupload_files_tags` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `tag`(191));
ALTER TABLE `[prefix]plupload_files_tags` DROP INDEX `tag`, ADD INDEX `tag` (`tag`(191));
ALTER TABLE `[prefix]plupload_files_tags` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]plupload_files`;
OPTIMIZE TABLE `[prefix]plupload_files`;
REPAIR TABLE `[prefix]plupload_files_tags`;
OPTIMIZE TABLE `[prefix]plupload_files_tags`;
