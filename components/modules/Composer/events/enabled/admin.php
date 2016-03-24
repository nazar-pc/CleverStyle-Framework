<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Language\Prefix;

Event::instance()->on(
	'admin/System/Menu',
	function () {
		$L     = new Prefix('composer_');
		$Menu  = Menu::instance();
		$route = Request::instance()->route_path;
		$Menu->add_item(
			'Composer',
			$L->general,
			[
				'href'    => 'admin/Composer',
				'primary' => $route[0] == 'general'
			]
		);
		$Menu->add_item(
			'Composer',
			$L->auth_json,
			[
				'href'    => 'admin/Composer/auth_json',
				'primary' => $route[0] == 'auth_json'
			]
		);
		Page::instance()->title(
			$L->{$route[0]}
		);
	}
);
