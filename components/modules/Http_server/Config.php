<?php
/**
 * @package   Http server
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
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
		Event::instance()->fire('System/Config/before_init');
		/**
		 * System initialization with current configuration
		 */
		$this->init();
		Event::instance()->fire('System/Config/after_init');
		if (!file_exists(MODULES.'/'.$this->core['default_module'])) {
			$this->core['default_module']	= 'System';
			$this->save();
		}
		/**
		 * Address routing
		 */
		$this->routing();
	}
}
