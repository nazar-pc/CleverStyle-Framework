<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Config;
use
	cs\Config,
	cs\DB;

/**
 * Class for getting of db and storage configuration of module
 */
class Options {
	/**
	 * Get formats for all supported options
	 *
	 * Format includes option type, default value, supported values or ranges, whether option is password or is multilingual
	 *
	 * @return array[]
	 */
	public static function get_formatting () {
		return static::get_formatting_normalize(
			[
				'array'        => [
					'url'           => [],
					'cookie_domain' => []
				],
				'int_bool'     => [
					'site_mode'                         => 1,
					'title_reverse'                     => 0,
					'cache_compress_js_css'             => 1,
					'frontend_load_optimization'        => 1,
					'vulcanization'                     => 1,
					'put_js_after_body'                 => 1,
					'disable_webcomponents'             => 0,
					'multilingual'                      => 0,
					'db_balance'                        => 0,
					'db_mirror_mode'                    => DB::MIRROR_MODE_MASTER_MASTER,
					'gravatar_support'                  => 0,
					'smtp'                              => 0,
					'smtp_auth'                         => 0,
					'allow_user_registration'           => 1,
					'require_registration_confirmation' => 1,
					'auto_sign_in_after_registration'   => 1,
					'registration_confirmation_time'    => 1,
					'remember_user_ip'                  => 0,
					'simple_admin_mode'                 => 1
				],
				'int_range'    => [
					'inserts_limit'                => [
						'min'   => 1,
						'value' => 1000
					],
					'key_expire'                   => [
						'min'   => 1,
						'value' => 60 * 2
					],
					'session_expire'               => [
						'min'   => 1,
						'value' => 3600 * 24 * 30
					],
					'update_ratio'                 => [
						'min'   => 0,
						'max'   => 100,
						'value' => 75
					],
					'sign_in_attempts_block_count' => [
						'min'   => 0,
						'value' => 0
					],
					'sign_in_attempts_block_time'  => [
						'min'   => 1,
						'value' => 5
					],
					'password_min_length'          => [
						'min'   => 1,
						'value' => 4
					],
					'password_min_strength'        => [
						'min'   => 0,
						'max'   => 7,
						'value' => 3
					]
				],
				'set_single'   => [
					'smtp_secure'    => [
						'value'  => '',
						'values' => ['', 'ssl', 'tls']
					],
					'language'       => [
						'value'  => 'English',
						'values' => static::get_active_languages(),
						'source' => 'active_languages'
					],
					'timezone'       => [
						'value'  => 'UTC',
						'values' => get_timezones_list()
					],
					'default_module' => [
						'value'  => Config::SYSTEM_MODULE,
						'values' => static::get_modules_that_can_be_default()
					],
					'theme'          => [
						'value'  => Config::SYSTEM_THEME,
						'values' => static::get_themes()
					]
				],
				'set_multiple' => [
					'active_languages' => [
						'value'  => [
							'English'
						],
						'values' => static::get_languages()
					]
				],
				'string'       => [
					'admin_email'   => '',
					'cookie_prefix' => '',
					'smtp_host'     => '',
					'smtp_port'     => '',
					'smtp_user'     => '',
					'smtp_password' => [
						'value'    => '',
						'password' => true
					],
					'mail_from'     => ''
				],
				'text'         => [
					'title_delimiter' => ' | ',
					'site_name'       => [
						'multilingual' => true,
						'value'        => ''
					],
					'closed_title'    => [
						'multilingual' => true,
						'value'        => 'Site closed'
					],
					'mail_from_name'  => [
						'multilingual' => true,
						'value'        => 'Administrator'
					]
				],
				'html'         => [
					'closed_text'    => [
						'multilingual' => true,
						'value'        => '<p>Site closed for maintenance</p>'
					],
					'mail_signature' => [
						'multilingual' => true,
						'value'        => ''
					]
				]
			]
		);
	}
	/**
	 * @return string[]
	 */
	protected static function get_languages () {
		$languages = defined('LANGUAGES')
			? array_unique(
				array_merge(
					_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
					_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
				)
			)
			: ['English'];
		asort($languages);
		return $languages;
	}
	/**
	 * @return string[]
	 */
	protected static function get_themes () {
		$themes = defined('THEMES') ? get_files_list(THEMES, false, 'd') : [Config::SYSTEM_THEME];
		asort($themes);
		return $themes;
	}
	/**
	 * @return string[]
	 */
	protected static function get_modules_that_can_be_default () {
		$Config = Config::instance(true);
		if (!defined('MODULES') || !isset($Config->components['modules'])) {
			return [Config::SYSTEM_MODULE];
		}
		/** @noinspection PhpParamsInspection */
		return array_filter(
			array_keys($Config->components['modules']),
			function ($module) use ($Config) {
				return $Config->module($module) && file_exists_with_extension(MODULES."/$module/index", ['php', 'html', 'json']);
			}
		);
	}
	/**
	 * @return string[]
	 */
	protected static function get_active_languages () {
		$Config = Config::instance(true);
		if (!isset($Config->core['active_languages'])) {
			return ['English'];
		}
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $Config->core['active_languages'];
	}
	/**
	 * @param array[] $format
	 *
	 * @return array[]
	 */
	protected static function get_formatting_normalize ($format) {
		foreach ($format as $type => &$items) {
			foreach ($items as $item => &$data) {
				if (!is_array($data) || !isset($data['value'])) {
					$data = [
						'value' => $data
					];
				}
				$data['type'] = $type;
				if (($type == 'set_single' || $type == 'set_multiple') && !is_array_assoc($data['values'])) {
					$data['values'] = array_combine($data['values'], $data['values']);
				}
			}
			unset($data);
		}
		return array_merge(...array_values($format));
	}
	/**
	 * Get default values for all supported options
	 *
	 * @return array
	 */
	public static function get_defaults () {
		return array_map(
			function ($option) {
				return $option['value'];
			},
			static::get_formatting()
		);
	}
	/**
	 * Get list of multilingual options
	 *
	 * @return string[]
	 */
	public static function get_multilingual () {
		return array_values(
			array_keys(
				array_filter(
					static::get_formatting(),
					function ($option) {
						return @$option['multilingual'];
					}
				)
			)
		);
	}
	/**
	 * Take options and check each value according to needed format, correct value or use default if needed
	 *
	 * @param array $target_options
	 *
	 * @return array
	 */
	public static function apply_formatting ($target_options) {
		$options = static::get_formatting();
		foreach ($target_options as $option => &$value) {
			if (!isset($options[$option])) {
				unset($target_options[$option]);
			} else {
				$format = $options[$option];
				switch ($format['type']) {
					case 'array':
						$value = xap((array)$value);
						break;
					case 'int_bool':
						$value = (int)(bool)$value;
						break;
					case 'int_range':
						if (isset($format['min'])) {
							$value = max($format['min'], (int)$value);
						}
						if (isset($format['max'])) {
							$value = min($format['max'], (int)$value);
						}
						break;
					case 'set_single':
						$value = (string)$value;
						if (!in_array($value, $format['values'], true)) {
							$value = $format['value'];
						}
						break;
					case 'set_multiple':
						$value = array_filter(
							(array)$value,
							function ($value) use ($format) {
								return in_array((string)$value, $format['values'], true);
							}
						);
						$value = $value ?: [$format['value']];
						break;
					case 'text':
						$value = xap($value);
						break;
					case 'html':
						$value = xap($value, true);
						break;
				}
			}
		}
		return $target_options;
	}
}
