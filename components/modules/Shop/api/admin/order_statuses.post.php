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
	cs\Config,
	cs\Page;

if (!isset(
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
)) {
	error_code(400);
	return;
}
$id = Order_statuses::instance()->add(
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
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
