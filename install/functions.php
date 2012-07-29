<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
function install_form () {
	$timezones = get_timezones_list();
	return h::section(
		h::nav(
			h::{'input[type=radio]'}([
				'name'		=> 'mode',
				'value'		=> ['regular', 'expert'],
				'in'		=> ['Regular user', 'Expert'],
				'onclick'	=> "var items = document.getElementsByClassName('expert');"
								."for (var i = 0; i < items.length; i++) {"
								."items.item(i).style.display = this.value == 'expert' ? 'table-row' : '';"
								."}"
			])
		).
		h::{'form[method=post][action=install.php]'}(
			h::table(
				h::{'tr td'}(
					'Site name:',
					h::{'input[name=site_name]'}()
				).
				h::{'tr.expert td'}(
					'Database engine:',
					h::{'select[name=db_engine][size=3][selected=MySQLi]'}(
						_mb_substr(get_files_list(DIR.'/core/engines/DB', '/^[^_].*?\.php$/i', 'f'), 0, -4)
					)
				).
				h::{'tr.expert td'}(
					'Database host:',
					h::{'input[name=db_host][value=localhost]'}()
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
					h::{'input[name=db_password]'}()
				).
				h::{'tr.expert td'}(
					'Database tables prefix:',
					h::{'input[name=db_prefix]'}([
						'value'	=> substr(md5(uniqid(microtime(true), true)), 0, 5).'_'
					])
				).
				h::{'tr.expert td'}(
					'Database charset:',
					h::{'input[name=db_charset][value=utf8]'}()
				).
				h::{'tr td'}(
					'Timezone:',
					h::{'select[name=timezone][size=7][selected=UTC]'}([
						'in'		=> array_keys($timezones),
						'value'		=> array_values($timezones)
					])
				).
				h::{'tr td'}(
					'Language:',
					h::{'select[name=language][size=3][selected=English]'}(
						array_merge(
							_mb_substr(get_files_list(DIR.'/core/languages', '/^lang\..*?\.php$/i', 'f'), 5, -4) ?: [],
							_mb_substr(get_files_list(DIR.'/core/languages', '/^lang\..*?\.json$/i', 'f'), 5, -5) ?: []
						)
					)
				).
				h::{'tr td'}(
					'Email of administrator:',
					h::{'input[type=email][name=admin_email]'}()
				).
				h::{'tr td'}(
					'Administrator password:',
					h::{'input[type=password][name=db_user]'}()
				)
			).
			h::{'button[type=submit]'}(
				'Install'
			)
		)
	);
}
function install_process () {
	$config						= _json_decode('{
  "name": "",
  "url": "",
  "keywords": "",
  "description": "",
  "admin_email": "",
  "admin_phone": "",
  "closed_title": "Site closed",
  "closed_text": "<p>Site closed for maintenance<\/p>",
  "site_mode": "1",
  "title_delimiter": "::",
  "title_reverse": "0",
  "debug": "0",
  "show_db_queries": "1",
  "show_cookies": "1",
  "show_objects_data": "1",
  "gzip_compression": "1",
  "cache_compress_js_css": "1",
  "theme": "CleverStyle",
  "allow_change_theme": "0",
  "themes": [
    "CleverStyle"
  ],
  "color_schemes": {
    "CleverStyle": [
      "Green (default)",
      "Green (strict)"
    ],
    "CleverStyle2": {
      "default": "Green (default)",
      "green_strict": "Green (strict)"
    }
  },
  "color_scheme": "Green (strict)",
  "language": "",
  "allow_change_language": "0",
  "multilingual": "0",
  "db_balance": "0",
  "maindb_for_write": "0",
  "active_themes": [
    "CleverStyle"
  ],
  "active_languages": [
    "English",
    "Русский"
  ],
  "cookie_domain": "",
  "cookie_path": "\/",
  "mirrors_url": [
    ""
  ],
  "mirrors_cookie_domain": [
    ""
  ],
  "mirrors_cookie_path": [
    ""
  ],
  "languages": [
    "English",
    "Русский"
  ],
  "inserts_limit": "1000",
  "key_expire": "120",
  "session_expire": "2592000",
  "update_ratio": "75",
  "login_attempts_block_count": "0",
  "login_attempts_block_time": "5",
  "cookie_prefix": "",
  "timezone": "",
  "password_min_length": "4",
  "password_min_strength": "0",
  "smtp": "0",
  "smtp_host": "",
  "smtp_port": "",
  "smtp_secure": "",
  "smtp_auth": "0",
  "smtp_user": "",
  "smtp_password": "",
  "mail_from_name": "",
  "allow_user_registration": "1",
  "require_registration_confirmation": "1",
  "autologin_after_registration": "1",
  "registration_confirmation_time": "1",
  "mail_signature": "",
  "mail_from": "",
  "rules": "<p>Site rules<\/p>",
  "show_tooltips": "1",
  "online_time": "300",
  "remember_user_ip": "0",
  "ip_black_list": [
    ""
  ],
  "ip_admin_list_only": "0",
  "ip_admin_list": [
    ""
  ],
  "on_error_globals_dump": "1",
  "simple_admin_mode": "0",
  "auto_translation": "0",
  "auto_translation_engine": {
    "name": "",
    "client_id": "",
    "client_secret": ""
  },
  "default_module": "System",
  "footer_text": "",
  "show_footer_info": "1"
}');
	$config['name']				= $config['description']	= (string)$_POST['site_name'];
	$config['keywords']			= implode(', ', _trim(explode(' ', $config['name']), ','));
	$config['url']				= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$config['url']				= substr(
		$config['url'],
		0,
		-strrpos($config['url'], '/')
	);
	$config['admin_email']		= $_POST['admin_email'];
	$config['cookie_domain']	= explode('/', explode('//', $config['url'])[1], 2);
	$config['cookie_path']		= isset($config['cookie_domain'][1]) && $config['cookie_domain'][1] ? '/'.trim($config['cookie_domain'][1], '/').'/' : '/';
	$config['cookie_domain']	= $config['cookie_domain'][0];
	$config['timezone']			= $_POST['timezone'];
	$config['mail_from_name']	= 'Administrator of '.$config['name'];
	$config['mail_from']		= $_POST['admin_email'];
}