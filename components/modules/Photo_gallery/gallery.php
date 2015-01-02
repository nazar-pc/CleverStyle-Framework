<?php
/**
 * @package        Photo gallery
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Photo_gallery;

use
	h,
	cs\Config,
	cs\DB,
	cs\Index,
	cs\Language,
	cs\Page\Meta,
	cs\Page,
	cs\User;

$Config        = Config::instance();
$Index         = Index::instance();
$L             = Language::instance();
$Photo_gallery = Photo_gallery::instance();
$gallery       = $Photo_gallery->get_gallery($Config->route[1]);
$User          = User::instance();
if ($User->user()) {
	$Index->content(
		h::{'p.cs-left a.uk-button.cs-button-compact.cs-photo-gallery-add-images'}(
			h::icon('plus').$L->photo_gallery_add_image,
			[
				'data-gallery' => $gallery['id']
			]
		)
	);
}
if (!$gallery['images']) {
	$Index->content(
		h::{'p.cs-center'}($L->photo_gallery_gallery_empty)
	);
	return;
}
$images        = $Photo_gallery->get($gallery['images']);
$images_titles = array_filter(array_column(array_slice($images, 0, 10), 'title'));
$Page          = Page::instance();
$Page->title($gallery['title']);
if (isset($images[0])) {
	Meta::instance()->image(array_map(function ($image) {
		return $image['original'];
	}, $images));
}
if ($images_titles) {
	$Page->Description = description($gallery['description']);
}
unset($images_titles);
$module = path($L->Photo_gallery);
$Page->canonical_url("{$Config->base_url()}/$module/$gallery[path]");
$Index->content(
	h::{'section.cs-photo-gallery-images.fotorama'}(
		h::div(array_map(
			function ($image) use ($L, $User) {
				$controls = '';
				if ($User->admin() || $image['user'] == $User->id) {
					$controls = h::{'a.cs-photo-gallery-image-control'}(
						[
							'&nbsp;'.h::icon('pencil'),
							[
								'href'       => "Photo_gallery/edit_images/$image[id]",
								'data-title' => $L->edit,
								'data-image' => $image['id']
							]
						],
						[
							'&nbsp;'.h::icon('trash-o'),
							[
								'data-title' => $L->delete,
								'class'      => 'cs-photo-gallery-image-delete',
								'data-image' => $image['id']
							]
						]
					);
				}
				return [
					$controls,
					[
						'data-caption' => $image['title'] ?: false,
						'data-img'     => $image['original'],
						'data-thumb'   => $image['preview']
					]
				];
			},
			$images
		)),
		[
			'data-allow-full-screen' => 'native',
			'data-controlsonstart'   => 'false',
			'data-fit'               => 'scaledown',
			'data-height'            => '80%',
			'data-keyboard'          => 'true',
			'data-nav'               => 'thumbs',
			'data-trackpad'          => 'true',
			'data-width'             => '100%'
		]
	)
);
