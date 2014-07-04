<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			cs\Config,
			cs\Trigger;
Trigger::instance()
	->register(
		'System/Config/routing_replace',
		function ($data) {
			if (!Config::instance()->module('Static_pages')->active() && substr($data['rc'], 0, 5) != 'admin') {
				return;
			}
			$rc						= explode('/', $data['rc']);
			switch ($rc[0]) {
				case 'admin':
				case 'api':
					return;
				case 'Static_pages':
					if (!isset($rc[1])) {
						$rc = ['index'];
					}
			}
			$Static_pages			= Static_pages::instance();
			$structure				= $Static_pages->get_structure();
			$categories				= array_slice($rc, 0, -1);
			if (!empty($categories)) {
				foreach ($categories as $category) {
					if (isset($structure['categories'][$category])) {
						$structure				= $structure['categories'][$category];
						$path[]					= $structure['path'];
					}
				}
				unset($category);
			}
			unset($categories);
			if (isset($structure['pages'][array_slice($rc, -1)[0]])) {
				$data['rc']	= 'Static_pages/'.$structure['pages'][array_slice($rc, -1)[0]];
			}
		}
	)
	->register(
		'System/Index/construct',
		function () {
			if (!admin_path()) {
				return;
			}
			switch (Config::instance()->components['modules']['Static_pages']['active']) {
				case 0:
				case 1:
					require __DIR__.'/trigger/installed.php';
			}
		}
	);
