--FILE--
<?php
namespace cs;
use
	cs\Page\Assets\RequireJS;

require_once __DIR__.'/../../functions.php';
define('DIR', __DIR__.'/RequireJS');
define('MODULES', __DIR__.'/RequireJS/modules');
include __DIR__.'/../../unit.php';
Config::instance_stub(
	[
		'components' => [
			'modules' => [
				'Disabled'    => [
					'active' => Config\Module_Properties::DISABLED
				],
				'Enabled'     => [
					'active' => Config\Module_Properties::ENABLED
				],
				'System'      => [
					'active' => Config\Module_Properties::ENABLED
				],
				'Uninstalled' => [
					'active' => Config\Module_Properties::UNINSTALLED
				]
			]
		]
	]
);
Event::instance_stub(
	[],
	[
		'fire' => function (...$arguments) {
			var_dump('cs\Event::fire() called with', $arguments);
			return true;
		}
	]
);

var_dump(RequireJS::get_config());
?>
--EXPECTF--
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(21) "System/Page/requirejs"
  [1]=>
  array(3) {
    ["paths"]=>
    &array(0) {
    }
    ["packages"]=>
    &array(0) {
    }
    ["directories_to_browse"]=>
    &array(2) {
      [0]=>
      string(%d) "%s/tests/quick/Page/RequireJS/bower_components"
      [1]=>
      string(%d) "%s/tests/quick/Page/RequireJS/node_modules"
    }
  }
}
array(2) {
  ["paths"]=>
  array(4) {
    ["Disabled"]=>
    string(27) "/modules/Disabled/assets/js"
    ["disabled_alias1"]=>
    string(27) "/modules/Disabled/assets/js"
    ["disabled_alias2"]=>
    string(27) "/modules/Disabled/assets/js"
    ["System"]=>
    string(25) "/modules/System/assets/js"
  }
  ["packages"]=>
  array(7) {
    [0]=>
    array(3) {
      ["name"]=>
      string(10) "package-js"
      ["main"]=>
      string(7) "package"
      ["location"]=>
      string(28) "/bower_components/package-js"
    }
    [1]=>
    array(3) {
      ["name"]=>
      string(14) "package-min-js"
      ["main"]=>
      string(11) "package.min"
      ["location"]=>
      string(32) "/bower_components/package-min-js"
    }
    [2]=>
    array(3) {
      ["name"]=>
      string(15) "package-browser"
      ["main"]=>
      string(7) "package"
      ["location"]=>
      string(29) "/node_modules/package-browser"
    }
    [3]=>
    array(3) {
      ["name"]=>
      string(18) "package-js-browser"
      ["main"]=>
      string(7) "package"
      ["location"]=>
      string(32) "/node_modules/package-js-browser"
    }
    [4]=>
    array(3) {
      ["name"]=>
      string(22) "package-js-browser-min"
      ["main"]=>
      string(11) "package.min"
      ["location"]=>
      string(36) "/node_modules/package-js-browser-min"
    }
    [5]=>
    array(3) {
      ["name"]=>
      string(17) "package-jspm-main"
      ["main"]=>
      string(12) "package.jspm"
      ["location"]=>
      string(31) "/node_modules/package-jspm-main"
    }
    [6]=>
    array(3) {
      ["name"]=>
      string(12) "package-main"
      ["main"]=>
      string(7) "package"
      ["location"]=>
      string(26) "/node_modules/package-main"
    }
  }
}
