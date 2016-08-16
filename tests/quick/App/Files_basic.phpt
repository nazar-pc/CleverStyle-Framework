--FILE--
<?php
namespace cs\App\Router {
	// Stub for trait
	trait CLI {
		protected function print_cli_structure ($path) {
			var_dump("cs\\App\\Router\\CLI::print_cli_structure('$path') called");
		}
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';
	define('MODULES', __DIR__.'/modules');

	class App_test extends App {
		public static function test () {
			$Request = Request::instance_stub(
				[
					'method'         => 'GET',
					'cli_path'       => false,
					'api_path'       => false,
					'admin_path'     => false,
					'current_module' => 'Module_with_files_routing'
				]
			);
			Response::instance_stub(
				[],
				[
					'header' => function (...$arguments) {
						var_dump("cs\\Response::header() called with", $arguments);
					}
				]
			);

			var_dump('Regular page request');
			$Router                    = new self;
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->controller_path   = [
				'index',
				'level10',
				'level21',
				'level30'
			];
			$Router->files_router($Request);

			var_dump('Admin page request');
			$Request->admin_path       = true;
			$Router                    = new self;
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->controller_path   = [
				'index',
				'level10',
				'level21',
				'level30'
			];
			$Router->files_router($Request);

			var_dump('API request (GET, method exists)');
			$Request->admin_path       = false;
			$Request->api_path         = true;
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->files_router($Request);

			var_dump('API request (PUT, method does not exists)');
			$Request->method = 'PUT';
			try {
				$Router->files_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('API request (OPTIONS, method does not exists)');
			$Request->method = 'OPTIONS';
			try {
				$Router->files_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('CLI request (GET, method exists)');
			$Request->api_path         = false;
			$Request->cli_path         = true;
			$Request->method           = 'GET';
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->files_router($Request);

			var_dump('CLI request (PUT, method does not exists)');
			$Request->method = 'PUT';
			try {
				$Router->files_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('CLI request (CLI, method does not exists)');
			$Request->method = 'CLI';
			try {
				$Router->files_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('API request (PUT, method does not exists, but exists OPTIONS)');
			$Request->cli_path         = false;
			$Request->api_path         = true;
			$Request->method           = 'PUT';
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->controller_path   = [
				'index',
				'level11'
			];
			try {
				$Router->files_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('CLI request (PUT, method does not exists, but exists CLI)');
			$Request->api_path         = false;
			$Request->cli_path         = true;
			$Router->working_directory = $Router->get_working_directory($Request);
			try {
				$Router->files_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}
		}
	}
	App_test::test();
}
?>
--EXPECTF--
string(20) "Regular page request"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/level10.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/level10/level21.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/level10/level21/level30.php"
string(18) "Admin page request"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/admin/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/admin/level10.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/admin/level10/level21.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/admin/level10/level21/level30.php"
string(32) "API request (GET, method exists)"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/index.get.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/level10.get.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/level10/level21.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/level10/level21/level30.get.php"
string(41) "API request (PUT, method does not exists)"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/level10/level21.php"
string(33) "cs\Response::header() called with"
array(2) {
  [0]=>
  string(5) "Allow"
  [1]=>
  string(9) "GET, POST"
}
int(501)
string(45) "API request (OPTIONS, method does not exists)"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/api/level10/level21.php"
string(33) "cs\Response::header() called with"
array(2) {
  [0]=>
  string(5) "Allow"
  [1]=>
  string(9) "GET, POST"
}
string(32) "CLI request (GET, method exists)"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/index.get.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/level10.get.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/level10/level21.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/level10/level21/level30.get.php"
string(41) "CLI request (PUT, method does not exists)"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/level10/level21.php"
string(49) "cs\App\Router\CLI::print_cli_structure('') called"
int(501)
string(41) "CLI request (CLI, method does not exists)"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/index.php"
string(%d) "%s/tests/quick/App/modules/Module_with_files_routing/cli/level10/level21.php"
string(49) "cs\App\Router\CLI::print_cli_structure('') called"
string(61) "API request (PUT, method does not exists, but exists OPTIONS)"
string(82) "%s/tests/quick/App/modules/Module_with_files_routing/api/index.php"
string(92) "%s/tests/quick/App/modules/Module_with_files_routing/api/level11.options.php"
string(57) "CLI request (PUT, method does not exists, but exists CLI)"
string(82) "%s/tests/quick/App/modules/Module_with_files_routing/cli/index.php"
string(88) "%s/tests/quick/App/modules/Module_with_files_routing/cli/level11.cli.php"
