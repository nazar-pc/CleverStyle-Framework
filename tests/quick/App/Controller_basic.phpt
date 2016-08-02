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
namespace cs\custom\modules\Module_with_controller_routing_custom {
	class Controller {
		public static function index () {
			return __METHOD__;
		}
		public static function level10 () {
			var_dump(__METHOD__);
		}
		public static function level10_level21 () {
			var_dump(__METHOD__);
		}
		public static function level10_level21_level30 () {
			var_dump(__METHOD__);
		}
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';
	define('MODULES', __DIR__.'/modules');

	class App_test extends App {
		public static function test () {
			Page::instance_stub(
				[],
				[
					'content' => function ($content) {
						var_dump("cs\\Page::content('$content') called");
					},
					'json'    => function ($content) {
						var_dump("cs\\Page::json() called with", $content);
					}
				]
			);
			$Request = Request::instance_stub(
				[
					'method'         => 'GET',
					'cli_path'       => false,
					'api_path'       => false,
					'admin_path'     => false,
					'current_module' => 'Module_with_controller_routing'
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
			$Router->controller_router($Request);

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
			$Router->controller_router($Request);

			var_dump('API request (GET, method exists)');
			$Request->admin_path       = false;
			$Request->api_path         = true;
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->controller_router($Request);

			var_dump('API request (PUT, method does not exists)');
			$Request->method = 'PUT';
			try {
				$Router->controller_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('API request (OPTIONS, method does not exists)');
			$Request->method = 'OPTIONS';
			try {
				$Router->controller_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('CLI request (GET, method exists)');
			$Request->api_path         = false;
			$Request->cli_path         = true;
			$Request->method           = 'GET';
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->controller_router($Request);

			var_dump('CLI request (PUT, method does not exists)');
			$Request->method = 'PUT';
			try {
				$Router->controller_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('CLI request (CLI, method does not exists)');
			$Request->method = 'CLI';
			try {
				$Router->controller_router($Request);
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Regular request, custom namespace');
			$Request->method           = 'GET';
			$Request->cli_path         = false;
			$Request->current_module   = 'Module_with_controller_routing_custom';
			$Router->working_directory = $Router->get_working_directory($Request);
			$Router->controller_router($Request);
		}
	}
	App_test::test();
}
?>
--EXPECTF--
string(20) "Regular page request"
string(86) "cs\Page::content('cs\modules\Module_with_controller_routing\Controller::index') called"
string(61) "cs\modules\Module_with_controller_routing\Controller::level10"
string(69) "cs\modules\Module_with_controller_routing\Controller::level10_level21"
string(77) "cs\modules\Module_with_controller_routing\Controller::level10_level21_level30"
string(18) "Admin page request"
string(92) "cs\Page::content('cs\modules\Module_with_controller_routing\admin\Controller::index') called"
string(67) "cs\modules\Module_with_controller_routing\admin\Controller::level10"
string(75) "cs\modules\Module_with_controller_routing\admin\Controller::level10_level21"
string(83) "cs\modules\Module_with_controller_routing\admin\Controller::level10_level21_level30"
string(32) "API request (GET, method exists)"
string(27) "cs\Page::json() called with"
string(63) "cs\modules\Module_with_controller_routing\api\Controller::index"
string(67) "cs\modules\Module_with_controller_routing\api\Controller::index_get"
string(69) "cs\modules\Module_with_controller_routing\api\Controller::level10_get"
string(73) "cs\modules\Module_with_controller_routing\api\Controller::level10_level21"
string(85) "cs\modules\Module_with_controller_routing\api\Controller::level10_level21_level30_get"
string(41) "API request (PUT, method does not exists)"
string(27) "cs\Page::json() called with"
string(63) "cs\modules\Module_with_controller_routing\api\Controller::index"
string(73) "cs\modules\Module_with_controller_routing\api\Controller::level10_level21"
string(33) "cs\Response::header() called with"
array(2) {
  [0]=>
  string(5) "Allow"
  [1]=>
  string(9) "GET, POST"
}
int(501)
string(45) "API request (OPTIONS, method does not exists)"
string(27) "cs\Page::json() called with"
string(63) "cs\modules\Module_with_controller_routing\api\Controller::index"
string(73) "cs\modules\Module_with_controller_routing\api\Controller::level10_level21"
string(33) "cs\Response::header() called with"
array(2) {
  [0]=>
  string(5) "Allow"
  [1]=>
  string(9) "GET, POST"
}
string(32) "CLI request (GET, method exists)"
string(90) "cs\Page::content('cs\modules\Module_with_controller_routing\cli\Controller::index') called"
string(67) "cs\modules\Module_with_controller_routing\cli\Controller::index_get"
string(69) "cs\modules\Module_with_controller_routing\cli\Controller::level10_get"
string(73) "cs\modules\Module_with_controller_routing\cli\Controller::level10_level21"
string(85) "cs\modules\Module_with_controller_routing\cli\Controller::level10_level21_level30_get"
string(41) "CLI request (PUT, method does not exists)"
string(90) "cs\Page::content('cs\modules\Module_with_controller_routing\cli\Controller::index') called"
string(73) "cs\modules\Module_with_controller_routing\cli\Controller::level10_level21"
string(49) "cs\App\Router\CLI::print_cli_structure('') called"
int(501)
string(41) "CLI request (CLI, method does not exists)"
string(90) "cs\Page::content('cs\modules\Module_with_controller_routing\cli\Controller::index') called"
string(73) "cs\modules\Module_with_controller_routing\cli\Controller::level10_level21"
string(49) "cs\App\Router\CLI::print_cli_structure('') called"
string(33) "Regular request, custom namespace"
string(100) "cs\Page::content('cs\custom\modules\Module_with_controller_routing_custom\Controller::index') called"
string(75) "cs\custom\modules\Module_with_controller_routing_custom\Controller::level10"
string(83) "cs\custom\modules\Module_with_controller_routing_custom\Controller::level10_level21"
string(91) "cs\custom\modules\Module_with_controller_routing_custom\Controller::level10_level21_level30"
