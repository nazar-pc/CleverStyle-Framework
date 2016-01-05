<?php
/**
 * @package    CleverStyle CMS
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
	cs\ExitException,
	cs\Page;

trait optimization {
	/**
	 * Get optimization settings
	 */
	static function admin_optimization_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'cache_compress_js_css' => $Config->core['cache_compress_js_css'],
				'vulcanization'         => $Config->core['vulcanization'],
				'put_js_after_body'     => $Config->core['put_js_after_body'],
				'inserts_limit'         => $Config->core['inserts_limit'],
				'update_ratio'          => $Config->core['update_ratio'],
				'cache_state'           => Cache::instance()->cache_state(),
				'show_tooltips'         => $Config->core['show_tooltips'],
				'simple_admin_mode'     => $Config->core['simple_admin_mode'],
				'applied'               => $Config->cancel_available()
			]
		);
	}
	/**
	 * Clean cache
	 *
	 * @throws ExitException
	 */
	static function admin_optimization_clean_cache () {
		$Cache = Cache::instance();
		time_limit_pause();
		if (@$_POST['path_prefix']) {
			$result = $Cache->del($_POST['path_prefix']);
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
	 * @throws ExitException
	 */
	static function admin_optimization_apply_settings () {
		static::admin_optimization_settings_common();
		$Config = Config::instance();
		if (!$Config->apply()) {
			throw new ExitException(500);
		}
		if (!$Config->core['cache_compress_js_css']) {
			static::admin_optimization_clean_pcache();
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_optimization_settings_common () {
		if (!isset(
			$_POST['cache_compress_js_css'],
			$_POST['vulcanization'],
			$_POST['put_js_after_body'],
			$_POST['inserts_limit'],
			$_POST['update_ratio']
		)
		) {
			throw new ExitException(400);
		}
		$Config                                = Config::instance();
		$Config->core['cache_compress_js_css'] = (int)(bool)$_POST['cache_compress_js_css'];
		$Config->core['vulcanization']         = (int)(bool)$_POST['vulcanization'];
		$Config->core['put_js_after_body']     = (int)(bool)$_POST['put_js_after_body'];
		$Config->core['inserts_limit']         = (int)$_POST['inserts_limit'];
		$Config->core['update_ratio']          = (int)$_POST['update_ratio'];
	}
	/**
	 * Save optimization settings
	 *
	 * @throws ExitException
	 */
	static function admin_optimization_save_settings () {
		static::admin_optimization_settings_common();
		$Config = Config::instance();
		if (!$Config->save()) {
			throw new ExitException(500);
		}
		if (!$Config->core['cache_compress_js_css']) {
			static::admin_optimization_clean_pcache();
		}
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
