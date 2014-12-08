<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

Index::instance()->buttons = false;
$L                         = new Prefix('shop_');
$Page                      = Page::instance();
$Page->title($L->categories);
$Categories     = Categories::instance();
$Attributes     = Attributes::instance();
$all_categories = $Categories->get($Categories->get_all());
$all_categories = array_map(function ($category) use ($Categories) {
	$parent = $category['parent'];
	while (
		$parent &&
		$parent != $category['id'] // infinite loop protection
	) {
		$parent            = $Categories->get($category['parent']);
		$category['title'] = "$parent[title] :: $category[title]";
		$parent            = $parent['parent'];
	}
	return $category;
}, $all_categories);

usort($all_categories, function ($cat1, $cat2) {
	return $cat1['title'] > $cat2['title'] ? 1 : -1;
});
$Page->content(
	h::{'h3.uk-lead.cs-center'}($L->categories).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			$L->title,
			$L->title_attribute,
			$L->visible,
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(array_map(
			function ($category) use ($L, $Attributes) {
				return [
					$category['title'],
					$Attributes->get($category['title_attribute'])['internal_title'],
					h::icon($category['visible'] ? 'check' : 'minus'),
					h::{'button.uk-button.cs-shop-category-edit'}(
						$L->edit,
						[
							'data-id' => $category['id']
						]
					).
					h::{'button.uk-button.cs-shop-category-delete'}(
						$L->delete,
						[
							'data-id' => $category['id']
						]
					)
				];
			},
			$all_categories
		) ?: false)
	).
	h::{'p button.uk-button.cs-shop-category-add'}($L->add)
);
