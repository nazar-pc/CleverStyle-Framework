<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Config,
	cs\Event;
_require_once(STORAGE.'/Composer/vendor/autoload.php', false);
Event::instance()->on(
	'System/Index/construct',
	function () {
		switch (Config::instance()->components['modules']['Composer']['active']) {
			case 1:
				require __DIR__.'/events/enabled.php';
			case 0:
				require __DIR__.'/events/installed.php';
		}
	}
);
