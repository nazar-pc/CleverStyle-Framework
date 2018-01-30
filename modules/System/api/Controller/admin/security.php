<?php
/**
 * @package    CleverStyle Framework
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license    0BSD
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Config;

trait security {
	protected static $security_options_keys = [
		'key_expire',
		'gravatar_support'
	];
	/**
	 * Get security settings
	 *
	 * @return array
	 */
	public static function admin_security_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$security_options_keys) + [
			'applied' => $Config->cancel_available()
		];
	}
	/**
	 * Apply security settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_security_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$security_options_keys);
	}
	/**
	 * Save security settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_security_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$security_options_keys);
	}
	/**
	 * Cancel security settings
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_security_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
