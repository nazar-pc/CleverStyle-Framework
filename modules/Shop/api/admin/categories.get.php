<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\ExitException,
	cs\Page,
	cs\Request;

$Request    = Request::instance();
$Page       = Page::instance();
$Categories = Categories::instance();
if (isset($_GET['ids'])) {
	$categories = $Categories->get(explode(',', $_GET['ids']));
	if (!$categories) {
		throw new ExitException(404);
	} else {
		$Page->json($categories);
	}
} elseif (isset($Request->route_ids[0])) {
	$category = $Categories->get($Request->route_ids[0]);
	if (!$category) {
		throw new ExitException(404);
	} else {
		$Page->json($category);
	}
} else {
	$Page->json(
		$Categories->get(
			$Categories->get_all()
		)
	);
}
