<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;
use
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\Language;

$L                = Language::instance();
$Content          = Content::instance();
$keys             = $Content->get_all();
$current_language = $L->clanguage;
foreach (Config::instance()->core['active_languages'] as $language) {
	$L->change($language);
	foreach ($keys as $key) {
		$data = $Content->get($key);
		$Content->set($key, $data['title'], $data['content'], $data['type']);
	}
}
foreach ($keys as $key) {
	Event::instance()->fire(
		'System/upload_files/del_tag',
		[
			'tag' => "Content/".md5($key).'%'
		]
	);
}
$L->change($current_language);
Cache::instance()->del('Content');
