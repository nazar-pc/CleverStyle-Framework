<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Shop;
use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$Config     = Config::instance();
$L          = new Prefix('shop_');
$Page       = Page::instance();
$Categories = Categories::instance();
$Attributes = Attributes::instance();
$Items      = Items::instance();
$item       = explode(
	':',
	array_slice(Request::instance()->route_path, -1)[0]
);
$item       = $Items->get_for_user(array_pop($item));
$Page->title($item['title']);
$Page->Description = description($item['description']);
$Page->canonical_url(
	"{$Config->base_url()}/".path($L->shop).'/'.path($L->items).'/'.path($Categories->get($item['category'])['title']).'/'.path($item['title']).":$item[id]"
);
$category = $Categories->get($item['category']);
unset(
	$item['attributes'][$category['title_attribute']],
	$item['attributes'][$category['description_attribute']]
);
$Page->content(
	h::cs_shop_item(
		h::section(
			h::{'#images'}(
				implode(
					'',
					array_map(
						function ($image) {
							return h::img(['src' => $image]);
						},
						$item['images'] ?: Items::DEFAULT_IMAGE
					)
				)
			).
			h::{'#videos a'}(
				array_map(
					function ($video) {
						$content = $video['poster'] ? h::img(['src' => $video['poster']]) : '';
						return [
							$content,
							[
								'href' => $video['video']
							]
						];
					},
					$item['videos']
				) ?: false
			).
			h::h1($item['title']).
			h::{'#description'}($item['description']).
			h::{'#attributes table tr| td'}(
				array_map(
					function ($attribute) use ($item, $Attributes) {
						return [
							$Attributes->get($attribute)['title'],
							$item['attributes'][$attribute]
						];
					},
					array_keys($item['attributes'])
				) ?: false
			)
		),
		[
			'item_id'  => $item['id'],
			'date'     => $item['date'],
			'price'    => $item['price'],
			'in_stock' => $item['in_stock'],
			'soon'     => $item['soon']
		]
	)
);
