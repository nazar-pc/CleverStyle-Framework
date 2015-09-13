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
	cs\Route,
	cs\User;

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
} elseif (isset($Route->path[1]) && $Route->path[1] == 'types') {
	$Page->json(
		$Attributes->get_type_to_name_array()
	);
} elseif (User::instance()->admin()) { //Hack to re-use contents of this file from `api/admin/attributes.get.php`
	$Page->json(
		$Attributes->get(
			$Attributes->get_all()
		)
	);
} else {
	throw new ExitException(400);
}
