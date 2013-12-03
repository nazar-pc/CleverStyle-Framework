<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2013
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Index				= Index::instance();
$Index->title_auto	= false;
$L					= Language::instance();
$Page				= Page::instance();
$Page->title($L->administration)->title($L->Photo_gallery);
$Page->main_sub_menu	= h::{'li.uk-active a'}(
	$L->photo_gallery_galleries,
	[
		'href'	=> 'admin/Photo_gallery'
	]
);