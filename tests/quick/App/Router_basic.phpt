--FILE--
<?php
namespace cs\App\Router {
	// Stub for trait
	trait CLI {
		protected function print_cli_structure ($path) {
			var_dump("cs\\App\\Router\\CLI::print_cli_structure('$path') called");
		}
	}
	// Stub for trait
	trait Controller {
		protected function controller_router ($Request) {
			var_dump("cs\\App\\Router\\Controller::controller_router() called");
		}
	}
	// Stub for trait
	trait Files {
		protected function files_router ($Request) {
			var_dump("cs\\App\\Router\\Files::files_router() called");
		}
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';
	define('MODULES', __DIR__.'/modules');

	class App_test extends App {
		public static function test () {
			$event_return = true;
			Event::instance_stub(
				[],
				[
					'fire' => function (...$arguments) use (&$event_return) {
						var_dump('cs\Event::fire() called with', $arguments);
						return $event_return;
					}
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
			$Request = Request::instance_stub(
				[
					'method'         => 'GET',
					'cli_path'       => false,
					'api_path'       => false,
					'admin_path'     => false,
					'current_module' => 'Module_with_index_html',
					'route'          => [],
					'route_path'     => [],
					'route_ids'      => [],
					'path'           => '/'
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
			$is_admin           = false;
			$permission_allowed = true;
			User::instance_stub(
				[],
				[
					'admin'          => &$is_admin,
					'get_permission' => function (...$arguments) use (&$permission_allowed) {
						var_dump('cs\User::get_permission() called with', $arguments);
						return $permission_allowed;
					}
				]
			);

			$Router = new self;
			var_dump('Init router');
			$Router->init_router();
			var_dump($Router->controller_path, $Router->working_directory);

			var_dump('Execute router (index.html)');
			$Router->execute_router($Request);
			var_dump($Router->controller_path);

			var_dump('Execute router (with controller-based routing)');
			$Request->current_module = 'Module_with_controller_routing';
			$Request->route_path     = [];
			$Router->init_router();
			$Router->execute_router($Request);
			var_dump($Router->controller_path);

			var_dump('Execute router (with files-based routing)');
			$Request->current_module = 'Module_with_files_routing';
			$Request->route_path     = [];
			$Router->init_router();
			$Router->execute_router($Request);
			var_dump($Router->controller_path);

			var_dump('Stop router execution in event early');
			$event_return        = false;
			$Request->route_path = [];
			$Router->init_router();
			$Router->execute_router($Request);
			var_dump($Router->controller_path);

			var_dump('Getting working directory (admin)');
			$Request->current_module = 'Module_with_controller_routing';
			$Request->admin_path     = true;
			var_dump($Router->get_working_directory($Request));

			var_dump('Getting working directory (api)');
			$Request->admin_path = false;
			$Request->api_path   = true;
			var_dump($Router->get_working_directory($Request));

			var_dump('Getting working directory (cli)');
			$Request->api_path = false;
			$Request->cli_path = true;
			var_dump($Router->get_working_directory($Request));

			var_dump('Getting non-existing working directory (api)');
			$Request->current_module = 'Module_does_not_exists';
			$Request->cli_path       = false;
			$Request->api_path       = true;
			try {
				var_dump($Router->get_working_directory($Request));
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Getting non-existing working directory (cli)');
			$Request->api_path = false;
			$Request->cli_path = true;
			$Request->method   = 'CLI';
			var_dump($Router->get_working_directory($Request));

			var_dump('Empty index.json');
			$Request->cli_path       = false;
			$Request->current_module = 'Module_with_empty_index_json';
			$Request->route_path     = [];
			$Router->init_router();
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->check_and_normalize_route($Request);
			var_dump($Router->controller_path);

			var_dump('Expand API path (success)');
			$Request->current_module = 'Module_with_controller_routing';
			$Request->api_path       = true;
			$Router->init_router();
			$Router->working_directory = $Router->get_working_directory($Request);
			$Request->route_path       = [];
			$Router->check_and_normalize_route($Request);
			var_dump($Router->controller_path);

			var_dump('Expand API path (fail)');
			$Request->current_module = 'Module_with_controller_routing';
			$Request->route_path     = ['level10'];
			$Router->init_router();
			$Router->working_directory = $Router->get_working_directory($Request);
			try {
				$Router->check_and_normalize_route($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Non-existing path');
			$Request->current_module = 'Module_with_controller_routing';
			$Request->route_path     = ['non_existing'];
			$Router->init_router();
			$Router->working_directory = $Router->get_working_directory($Request);
			try {
				$Router->check_and_normalize_route($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Permission denied');
			$Request->current_module = 'Module_with_controller_routing';
			$Request->route_path     = [];
			$permission_allowed      = false;
			$Router->init_router();
			$Router->working_directory = $Router->get_working_directory($Request);
			try {
				$Router->check_and_normalize_route($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Handler not found (no methods found)');
			try {
				$Router->handler_not_found([], 'get', $Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Handler not found (CLI, non-CLI method)');
			$Request->api_path = false;
			$Request->cli_path = true;
			try {
				$Router->handler_not_found(['get', 'post', 'put'], 'get', $Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Handler not found (CLI, CLI method)');
			$Router->handler_not_found(['get', 'post', 'put'], 'cli', $Request);

			var_dump('Handler not found (non-CLI, non-OPTIONS method)');
			$Request->cli_path = false;
			$Request->api_path = true;
			try {
				$Router->handler_not_found(['get', 'post', 'put'], 'get', $Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Handler not found (non-CLI, OPTIONS method)');
			$Router->handler_not_found(['get', 'post', 'put'], 'options', $Request);
		}
	}
	App_test::test();
}
?>
--EXPECTF--
string(11) "Init router"
array(1) {
  [0]=>
  string(5) "index"
}
string(0) ""
string(27) "Execute router (index.html)"
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(32) "System/App/execute_router/before"
}
string(67) "cs\Page::content('index.html contents
With PHP interpreted') called"
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(31) "System/App/execute_router/after"
}
array(1) {
  [0]=>
  string(5) "index"
}
string(46) "Execute router (with controller-based routing)"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(30) "Module_with_controller_routing"
  [1]=>
  string(7) "level10"
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(30) "Module_with_controller_routing"
  [1]=>
  string(7) "level20"
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(30) "Module_with_controller_routing"
  [1]=>
  string(7) "level30"
}
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(32) "System/App/execute_router/before"
}
string(52) "cs\App\Router\Controller::controller_router() called"
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(31) "System/App/execute_router/after"
}
array(4) {
  [0]=>
  string(5) "index"
  [1]=>
  string(7) "level10"
  [2]=>
  string(7) "level20"
  [3]=>
  string(7) "level30"
}
string(41) "Execute router (with files-based routing)"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(25) "Module_with_files_routing"
  [1]=>
  string(7) "level10"
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(25) "Module_with_files_routing"
  [1]=>
  string(7) "level20"
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(25) "Module_with_files_routing"
  [1]=>
  string(7) "level30"
}
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(32) "System/App/execute_router/before"
}
string(42) "cs\App\Router\Files::files_router() called"
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(31) "System/App/execute_router/after"
}
array(4) {
  [0]=>
  string(5) "index"
  [1]=>
  string(7) "level10"
  [2]=>
  string(7) "level20"
  [3]=>
  string(7) "level30"
}
string(36) "Stop router execution in event early"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(25) "Module_with_files_routing"
  [1]=>
  string(7) "level10"
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(25) "Module_with_files_routing"
  [1]=>
  string(7) "level20"
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(25) "Module_with_files_routing"
  [1]=>
  string(7) "level30"
}
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(32) "System/App/execute_router/before"
}
array(4) {
  [0]=>
  string(5) "index"
  [1]=>
  string(7) "level10"
  [2]=>
  string(7) "level20"
  [3]=>
  string(7) "level30"
}
string(33) "Getting working directory (admin)"
string(%d) "%s/tests/quick/App/modules/Module_with_controller_routing/admin"
string(31) "Getting working directory (api)"
string(%d) "%s/tests/quick/App/modules/Module_with_controller_routing/api"
string(31) "Getting working directory (cli)"
string(%d) "%s/tests/quick/App/modules/Module_with_controller_routing/cli"
string(44) "Getting non-existing working directory (api)"
int(404)
string(44) "Getting non-existing working directory (cli)"
string(%d) "%s/tests/quick/App/modules/Module_does_not_exists/cli"
string(16) "Empty index.json"
array(1) {
  [0]=>
  string(5) "index"
}
string(25) "Expand API path (success)"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(34) "api/Module_with_controller_routing"
  [1]=>
  string(1) "_"
}
array(2) {
  [0]=>
  string(5) "index"
  [1]=>
  string(1) "_"
}
string(22) "Expand API path (fail)"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(34) "api/Module_with_controller_routing"
  [1]=>
  string(7) "level10"
}
int(404)
string(17) "Non-existing path"
int(404)
string(17) "Permission denied"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(34) "api/Module_with_controller_routing"
  [1]=>
  string(1) "_"
}
int(403)
string(36) "Handler not found (no methods found)"
int(404)
string(39) "Handler not found (CLI, non-CLI method)"
string(50) "cs\App\Router\CLI::print_cli_structure('/') called"
int(501)
string(35) "Handler not found (CLI, CLI method)"
string(50) "cs\App\Router\CLI::print_cli_structure('/') called"
string(47) "Handler not found (non-CLI, non-OPTIONS method)"
string(33) "cs\Response::header() called with"
array(2) {
  [0]=>
  string(5) "Allow"
  [1]=>
  string(14) "get, post, put"
}
int(501)
string(43) "Handler not found (non-CLI, OPTIONS method)"
string(33) "cs\Response::header() called with"
array(2) {
  [0]=>
  string(5) "Allow"
  [1]=>
  string(14) "get, post, put"
}
