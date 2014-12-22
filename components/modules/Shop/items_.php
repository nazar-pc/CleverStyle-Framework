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
$Config            = Config::instance();
$Index             = Index::instance();
$L                 = new Prefix('shop_');
$Page              = Page::instance();
$Categories        = Categories::instance();
$Items             = Items::instance();
$item              = explode(
	':',
	array_slice($Index->route_path, -1)[0]
);
$item              = $Items->get(array_pop($item));
$Page->title($item['title']);
$Page->Description = description($item['description']);
$Page->canonical_url(
	"{$Config->base_url()}/".path($L->shop).'/'.path($L->items).'/'.path($Categories->get($item['category'])['title']).'/'.path($item['title']).":$item[id]"
);
$Page->content(
	h::{'section[is=cs-shop-item]'}(
		h::{'div[is=cs-shop-item-images] img'}(array_map(function ($image) {
			return [
				'src' => $image
			];
		}, $item['images'])).
		h::h1($item['title']).
		h::{'#description'}($item['description']).
		h::{'#attributes'}(
			// TODO add attributes here
		),
		[
			'data-id'       => $item['id'],
			'data-date'     => $item['price'],
			'data-price'    => $item['price'],
			'data-in_stock' => $item['in_stock'],
			'data-soon'     => $item['soon']
		]
	)
);
