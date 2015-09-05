<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\modules\Photo_gallery;
use            h,
	cs\Config,
	cs\Index,
	cs\Language,
	cs\Page;
$Config = Config::instance();
$Index  = Index::instance();
$L      = Language::instance();
Page::instance()->title($L->photo_gallery_addition_of_gallery);
$Index->cancel_button_back    = true;
$Index->action                = 'admin/Photo_gallery/galleries/browse';
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::{'h2.cs-text-center'}(
		$L->photo_gallery_addition_of_gallery
	).
	h::label($L->photo_gallery_gallery_title).
	h::{'input[is=cs-input-text][name=add[title]]'}().
	($Config->core['simple_admin_mode'] ? '' :
		h::label(h::info('photo_gallery_gallery_path')).
		h::{'input[is=cs-input-text][name=add[path]]'}()
	).
	h::label($L->photo_gallery_gallery_description).
	h::{'textarea[is=cs-textarea][name=add[description]]'}().
	h::label($L->state).
	h::{'div radio[name=add[active]][checked=1]'}(
		[
			'value' => [0, 1],
			'in'    => [$L->off, $L->on]
		]
	).
	h::label($L->photo_gallery_gallery_start_from).
	h::{'div radio[name=add[preview_image]][checked=last]'}(
		[
			'value' => ['first', 'last'],
			'in'    => [$L->photo_gallery_first_uploaded, $L->photo_gallery_last_uploaded]
		]
	).
	h::br()
);
