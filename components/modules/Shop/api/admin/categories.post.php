<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\Page;

if (!isset(
	$_POST['parent'],
	$_POST['title'],
	$_POST['description'],
	$_POST['title_attribute'],
	$_POST['order_status_on_creation'],
	$_POST['visible'],
	$_POST['attributes']
)) {
	error_code(400);
	return;
}
$id = Categories::instance()->add(
	$_POST['parent'],
	$_POST['title'],
	$_POST['description'],
	$_POST['title_attribute'],
	$_POST['order_status_on_creation'],
	$_POST['visible'],
	$_POST['attributes']
);
if (!$id) {
	error_code(500);
	return;
}
code_header(201);
$Config = Config::instance();
Page::instance()->json(
	$Config->core_url().'/'.$Config->server['relative_address']."/$id"
);
