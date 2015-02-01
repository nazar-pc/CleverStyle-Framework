<?php
/**
 * @package   WebSockets
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'WebSockets') {
			return;
		}
		Config::instance()->module('WebSockets')->set([
			'security_key'   => hash('sha224', openssl_random_pseudo_bytes(100)),
			'listen_port'    => 8080,
			'listen_locally' => 1
		]);
		return;
	}
);
