<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\ExitException,
	cs\Page,
	cs\Request;

$Page    = Page::instance();
$Request = Request::instance();
$Items   = Items::instance();
if (isset($_GET['ids'])) {
	$items = $Items->get(explode(',', $_GET['ids']));
	if (!$items) {
		throw new ExitException(404);
	} else {
		$Page->json($items);
	}
} elseif (isset($Request->route_ids[0])) {
	$item = $Items->get($Request->route_ids[0]);
	if (!$item) {
		throw new ExitException(404);
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
