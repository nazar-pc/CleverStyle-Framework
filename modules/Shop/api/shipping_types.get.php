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

$Page           = Page::instance();
$Request        = Request::instance();
$Shipping_types = Shipping_types::instance();
if (isset($_GET['ids'])) {
	$shipping_types = $Shipping_types->get_for_user(explode(',', $Request->route_ids[0]));
	if (!$shipping_types) {
		throw new ExitException(404);
	} else {
		$Page->json($shipping_types);
	}
} elseif (isset($Request->route_ids[0])) {
	$shipping_type = $Shipping_types->get_for_user($Request->route_ids[0]);
	if (!$shipping_type) {
		throw new ExitException(404);
	} else {
		$Page->json($shipping_type);
	}
} else {
	$Page->json(
		$Shipping_types->get_for_user(
			$Shipping_types->get_all()
		)
	);
}
