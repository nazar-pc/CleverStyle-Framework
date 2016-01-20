<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	cs\Event;

Event::instance()->on(
	'admin/System/components/modules/install/after',
	function ($data) {
		if ($data['name'] == 'Composer') {
			copy(MODULES.'/Composer/00.Composer_autoloader.php', CUSTOM.'/00.Composer_autoloader.php');
		}
	}
);
