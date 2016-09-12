--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('DIR', make_tmp_dir());
define('MODULES', make_tmp_dir());
Config::instance_stub(
	[
		'core'       => [
			'url'            => [
				'http://cscms.travis'
			],
			'default_module' => Config::SYSTEM_MODULE,
			'language'       => 'English',
			'multilingual'   => 0
		],
		'components' => [
			'modules' => [
				'System'             => [
					'active' => Config\Module_Properties::ENABLED
				],
				'Enabled_module'     => [
					'active' => Config\Module_Properties::ENABLED
				],
				'Disabled_module'    => [
					'active' => Config\Module_Properties::DISABLED
				],
				'Uninstalled_module' => [
					'active' => Config\Module_Properties::UNINSTALLED
				]
			]
		]
	]
);
class Language_test extends Language {
	public $Enabled_module = 'Enabled_module_localized';
	protected function construct () {
	}
	function get_aliases () {
		return [
			'en'    => 'English',
			'en_gb' => 'English',
			'en_us' => 'English',
			'ru'    => 'Russian',
			'ru_ru' => 'Russian',
			'ru_ua' => 'Russian',
			'uk'    => 'Ukrainian',
			'uk_ua' => 'Ukrainian'
		];
	}
	function __get ($item) {
		return $item;
	}
}
Language::instance_replace(
	Language_test::instance()
);
Event::instance()->on(
	'System/Request/routing_replace/before',
	function ($data) {
		var_dump('System/Request/routing_replace/before event fired with', $data);
	}
);
Event::instance()->on(
	'System/Request/routing_replace/after',
	function ($data) {
		var_dump('System/Request/routing_replace/after event fired with', $data);
	}
);
$server  = [
	'HTTP_HOST'            => 'cscms.travis',
	'HTTP_ACCEPT_LANGUAGE' => 'en-us;q=0.5,en;q=0.3',
	'SERVER_NAME'          => 'cscms.travis',
	'SERVER_PROTOCOL'      => 'HTTP/1.1',
	'REQUEST_METHOD'       => 'GET',
	'QUERY_STRING'         => '',
	'REQUEST_URI'          => '/',
	'CONTENT_TYPE'         => 'text/html'
];
$Request = Request::instance();

var_dump('Home page');
$Request->init_server($server);
$Request->init_route();
var_dump(
	$Request->mirror_index,
	$Request->path_normalized,
	$Request->route,
	$Request->route_path,
	$Request->route_ids,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path,
	$Request->current_module,
	$Request->home_page
);

var_dump('Home page, language in URL');
$Request->init_server(['REQUEST_URI' => '/en'] + $server);
$Request->init_route();
var_dump($Request->path_normalized, $Request->route, $Request->current_module, $Request->home_page);

