<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System\general\about_server;
use			h,
			cs\Config,
			cs\Core,
			cs\DB,
			cs\Index,
			cs\Language;
$Config			= Config::instance();
$Core			= Core::instance();
$Index			= Index::instance();
$L				= Language::instance();
if (isset($Config->route[2]) && $Config->route[2] == 'phpinfo') {
	interface_off();
	ob_start();
	phpinfo();
	$Index->content(ob_get_clean());
	$Index->stop;
	return;
}
$Index->form	= false;
$Index->content(
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}(
		[
			h::{'a.cs-button[target=_blank]'}(
				'phpinfo()',
				[
					'href'	=> "$Index->action/phpinfo"
				]
			).
			h::{'a.cs-button[target=_blank]'}(
				$L->information_about_system,
				[
				'href'	=> 'readme.html'
				]
			).
			h::{'div#cs-system-license.cs-dialog pre'}(
				file_get_contents(DIR.'/license.txt'),
				[
					'title'			=> "$L->system » $L->license"
				]
			).
			h::{'button#cs-system-license-open'}(
				$L->license,
				[
					'data-title'	=> $L->click_to_view_details
				]
			),
			[
				'colspan'	=> 2
			]
		],
		[
			"$L->operation_system:",
			php_uname('s').' '.php_uname('r').' '.php_uname('v')
		],
		[
			"$L->server_type:",
			server_api()
		],
		preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE']) ? [
			$L->version_of('Apache').':',
			$_SERVER['SERVER_SOFTWARE']
		] : false,
		preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE']) ? [
			$L->version_of('Nginx').':',
			$_SERVER['SERVER_SOFTWARE']
		] : false,
		[
			"$L->available_ram:",
			str_replace(
				['K', 'M', 'G'],
				[" $L->KB", " $L->MB", " $L->GB"],
				ini_get('memory_limit')
			)
		],
		[
			"$L->free_disk_space:",
			format_filesize(disk_free_space('./'), 2)
		],
		[
			$L->version_of('PHP').':',
			PHP_VERSION
		],
		[
			"$L->php_components:",
			h::{'table.cs-left-odd.cs-table-borderless tr| td'}(
				[
					"$L->mcrypt:",
					[
						check_mcrypt() ? $L->on : $L->off.h::icon('info-sign', ['data-title'	=> $L->mcrypt_warning]),
						[
							'class' => state(check_mcrypt())
						]
					]
				],
				[
					"$L->zlib:",
					$L->get(zlib())
				],
				zlib() ? [
					"$L->zlib_compression:",
					$L->get(zlib_compression())
				] : false,
				zlib_compression() ? [
					"$L->zlib_compression_level:",
					zlib_compression_level()
				] : false,
				[
					"$L->curl_lib:",
					[
						$L->get(curl()),
						[
							'class' => state(curl())
						]
					]
				],
				[
					"$L->apc_module:",
					[
						$L->get(apc()),
						[
							'class' => version_compare(PHP_VERSION, '5.5', '>=') ? false : state(apc())
						]
					]
				],
				[
					"$L->memcached_module:",
					[
						$L->get(memcached())
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
			h::{'table.cs-left-odd.cs-table-borderless tr| td'}(
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
			"$L->php_ini_settings:",
			h::{'table.cs-left-odd.cs-table-borderless tr| td'}(
				[
					"$L->allow_file_upload:",
					[
						$L->get(ini_get('file_uploads')),
						[
							'class' => state(ini_get('file_uploads'))
						]
					]
				],
				[
					"$L->max_file_uploads:",
					ini_get('max_file_uploads')
				],
				[
					"$L->upload_limit:",
					str_replace(
						['K', 'M', 'G'],
						[" $L->KB", " $L->MB", " $L->GB"],
						ini_get('upload_max_filesize')
					)
				],
				[
					"$L->post_max_size:",
					str_replace(
						['K', 'M', 'G'],
						[" $L->KB", " $L->MB", " $L->GB"],
						ini_get('post_max_size')
					)
				],
				[
					"$L->max_execution_time:",
					format_time(ini_get('max_execution_time'))
				],
				[
					"$L->max_input_time:",
					format_time(ini_get('max_input_time'))
				],
				[
					"$L->default_socket_timeout:",
					format_time(ini_get('default_socket_timeout'))
				],
				[
					"$L->allow_url_fopen:",
					[
						$L->get(ini_get('allow_url_fopen')),
						[
							'class' => state(ini_get('allow_url_fopen'))
						]
					]
				],
				[
					"$L->display_errors:",
					[
						$L->get(display_errors()),
						[
							'class' => state(!display_errors())
						]
					]
				]
			)
		]
	)
);
function state ($state) {
	return $state ? 'uk-alert-success' : 'uk-alert-danger';
}
/**
 * Returns server type
 *
 * @return string
 */
function server_api () {
	$tmp = ob_wrapper(function () {
		phpinfo(INFO_GENERAL);
	});
	preg_match('/Server API <\/td><td class="v">(.*?) <\/td><\/tr>/', $tmp, $tmp);
	if ($tmp[1]) {
		return $tmp[1];
	} else {
		return Language::instance()->indefinite;
	}
}
/**
 * Returns autocompression level of zlib library
 *
 * @return bool
 */
function zlib_compression_level () {
	return ini_get('zlib.output_compression_level');
}
/**
 * Check of "display_errors" configuration of php.ini
 *
 * @return bool
 */
function display_errors () {
	return (bool)ini_get('display_errors');
}