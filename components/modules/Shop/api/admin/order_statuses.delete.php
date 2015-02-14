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
	cs\Index;

$Index = Index::instance();
if (!isset($Index->route_ids[0])) {
	error_code(400);
	return;
}
if (!Order_statuses::instance()->del($Index->route_ids[0])) {
	error_code(500);
}
