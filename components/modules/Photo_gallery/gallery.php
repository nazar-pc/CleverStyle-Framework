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
$Index				= Index::instance();
$L					= Language::instance();
$Photo_gallery		= Photo_gallery::instance();
$galleries			= $Photo_gallery->get_galleries_list();
$gallery			= Config::instance()->route[1];
if (!isset($galleries[$gallery])) {
	error_code(404);
	return;
}
$gallery			= $Photo_gallery->get_gallery($galleries[$gallery]);
$Index->content(
	h::{'p.cs-left a.cs-button-compact.cs-photo-gallery-add-images'}(
		h::icon('plus').$L->photo_gallery_add_image,
		[
			'data-gallery'	=> $gallery['id']
		]
	)
);
if (!$gallery['images']) {
	$Index->content(
		h::{'p.cs-center'}($L->photo_gallery_gallery_empty)
	);
	return;
}
$images				= $Photo_gallery->get($gallery['images']);
$images_titles		= array_filter(array_column(array_slice($images, 0, 10), 'title'));
$Page				= Page::instance();
$Page->title($gallery['title']);
if ($images_titles) {
	$Page->Description	= description($gallery['description']);
}
unset($images_titles);
$User				= User::instance();
$module				= $L->Photo_gallery;
$Index->content(
	h::{'section.cs-photo-gallery-images.fotorama'}(
		h::div(array_map(
			function ($image) use ($L, $User, $gallery, $module) {
				return [
					(
						$User->admin() || $image['user'] == $User->id ? h::{'a.cs-photo-gallery-image-control'}(
							[
								'&nbsp;'.h::icon('pencil'),
								[
									'data-title'	=> $L->edit,
									'class'			=> 'cs-photo-gallery-image-edit',
									'data-image'	=> $image['id']
								]
							],
							[
								'&nbsp;'.h::icon('trash-o'),
								[
									'data-title'	=> $L->delete,
									'class'			=> 'cs-photo-gallery-image-delete',
									'data-image'	=> $image['id']
								]
							]
						) : ''
					),
					[
						'data-caption'	=> $image['title'] ?: false,
						'data-img'		=> $image['original'],
						'data-thumb'	=> $image['preview']
					]
				];
			},
			$images
		)),
		[
			'data-allow-full-screen'	=> 'native',
			'data-fit'					=> 'scaledown',
			'data-height'				=> '80%',
			'data-keyboard'				=> 'true',
			'data-nav'					=> 'thumbs',
			'data-width'				=> '100%'
		]
	)
);
