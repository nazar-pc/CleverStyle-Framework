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
	cs\Language,
	cs\Page,
	cs\Request;

$Request = Request::instance();
$Page    = Page::instance();
$Orders  = Orders::instance();
/**
 * Get order items, not order itself
 */
if (isset($Request->route_ids[0], $Request->route_path[2])) {
	switch ($Request->route_path[2]) {
		case 'items':
			$Page->json(
				$Orders->get_items($Request->route_ids[0])
			);
			break;
		case 'statuses':
			$Language = Language::instance();
			$Page->json(
				array_map(
					function ($status) use ($Language) {
						if (time() - $status['date'] < 24 * 3600) {
							$status['date_formatted'] = $Language->to_locale(
								date($Language->_time, $status['date'])
							);
						} else {
							$status['date_formatted'] = $Language->to_locale(
								date($Language->_datetime_long, $status['date'])
							);
						}
						return $status;
					},
					$Orders->get_statuses($Request->route_ids[0])
				)
			);
			break;
	}
} elseif (isset($Request->route_ids[0])) {
	$order = $Orders->get($Request->route_ids[0]);
	if (!$order) {
		throw new ExitException(404);
	} else {
		$Page->json($order);
	}
} else {
	$Page->json(
		$Orders->get(
			$Orders->get_all()
		)
	);
}
