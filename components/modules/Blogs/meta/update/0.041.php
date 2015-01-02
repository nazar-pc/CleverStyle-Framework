<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Config, $db, $L;
/**
 *	@var \cs\DB\_Abstract $cdb
 */
$cdb	= $db->{$Config->module('Blogs')->db('posts')}();
$tags	= $cdb->qfa(
	"SELECT `t`.`id`, `t`.`label`, `d`.`text`, `d`.`lang`
	FROM `[prefix]texts` AS `t`
	LEFT JOIN `[prefix]texts_data` AS `d`
	ON `t`.`id` = `d`.`id`
	WHERE `group` = 'Blogs/tags'"
);
foreach ($tags as $t) {
	if ($id	= $cdb->qfs([
		"SELECT `id`
		FROM `[prefix]blogs_tags`
		WHERE `text` = '%s'
		LIMIT 1",
		$t['text']
	])) {
		$cdb->q(
			"UPDATE `[prefix]blogs_posts_tags`
			SET `tag` = '$t[id]'
			WHERE `tag` = id"
		);
		$cdb->q(
			"DELETE FROM `[prefix]blogs_tags`
			WHERE `id` = $id"
		);
		continue;
	}
	$cdb->q(
		"UPDATE `[prefix]blogs_tags`
		SET
			`text`	= '%s'
		WHERE `id` = '%s'",
		$t['text'],
		$t['label']
	);
}
unset($t, $id);
$tags	= implode(',', array_column($tags, 'id'));
$cdb->q([
	"DELETE FROM `[prefix]texts`
	WHERE `id` IN($tags)",
	"DELETE FROM `[prefix]texts_data`
	WHERE `id` IN($tags)"
]);
$cdb->q(
	"UPDATE `[prefix]blogs_posts_tags`
	SET `lang` = '%s'
	WHERE `lang` = ''",
	$L->clang
);
