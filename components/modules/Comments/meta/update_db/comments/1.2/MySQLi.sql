ALTER TABLE `[prefix]comments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]comments`;
OPTIMIZE TABLE `[prefix]comments`;
