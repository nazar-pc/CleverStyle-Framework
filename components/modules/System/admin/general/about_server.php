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
use			h;
global $L, $Core, $Index, $db, $PHP, $mcrypt, $Config;
global ${$Core->db_type};
if (isset($Config->route[2]) && $Config->route[2] == 'phpinfo') {
	interface_off();
	ob_start();
	phpinfo();
	$Index->content(ob_get_clean());
	$Index->stop;
	return;
}
$Index->form	= false;
function state ($state) {
	return ($state ? 'ui-state-highlight' : 'ui-state-error').' ui-corner-all';
};
$Index->content(
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		[
			h::{'a.cs-button[target=_blank]'}(
				'phpinfo()',
				[
					'href'	=> $Index->action.'/phpinfo'
				]
			).
			h::{'a.cs-button[target=_blank]'}(
				$L->information_about_system,
				[
				'href'	=> 'readme.html'
				]
			).
			h::{'pre#system_license'}(
				file_get_contents(DIR.'/license.txt'),
				[
					'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
					'title'			=> $L->system.' » '.$L->license
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
			h::{'table.cs-left-odd.cs-fullwidth-table tr| td'}(
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
					$L->apc_module.':',
					[
						$L->get(apc()),
						[
							'class' => state(apc())
						]
					]
				],
				[
					$L->memcached_module.':',
					[
						$L->get(memcached()),
						[
							'class' => state(memcached())
						]
					]
				]
			)
		],
		[
			$L->main_db.':',
			$Core->db_type
		],
		[
			$L->properties.' '.$Core->db_type.':',
			h::{'table.cs-left-odd.cs-fullwidth-table tr| td'}(
				[
					$L->host.':',
					$Core->db_host
				],
				[
					$L->version.' '.$Core->db_type.':',
					[
						$db->server().(check_db() ? '' : ' ('.$L->required.' '.${$Core->db_type}.' '.$L->or_higher.')'),
						[
							'class' => state(check_db())
						]
					]
				],
				[
					$L->name_of_db.':',
					$Core->db_name
				],
				[
					$L->prefix_for_db_tables.':',
					$Core->db_prefix
				]
			)
		],
		[
			$L->main_storage.':',
			$Core->storage_type
		],
		[
			$L->cache_engine.':',
			$Core->cache_engine
		],
		function_exists('apache_get_version') ? [
			$L->php_ini_settings.':',
			h::{'table.cs-left-odd.cs-fullwidth-table tr| td'}(
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