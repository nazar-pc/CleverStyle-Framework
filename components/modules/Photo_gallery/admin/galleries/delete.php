<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$gallery					= Photo_gallery::instance()->get_gallery(Config::instance()->route[1]);
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->photo_gallery_deletion_of_gallery($gallery['title']));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Photo_gallery/galleries/browse';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->photo_gallery_sure_to_delete_gallery($gallery['title'])
	).
	h::{'button[type=submit]'}($L->yes).
	h::{'input[type=hidden][name=delete]'}([
		'value'	=> $gallery['id']
	])
);