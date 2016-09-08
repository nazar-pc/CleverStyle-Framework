<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config;

trait system {
	protected static $system_options_keys = [
		'site_mode',
		'closed_title',
		'closed_text',
		'title_delimiter',
		'title_reverse',
		'simple_admin_mode'
	];
	/**
	 * Get system settings
	 *
	 * @return array
	 */
	public static function admin_system_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$system_options_keys) + [
			'applied' => $Config->cancel_available()
		];
	}
	/**
	 * Apply system settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_system_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$system_options_keys);
	}
	/**
	 * Save system settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_system_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$system_options_keys);
	}
	/**
	 * Cancel system settings
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_system_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
