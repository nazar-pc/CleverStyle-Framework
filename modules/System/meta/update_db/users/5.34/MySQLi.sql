ALTER TABLE `[prefix]users` CHANGE `reg_ip` `reg_ip` VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'hex value, obtained by function ip2hex()';
