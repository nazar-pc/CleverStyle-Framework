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
	cs\Page,
	cs\Response,
	cs\User;

$Config        = Config::instance();
$L             = new Prefix('photo_gallery_');
$Page          = Page::instance();
$User          = User::instance();
$Photo_gallery = Photo_gallery::instance();
$galleries     = $Photo_gallery->get_galleries_list();
$module        = path($L->Photo_gallery);
if (count($galleries) > 1) {
	$galleries        = $Photo_gallery->get_gallery(array_values($galleries));
	$galleries_titles = array_filter(array_column(array_slice($galleries, 0, 10), 'title'));
	$Page->canonical_url("{$Config->base_url()}/$module");
	if ($galleries_titles) {
		$Page->Description = description(implode('; ', $galleries_titles));
	}
	unset($galleries_titles);
	$Page->content(
		h::{'section.cs-photo-gallery-galleries article'}(
			array_map(
				function ($gallery) use ($L, $User, $module) {
					$controls = '';
					if ($User->admin()) {
						$controls =
							h::{'cs-link-button.cs-photo-gallery-gallery-control'}(
								h::a([
									'href' => "admin/Photo_gallery/galleries/edit/$gallery[id]"
								]),
								[
									'icon'    => 'pencil',
									'tooltip' => $L->edit
								]
							).
							h::{'cs-link-button.cs-photo-gallery-gallery-control'}(
								h::a([
									'href' => "admin/Photo_gallery/galleries/delete/$gallery[id]"
								]),
								[
									'icon'    => 'trash',
									'tooltip' => $L->delete
								]
							);
					}
					return h::header(
						h::a(
							h::img(
								[
									'src'   => $gallery['preview'] ?: 'modules/Photo_gallery/assets/img/empty.gif',
									'title' => $gallery['title'],
									'alt'   => $gallery['title']
								]
							),
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
			)
		)
	);
} elseif (count($galleries) == 1) {
	$Page->interface = false;
	$path            = array_keys($galleries)[0];
	Response::instance()->redirect($Config->base_url()."/$module/$path", 307);
} else {
	$Page->content(
		$L->no_galleries_yet
	);
}
