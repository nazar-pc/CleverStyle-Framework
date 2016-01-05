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
	cs\Config,
	cs\ExitException,
	cs\Page;

trait system {
	/**
	 * Get system settings
	 */
	static function admin_system_get_settings () {
		$Config = Config::instance();
		Page::instance()->json(
			[
				'site_mode'         => $Config->core['site_mode'],
				'closed_title'      => get_core_ml_text('closed_title'),
				'closed_text'       => get_core_ml_text('closed_text'),
				'title_delimiter'   => $Config->core['title_delimiter'],
				'title_reverse'     => $Config->core['title_reverse'],
				'show_tooltips'     => $Config->core['show_tooltips'],
				'simple_admin_mode' => $Config->core['simple_admin_mode'],
				'applied'           => $Config->cancel_available()
			]
		);
	}
	/**
	 * Apply system settings
	 *
	 * @throws ExitException
	 */
	static function admin_system_apply_settings () {
		static::admin_system_settings_common();
		if (!Config::instance()->apply()) {
			throw new ExitException(500);
		}
	}
	/**
	 * @throws ExitException
	 */
	protected static function admin_system_settings_common () {
		if (!isset(
			$_POST['site_mode'],
			$_POST['closed_title'],
			$_POST['closed_text'],
			$_POST['title_delimiter'],
			$_POST['title_reverse'],
			$_POST['show_tooltips'],
			$_POST['simple_admin_mode']
		)
		) {
			throw new ExitException(400);
		}
		$Config                            = Config::instance();
		$Config->core['site_mode']         = (int)(bool)$_POST['site_mode'];
		$Config->core['closed_title']      = set_core_ml_text('closed_title', xap($_POST['closed_title']));
		$Config->core['closed_text']       = set_core_ml_text('closed_text', xap($_POST['closed_text'], true));
		$Config->core['title_delimiter']   = xap($_POST['title_delimiter']);
		$Config->core['title_reverse']     = (int)(bool)$_POST['title_reverse'];
		$Config->core['show_tooltips']     = (int)(bool)$_POST['show_tooltips'];
		$Config->core['simple_admin_mode'] = (int)(bool)$_POST['simple_admin_mode'];
	}
	/**
	 * Save system settings
	 *
	 * @throws ExitException
	 */
	static function admin_system_save_settings () {
		static::admin_system_settings_common();
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
