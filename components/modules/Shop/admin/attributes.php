<?php
/**
 * @package    Shop
 * @attribute  modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2014, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
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
$Page->title($L->attributes);
$Attributes      = Attributes::instance();
$all_attributes  = $Attributes->get($Attributes->get_all());
$attribute_types = $Attributes->get_type_to_name_array();
usort($all_attributes, function ($attr1, $attr2) {
	return $attr1['internal_title'] > $attr2['internal_title'] ? 1 : -1;
});
$Page->content(
	h::{'cs-table[list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			$L->internal_title,
			$L->title,
			$L->attribute_type,
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(array_map(
			function ($attribute) use ($L, $attribute_types) {
				return [
					$attribute['internal_title'],
					$attribute['title'],
					$attribute_types[$attribute['type']],
					h::{'button.uk-button.cs-shop-attribute-edit'}(
						$L->edit,
						[
							'data-id' => $attribute['id']
						]
					).
					h::{'button.uk-button.cs-shop-attribute-delete'}(
						$L->delete,
						[
							'data-id' => $attribute['id']
						]
					)
				];
			},
			$all_attributes
		) ?: false)
	).
	h::{'p button.uk-button.cs-content-add'}($L->add)
);
