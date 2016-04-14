<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Config,
	cs\Event,
	cs\Language\Prefix,
	cs\Menu,
	cs\Request;

Event::instance()
	->on(
		'admin/System/Menu',
		function () {
			$L       = new Prefix('composer_');
			$Menu    = Menu::instance();
			$Request = Request::instance();
			foreach (['general', 'auth_json'] as $section) {
				$Menu->add_item(
					'Composer',
					$L->$section,
					[
						'href'    => "admin/Composer/$section",
						'primary' => $Request->route_path(0) == $section
					]
				);
			}
		}
	)
	->on(
		'admin/System/components/modules/uninstall/after',
		function ($data) {
			if ($data['name'] == 'Composer') {
				$dir = DIR.'/storage/Composer';
				unlink(CUSTOM.'/00.Composer_autoloader.php');
				if (!rmdir_recursive($dir)) {
					trigger_error("Composer's directory $dir was not removed completely", E_USER_WARNING);
				}
			}
		}
	)
	->on(
		'admin/System/components/modules/install/after',
		function ($data) {
			if ($data['name'] == 'Composer') {
				copy(MODULES.'/Composer/00.Composer_autoloader.php', CUSTOM.'/00.Composer_autoloader.php');
			}
		}
	);
