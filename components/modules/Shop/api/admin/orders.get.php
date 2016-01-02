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
	cs\ExitException,
	cs\Language,
	cs\Page,
	cs\Route;

$Route  = Route::instance();
$Page   = Page::instance();
$Orders = Orders::instance();
/**
 * Get order items, not order itself
 */
if (isset($Route->ids[0], $Route->path[2])) {
	switch ($Route->path[2]) {
		case 'items':
			$Page->json(
				$Orders->get_items($Route->ids[0])
			);
			break;
		case 'statuses':
			$Language = Language::instance();
			$Page->json(
				array_map(
					function ($status) use ($Language) {
						$status['date_formatted'] = $Language->to_locale(
							date($Language->{TIME - $status['date'] < 24 * 3600 ? '_time' : '_datetime_long'}, $status['date'])
						);
						return $status;
					},
					$Orders->get_statuses($Route->ids[0])
				)
			);
			break;
	}
} elseif (isset($Route->ids[0])) {
	$order = $Orders->get($Route->ids[0]);
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
