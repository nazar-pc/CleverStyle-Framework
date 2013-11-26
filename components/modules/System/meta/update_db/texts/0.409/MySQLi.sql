ALTER TABLE `[prefix]texts_data` ADD `text_md5` VARCHAR(32) NOT NULL,
ADD INDEX (`text_md5`);