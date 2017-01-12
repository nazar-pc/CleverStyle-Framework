<?php
/**
 * @package   Deferred tasks
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

Event::instance()->on(
	'admin/System/modules/install/after',
	function ($data) {
		if ($data['name'] != 'Deferred_tasks') {
			return;
		}
		Config::instance()->module('Deferred_tasks')->set(
			[
				'security_key'          => hash('sha224', random_bytes(1000)),
				'max_number_of_workers' => 5
			]
		);
	}
);
