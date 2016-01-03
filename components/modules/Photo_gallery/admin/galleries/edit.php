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
	cs\Language\Prefix,
	cs\Page,
	cs\Route;

$Config  = Config::instance();
$gallery = Photo_gallery::instance()->get_gallery(Route::instance()->route[2]);
$L       = new Prefix('photo_gallery_');
Page::instance()
	->title($L->editing_of_gallery($gallery['title']))
	->content(
		h::{'form[is=cs-form][action=admin/Photo_gallery/galleries/browse]'}(
			h::{'h2.cs-text-center'}(
				$L->editing_of_gallery($gallery['title'])
			).
			h::label($L->gallery_title).
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
			h::label($L->gallery_description).
			h::{'textarea[is=cs-textarea][name=edit[description]]'}($gallery['description']).
			h::label($L->state).
			h::{'div radio[name=edit[active]]'}(
				[
					'value'   => [0, 1],
					'in'      => [$L->off, $L->on],
					'checked' => $gallery['active']
				]
			).
			h::label($L->gallery_start_from).
			h::{'div radio[name=edit[preview_image]]'}(
				[
					'value'   => ['first', 'last'],
					'in'      => [$L->first_uploaded, $L->last_uploaded],
					'checked' => $gallery['preview_image']
				]
			).
			h::{'input[type=hidden][name=edit[id]]'}(
				[
					'value' => $gallery['id']
				]
			).
			h::p(
				h::{'button[is=cs-button][type=submit]'}(
					$L->save,
					[
						'tooltip' => $L->save_info
					]
				).
				h::{'button[is=cs-button][type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
