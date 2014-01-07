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
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->photo_gallery_addition_of_gallery);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Photo_gallery/galleries/browse';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->photo_gallery_addition_of_gallery
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
			h::{'input[name=add[title]]'}(),
			($Config->core['simple_admin_mode'] ? false : h::{'input[name=add[path]]'}()),
			h::{'textarea[name=add[description]]'}(),
			h::{'input[type=radio][name=add[active]][checked=1]'}([
				'value'	=> [0, 1],
				'in'	=> [$L->off, $L->on]
			]),
			h::{'input[type=radio][name=add[preview_image]][checked=last]'}([
				'value'	=> ['first', 'last'],
				'in'	=> [$L->photo_gallery_first, $L->photo_gallery_last]
			])
		)
	)
);
