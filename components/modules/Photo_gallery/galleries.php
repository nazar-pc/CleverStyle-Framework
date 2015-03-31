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
	cs\Index,
	cs\Language,
	cs\Page,
	cs\User;

$Config        = Config::instance();
$Index         = Index::instance();
$L             = Language::instance();
$User          = User::instance();
$Photo_gallery = Photo_gallery::instance();
$galleries     = $Photo_gallery->get_galleries_list();
$module        = path(Language::instance()->Photo_gallery);
if (count($galleries) > 1) {
	$galleries        = $Photo_gallery->get_gallery(array_values($galleries));
	$galleries_titles = array_filter(array_column(array_slice($galleries, 0, 10), 'title'));
	$Page             = Page::instance();
	$Page->canonical_url("{$Config->base_url()}/$module");
	if ($galleries_titles) {
		$Page->Description = description(implode('; ', $galleries_titles));
	}
	unset($galleries_titles);
	$Index->content(
		h::{'section.cs-photo-gallery-galleries article'}(array_map(
			function ($gallery) use ($L, $User, $module) {
				$controls = '';
				if ($User->admin()) {
					$controls = h::{'a.cs-photo-gallery-gallery-control'}(
						[
							h::icon('pencil'),
							[
								'href'       => "admin/Photo_gallery/galleries/edit/$gallery[id]",
								'data-title' => $L->edit
							]
						],
						[
							h::icon('trash-o'),
							[
								'href'       => "admin/Photo_gallery/galleries/delete/$gallery[id]",
								'data-title' => $L->delete
							]
						]
					);
				}
				return h::header(
					h::a(
						h::img([
							'src'   => $gallery['preview'] ?: 'components/modules/Photo_gallery/includes/img/empty.gif',
							'title' => $gallery['title'],
							'alt'   => $gallery['title']
						]),
						[
							'href' => "$module/$gallery[path]"
						]
					).
					h::p($gallery['title'] ?: false).
					$controls
				).
				h::footer($gallery['description'] ?: false);
			},
			$galleries
		))
	);
} elseif (count($galleries) == 1) {
	interface_off();
	$path = array_keys($galleries)[0];
	_header("Location: {$Config->base_url()}/$module/$path", true, 307);
} else {
	$Index->content(
		$L->photo_gallery_no_galleries_yet
	);
}
