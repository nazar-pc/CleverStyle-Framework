<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;
use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page;

$Config = Config::instance();
$L      = new Prefix('photo_gallery_');
Page::instance()
	->title($L->addition_of_gallery)
	->content(
		h::{'cs-form form[action=admin/Photo_gallery/galleries/browse]'}(
			h::{'h2.cs-text-center'}(
				$L->addition_of_gallery
			).
			h::label($L->gallery_title).
			h::{'cs-input-text input[name=add[title]]'}().
			($Config->core['simple_admin_mode'] ? '' :
				h::label(h::info('photo_gallery_gallery_path')).
				h::{'cs-input-text input[name=add[path]]'}()
			).
			h::label($L->gallery_description).
			h::{'textarea[is=cs-textarea][name=add[description]]'}().
			h::label($L->state).
			h::{'div radio[name=add[active]][checked=1]'}(
				[
					'value' => [0, 1],
					'in'    => [$L->off, $L->on]
				]
			).
			h::label($L->gallery_start_from).
			h::{'div radio[name=add[preview_image]][checked=last]'}(
				[
					'value' => ['first', 'last'],
					'in'    => [$L->first_uploaded, $L->last_uploaded]
				]
			).
			h::p(
				h::cs_button(
					h::{'button[type=submit]'}($L->save),
					[
						'tooltip' => $L->save_info
					]
				).
				h::{'cs-button button[type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
