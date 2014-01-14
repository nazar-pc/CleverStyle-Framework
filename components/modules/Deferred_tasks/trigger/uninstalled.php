<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'Deferred_tasks') {
			return;
		}
		Config::instance()->module('Deferred_tasks')->set([
			'security_key'			=> hash('sha224', MICROTIME . uniqid()),
			'max_number_of_workers'	=> 5
		]);
		return;
	}
);
