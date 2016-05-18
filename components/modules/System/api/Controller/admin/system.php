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
	cs\Config,
	cs\ExitException;

trait system {
	/**
	 * Get system settings
	 */
	static function admin_system_get_settings () {
		$Config = Config::instance();
		return [
			'site_mode'         => $Config->core['site_mode'],
			'closed_title'      => get_core_ml_text('closed_title'),
			'closed_text'       => get_core_ml_text('closed_text'),
			'title_delimiter'   => $Config->core['title_delimiter'],
			'title_reverse'     => $Config->core['title_reverse'],
			'simple_admin_mode' => $Config->core['simple_admin_mode'],
			'applied'           => $Config->cancel_available()
		];
	}
	/**
	 * Apply system settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_system_apply_settings ($Request) {
		static::admin_system_settings_common($Request);
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	protected static function admin_system_settings_common ($Request) {
		$data = $Request->data('site_mode', 'closed_title', 'closed_text', 'title_delimiter', 'title_reverse', 'simple_admin_mode');
		if (!$data) {
			throw new ExitException(400);
		}
		$Config                            = Config::instance();
		$Config->core['site_mode']         = (int)(bool)$data['site_mode'];
		$Config->core['closed_title']      = set_core_ml_text('closed_title', xap($data['closed_title']));
		$Config->core['closed_text']       = set_core_ml_text('closed_text', xap($data['closed_text'], true));
		$Config->core['title_delimiter']   = xap($data['title_delimiter']);
		$Config->core['title_reverse']     = (int)(bool)$data['title_reverse'];
		$Config->core['simple_admin_mode'] = (int)(bool)$data['simple_admin_mode'];
	}
	/**
	 * Save system settings
	 *
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function admin_system_save_settings ($Request) {
		static::admin_system_settings_common($Request);
		if (!Config::instance()->save()) {
			throw new ExitException(500);
		}
	}
	/**
	 * Cancel system settings
	 *
	 * @throws ExitException
	 */
	static function admin_system_cancel_settings () {
		if (!Config::instance()->cancel()) {
			throw new ExitException(500);
		}
	}
}
