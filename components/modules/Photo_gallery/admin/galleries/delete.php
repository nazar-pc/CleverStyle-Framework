<?php
/**
 * @package   Photo gallery
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;
use
	h,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$gallery = Photo_gallery::instance()->get_gallery(Route::instance()->route[1]);
$Index   = Index::instance();
$L       = Language::instance();
Page::instance()->title($L->photo_gallery_deletion_of_gallery($gallery['title']));
$Index->buttons            = false;
$Index->cancel_button_back = true;
$Index->action             = 'admin/Photo_gallery/galleries/browse';
$Index->content(
	h::{'h2.cs-center'}(
		$L->photo_gallery_sure_to_delete_gallery($gallery['title'])
	).
	h::{'button.uk-button[type=submit]'}($L->yes).
	h::{'input[type=hidden][name=delete]'}(
		[
			'value' => $gallery['id']
		]
	)
);
