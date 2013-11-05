<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
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
			'posts_per_page'	=> hash('sha224', MICROTIME . uniqid()),
			'number_of_workers'	=> 5
		]);
		return;
	}
);