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
	cs\Config,
	cs\ExitException,
	cs\Page,
	cs\Request,
	cs\Response;

if (!isset(
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
)
) {
	throw new ExitException(400);
}
$id = Order_statuses::instance()->add(
	$_POST['title'],
	$_POST['type'],
	$_POST['color'],
	$_POST['send_update_status_email'],
	$_POST['comment']
);
if (!$id) {
	throw new ExitException(500);
}
Response::instance()->code = 201;
Page::instance()->json(
	Config::instance()->core_url().'/'.Request::instance()->path_normalized."/$id"
);
