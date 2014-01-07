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
$Config						= Config::instance();
$gallery					= Photo_gallery::instance()->get_gallery($Config->route[1]);
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->photo_gallery_editing_of_gallery($gallery['title']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Photo_gallery/galleries/browse';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->photo_gallery_editing_of_gallery($gallery['title'])
	).
	h::{'table.cs-table-borderless.cs-center-all'}(
		h::{'thead tr th'}(
			$L->photo_gallery_gallery_title,
			($Config->core['simple_admin_mode'] ? false : h::info('photo_gallery_gallery_path')),
			$L->photo_gallery_gallery_description,
			$L->state,
			$L->photo_gallery_gallery_preview_image
		),
		h::{'tbody tr td'}(
			h::{'input[name=edit[title]]'}([
				$gallery['title']
			]),
			($Config->core['simple_admin_mode'] ? false : h::{'input[name=edit[path]]'}([
				$gallery['path']
			])),
			h::{'textarea[name=edit[description]]'}($gallery['description']),
			h::{'input[type=radio][name=edit[active]]'}([
				'value'		=> [0, 1],
				'in'		=> [$L->off, $L->on],
				'checked'	=> $gallery['active']
			]),
			h::{'input[type=radio][name=edit[preview_image]]'}([
				'value'		=> ['first', 'last'],
				'in'		=> [$L->photo_gallery_first, $L->photo_gallery_last],
				'checked'	=> $gallery['first']
			])
		)
	).
	h::{'input[type=hidden][name=edit[id]]'}([
		'value'	=> $gallery['id']
	])
);
