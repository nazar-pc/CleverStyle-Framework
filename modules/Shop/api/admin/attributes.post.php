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
	cs\Config,
	cs\ExitException,
	cs\Page,
	cs\Request,
	cs\Response;

if (!isset(
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
)
) {
	throw new ExitException(400);
}
$id = Attributes::instance()->add(
	$_POST['type'],
	$_POST['title'],
	$_POST['title_internal'],
	$_POST['value']
);
if (!$id) {
	throw new ExitException(500);
}
Response::instance()->code = 201;
Page::instance()->json(
	Config::instance()->core_url().'/'.Request::instance()->path_normalized."/$id"
);
