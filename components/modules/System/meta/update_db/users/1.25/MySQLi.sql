ALTER TABLE `[prefix]sessions` CHANGE `ip` `remote_addr` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()';
ALTER TABLE `[prefix]sessions` CHANGE `forwarded_for` `ip` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'hex value, obtained by function ip2hex()';
ALTER TABLE `[prefix]sessions` DROP `client_ip`;
