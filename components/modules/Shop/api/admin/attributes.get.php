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
	cs\ExitException,
	cs\Page,
	cs\Route;

$Route      = Route::instance();
$Page       = Page::instance();
$Attributes = Attributes::instance();
if (isset($_GET['ids'])) {
	$attributes = $Attributes->get(explode(',', $_GET['ids']));
	if (!$attributes) {
		throw new ExitException(404);
	} else {
		$Page->json($attributes);
	}
} elseif (isset($Route->ids[0])) {
	$attribute = $Attributes->get($Route->ids[0]);
	if (!$attribute) {
		throw new ExitException(404);
	} else {
		$Page->json($attribute);
	}
} elseif (isset($Route->path[2]) && $Route->path[2] == 'types') {
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
