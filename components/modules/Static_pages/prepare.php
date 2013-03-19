<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h;
global $Index, $Config, $Static_pages;
$Index->title_auto	= false;
$data				= $Static_pages->get(
	HOME ? $Static_pages->get_structure()['pages']['index'] : $Config->route[0]
);
global $Page;
if ($data['interface']) {
	if (!HOME) {
		if (!empty($Static_pages->title)) {
			foreach ($Static_pages->title as $title) {
				$Page->title($title);
			}
			unset($title);
		}
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