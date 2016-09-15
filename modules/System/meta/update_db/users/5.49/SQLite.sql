DROP TABLE `[prefix]sign_ins`;
CREATE TABLE `[prefix]sign_ins` (
	`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
	`expire` bigint(20) NOT NULL,
	`login_hash` varchar(56) NOT NULL,
	`ip` varchar(32) NOT NULL
);
CREATE INDEX `[prefix]sign_ins_expire` ON `[prefix]sign_ins` (`expire`,`login_hash`,`ip`);
