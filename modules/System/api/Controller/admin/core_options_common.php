<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config,
	cs\ExitException;

trait core_options_common {
	/**
	 * Apply mail settings
	 *
	 * @param \cs\Request $Request
	 * @param string[]    $options
	 *
	 * @throws ExitException
	 */
	protected static function admin_core_options_apply ($Request, $options) {
		static::admin_core_options_common($Request, $options);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 * @param string[]    $options
	 *
	 * @throws ExitException
	 */
	protected static function admin_core_options_common ($Request, $options) {
		$data = $Request->data($options);
		if (!$data) {
			throw new ExitException(400);
		}
		$Config       = Config::instance();
		$Config->core = $data + $Config->core;
	}
	/**
	 * Save mail settings
	 *
	 * @param \cs\Request $Request
	 * @param string[]    $options
	 *
	 * @throws ExitException
	 */
	protected static function admin_core_options_save ($Request, $options) {
		static::admin_core_options_common($Request, $options);
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel mail settings
	 *
	 * @throws ExitException
	 */
	protected static function admin_core_options_cancel () {
		Config::instance()->cancel();
	}
}
