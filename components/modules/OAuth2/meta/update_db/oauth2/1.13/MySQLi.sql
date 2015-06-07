ALTER TABLE `[prefix]oauth2_clients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]oauth2_clients_grant_access` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `[prefix]oauth2_clients_sessions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
REPAIR TABLE `[prefix]oauth2_clients`;
OPTIMIZE TABLE `[prefix]oauth2_clients`;
REPAIR TABLE `[prefix]oauth2_clients_grant_access`;
OPTIMIZE TABLE `[prefix]oauth2_clients_grant_access`;
REPAIR TABLE `[prefix]oauth2_clients_sessions`;
OPTIMIZE TABLE `[prefix]oauth2_clients_sessions`;
