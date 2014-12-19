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
	cs\Language\Prefix,
	cs\Page;
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
$categories_list = [];
foreach ($categories_tree[0] as $category) {
	$category                            = $all_categories[$category];
	$categories_list[$category['title']] = h::{'article[is=cs-shop-root-category]'}(
		h::img([
			'src'	=> $category['image'],
			'title'	=> h::prepare_attr_value($category['title'])
		]).
		h::h1(
			h::a(
				$category['title'],
				[
					'href' => path($L->shop).'/'.path($L->categories).'/'.path($category['title'])
				]
			)
		).
		h::{'.description'}($category['description'])
	);
}
ksort($categories_list);
$Page->content(
	h::{'section[is=cs-shop-categories]'}(
		implode('', $categories_list)
	)
);
