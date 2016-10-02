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

var_dump(RequireJS::get_paths());
?>
--EXPECTF--
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(21) "System/Page/requirejs"
  [1]=>
  array(2) {
    ["paths"]=>
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
array(11) {
  ["Disabled"]=>
  string(27) "/modules/Disabled/assets/js"
  ["disabled_alias1"]=>
  string(27) "/modules/Disabled/assets/js"
  ["disabled_alias2"]=>
  string(27) "/modules/Disabled/assets/js"
  ["System"]=>
  string(25) "/modules/System/assets/js"
  ["package-js"]=>
  string(36) "/bower_components/package-js/package"
  ["package-min-js"]=>
  string(44) "/bower_components/package-min-js/package.min"
  ["package-browser"]=>
  string(37) "/node_modules/package-browser/package"
  ["package-js-browser"]=>
  string(40) "/node_modules/package-js-browser/package"
  ["package-js-browser-min"]=>
  string(48) "/node_modules/package-js-browser-min/package.min"
  ["package-jspm-main"]=>
  string(44) "/node_modules/package-jspm-main/package.jspm"
  ["package-main"]=>
  string(34) "/node_modules/package-main/package"
}
