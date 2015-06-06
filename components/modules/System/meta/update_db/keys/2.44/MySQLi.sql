ALTER TABLE `[prefix]keys` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]keys`;
OPTIMIZE TABLE `[prefix]keys`;
