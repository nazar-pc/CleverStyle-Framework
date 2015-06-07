ALTER TABLE `[prefix]users_social_integration` DROP INDEX `provider`, ADD UNIQUE `provider` (`provider`(191), `identifier`(191));
ALTER TABLE `[prefix]users_social_integration` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]users_social_integration_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]users_social_integration`;
OPTIMIZE TABLE `[prefix]users_social_integration`;
REPAIR TABLE `[prefix]users_social_integration_contacts`;
OPTIMIZE TABLE `[prefix]users_social_integration_contacts`;
