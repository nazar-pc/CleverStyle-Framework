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
	cs\Page,
	cs\Request;

$Request    = Request::instance();
$Page       = Page::instance();
$Attributes = Attributes::instance();
if (isset($_GET['ids'])) {
	$attributes = $Attributes->get(explode(',', $_GET['ids']));
	if (!$attributes) {
		throw new ExitException(404);
	} else {
		$Page->json($attributes);
	}
} elseif (isset($Request->route_ids[0])) {
	$attribute = $Attributes->get($Request->route_ids[0]);
	if (!$attribute) {
		throw new ExitException(404);
	} else {
		$Page->json($attribute);
	}
} elseif (isset($Request->route_path[1]) && $Request->route_path[1] == 'types') {
	$Page->json(
		$Attributes->get_type_to_name_array()
	);
} else {
	throw new ExitException(400);
}
