<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
/**
 * Requirements:
 * * Apache2
 *  Apache2 modules MUST be enabled:
 *  * rewrite
 *  * headers
 *  Optional Apache2 modules:
 *  * expires
 * * or Nginx
 * * PHP 7.0+
 *  PHP libraries MUST be present:
 *  * cURL
 *  Optional PHP libraries:
 *  * APCu, Memcached
 * * MySQL 5.6+
 * * or MariaDB 10.0.5+
 */
namespace cs;

if (version_compare(PHP_VERSION, '7.0', '<')) {
	echo 'CleverStyle Framework requires PHP 7.0 or higher';
	return;
}

require_once __DIR__.'/core/bootstrap.php';

try {
	Response::instance()->init_with_typical_default_settings();
	Request::instance()->init_from_globals();
	App::instance()->execute();
} catch (ExitException $e) {
	if ($e->getCode() >= 400) {
		Page::instance()->error($e->getMessage() ?: null, $e->getJson());
	}
}
Response::instance()->output_default();
