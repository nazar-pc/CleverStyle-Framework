--FILE--
<?php
namespace cli {
	// Hack for CLI output
	function posix_isatty () {
		return true;
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';
	define('MODULES', __DIR__.'/modules');

	class App_test extends App {
		public static function test () {
			Config::instance_stub(
				[
					'components' => [
						'modules' => [
							'Module_with_controller_routing' => [
								'active' => Config\Module_Properties::ENABLED
							],
							'Module_with_files_routing'      => [
								'active' => Config\Module_Properties::ENABLED
							],
							'Module_with_cli_index_php'      => [
								'active' => Config\Module_Properties::ENABLED
							]
						]
					]
				]
			);
			Page::instance_stub(
				[],
				[
					'content' => function ($content) {
						var_dump("cs\\Page::content('$content') called");
					}
				]
			);

			var_dump('Print CLI structure (all)');
			$Router = new self;
			$Router->print_cli_structure('');

			var_dump('Print CLI structure (controllers-based routing path)');
			$Router = new self;
			$Router->print_cli_structure('/cli/Module_with_controller_routing/level10');

			var_dump('Print CLI structure (files-based routing path)');
			$Router = new self;
			$Router->print_cli_structure('/cli/Module_with_files_routing/level10');
		}
	}
	App_test::test();
}
?>
--EXPECT--
string(25) "Print CLI structure (all)"
string(54) "cs\Page::content('%yAll paths and methods:%n
') called"
string(738) "cs\Page::content('+--------------------------------------------------------+-------------------+
| Path                                                   | Methods available |
+--------------------------------------------------------+-------------------+
| Module_with_controller_routing/level10/level21/level30 | get, post         |
| Module_with_controller_routing/level11                 | cli               |
| Module_with_files_routing/level10/level21/level30      | get, post         |
| Module_with_files_routing/level11                      | cli               |
| Module_with_cli_index_php                              | get               |
+--------------------------------------------------------+-------------------+
') called"
string(52) "Print CLI structure (controllers-based routing path)"
string(95) "cs\Page::content('%yPaths and methods for "Module_with_controller_routing/level10":%n
') called"
string(422) "cs\Page::content('+--------------------------------------------------------+-------------------+
| Path                                                   | Methods available |
+--------------------------------------------------------+-------------------+
| Module_with_controller_routing/level10/level21/level30 | get, post         |
+--------------------------------------------------------+-------------------+
') called"
string(46) "Print CLI structure (files-based routing path)"
string(90) "cs\Page::content('%yPaths and methods for "Module_with_files_routing/level10":%n
') called"
string(397) "cs\Page::content('+---------------------------------------------------+-------------------+
| Path                                              | Methods available |
+---------------------------------------------------+-------------------+
| Module_with_files_routing/level10/level21/level30 | get, post         |
+---------------------------------------------------+-------------------+
') called"
