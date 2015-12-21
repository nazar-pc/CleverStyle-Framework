<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin\Controller;
use
	cs\Cache,
	cs\Config,
	cs\Core,
	cs\DB,
	cs\Index,
	cs\Language,
	h;

trait general {
	static function general_about_server (
		/** @noinspection PhpUnusedParameterInspection */
		$route_ids,
		$route_path
	) {
		$Core  = Core::instance();
		$Index = Index::instance();
		$L     = Language::instance();
		if (isset($route_path[2])) {
			interface_off();
			$Index->form = false;
			switch ($route_path[2]) {
				case 'phpinfo':
					$Index->Content = ob_wrapper(
						function () {
							phpinfo();
						}
					);
					break;
				case 'readme.html':
					$Index->Content = file_get_contents(DIR.'/readme.html');
			}
			return;
		}
		$hhvm_version = defined('HHVM_VERSION') ? HHVM_VERSION : false;
		$Index->form  = false;
		$Index->content(
			h::{'div.cs-text-right'}(
				h::{'a[is=cs-link-button][target=_blank]'}(
					'phpinfo()',
					[
						'href' => "$Index->action/phpinfo"
					]
				).
				h::{'a[is=cs-link-button][icon=info][target=_blank]'}(
					$L->information_about_system,
					[
						'href' => "$Index->action/readme.html"
					]
				).
				h::{'button[is=cs-button][icon=legal]'}(
					$L->license
				).
				h::{'section[is=cs-section-modal] pre'}(
					file_get_contents(DIR.'/license.txt')
				)
			).
			static::vertical_table(
				[
					"$L->operation_system:",
					php_uname('s').' '.php_uname('r').' '.php_uname('v')
				],
				[
					"$L->server_type:",
					self::server_api()
				],
				stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false ? [
					$L->version_of('Apache').':',
					self::apache_version()
				] : false,
				stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false ? [
					$L->version_of('Nginx').':',
					explode('/', $_SERVER['SERVER_SOFTWARE'])[1]
				] : false,
				$hhvm_version ? [
					$L->version_of('HHVM').':',
					$hhvm_version
				] : false,
				$hhvm_version ? false : [
					"$L->available_ram:",
					str_replace(
						['K', 'M', 'G'],
						[" $L->KB", " $L->MB", " $L->GB"],
						ini_get('memory_limit')
					)
				],
				[
					$L->version_of('PHP').':',
					PHP_VERSION
				],
				[
					"$L->php_components:",
					h::{'table.cs-table tr| td'}(
						[
							"$L->openssl:",
							[
								extension_loaded('openssl') ? $L->on : $L->off.' '.h::icon('info-circle', ['tooltip' => $L->openssl_warning]),
								[
									'class' => self::state(extension_loaded('openssl'))
								]
							]
						],
						[
							"$L->curl_lib:",
							[
								$L->get(extension_loaded('curl')),
								[
									'class' => self::state(extension_loaded('curl'))
								]
							]
						],
						[
							"$L->apcu_module:",
							[
								$L->get(extension_loaded('apcu')),
								[
									'class' => self::state(extension_loaded('apcu'))
								]
							]
						],
						[
							"$L->memcached_module:",
							[
								$L->get(extension_loaded('memcached'))
							]
						]
					)
				],
				[
					"$L->main_db:",
					$Core->db_type
				],
				[
					"$L->properties $Core->db_type:",
					h::{'table.cs-table tr| td'}(
						[
							"$L->host:",
							$Core->db_host
						],
						[
							$L->version_of($Core->db_type).':',
							[
								DB::instance()->server()
							]
						],
						[
							"$L->name_of_db:",
							$Core->db_name
						],
						[
							"$L->prefix_for_db_tables:",
							$Core->db_prefix
						]
					)
				],
				[
					"$L->main_storage:",
					$Core->storage_type
				],
				[
					"$L->cache_engine:",
					$Core->cache_engine
				],
				[
					"$L->free_disk_space:",
					format_filesize(disk_free_space('./'), 2)
				],
				[
					"$L->php_ini_settings:",
					h::{'table.cs-table tr| td'}(
						[
							"$L->allow_file_upload:",
							[
								$L->get(ini_get('file_uploads')),
								[
									'class' => self::state(ini_get('file_uploads'))
								]
							]
						],
						$hhvm_version ? false : [
							"$L->max_file_uploads:",
							ini_get('max_file_uploads')
						],
						[
							"$L->upload_limit:",
							format_filesize(
								str_replace(
									['K', 'M', 'G'],
									[" $L->KB", " $L->MB", " $L->GB"],
									ini_get('upload_max_filesize')
								)
							)
						],
						[
							"$L->post_max_size:",
							format_filesize(
								str_replace(
									['K', 'M', 'G'],
									[" $L->KB", " $L->MB", " $L->GB"],
									ini_get('post_max_size')
								)
							)
						],
						$hhvm_version ? false : [
							"$L->max_execution_time:",
							format_time(ini_get('max_execution_time'))
						],
						$hhvm_version ? false : [
							"$L->max_input_time:",
							format_time(ini_get('max_input_time'))
						],
						$hhvm_version ? false : [
							"$L->default_socket_timeout:",
							format_time(ini_get('default_socket_timeout'))
						],
						[
							"$L->allow_url_fopen:",
							[
								$L->get(ini_get('allow_url_fopen')),
								[
									'class' => self::state(ini_get('allow_url_fopen'))
								]
							]
						],
						[
							"$L->display_errors:",
							[
								$L->get((bool)ini_get('display_errors')),
								[
									'class' => self::state(!ini_get('display_errors'))
								]
							]
						]
					)
				]
			)
		);
	}
	static private function state ($state) {
		return $state ? 'cs-block-success cs-text-success' : 'cs-block-error cs-text-error';
	}
	/**
	 * Returns server type
	 *
	 * @return string
	 */
	static private function server_api () {
		$phpinfo = ob_wrapper('phpinfo');
		if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
			return 'Apache'.(stripos($phpinfo, 'mod_php') !== false ? ' + mod_php' : '');
		} elseif (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
			$return = 'Nginx';
			if (stripos($phpinfo, 'php-fpm') !== false) {
				$return .= ' + PHP-FPM';
			} elseif (defined('HHVM_VERSION')) {
				$return .= ' + HHVM';
			}
			return $return;
		} elseif (isset($_SERVER['SERVER_SOFTWARE'])) {
			return $_SERVER['SERVER_SOFTWARE'];
		} else {
			return Language::instance()->indefinite;
		}
	}
	static private function apache_version () {
		preg_match(
			'/Apache[\-\/]([0-9\.\-]+)/',
			ob_wrapper('phpinfo'),
			$version
		);
		return $version[1];
	}
	static function general_appearance () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_themes()
		);
	}
	static function general_languages () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_languages()
		);
	}
	static function general_optimization () {
		$Config              = Config::instance();
		$Index               = Index::instance();
		$L                   = Language::instance();
		$sa                  = $Config->core['simple_admin_mode'];
		$Index->apply_button = true;
		$Index->content(
			static::vertical_table(
				static::core_input('cache_compress_js_css', 'radio'),
				static::core_input('vulcanization', 'radio'),
				static::core_input('put_js_after_body', 'radio'),
				(!$sa ? static::core_input('inserts_limit', 'number', null, false, 1) : false),
				(!$sa ? static::core_input('update_ratio', 'number', null, false, 0, 100) : false),
				[
					h::{'div#clean_cache'}(),
					h::{'div#clean_pcache'}()
				],
				[
					h::{'input[is=cs-input-text][compact][tight]'}(
						[
							'placeholder' => $L->partial_cache_cleaning,
							'style'       => $Config->core['simple_admin_mode'] ? 'display:none;' : false
						]
					).
					h::{'button[is=cs-button]'}(
						$L->clean_settings_cache,
						Cache::instance()->cache_state() ? [
							'onMouseDown' => "cs.admin_cache('#clean_cache', '{$Config->base_url()}/api/System/admin/cache/clean_cache', this.previousElementSibling.value);"
						] : ['disabled']
					),
					h::{'button[is=cs-button]'}(
						$L->clean_scripts_styles_cache,
						$Config->core['cache_compress_js_css'] ? [
							'onMouseDown' => "cs.admin_cache('#clean_pcache', '{$Config->base_url()}/api/System/admin/cache/clean_pcache');"
						] : ['disabled']
					)
				]
			)
		);
	}
	static function general_site_info () {
		$Index       = Index::instance();
		$Index->form = false;
		$Index->content(
			h::cs_system_admin_site_info()
		);
	}
	static function general_system () {
		$Config              = Config::instance();
		$Index               = Index::instance();
		$sa                  = $Config->core['simple_admin_mode'];
		$Index->apply_button = true;
		$Index->content(
			static::vertical_table(
				static::core_input('site_mode', 'radio'),
				static::core_input('closed_title'),
				static::core_textarea('closed_text', 'cs-editor'),
				static::core_input('title_delimiter'),
				static::core_input('title_reverse', 'radio'),
				static::core_input('show_tooltips', 'radio', false),
				static::core_input('simple_admin_mode', 'radio'),
				!$sa ? [
					h::info('routing'),
					h::{'table.cs-table[center] tr| td'}(
						[
							h::info('routing_in'),
							h::info('routing_out')
						],
						[
							h::{'textarea[is=cs-textarea][autosize]'}(
								$Config->routing['in'],
								[
									'name' => 'routing[in]'
								]
							),
							h::{'textarea[is=cs-textarea][autosize]'}(
								$Config->routing['out'],
								[
									'name' => 'routing[out]'
								]
							)
						]
					)
				] : false,
				!$sa ? [
					h::info('replace'),
					h::{'table.cs-table[center] tr| td'}(
						[
							h::info('replace_in'),
							h::info('replace_out')
						],
						[
							h::{'textarea[is=cs-textarea][autosize]'}(
								$Config->replace['in'],
								[
									'name' => 'replace[in]'
								]
							),
							h::{'textarea[is=cs-textarea][autosize]'}(
								$Config->replace['out'],
								[
									'name' => 'replace[out]'
								]
							)
						]
					)
				] : false
			)
		);
	}
}
