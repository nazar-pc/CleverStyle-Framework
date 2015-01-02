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
	cs\Index,
	cs\Page;

$Index      = Index::instance();
$Page       = Page::instance();
$Items = Items::instance();
if (isset($_GET['ids'])) {
	$items = $Items->get(explode(',', $Index->route_ids[0]));
	if (!$items) {
		error_code(404);
	} else {
		$Page->json($items);
	}
} elseif (isset($Index->route_ids[0])) {
	$item = $Items->get($Index->route_ids[0]);
	if (!$item) {
		error_code(404);
	} else {
		$Page->json($item);
	}
} else {
	$Page->json(
		$Items->get(
			$Items->get_all()
		)
	);
}
