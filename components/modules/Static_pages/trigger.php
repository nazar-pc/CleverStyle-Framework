<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
global $Core;
$Core->register_trigger(
	'System/Config/routing_replace',
	function ($data) {
		global $Config;
		if (!$Config->module('Static_pages')->active() && substr($data['rc'], 0, 5) != 'admin') {
			return;
		}
		$rc						= explode('/', $data['rc']);
		global $Core, $Static_pages;
		$Core->create('cs\\modules\\Static_pages\\Static_pages');
		switch ($rc[0]) {
			case 'admin':
			case 'api':
				return;
			case 'Static_pages':
				$rc = ['index'];
		}
		$structure				= $Static_pages->get_structure();
		$categories				= array_slice($rc, 0, -1);
		$Static_pages->title	= [];
		if (!empty($categories)) {
			foreach ($categories as $category) {
				if (isset($structure['categories'][$category])) {
					$structure				= $structure['categories'][$category];
					$path[]					= $structure['path'];
					$Static_pages->title[]	= $structure['title'];
				}
			}
			unset($category);
		}
		unset($categories);
		if (isset($structure['pages'][array_slice($rc, -1)[0]])) {
			$data['rc']	= 'Static_pages/'.$structure['pages'][array_slice($rc, -1)[0]];
		}
	}
);
$Core->register_trigger(
	'System/Index/construct',
	function () {
		if (!ADMIN) {
			return;
		}
		global $Config;
		switch ($Config->components['modules']['Blogs']['active']) {
			case 0:
			case 1:
				require __DIR__.'/trigger/installed.php';
		}
	}
);