var_dump('Module page');
$Request->init_server(['REQUEST_URI' => '/Enabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->mirror_index,
	$Request->path_normalized,
	$Request->route,
	$Request->route_path,
	$Request->route_ids,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path,
	$Request->current_module,
	$Request->home_page
);

var_dump('Module page, language in URL');
$Request->init_server(['REQUEST_URI' => '/en/Enabled_module'] + $server);
$Request->init_route();
var_dump($Request->path_normalized, $Request->route, $Request->current_module, $Request->home_page);

var_dump('API page');
$Request->init_server(['REQUEST_URI' => '/api/Enabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->route,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('API page, language in URL');
$Request->init_server(['REQUEST_URI' => '/en/api/Enabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->route,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Admin page');
$Request->init_server(['REQUEST_URI' => '/admin/Enabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->route,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Admin page, language in URL');
$Request->init_server(['REQUEST_URI' => '/en/admin/Enabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->route,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('CLI');
$Request->init_server(['CLI' => true, 'REQUEST_URI' => '/cli/Enabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->route,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Localized module name');
$Request->init_server(['REQUEST_URI' => '/en/Enabled_module_localized'] + $server);
$Request->init_route();
var_dump($Request->path_normalized, $Request->current_module);

var_dump('Admin request without module specified');
$Request->init_server(['REQUEST_URI' => '/admin'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('API request without module specified');
$Request->init_server(['REQUEST_URI' => '/api'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('CLI request without module specified');
$Request->init_server(['CLI' => true, 'REQUEST_URI' => '/cli'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Request to regular page of disabled module');
$Request->init_server(['REQUEST_URI' => '/Disabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Request to admin page of disabled module');
$Request->init_server(['REQUEST_URI' => '/admin/Disabled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Request to regular page of uninstalled module');
$Request->init_server(['REQUEST_URI' => '/Uninstalled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Request to admin page of uninstalled module');
$Request->init_server(['REQUEST_URI' => '/admin/Uninstalled_module'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->api_path,
	$Request->admin_path,
	$Request->cli_path,
	$Request->regular_path
);

var_dump('Page with route');
$Request->init_server(['REQUEST_URI' => '/api/Enabled_module/path/subpath/10/15?page=3&count=10'] + $server);
$Request->init_route();
var_dump(
	$Request->path_normalized,
	$Request->current_module,
	$Request->route,
	$Request->route_path,
	$Request->route_ids,
	$Request->route(0),
	$Request->route_path(0),
	$Request->route_ids(0)
);

var_dump('Not allowed host');
$Request->init_server(['SERVER_NAME' => 'abc.xyz'] + $server);
try {
	$Request->init_route();
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}
var_dump($Request->mirror_index);

var_dump('Correct redirect');
Response::instance_stub(
	[
		'code' => 200
	],
	[
		'redirect' => function (...$arguments) {
			var_dump('Redirect called with', $arguments);
		}
	]
);
$Request->init_server(['REQUEST_URI' => '/redirect/http://google.com', 'HTTP_REFERER' => 'http://cscms.travis/Some_page'] + $server);
try {
	$Request->init_route();
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}

var_dump('Incorrect redirect (no header)');
Response::instance_stub(
	[
		'code' => 200
	],
	[
		'redirect' => function (...$arguments) {
			var_dump('Redirect called with', $arguments);
		}
	]
);
$Request->init_server(['REQUEST_URI' => '/redirect/http://google.com'] + $server);
try {
	$Request->init_route();
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}

var_dump('Incorrect redirect (with header)');
Response::instance_stub(
	[
		'code' => 200
	],
	[
		'redirect' => function (...$arguments) {
			var_dump('Redirect called with', $arguments);
		}
	]
);
$Request->init_server(['REQUEST_URI' => '/redirect/http://google.com', 'HTTP_REFERER' => 'http://abc.xyz/Some_page'] + $server);
try {
	$Request->init_route();
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}

var_dump('Incorrect redirect (with wrong subdomain in header)');
Response::instance_stub(
	[
		'code' => 200
	],
	[
		'redirect' => function (...$arguments) {
			var_dump('Redirect called with', $arguments);
		}
	]
);
$Request->init_server(['REQUEST_URI' => '/redirect/http://google.com', 'HTTP_REFERER' => 'http://cscms.travis.abc.xyz/Some_page'] + $server);
try {
	$Request->init_route();
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}

var_dump('Update route in System/Request/routing_replace/after without updating path and ids should cause path and ids re-calculation');
Event::instance()->once(
	'System/Request/routing_replace/after',
	function ($data) {
		$data['route'] = [
			'path',
			'1',
			'page',
			'2'
		];
	}
);
$Request->init_server(['REQUEST_URI' => '/path/1/page/2/part/3'] + $server);
$Request->init_route();
var_dump($Request->route_path, $Request->route_ids);
?>
--EXPECT--
string(9) "Home page"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(0) ""
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(true)
}
int(0)
string(6) "System"
array(0) {
}
array(0) {
}
array(0) {
}
bool(false)
bool(false)
bool(false)
bool(true)
string(6) "System"
bool(true)
string(26) "Home page, language in URL"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(2) "en"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(true)
}
string(6) "System"
array(0) {
}
string(6) "System"
bool(true)
string(11) "Module page"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(14) "Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
int(0)
string(14) "Enabled_module"
array(0) {
}
array(0) {
}
array(0) {
}
bool(false)
bool(false)
bool(false)
bool(true)
string(14) "Enabled_module"
bool(false)
string(28) "Module page, language in URL"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(17) "en/Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(14) "Enabled_module"
array(0) {
}
string(14) "Enabled_module"
bool(false)
string(8) "API page"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(18) "api/Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(4) "api/"
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(18) "api/Enabled_module"
array(0) {
}
string(14) "Enabled_module"
bool(true)
bool(false)
bool(false)
bool(false)
string(25) "API page, language in URL"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(21) "en/api/Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(4) "api/"
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(18) "api/Enabled_module"
array(0) {
}
string(14) "Enabled_module"
bool(true)
bool(false)
bool(false)
bool(false)
string(10) "Admin page"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(20) "admin/Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(6) "admin/"
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(20) "admin/Enabled_module"
array(0) {
}
string(14) "Enabled_module"
bool(false)
bool(true)
bool(false)
bool(false)
string(27) "Admin page, language in URL"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(23) "en/admin/Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(6) "admin/"
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(20) "admin/Enabled_module"
array(0) {
}
string(14) "Enabled_module"
bool(false)
bool(true)
bool(false)
bool(false)
string(3) "CLI"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(18) "cli/Enabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(4) "cli/"
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(18) "cli/Enabled_module"
array(0) {
}
string(14) "Enabled_module"
bool(false)
bool(false)
bool(true)
bool(false)
string(21) "Localized module name"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(27) "en/Enabled_module_localized"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(14) "Enabled_module"
string(14) "Enabled_module"
string(38) "Admin request without module specified"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(5) "admin"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(6) "admin/"
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(12) "admin/System"
string(6) "System"
bool(false)
bool(true)
bool(false)
bool(false)
string(36) "API request without module specified"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(3) "api"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(4) "api/"
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(10) "api/System"
string(6) "System"
bool(true)
bool(false)
bool(false)
bool(false)
string(36) "CLI request without module specified"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(3) "cli"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(4) "cli/"
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(10) "cli/System"
string(6) "System"
bool(false)
bool(false)
bool(true)
bool(false)
string(42) "Request to regular page of disabled module"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(15) "Disabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(15) "Disabled_module"
  ["route"]=>
  &array(1) {
    [0]=>
    string(15) "Disabled_module"
  }
  ["route_path"]=>
  &array(1) {
    [0]=>
    string(15) "Disabled_module"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(22) "System/Disabled_module"
string(6) "System"
bool(false)
bool(false)
bool(false)
bool(true)
string(40) "Request to admin page of disabled module"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(21) "admin/Disabled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(0) ""
  ["route"]=>
  &array(0) {
  }
  ["route_path"]=>
  &array(0) {
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(6) "admin/"
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(15) "Disabled_module"
  ["home_page"]=>
  &bool(false)
}
string(21) "admin/Disabled_module"
string(15) "Disabled_module"
bool(false)
bool(true)
bool(false)
bool(false)
string(45) "Request to regular page of uninstalled module"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(18) "Uninstalled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(18) "Uninstalled_module"
  ["route"]=>
  &array(1) {
    [0]=>
    string(18) "Uninstalled_module"
  }
  ["route_path"]=>
  &array(1) {
    [0]=>
    string(18) "Uninstalled_module"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(25) "System/Uninstalled_module"
string(6) "System"
bool(false)
bool(false)
bool(false)
bool(true)
string(43) "Request to admin page of uninstalled module"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(24) "admin/Uninstalled_module"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(18) "Uninstalled_module"
  ["route"]=>
  &array(1) {
    [0]=>
    string(18) "Uninstalled_module"
  }
  ["route_path"]=>
  &array(1) {
    [0]=>
    string(18) "Uninstalled_module"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(6) "admin/"
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(31) "admin/System/Uninstalled_module"
string(6) "System"
bool(false)
bool(true)
bool(false)
bool(false)
string(15) "Page with route"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(37) "api/Enabled_module/path/subpath/10/15"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(18) "path/subpath/10/15"
  ["route"]=>
  &array(4) {
    [0]=>
    string(4) "path"
    [1]=>
    string(7) "subpath"
    [2]=>
    string(2) "10"
    [3]=>
    string(2) "15"
  }
  ["route_path"]=>
  &array(2) {
    [0]=>
    string(4) "path"
    [1]=>
    string(7) "subpath"
  }
  ["route_ids"]=>
  &array(2) {
    [0]=>
    string(2) "10"
    [1]=>
    string(2) "15"
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(4) "api/"
  ["regular_path"]=>
  bool(false)
  ["current_module"]=>
  &string(14) "Enabled_module"
  ["home_page"]=>
  &bool(false)
}
string(37) "api/Enabled_module/path/subpath/10/15"
string(14) "Enabled_module"
array(4) {
  [0]=>
  string(4) "path"
  [1]=>
  string(7) "subpath"
  [2]=>
  string(2) "10"
  [3]=>
  string(2) "15"
}
array(2) {
  [0]=>
  string(4) "path"
  [1]=>
  string(7) "subpath"
}
array(2) {
  [0]=>
  string(2) "10"
  [1]=>
  string(2) "15"
}
string(4) "path"
string(4) "path"
string(2) "10"
string(16) "Not allowed host"
int(400)
string(26) "Mirror abc.xyz not allowed"
int(-1)
string(16) "Correct redirect"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
  ["route"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_path"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
string(20) "Redirect called with"
array(2) {
  [0]=>
  string(17) "http://google.com"
  [1]=>
  int(301)
}
int(200)
string(0) ""
string(30) "Incorrect redirect (no header)"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
  ["route"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_path"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
int(400)
string(0) ""
string(32) "Incorrect redirect (with header)"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
  ["route"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_path"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
int(400)
string(0) ""
string(51) "Incorrect redirect (with wrong subdomain in header)"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(26) "redirect/http://google.com"
  ["route"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_path"]=>
  &array(4) {
    [0]=>
    string(8) "redirect"
    [1]=>
    string(5) "http:"
    [2]=>
    string(0) ""
    [3]=>
    string(10) "google.com"
  }
  ["route_ids"]=>
  &array(0) {
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
int(400)
string(0) ""
string(123) "Update route in System/Request/routing_replace/after without updating path and ids should cause path and ids re-calculation"
string(54) "System/Request/routing_replace/before event fired with"
array(1) {
  ["rc"]=>
  &string(20) "path/1/page/2/part/3"
}
string(53) "System/Request/routing_replace/after event fired with"
array(10) {
  ["rc"]=>
  &string(20) "path/1/page/2/part/3"
  ["route"]=>
  &array(6) {
    [0]=>
    string(4) "path"
    [1]=>
    string(1) "1"
    [2]=>
    string(4) "page"
    [3]=>
    string(1) "2"
    [4]=>
    string(4) "part"
    [5]=>
    string(1) "3"
  }
  ["route_path"]=>
  &array(3) {
    [0]=>
    string(4) "path"
    [1]=>
    string(4) "page"
    [2]=>
    string(4) "part"
  }
  ["route_ids"]=>
  &array(3) {
    [0]=>
    string(1) "1"
    [1]=>
    string(1) "2"
    [2]=>
    string(1) "3"
  }
  ["cli_path"]=>
  &string(0) ""
  ["admin_path"]=>
  &string(0) ""
  ["api_path"]=>
  &string(0) ""
  ["regular_path"]=>
  bool(true)
  ["current_module"]=>
  &string(6) "System"
  ["home_page"]=>
  &bool(false)
}
array(2) {
  [0]=>
  string(4) "path"
  [1]=>
  string(4) "page"
}
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
}
