<?php
/**
 * @package  Shop
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Shop;
use
	cs\ExitException,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$L           = new Prefix('shop_');
$Request     = Request::instance();
$Page        = Page::instance();
$Categories  = Categories::instance();
$Items       = Items::instance();
$module_path = path($L->shop);
$items_path  = path($L->items);
if (isset($_GET['ids'])) {
	$items = $Items->get_for_user(explode(',', $_GET['ids']));
	if (!$items) {
		throw new ExitException(404);
	} else {
		foreach ($items as &$item) {
			$item['localized_href']    = "$module_path/$items_path/".path($Categories->get($item['category'])['title']).'/'.path($item['title']).":$item[id]";
			$item['short_description'] = truncate($item['description'], 200) ?: false;
		}
		$Page->json($items);
	}
} elseif (isset($Request->route_ids[0])) {
	$item = $Items->get_for_user($Request->route_ids[0]);
	if (!$item) {
		throw new ExitException(404);
	} else {
		$item['localized_href']    = "$module_path/$items_path/".path($Categories->get($item['category'])['title']).'/'.path($item['title']).":$item[id]";
		$item['short_description'] = truncate($item['description'], 200) ?: false;
		$Page->json($item);
	}
} else {
	throw new ExitException(400);
}
