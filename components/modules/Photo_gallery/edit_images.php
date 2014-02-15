<?php
/**
 * @package		Photo gallery
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Photo_gallery;
use			h,
			cs\Config,
			cs\DB,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\User;
$Index					= Index::instance();
$L						= Language::instance();
$User					= User::instance();
Page::instance()->title($L->photo_gallery_images_editing);
$Photo_gallery			= Photo_gallery::instance();
$images					= $Photo_gallery->get(explode(',', Config::instance()->route[1])) ?: [];
$Index->form			= true;
$Index->apply_button	= false;
$Index->action			= path($L->Photo_gallery).($images ? '/'.$Photo_gallery->get_gallery($images[0]['gallery'])['path'] : '');
$Index->content(
	h::{'section.cs-photo-gallery-edit-images article'}(array_map(
		function ($image) use ($L, $User) {
			if (!$User->admin() && $image['user'] != $User->id) {
				return false;
			}
			return	h::{'a[target=_new]'}(
						h::img([
							'src'	=> $image['preview']
						]),
						[
							'href'	=> $image['original']
						]
					).
					h::div(
						h::p($L->photo_gallery_image_title).
						h::input([
							'name'	=> "edit_images[$image[id]][title]",
							'value'	=> $image['title']
						]).
						h::p($L->photo_gallery_image_description).
						h::textarea(
							$image['description'],
							[
								'name'	=> "edit_images[$image[id]][description]"
							]
						).
						h::br(2).
						h::{'input.build-mode[name=mode][type=radio]'}([
							'name'	=> "edit_images[$image[id]][delete]",
							'value'		=> [0, 1],
							'in'		=> [$L->edit, $L->photo_gallery_delete_image]
						])
					);
		},
		$images
	))
);
