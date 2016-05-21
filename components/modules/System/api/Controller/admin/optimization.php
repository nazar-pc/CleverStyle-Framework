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
	/**
	 * Get optimization settings
	 */
	static function admin_optimization_get_settings () {
		$Config = Config::instance();
		return [
			'cache_compress_js_css'      => $Config->core['cache_compress_js_css'],
			'frontend_load_optimization' => $Config->core['frontend_load_optimization'],
			'vulcanization'              => $Config->core['vulcanization'],
			'put_js_after_body'          => $Config->core['put_js_after_body'],
			'inserts_limit'              => $Config->core['inserts_limit'],
			'update_ratio'               => $Config->core['update_ratio'],
			'cache_state'                => Cache::instance()->cache_state(),
			'simple_admin_mode'          => $Config->core['simple_admin_mode'],
			'applied'                    => $Config->cancel_available()
		];
	}
	/**
	 * Clean cache
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_optimization_clean_cache ($Request) {
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
	static function admin_optimization_clean_pcache () {
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
	static function admin_optimization_apply_settings ($Request) {
		static::admin_optimization_settings_common($Request);
		$Config = Config::instance();
		if (!$Config->apply()) {
			throw new ExitException(500);
		}
		static::admin_optimization_clean_pcache();
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_optimization_settings_common ($Request) {
		$data = $Request->data('cache_compress_js_css', 'frontend_load_optimization', 'vulcanization', 'put_js_after_body', 'inserts_limit', 'update_ratio');
		if (!$data) {
			throw new ExitException(400);
		}
		$Config                                     = Config::instance();
		$Config->core['cache_compress_js_css']      = (int)(bool)$data['cache_compress_js_css'];
		$Config->core['frontend_load_optimization'] = (int)(bool)$data['frontend_load_optimization'];
		$Config->core['vulcanization']              = (int)(bool)$data['vulcanization'];
		$Config->core['put_js_after_body']          = (int)(bool)$data['put_js_after_body'];
		$Config->core['inserts_limit']              = (int)$data['inserts_limit'];
		$Config->core['update_ratio']               = (int)$data['update_ratio'];
	}
	/**
	 * Save optimization settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_optimization_save_settings ($Request) {
		static::admin_optimization_settings_common($Request);
		$Config = Config::instance();
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		static::admin_optimization_clean_pcache();
	}
	/**
	 * Cancel optimization settings
	 *
	 * @throws ExitException
	 */
	static function admin_optimization_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
