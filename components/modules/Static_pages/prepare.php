<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h,
			cs\Config,
			cs\Index,
			cs\Page;
$Static_pages		= Static_pages::instance();
$data				= $Static_pages->get(
	HOME ? $Static_pages->get_structure()['pages']['index'] : Config::instance()->route[0]
);
$Page				= Page::instance();
if ($data['interface']) {
	if (!HOME) {
		Index::instance()->title_auto	= false;
		$Page->title($data['title']);
	}
	$Page->Keywords		= keywords($data['title']);
	$Page->Description	= description($data['content']);
	$Page->og('type', 'article');
	$Page->content(
		h::section($data['content'])
	);
} else {
	interface_off();
	$Page->Content	= $data['content'];
}