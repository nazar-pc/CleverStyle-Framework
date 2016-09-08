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
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\ExitException;

trait optimization {
	protected static $optimization_options_keys = [
		'cache_compress_js_css',
		'frontend_load_optimization',
		'vulcanization',
		'put_js_after_body',
		'disable_webcomponents',
		'inserts_limit',
		'update_ratio'
	];
	/**
	 * Get optimization settings
	 *
	 * @return array
	 */
	public static function admin_optimization_get_settings () {
		$Config = Config::instance();
		return $Config->core(static::$optimization_options_keys) + [
			'cache_state' => Cache::instance()->cache_state(),
			'applied'     => $Config->cancel_available()
		];
	}
	/**
	 * Clean cache
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_optimization_clean_cache ($Request) {
		$Cache = Cache::instance();
		time_limit_pause();
		$path_prefix = $Request->data('path_prefix');
		if ($path_prefix) {
			$result = $Cache->del($path_prefix);
		} else {
			$result = $Cache->clean();
			clean_classes_cache();
		}
		time_limit_pause(false);
		if (!$result) {
			throw new ExitException(500);
		}
		$Cache->disable();
	}
	/**
	 * Clean public cache (CSS/JS/HTML)
	 *
	 * @throws ExitException
	 */
	public static function admin_optimization_clean_pcache () {
		if (!clean_pcache()) {
			throw new ExitException(500);
		}
		Event::instance()->fire('admin/System/general/optimization/clean_pcache');
	}
	/**
	 * Apply optimization settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_optimization_apply_settings ($Request) {
		static::admin_core_options_apply($Request, static::$optimization_options_keys);
		static::admin_optimization_clean_pcache();
	}
	/**
	 * Save optimization settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	public static function admin_optimization_save_settings ($Request) {
		static::admin_core_options_save($Request, static::$optimization_options_keys);
		static::admin_optimization_clean_pcache();
	}
	/**
	 * Cancel optimization settings
	 *
	 * @throws ExitException
	 */
	public static function admin_optimization_cancel_settings () {
		static::admin_core_options_cancel();
	}
}
