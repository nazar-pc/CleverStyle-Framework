ALTER TABLE `[prefix]texts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]texts_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]texts`;
OPTIMIZE TABLE `[prefix]texts`;
REPAIR TABLE `[prefix]texts_data`;
OPTIMIZE TABLE `[prefix]texts_data`;
