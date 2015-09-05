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
	cs\Page;
$Index          = Index::instance();
$L              = Language::instance();
$Index->buttons = false;
Page::instance()->title($L->photo_gallery_browse_galleries);
$Photo_gallery = Photo_gallery::instance();
$module        = path($L->Photo_gallery);
$Index->content(
	h::{'table.cs-table[list]'}(
		h::{'tr th'}(
			$L->photo_gallery_galleries,
			$L->action
		).
		h::{'tr| td'}(array_map(
			function ($gallery) use ($Photo_gallery, $L, $module) {
				$gallery = $Photo_gallery->get_gallery($gallery);
				return [
					h::a(
						$gallery['title'],
						[
							'href' => "$module/$gallery[path]"
						]
					),
					h::{'a[is=cs-link-button][icon=pencil][level=0]'}(
						[
							'href'    => "admin/Photo_gallery/galleries/edit/$gallery[id]",
							'tooltip' => $L->edit
						]
					).
					h::{'a[is=cs-link-button][icon=trash][level=0]'}(
						[
							'href'    => "admin/Photo_gallery/galleries/delete/$gallery[id]",
							'tooltip' => $L->delete
						]
					)
				];
			},
			array_values($Photo_gallery->get_galleries_list())
		))
	).
	h::{'p.cs-text-left a[is=cs-link-button]'}(
		$L->photo_gallery_add_gallery,
		[
			'href' => 'admin/Photo_gallery/galleries/add'
		]
	)
);
