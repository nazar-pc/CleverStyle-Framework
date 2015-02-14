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

$Index = Index::instance();
if (!isset(
	$Index->route_ids[0],
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
)) {
	error_code(400);
	return;
}
$result = Attributes::instance()->set(
	$Index->route_ids[0],
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
);
if (!$result) {
	error_code(500);
	return;
}
