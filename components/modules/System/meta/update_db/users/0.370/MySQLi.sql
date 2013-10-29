DELETE FROM `[prefix]users`
WHERE
	`login_hash`	= '' AND
	`email_hash`	= '' AND
	`password_hash`	= '' AND
	`status`		= '-1' AND
	`id`			!= 1 AND
	`id`			!= 2;
