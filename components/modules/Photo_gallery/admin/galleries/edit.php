<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\modules\Photo_gallery;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$Config  = Config::instance();
$gallery = Photo_gallery::instance()->get_gallery(Route::instance()->route[2]);
$Index   = Index::instance();
$L       = Language::instance();
Page::instance()->title($L->photo_gallery_editing_of_gallery($gallery['title']));
$Index->cancel_button_back    = true;
$Index->action                = 'admin/Photo_gallery/galleries/browse';
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::{'h2.cs-text-center'}(
		$L->photo_gallery_editing_of_gallery($gallery['title'])
	).
	h::label($L->photo_gallery_gallery_title).
	h::{'input[is=cs-input-text][name=edit[title]]'}(
		[
			'value' => $gallery['title']
		]
	).
	($Config->core['simple_admin_mode'] ? '' :
		h::label(h::info('photo_gallery_gallery_path')).
		h::{'input[is=cs-input-text][name=edit[path]]'}(
			[
				'value' => $gallery['path']
			]
		)
	).
	h::label($L->photo_gallery_gallery_description).
	h::{'textarea[is=cs-textarea][name=edit[description]]'}($gallery['description']).
	h::label($L->state).
	h::{'div radio[name=edit[active]]'}(
		[
			'value'   => [0, 1],
			'in'      => [$L->off, $L->on],
			'checked' => $gallery['active']
		]
	).
	h::label($L->photo_gallery_gallery_start_from).
	h::{'div radio[name=edit[preview_image]]'}(
		[
			'value'   => ['first', 'last'],
			'in'      => [$L->photo_gallery_first_uploaded, $L->photo_gallery_last_uploaded],
			'checked' => $gallery['preview_image']
		]
	).
	h::br().
	h::{'input[type=hidden][name=edit[id]]'}(
		[
			'value' => $gallery['id']
		]
	)
);
