<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Language\Prefix;
Event::instance()->on(
	'admin/System/Menu',
	function () {
		$L		= new Prefix('composer_');
		$Menu	= Menu::instance();
		$route	= Route::instance()->path;
		$Menu->add_item(
			'Composer',
			$L->general,
			'admin/Composer',
			[
				'class'	=> $route[0] == 'general' ? 'uk-active' : false
			]
		);
		$Menu->add_item(
			'Composer',
			$L->auth_json,
			'admin/Composer/auth_json',
			[
				'class'	=> $route[0] == 'auth_json' ? 'uk-active' : false
			]
		);
		Page::instance()->title(
			$L->{$route[0]}
		);
	}
);
