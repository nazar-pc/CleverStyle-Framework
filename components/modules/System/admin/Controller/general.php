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
	cs\Page,
	cs\Session,
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
			h::{'div.cs-right'}(
				h::{'a.uk-button[target=_blank]'}(
					'phpinfo()',
					[
						'href' => "$Index->action/phpinfo"
					]
				).
				h::{'a.uk-button[target=_blank]'}(
					h::icon('info').$L->information_about_system,
					[
						'href' => "$Index->action/readme.html"
					]
				).
				h::{'div#cs-system-license.uk-modal pre.uk-modal-dialog.uk-modal-dialog-large.cs-left'}(
					file_get_contents(DIR.'/license.txt'),
					[
						'title' => "$L->system » $L->license"
					]
				).
				h::{'button#cs-system-license-open.uk-button'}(
					h::icon('legal').$L->license,
					[
						'data-uk-modal' => "{target:'#cs-system-license'}"
					]
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
				preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE']) ? [
					$L->version_of('Apache').':',
					self::apache_version()
				] : false,
				preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE']) ? [
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
					h::{'cs-table cs-table-row| cs-table-cell'}(
						[
							"$L->openssl:",
							[
								extension_loaded('openssl') ? $L->on : $L->off.h::icon('info-sign', ['data-title' => $L->openssl_warning]),
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
					h::{'cs-table cs-table-row| cs-table-cell'}(
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
					h::{'cs-table cs-table-row| cs-table-cell'}(
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
		return $state ? 'uk-alert-success' : 'uk-alert-danger';
	}
	/**
	 * Returns server type
	 *
	 * @return string
	 */
	static private function server_api () {
		$phpinfo = ob_wrapper('phpinfo');
		if (preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE'])) {
			return 'Apache'.(preg_match('/mod_php/i', $phpinfo) ? ' + mod_php' : '');
		} elseif (preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE'])) {
			$return = 'Nginx';
			if (preg_match('/php-fpm/i', $phpinfo)) {
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
		$Config              = Config::instance();
		$Index               = Index::instance();
		$L                   = Language::instance();
		$Page                = Page::instance();
		$Index->apply_button = true;
		if (isset($_POST['action'])) {
			switch ($_POST['action']) {
				case 'upload':
					$tmp_file = static::move_uploaded_file_to_tmp('upload_theme');
					if (!$tmp_file) {
						break;
					}
					$tmp_dir = "phar://$tmp_file";
					$meta    = file_exists("$tmp_dir/meta.json") ? file_get_json("$tmp_dir/meta.json") : false;
					if (
						!$meta ||
						@$meta['category'] != 'themes' ||
						!@$meta['package']
					) {
						$Page->warning($L->this_is_not_theme_installer_file);
						unlink($tmp_file);
						break;
					}
					$theme = $meta['package'];
					if (in_array($theme, $Config->core['themes'])) {
						$current_version = file_get_json(THEMES."/$theme/meta.json")['version'];
						$new_version     = $meta['version'];
						if (!version_compare($current_version, $new_version, '<')) {
							$Page->warning($L->update_theme_impossible_older_version($theme));
							unlink($tmp_file);
							break;
						}
						$Page->title($L->updating_of_theme($theme));
						rename($tmp_file, $tmp_file = TEMP.'/'.Session::instance()->get_id().'_theme_update.phar');
						$Index->content(
							h::{'h2.cs-center'}(
								$L->update_theme(
									$theme,
									$current_version,
									$new_version
								)
							).
							h::{'input[type=hidden]'}(
								[
									'name'  => 'update_theme',
									'value' => $theme
								]
							)
						);
						$Index->buttons            = false;
						$Index->cancel_button_back = true;
						$Index->content(
							h::{'button.uk-button[type=submit][name=action][value=update]'}($L->yes)
						);
						return;
					}
					if (!file_exists(THEMES."/$theme") && !mkdir(THEMES."/$theme", 0770)) {
						$Page->warning($L->cant_unpack_theme_no_write_permissions);
						unlink($tmp_file);
						break;
					}
					$extract = static::install_extract(THEMES."/$theme", $tmp_file);
					unset($tmp_file, $tmp_dir, $theme);
					if (!$extract) {
						$Page->warning($L->theme_files_unpacking_error);
						break;
					}
					$Index->save(true);
					break;
				case 'update':
					if (!isset($_POST['update_theme'])) {
						break;
					}
					$Session   = Session::instance();
					$theme_dir = THEMES."/$_POST[update_theme]";
					if (!static::update_extract($theme_dir, TEMP.'/'.$Session->get_id().'_theme_update.phar')) {
						$Page->warning($L->theme_files_unpacking_error);
						break;
					}
					/**
					 * Clean themes cache
					 */
					$Index->save(true);
					clean_pcache();
					break;
				case 'remove':
					if (!isset($_POST['remove_theme'])) {
						break;
					}
					$Page->title($L->complete_removal_of_theme($_POST['remove_theme']));
					$Index->content(
						h::{'h2.cs-center'}(
							$L->completely_remove_theme($_POST['remove_theme'])
						)
					);
					$Index->buttons            = false;
					$Index->cancel_button_back = true;
					$Index->content(
						h::{'button.uk-button[type=submit][name=action][value=remove_confirmed]'}($L->yes).
						h::{'input[type=hidden]'}(
							[
								'name'  => 'remove_theme_confirmed',
								'value' => $_POST['remove_theme']
							]
						)
					);
					return;
				case 'remove_confirmed':
					$theme = $_POST['remove_theme_confirmed'];
					if ($theme == 'CleverStyle' || $theme == $Config->core['theme']) {
						break;
					}
					if (static::recursive_directory_removal(THEMES."/$theme")) {
						$Index->save();
					} else {
						$Index->save(false);
					}
					break;
			}
		}

		$Config->reload_themes();
		$themes_for_removal = array_values(
			array_filter(
				get_files_list(THEMES, '/[^CleverStyle)]/', 'd'),
				function ($theme) use ($Config) {
					return $theme != $Config->core['theme'];
				}
			)
		);
		$Index->file_upload = true;
		$Index->content(
			static::vertical_table(
				[
					static::core_select($Config->core['themes'], 'theme', null, 'current_theme')
				]
			).
			h::p(
				h::{'input[type=file][name=upload_theme]'}().
				h::{'button.uk-button[type=submit][name=action][value=upload]'}(
					h::icon('upload').$L->upload_and_install_update_theme,
					[
						'formaction' => $Index->action
					]
				)
			).
			(
			$themes_for_removal
				? h::p(
				h::{'select[name=remove_theme]'}($themes_for_removal).
				h::{'button.uk-button[type=submit][name=action][value=remove]'}(
					h::icon('trash-o').$L->complete_theme_removal,
					[
						'formaction' => $Index->action
					]
				)
			)
				: ''
			)
		);
	}
	static function general_languages () {
		$Config = Config::instance();
		$Index  = Index::instance();
		$L      = Language::instance();
		$Config->reload_languages();
		$Index->apply_button = true;
		$Index->content(
			static::vertical_table(
				static::core_select($Config->core['active_languages'], 'language', 'change_language', 'current_language'),
				static::core_select($Config->core['languages'], 'active_languages', 'change_active_languages', null, true),
				[
					h::info('multilingual'),
					h::radio(
						[
							'name'    => 'core[multilingual]',
							'checked' => $Config->core['multilingual'],
							'value'   => [0, 1],
							'in'      => [$L->off, $L->on]
						]
					)
				]
			)
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
					h::{'input[style=width:auto;]'}(
						[
							'placeholder' => $L->partial_cache_cleaning,
							'style'       => $Config->core['simple_admin_mode'] ? 'display:none;' : false
						]
					).
					h::{'button.uk-button'}(
						$L->clean_settings_cache,
						Cache::instance()->cache_state() ? [
							'onMouseDown' => "cs.admin_cache('#clean_cache', '{$Config->base_url()}/api/System/admin/cache/clean_cache', $(this).prev().val());"
						] : ['disabled']
					),
					h::{'button.uk-button'}(
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
		$Config              = Config::instance();
		$Index               = Index::instance();
		$timezones           = get_timezones_list();
		$sa                  = $Config->core['simple_admin_mode'];
		$Index->apply_button = true;
		Index::instance()->content(
			static::vertical_table(
				static::core_input('name', 'text', 'site_name'),
				!$sa ? static::core_textarea('url') : false,
				!$sa ? static::core_textarea('cookie_domain') : false,
				!$sa ? static::core_textarea('cookie_path') : false,
				!$sa ? static::core_input('cookie_prefix') : false,
				[
					h::info('timezone'),
					h::select(
						[
							'in'    => array_keys($timezones),
							'value' => array_values($timezones)
						],
						[
							'name'     => 'core[timezone]',
							'selected' => $Config->core['timezone'],
							'size'     => 7
						]
					)
				],
				static::core_input('admin_email', 'email')
			)
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
				static::core_textarea('closed_text', 'SIMPLE_EDITOR'),
				static::core_input('title_delimiter'),
				static::core_input('title_reverse', 'radio'),
				static::core_input('show_tooltips', 'radio', false),
				static::core_input('simple_admin_mode', 'radio'),
				!$sa ? [
					h::info('routing'),
					h::{'cs-table[center] cs-table-row| cs-table-cell'}(
						[
							h::info('routing_in'),
							h::info('routing_out')
						],
						[
							h::textarea(
								$Config->routing['in'],
								[
									'name' => 'routing[in]'
								]
							),
							h::textarea(
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
					h::{'cs-table[center] cs-table-row| cs-table-cell'}(
						[
							h::info('replace_in'),
							h::info('replace_out')
						],
						[
							h::textarea(
								$Config->replace['in'],
								[
									'name' => 'replace[in]'
								]
							),
							h::textarea(
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
