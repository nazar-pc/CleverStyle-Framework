<?php
/**
 * @package       Shop
 * @order_status  modules
 * @author        Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright     Copyright (c) 2014, Nazar Mokrynskyi
 * @license       MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;
$Config          = Config::instance();
$Index           = Index::instance();
$L               = new Prefix('shop_');
$Page            = Page::instance();
$Categories      = Categories::instance();
$all_categories  = $Categories->get($Categories->get_all());
$all_categories  = array_combine(array_column($all_categories, 'id'), $all_categories);
$categories_tree = [];
foreach ($all_categories as $category) {
	$categories_tree[$category['parent']][] = $category['id'];
}
unset($category);
$current_category = 0;
if ($Index->route_path) {
	$current_category = array_slice($Index->route_path, -1)[0];
	$current_category = explode(':', $current_category);
	$current_category = (int)array_pop($current_category);
}
$module_path     = path($L->shop);
$categories_path = path($L->categories);
if (isset($all_categories[$current_category])) {
	$category = $all_categories[$current_category];
	$Page->title($category['title']);
	$Page->canonical_url(
		"{$Config->base_url()}/$module_path/$categories_path/".path($category['title']).":$category[id]"
	);
	unset($category);
} elseif ($current_category === 0) {
	$Page->canonical_url(
		"{$Config->base_url()}/$module_path"
	);
} else {
	error_code(404);
	return;
}
if (@$categories_tree[$current_category]) {
	$categories_list = [];
	foreach ($categories_tree[$current_category] as $category) {
		$category                            = $all_categories[$category];
		$categories_list[$category['title']] = h::{'article[is=cs-shop-root-category]'}(
			h::{'img#img'}([
				'src'   => $category['image'] ?: 'components/modules/Shop/includes/img/no-image.svg',
				'title' => h::prepare_attr_value($category['title'])
			]).
			h::{'h1 a#link'}(
				$category['title'],
				[
					'href' => "$module_path/$categories_path/".path($category['title']).":$category[id]"
				]
			).
			h::{'#description'}($category['description'] ?: false).
			h::{'section#nested article[is=cs-shop-nested-category]'}(array_map(function ($category) use ($L, $all_categories, $module_path, $categories_path) {
				$category = $all_categories[$category];
				return
					h::{'img#img'}([
						'src'   => $category['image'] ?: 'components/modules/Shop/includes/img/no-image.svg',
						'title' => h::prepare_attr_value($category['title'])
					]).
					h::{'h1 a#link'}(
						$category['title'],
						[
							'href' => "$module_path/$categories_path/".path($category['title']).":$category[id]"
						]
					);
			}, @$categories_tree[$category['id']] ?: []) ?: false)
		);
	}
	ksort($categories_list);
	$Page->content(
		h::{'section[is=cs-shop-categories]'}(
			implode('', $categories_list)
		)
	);
	unset($categories_list);
}
//TODO items list
