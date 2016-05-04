<?php
/**
 * @package    CleverStyle CMS
 * @subpackage Installer
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;
use
	h;

function install_form () {
	$timezones = get_timezones_list();
	return h::{'form[method=post]'}(
		h::nav(
			h::{'radio[name=mode]'}(
				[
					'value'   => ['1', '0'],
					'in'      => [h::span('Regular user'), h::span('Expert')],
					'onclick' =>
						"var items = document.getElementsByClassName('expert');"
						."for (var i = 0; i < items.length; i++) {"
						."items.item(i).style.display = this.value == '0' ? 'table-row' : '';"
						."}"
				]
			)
		).
		h::table(
			h::{'tr td'}(
				'Site name:',
				h::{'input[name=site_name]'}()
			).
			h::{'tr.expert td'}(
				'Database engine:',
				h::{'select[name=db_engine][size=3][selected=MySQLi]'}(
					file_get_json(__DIR__.'/../db_engines.json')
				)
			).
			h::{'tr.expert td'}(
				'Database host:',
				h::{'input[name=db_host][value=localhost]'}(
					[
						'placeholder' => 'Relative or absolute path to DB for SQLite'
					]
				)
			).
			h::{'tr td'}(
				'Database name:',
				h::{'input[name=db_name]'}()
			).
			h::{'tr td'}(
				'Database user:',
				h::{'input[name=db_user]'}()
			).
			h::{'tr td'}(
				'Database user password:',
				h::{'input[type=password][name=db_password]'}()
			).
			h::{'tr.expert td'}(
				'Database tables prefix:',
				h::{'input[name=db_prefix]'}(
					[
						'value' => substr(md5(random_bytes(1000)), 0, 5).'_'
					]
				)
			).
			h::{'tr.expert td'}(
				'Database charset:',
				h::{'input[name=db_charset][value=utf8mb4]'}()
			).
			h::{'tr td'}(
				'Timezone:',
				h::{'select[name=timezone][size=7][selected=UTC]'}(
					[
						'in'    => array_keys($timezones),
						'value' => array_values($timezones)
					]
				)
			).
			h::{'tr td'}(
				'Language:',
				h::{'select[name=language][size=3][selected=English]'}(
					file_get_json(__DIR__.'/../languages.json')
				)
			).
			h::{'tr td'}(
				'Email of administrator:',
				h::{'input[type=email][name=admin_email]'}()
			).
			h::{'tr td'}(
				'Administrator password:',
				h::{'input[type=password][name=admin_password]'}()
			)
		).
		h::{'button.readme'}(
			'Readme',
			[
				'onclick' => "window.open('readme.html', 'readme', 'location=no')"
			]
		).
		h::{'button.license'}(
			'License',
			[
				'onclick' => "window.open('license.txt', 'license', 'location=no')"
			]
		).
		h::{'button[type=submit]'}(
			'Install'
		)
	);
}

/**
 * @return string
 */
function install_process () {
	if (isset($_POST['site_url'])) {
		$url = $_POST['site_url'];
	} else {
		$https  = @$_SERVER['HTTPS'] ? $_SERVER['HTTPS'] !== 'off' : (
			@$_SERVER['REQUEST_SCHEME'] === 'https' ||
			@$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
		);
		$scheme = $https ? 'https' : 'http';
		$host   = explode(':', $_SERVER['HTTP_HOST'])[0];
		$path   = explode('?', $_SERVER['REQUEST_URI'])[0] ?: '/';
		$url    = "$scheme://$host$path";
		$url    = implode('/', array_slice(explode('/', $url), 0, -2)); //Remove 2 last items
	}
	try {
		Installer::install(
			__DIR__.'/..',
			getcwd(),
			$_POST['site_name'],
			$url,
			$_POST['timezone'],
			$_POST['db_host'],
			$_POST['db_engine'],
			$_POST['db_name'],
			$_POST['db_user'],
			$_POST['db_password'],
			$_POST['db_prefix'],
			$_POST['db_charset'],
			$_POST['language'],
			$_POST['admin_email'],
			$_POST['admin_password'],
			!isset($_POST['mode']) || $_POST['mode'] ? 1 : 0
		);
	} catch (\Exception $e) {
		return $e->getMessage();
	}
	$admin_login = strstr($_POST['admin_email'], '@', true);
	$warning     = false;
	// Removing of installer file
	$installer = getcwd().'/'.basename(__DIR__.'/..');
	if (!is_writable($installer) || !unlink($installer)) {
		$warning = "Please, remove installer file $installer for security!\n";
	}
	return
		h::h3(
			'Congratulations! CleverStyle CMS has been installed successfully!'
		).
		h::{'table tr| td'}(
			[
				'Your sign in information:',
				[
					'colspan' => 2
				]
			],
			[
				'Login:',
				$admin_login
			],
			[
				'Password:',
				$_POST['admin_password']
			]
		).
		h::p(
			$warning,
			[
				'style' => 'color: red;'
			]
		).
		h::button(
			'Go to website',
			[
				'onclick' => "location.href = '/';"
			]
		);
}

if (count(explode('/', $_SERVER['REQUEST_URI'])) > 3) {
	echo 'Installation into subdirectory is not supported!';
	return;
}

header('Content-Type: text/html; charset=utf-8');
header('Connection: close');
$version = file_get_json(__DIR__.'/../meta.json')['version'];
echo
	"<!doctype html>\n".
	h::title("CleverStyle CMS $version Installation").
	h::meta(
		[
			'charset' => 'utf-8'
		]
	).
	h::style(file_get_contents(__DIR__.'/../install/style.css')).
	h::header(
		h::img(
			[
				'src' => 'data:image/png;charset=utf-8;base64,'.base64_encode(file_get_contents(__DIR__.'/../install/logo.png'))
			]
		).
		h::h1("CleverStyle CMS $version Installation")
	).
	h::section(
		isset($_POST['site_name']) ? install_process() : install_form()
	).
	h::footer(
		'Copyright (c) 2011-2016, Nazar Mokrynskyi'
	);
