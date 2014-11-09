<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System\general\about_server;
use
	h,
	cs\Core,
	cs\DB,
	cs\Index,
	cs\Language;
$Core			= Core::instance();
$Index			= Index::instance();
$L				= Language::instance();
if (isset($Index->route_path[2])) {
	interface_off();
	$Index->form	= false;
	switch ($Index->route_path[2]) {
		case 'phpinfo':
			$Index->Content	= ob_wrapper(function () {
				phpinfo();
			});
		break;
		case 'readme.html':
			$Index->Content	= file_get_contents(DIR.'/readme.html');
	}
	return;
}
$hhvm_version	= defined('HHVM_VERSION') ? HHVM_VERSION : false;
$Index->form	= false;
$Index->content(
	h::{'div.cs-right'}(
		h::{'a.uk-button[target=_blank]'}(
			'phpinfo()',
			[
				'href'	=> "$Index->action/phpinfo"
			]
		).
		h::{'a.uk-button[target=_blank]'}(
			h::icon('info').$L->information_about_system,
			[
				'href'	=> "$Index->action/readme.html"
			]
		).
		h::{'div#cs-system-license.uk-modal pre.uk-modal-dialog-large.cs-left'}(
			file_get_contents(DIR.'/license.txt'),
			[
				'title'			=> "$L->system » $L->license"
			]
		).
		h::{'button#cs-system-license-open.uk-button'}(
			h::icon('legal').$L->license,
			[
				'data-title'	=> $L->click_to_view_details
			]
		)
	).
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
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
			apache_version()
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
							'class' => state(ini_get('file_uploads'))
						]
					]
				],
				$hhvm_version ? false : [
					"$L->max_file_uploads:",
					ini_get('max_file_uploads')
				],
				[
					"$L->upload_limit:",
					format_filesize(str_replace(
						['K', 'M', 'G'],
						[" $L->KB", " $L->MB", " $L->GB"],
						ini_get('upload_max_filesize')
					))
				],
				[
					"$L->post_max_size:",
					format_filesize(str_replace(
						['K', 'M', 'G'],
						[" $L->KB", " $L->MB", " $L->GB"],
						ini_get('post_max_size')
					))
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
	$phpinfo = ob_wrapper(function () {
		phpinfo();
	});
	if (preg_match('/apache/i', $_SERVER['SERVER_SOFTWARE'])) {
		return 'Apache'.(preg_match('/mod_php/i', $phpinfo) ? ' + mod_php' : '');
	} elseif (preg_match('/nginx/i', $_SERVER['SERVER_SOFTWARE'])) {
		$return = 'Nginx';
		if (preg_match('/php-fpm/i', $phpinfo)) {
			$return	.= ' + PHP-FPM';
		} elseif (defined('HHVM_VERSION')) {
			$return	.= ' + HHVM';
		}
		return $return;
	} elseif (defined('HHVM_VERSION')) {
		return 'HipHop Virtual Machine';
	} else {
		return Language::instance()->indefinite;
	}
}
function apache_version () {
	preg_match(
		'/Apache[\-\/]([0-9\.\-]+)/',
		ob_wrapper(function () {
			phpinfo();
		}),
		$version
	);
	return $version[1];
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
