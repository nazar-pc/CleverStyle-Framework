<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	h,
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
		if ($parent['parent'] == $category['id']) { // infinite loop protection
			break;
		}
		$category['title'] = "$parent[title] :: $category[title]";
		$parent            = $parent['parent'];
	}
	return $category;
}, $all_categories);

usort($all_categories, function ($cat1, $cat2) {
	return $cat1['title'] > $cat2['title'] ? 1 : -1;
});
$Page->content(
	h::{'h3.cs-text-center'}($L->categories).
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			'id',
			"$L->title ".h::icon('caret-down'),
			$L->title_attribute,
			$L->description_attribute,
			$L->visible,
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(array_map(
			function ($category) use ($L, $Attributes) {
				return [
					$category['id'],
					$category['title'],
					$Attributes->get($category['title_attribute'])['title_internal'],
					@$Attributes->get($category['description_attribute'])['title_internal'] ?: '',
					h::icon($category['visible'] ? 'check' : 'minus'),
					h::{'button.cs-shop-category-edit[is=cs-button]'}(
						$L->edit,
						[
							'data-id' => $category['id']
						]
					).
					h::{'button.cs-shop-category-delete[is=cs-button]'}(
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
	h::{'p button.cs-shop-category-add[is=cs-button]'}($L->add)
);
