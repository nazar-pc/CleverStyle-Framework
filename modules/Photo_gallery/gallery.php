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
	cs\Page\Meta,
	cs\Page,
	cs\Request,
	cs\User;

$Config        = Config::instance();
$L             = new Prefix('photo_gallery_');
$Page          = Page::instance();
$User          = User::instance();
$Photo_gallery = Photo_gallery::instance();
$gallery       = $Photo_gallery->get_gallery(Request::instance()->route[1]);
if ($User->user()) {
	$Page->content(
		h::{'p.cs-text-left cs-link-button[icon=plus] a.cs-photo-gallery-add-images'}(
			$L->add_image,
			[
				'data-gallery' => $gallery['id']
			]
		)
	);
}
if (!$gallery['images']) {
	$Page->content(
		h::{'p.cs-text-center'}($L->gallery_empty)
	);
	return;
}
$images        = $Photo_gallery->get($gallery['images']);
$images_titles = array_filter(array_column(array_slice($images, 0, 10), 'title'));
$Page->title($gallery['title']);
if (isset($images[0])) {
	Meta::instance()->image(
		array_map(
			function ($image) {
				return $image['original'];
			},
			$images
		)
	);
}
if ($images_titles) {
	$Page->Description = description($gallery['description']);
}
unset($images_titles);
$module = path($L->Photo_gallery);
$Page->canonical_url("{$Config->base_url()}/$module/$gallery[path]");
$Page->content(
	h::{'cs-fotorama-styles-wrapper section.cs-photo-gallery-images'}(
		h::div(
			array_map(
				function ($image) use ($L, $User) {
					$controls = '';
					if ($User->admin() || $image['user'] == $User->id) {
						$controls =
							h::{'cs-link-button.cs-photo-gallery-image-control'}(
								h::a([
									'href'       => "Photo_gallery/edit_images/$image[id]"
								]),
								[
									'icon'       => 'pencil',
									'tooltip'    => $L->edit,
									'data-image' => $image['id']
								]
							).
							h::{'cs-link-button.cs-photo-gallery-image-control'}(
								h::a([
									'icon'       => 'trash'
								]),
								[
									'tooltip'    => $L->delete,
									'class'      => 'cs-photo-gallery-image-delete',
									'data-image' => $image['id']
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
			)
		)
	)
);
