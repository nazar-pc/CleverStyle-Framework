ALTER TABLE `[prefix]polls` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]polls_options` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]polls_options_answers` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]polls`;
OPTIMIZE TABLE `[prefix]polls`;
REPAIR TABLE `[prefix]polls_options`;
OPTIMIZE TABLE `[prefix]polls_options`;
REPAIR TABLE `[prefix]polls_options_answers`;
OPTIMIZE TABLE `[prefix]polls_options_answers`;
