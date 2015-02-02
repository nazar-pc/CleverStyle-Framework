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
$Attributes = Attributes::instance();
if (isset($_GET['ids'])) {
	$attributes = $Attributes->get(explode(',', $_GET['ids']));
	if (!$attributes) {
		error_code(404);
	} else {
		$Page->json($attributes);
	}
} elseif (isset($Index->route_ids[0])) {
	$attribute = $Attributes->get($Index->route_ids[0]);
	if (!$attribute) {
		error_code(404);
	} else {
		$Page->json($attribute);
	}
} elseif (isset($Index->route_path[1]) && $Index->route_path[1] == 'types') {
	$Page->json(
		$Attributes->get_type_to_name_array()
	);
} else {
	$Page->json(
		$Attributes->get(
			$Attributes->get_all()
		)
	);
}
