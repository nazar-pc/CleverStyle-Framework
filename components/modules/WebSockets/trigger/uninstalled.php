<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Trigger::instance()->register(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'WebSockets') {
			return;
		}
		Config::instance()->module('WebSockets')->set([
			'security_key'   => hash('sha224', MICROTIME.uniqid()),
			'listen_port'    => 8080,
			'listen_locally' => 1
		]);
		return;
	}
);
