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

trait site_info {
	protected static $site_info_options_keys = [
		'site_name',
		'url',
		'cookie_domain',
		'cookie_prefix',
		'timezone',
		'admin_email'
	];
	/**
	 * Get site info settings
	 *
	 * @return array
	 */
	public static function admin_site_info_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$site_info_options_keys) + [
			'applied' => $Config->cancel_available()
		];
	}
	/**
	 * Apply site info settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_site_info_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$site_info_options_keys);
	}
	/**
	 * Save site info settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_site_info_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$site_info_options_keys);
	}
	/**
	 * Cancel site info settings
	 *
	 * @throws \cs\ExitException
	 */
	public static function admin_site_info_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
