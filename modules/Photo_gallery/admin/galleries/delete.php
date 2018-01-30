<?php
/**
 * @package  Photo gallery
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Photo_gallery;
use
	h,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$gallery = Photo_gallery::instance()->get_gallery(Request::instance()->route[1]);
$L       = new Prefix('photo_gallery_');
Page::instance()
	->title($L->deletion_of_gallery($gallery['title']))
	->content(
		h::{'cs-form form[action=admin/Photo_gallery/galleries/browse]'}(
			h::{'h2.cs-text-center'}(
				$L->sure_to_delete_gallery($gallery['title'])
			).
			h::{'input[type=hidden][name=delete]'}(
				[
					'value' => $gallery['id']
				]
			).
			h::p(
				h::{'cs-button button[type=submit]'}(
					$L->yes
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
