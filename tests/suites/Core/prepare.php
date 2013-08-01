<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	Tester
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
require DIR.'/core/traits/Singleton.php';
require DIR.'/core/classes/Core.php';
define('CONFIG', TEMP);
file_put_contents(CONFIG.'/main.json', '{
//Domain of main mirror
	"domain"			: "example.com",
//Base timezone
	"timezone"			: "UTC",
//Settings of main DB
	"db_host"			: "localhost",
	"db_type"			: "MySQLi",
	"db_name"			: "CleverStyle_db",
	"db_user"			: "CleverStyle_user",
	"db_password"		: "CleverStyle_password",
	"db_prefix"			: "CleverStyle_prefix",
	"db_charset"		: "utf8",
//Settings of main Storage
	"storage_type"		: "Local",
	"storage_url"		: "",
	"storage_host"		: "localhost",
	"storage_user"		: "",
	"storage_password"	: "",
//Base language
	"language"			: "Русский",
//Cache engine
	"cache_engine"		: "FileSystem",
//Settings of Memcached cache engine
	"memcache_host"		: "127.0.0.1",
	"memcache_port"		: "11211",
//Cache size in MB for FileSystem storage engine
	"cache_size"		: "5",
//Will be truncated to 56 symbols
	"key"				: "11111111111111111111111111111111111111111111111111111111",
//Will be truncated to 8 symbols
	"iv"				: "22222222",
//Any length
	"public_key"		: "33333333333333333333333333333333333333333333333333333333"
}');
define('STORAGE', TEMP.'/storage');
define('CACHE', TEMP.'/cache');
define('PCACHE', TEMP.'/pcache');
define('LOGS', TEMP.'/logs');