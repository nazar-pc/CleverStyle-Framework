<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\custom;
use
	cs\Config as Config_original,
	cs\Event;
/**
 * @inheritdoc
 */
class Config extends Config_original {
	function reinit () {
		Event::instance()->fire('System/Config/init/before');
		/**
		 * System initialization with current configuration
		 */
		$this->init();
		Event::instance()->fire('System/Config/init/after');
	}
}
