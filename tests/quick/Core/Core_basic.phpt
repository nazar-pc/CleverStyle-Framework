--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('DIR', make_tmp_dir());
/** @noinspection MkdirRaceConditionInspection */
@mkdir(DIR.'/config');
file_put_contents(
	DIR.'/config/main.json',
	/** @lang JSON */
	<<<JSON
{
	//Domain of main mirror
	"domain"			: "cscms.travis",
	//Base timezone
	"timezone"			: "UTC",
	//Settings of main DB
	"db_host"			: "127.0.0.1",
	"db_type"			: "MySQLi",
	"db_name"			: "cscms.travis.name",
	"db_user"			: "cscms.travis.user",
	"db_password"		: "cscms.travis.password",
	"db_prefix"			: "[prefix]",
	//Settings of main Storage
	"storage_driver"		: "cscms.travis.type",
	"storage_url"		: "cscms.travis.url",
	"storage_host"		: "cscms.travis.host",
	"storage_user"		: "cscms.travis.user",
	"storage_password"	: "cscms.travis.password",
	//Base language
	"language"			: "cscms.travis.language",
	//Cache driver
	"cache_driver"		: "cscms.travis.cache",
	//Settings of Memcached cache driver
	"memcache_host"		: "localhost",
	"memcache_port"		: "11211",
	//Any length
	"public_key"		: "cscms.travis.public_key"
}
JSON
);
file_put_contents(
	DIR.'/config/main.php',
	/** @lang PHP */
	<<<PHP
<?php
\$Core = cs\Core_test::instance();
\$Core->language = 'Alternative language';
\$Core->custom_property = 'Custom property';
\$Core->set('custom_property2', 'Custom property');
PHP
);

class Core_test extends Core {
	/**
	 * @return self
	 */
	static function test_init () {
		$Core = new self;
		self::instance_replace($Core);
		return $Core;
	}
	static function test () {
		$Core = self::test_init();
		var_dump('load config');
		var_dump($Core->load_config());

		$Core = self::test_init();
		var_dump('override config from PHP');
		$Core->construct();
		var_dump($Core->config);

		$Core = self::test_init();
		var_dump('override config from PHP (failed)');
		$Core->construct();
		$Core->language = 'Language changed after';
		var_dump($Core->config);

		var_dump('get existing property');
		var_dump($Core->language, $Core->get('language'));

		var_dump('get non-existing property');
		var_dump($Core->not_found, $Core->get('not_found'));
	}
}
Core_test::test();
?>
--EXPECT--
string(11) "load config"
array(18) {
  ["domain"]=>
  string(12) "cscms.travis"
  ["timezone"]=>
  string(3) "UTC"
  ["db_host"]=>
  string(9) "127.0.0.1"
  ["db_type"]=>
  string(6) "MySQLi"
  ["db_name"]=>
  string(17) "cscms.travis.name"
  ["db_user"]=>
  string(17) "cscms.travis.user"
  ["db_password"]=>
  string(21) "cscms.travis.password"
  ["db_prefix"]=>
  string(8) "[prefix]"
  ["storage_driver"]=>
  string(17) "cscms.travis.type"
  ["storage_url"]=>
  string(16) "cscms.travis.url"
  ["storage_host"]=>
  string(17) "cscms.travis.host"
  ["storage_user"]=>
  string(17) "cscms.travis.user"
  ["storage_password"]=>
  string(21) "cscms.travis.password"
  ["language"]=>
  string(21) "cscms.travis.language"
  ["cache_driver"]=>
  string(18) "cscms.travis.cache"
  ["memcache_host"]=>
  string(9) "localhost"
  ["memcache_port"]=>
  string(5) "11211"
  ["public_key"]=>
  string(23) "cscms.travis.public_key"
}
string(24) "override config from PHP"
array(20) {
  ["domain"]=>
  string(12) "cscms.travis"
  ["timezone"]=>
  string(3) "UTC"
  ["db_host"]=>
  string(9) "127.0.0.1"
  ["db_type"]=>
  string(6) "MySQLi"
  ["db_name"]=>
  string(17) "cscms.travis.name"
  ["db_user"]=>
  string(17) "cscms.travis.user"
  ["db_password"]=>
  string(21) "cscms.travis.password"
  ["db_prefix"]=>
  string(8) "[prefix]"
  ["storage_driver"]=>
  string(17) "cscms.travis.type"
  ["storage_url"]=>
  string(16) "cscms.travis.url"
  ["storage_host"]=>
  string(17) "cscms.travis.host"
  ["storage_user"]=>
  string(17) "cscms.travis.user"
  ["storage_password"]=>
  string(21) "cscms.travis.password"
  ["language"]=>
  string(20) "Alternative language"
  ["cache_driver"]=>
  string(18) "cscms.travis.cache"
  ["memcache_host"]=>
  string(9) "localhost"
  ["memcache_port"]=>
  string(5) "11211"
  ["public_key"]=>
  string(23) "cscms.travis.public_key"
  ["custom_property"]=>
  string(15) "Custom property"
  ["custom_property2"]=>
  string(15) "Custom property"
}
string(33) "override config from PHP (failed)"
array(20) {
  ["domain"]=>
  string(12) "cscms.travis"
  ["timezone"]=>
  string(3) "UTC"
  ["db_host"]=>
  string(9) "127.0.0.1"
  ["db_type"]=>
  string(6) "MySQLi"
  ["db_name"]=>
  string(17) "cscms.travis.name"
  ["db_user"]=>
  string(17) "cscms.travis.user"
  ["db_password"]=>
  string(21) "cscms.travis.password"
  ["db_prefix"]=>
  string(8) "[prefix]"
  ["storage_driver"]=>
  string(17) "cscms.travis.type"
  ["storage_url"]=>
  string(16) "cscms.travis.url"
  ["storage_host"]=>
  string(17) "cscms.travis.host"
  ["storage_user"]=>
  string(17) "cscms.travis.user"
  ["storage_password"]=>
  string(21) "cscms.travis.password"
  ["language"]=>
  string(20) "Alternative language"
  ["cache_driver"]=>
  string(18) "cscms.travis.cache"
  ["memcache_host"]=>
  string(9) "localhost"
  ["memcache_port"]=>
  string(5) "11211"
  ["public_key"]=>
  string(23) "cscms.travis.public_key"
  ["custom_property"]=>
  string(15) "Custom property"
  ["custom_property2"]=>
  string(15) "Custom property"
}
string(21) "get existing property"
string(20) "Alternative language"
string(20) "Alternative language"
string(25) "get non-existing property"
bool(false)
bool(false)
