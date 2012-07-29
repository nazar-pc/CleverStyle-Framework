<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\System\general\about_server;
use			\h;
global $L, $Core, $Index, $db, $PHP, $mcrypt, $Config;
global ${$Core->config('db_type')};
if (isset($Config->routing['current'][2]) && $Config->routing['current'][2] == 'phpinfo') {
	interface_off();
	ob_start();
	phpinfo();
	$Index->content(ob_get_clean());
}
$Index->form	= false;
function state ($state) {
	return ($state ? 'ui-state-highlight' : 'ui-state-error').' ui-corner-all';
};
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		[
			h::{'a.cs-button[target=_new]'}(
				'phpinfo()',
				[
					'href'	=> $Index->action.'/phpinfo'
				]
			).
			h::{'div#system_readme.cs-dialog'}(
				file_get_contents(DIR.'/readme.html'),
				[
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $L->system.' -> '.$L->information_about_system
				]
			).
			h::{'button#system_readme_open'}(
				$L->information_about_system,
				[
					'data-title'	=> $L->click_to_view_details
				]
			).
			h::{'pre#system_license.cs-dialog'}(
				file_get_contents(DIR.'/license.txt'),
				[
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $L->system.' -> '.$L->license
				]
			).
			h::{'button#system_license_open'}(
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
			$L->operation_system.':',
			php_uname('s').' '.php_uname('r').' '.php_uname('v')
		],
		[
			$L->server_type.':',
			server_api()
		],
		function_exists('apache_get_version') ? [
			$L->version.' Apache:',
			apache_get_version()
		] : false,
		[
			$L->available_ram.':',
			str_replace(
				['K', 'M', 'G'],
				[' '.$L->KB, ' '.$L->MB, ' '.$L->GB],
				ini_get('memory_limit')
			)
		],
		[
			$L->free_disk_space.':',
			format_filesize(disk_free_space('./'), 2)
		],
		[
			$L->version.' PHP:',
			[
				PHP_VERSION.(!check_php() ? ' ('.$L->required.' '.$PHP.' '.$L->or_higher.')' : ''),
				[
					'class' => state(check_php())
				]
			]
		],
		[
			$L->components.' PHP:',
			h::{'table.cs-left-odd.cs-php-components tr| td'}(
				[
					$L->mcrypt.':',
					[
						check_mcrypt() ? $L->on : $L->off.h::sup('(!)', ['title'	=> $L->mcrypt_warning]),
						[
							'class' => state(check_mcrypt())
						]
					]
				],
				check_mcrypt() ? [
					$L->version.' mcrypt:',
					[
						check_mcrypt().(!check_mcrypt(1) ? ' ('.$L->required.' '.$mcrypt.' '.$L->or_higher.')' : ''),
						[
							'class' => state(check_mcrypt(1))
						]
					]
				] : false,
				[
					$L->zlib.':',
					$L->get(zlib())
				],
				zlib() ? [
					$L->zlib_compression.':',
					$L->get(zlib_compression())
				] : false,
				zlib_compression() ? [
					$L->zlib_compression_level.':',
					zlib_compression_level()
				] : false,
				[
					$L->curl_lib.':',
					[
						$L->get(curl()),
						[
							'class' => state(curl())
						]
					]
				],
				[
					$L->apc_mod.':',
					[
						$L->get(apc()),
						[
							'class' => state(apc())
						]
					]
				]
			)
		],
		[
			$L->main_db.':',
			$Core->config('db_type')
		],
		[
			$L->properties.' '.$Core->config('db_type').':',
			h::{'table.cs-left-odd.cs-sql-properties tr| td'}(
				[
					$L->host.':',
					$Core->config('db_host')
				],
				[
					$L->version.' '.$Core->config('db_type').':',
					[
						$db->server().(check_db() ? '' : ' ('.$L->required.' '.${$Core->config('db_type')}.' '.$L->or_higher.')'),
						[
							'class' => state(check_db())
						]
					]
				],
				[
					$L->name_of_db.':',
					$Core->config('db_name')
				],
				[
					$L->prefix_for_db_tables.':',
					$Core->config('db_prefix')
				]
			)
		],
		[
			$L->main_storage.':',
			$Core->config('storage_type')
		],
		function_exists('apache_get_version') ? [
			$L->php_ini_settings.' "php.ini":',
			h::{'table.cs-left-odd.cs-php-ini-settings tr| td'}(
				[
					$L->allow_file_upload.':',
					[
						$L->get(ini_get('file_uploads')),
						[
							'class' => state(ini_get('file_uploads'))
						]
					]
				],
				[
					$L->max_file_uploads.':',
					ini_get('max_file_uploads')
				],
				[
					$L->upload_limit.':',
					str_replace(
						['K', 'M', 'G'],
						[' '.$L->KB, ' '.$L->MB, ' '.$L->GB],
						ini_get('upload_max_filesize')
					)
				],
				[
					$L->post_max_size.':',
					str_replace(
						['K', 'M', 'G'],
						[' '.$L->KB, ' '.$L->MB, ' '.$L->GB],
						ini_get('post_max_size')
					)
				],
				[
					$L->max_execution_time.':',
					format_time(ini_get('max_execution_time'))
				],
				[
					$L->max_input_time.':',
					format_time(ini_get('max_input_time'))
				],
				[
					$L->default_socket_timeout.':',
					format_time(ini_get('default_socket_timeout'))
				],
				[
					$L->module.' mod_rewrite:',
					[
						$L->get(
							$rewrite = function_exists('apache_get_modules') && in_array('mod_rewrite',apache_get_modules())
						),
						[
							 'class' => state($rewrite)
						]
					]
				],
				[
					$L->allow_url_fopen.':',
					[
						$L->get(ini_get('allow_url_fopen')),
						[
							'class' => state(ini_get('allow_url_fopen'))
						]
					]
				],
				[
					$L->display_errors.':',
					[
						$L->get(display_errors()),
						[
							'class' => state(!display_errors())
						]
					]
				]
			)
		] : false
	)
);