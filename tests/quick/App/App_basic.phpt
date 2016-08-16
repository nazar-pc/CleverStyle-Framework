--FILE--
<?php
namespace cs\App {
	// Stub for trait
	trait Router {
		protected $controller_path = ['x', 'y', 'z'];
		protected function init_router () {
			var_dump('cs\App\Router::init_router() called');
		}
		protected function execute_router ($Request) {
			var_dump('cs\App\Router::execute_router() called');
		}
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';
	define('BLOCKS', __DIR__.'/blocks');
	function get_core_ml_text ($item) {
		return $item;
	}

	class App_test extends App {
		public static function test () {
			$Config = Config::instance_stub(
				[
					'components' => [
						'blocks' => []
					],
					'core'       => [
						'site_mode' => 1
					]
				],
				[
					'module' => function () {
						return False_class::instance();
					}
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
			Language::instance_stub(
				[
					'system_admin_administration' => 'Administration',
					'system_home'                 => 'Home',
					'System'                      => 'System'
				]
			);
			$Page    = Page::instance_stub(
				[
					'interface' => true
				],
				[
					'title'   => function ($title) {
						var_dump("cs\\Page::title('$title') called");
					},
					'warning' => function ($text) {
						var_dump("cs\\Page::warning('$text') called");
					},
					'replace' => function (...$arguments) {
						var_dump("cs\\Page::replace() called with", $arguments);
					}
				]
			);
			$Request = Request::instance_stub(
				[
					'method'         => 'GET',
					'cli_path'       => false,
					'api_path'       => false,
					'admin_path'     => false,
					'current_module' => 'System',
					'route'          => [],
					'home_page'      => true
				]
			);
			Text::instance_stub(
				[],
				[
					'process' => function ($database, $data) {
						return $data;
					}
				]
			);
			$is_admin           = false;
			$permission_allowed = true;
			$User               = User::instance_stub(
				[],
				[
					'admin'          => &$is_admin,
					'get_permission' => function (...$arguments) use (&$permission_allowed) {
						var_dump('cs\User::get_permission() called with', $arguments);
						return $permission_allowed;
					}
				]
			);

			var_dump('Home page execution');
			self::instance_reset();
			self::instance()->execute();

			var_dump('Home page execution (bad request method)');
			$Request->method = 'x y z';
			try {
				self::instance_reset();
				self::instance()->execute();
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Home page execution (closed site)');
			$Request->method           = 'GET';
			$Config->core['site_mode'] = 0;
			try {
				self::instance_reset();
				self::instance()->execute();
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('Home page execution (closed site, admin)');
			$is_admin = true;
			self::instance_reset();
			self::instance()->execute();

			var_dump('Home page execution (permission denied)');
			$permission_allowed = false;
			try {
				self::instance_reset();
				self::instance()->execute();
			} catch (ExitException $e) {
				var_dump($e->getCode());
			}

			var_dump('CLI pages permissions ignored');
			$App               = new self;
			$Request->cli_path = true;
			var_dump($App->check_permission($Request, 'index'));

			var_dump('Administration page uses different permissions group');
			$permission_allowed  = true;
			$Request->cli_path   = false;
			$Request->admin_path = true;
			var_dump($App->check_permission($Request, 'index'));

			var_dump('API page uses different permissions group');
			$Request->admin_path = false;
			$Request->api_path   = true;
			var_dump($App->check_permission($Request, 'index'));

			var_dump('Closed site request allowed for sign in endpoint');
			$is_admin        = false;
			$Request->route  = ['profile'];
			$Request->method = 'SIGN_IN';
			var_dump($App->allow_closed_site_request($Request));

			var_dump('Render CLI, API or page without interface');
			$App->render($Request);
			$Request->api_path = false;
			$Request->cli_path = true;
			$App->render($Request);
			$Request->cli_path = false;
			$Page->interface   = false;
			$App->render($Request);

			var_dump('Admin page should render title');
			$Request->admin_path = true;
			$Request->home_page  = false;
			$Page->interface     = true;
			$App->render($Request);

			var_dump('Getting $App->controller_path property should work');
			var_dump($App->__get('controller_path'));

			var_dump('Other properties not allowed');
			var_dump($App->__get('working_directory'));

			var_dump('Render blocks');
			$Config->components['blocks'] = [
				[
					'title'    => 'HTML block title',
					'type'     => 'html',
					'active'   => 1,
					'start'    => 0,
					'expire'   => 0,
					'content'  => 'HTML block content',
					'position' => 'left',
					'index'    => intval(substr(round(microtime(true) * 1000), 3), 10)
				],
				[
					'title'    => 'Raw HTML block title',
					'type'     => 'raw_html',
					'active'   => 1,
					'start'    => 0,
					'expire'   => 0,
					'content'  => 'Raw HTML block content',
					'position' => 'right',
					'index'    => intval(substr(round(microtime(true) * 1000), 3), 10)
				],
				[
					'title'    => 'Custom block title',
					'type'     => 'Custom_block',
					'active'   => 1,
					'start'    => 0,
					'expire'   => 0,
					'content'  => '',
					'position' => 'floating',
					'index'    => intval(substr(round(microtime(true) * 1000), 3), 10)
				],
				[
					'title'    => 'Block expired',
					'type'     => 'html',
					'active'   => 1,
					'start'    => 0,
					'expire'   => time() - 1,
					'content'  => '',
					'position' => 'left',
					'index'    => intval(substr(round(microtime(true) * 1000), 3), 10)
				],
				[
					'title'    => 'Block not started',
					'type'     => 'html',
					'active'   => 1,
					'start'    => time() + 99,
					'expire'   => 0,
					'content'  => '',
					'position' => 'left',
					'index'    => intval(substr(round(microtime(true) * 1000), 3), 10)
				]
			];
			$Page->Top                    = '';
			$Page->Left                   = '';
			$Page->Right                  = '';
			$Page->Bottom                 = '';
			$App->render_blocks($Page);
			var_dump($Page->Top, $Page->Left, $Page->Right, $Page->Bottom);
		}
	}
	App_test::test();
}
?>
--EXPECTF--
string(19) "Home page execution"
string(35) "cs\App\Router::init_router() called"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(6) "System"
  [1]=>
  string(5) "index"
}
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(24) "System/App/render/before"
}
string(29) "cs\Page::title('Home') called"
string(38) "cs\App\Router::execute_router() called"
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(23) "System/App/render/after"
}
string(40) "Home page execution (bad request method)"
string(35) "cs\App\Router::init_router() called"
int(400)
string(33) "Home page execution (closed site)"
string(35) "cs\App\Router::init_router() called"
int(503)
string(40) "Home page execution (closed site, admin)"
string(35) "cs\App\Router::init_router() called"
string(39) "cs\Page::warning('closed_title') called"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(6) "System"
  [1]=>
  string(5) "index"
}
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(24) "System/App/render/before"
}
string(29) "cs\Page::title('Home') called"
string(38) "cs\App\Router::execute_router() called"
string(28) "cs\Event::fire() called with"
array(1) {
  [0]=>
  string(23) "System/App/render/after"
}
string(39) "Home page execution (permission denied)"
string(35) "cs\App\Router::init_router() called"
string(39) "cs\Page::warning('closed_title') called"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(6) "System"
  [1]=>
  string(5) "index"
}
int(403)
string(29) "CLI pages permissions ignored"
bool(true)
string(52) "Administration page uses different permissions group"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(12) "admin/System"
  [1]=>
  string(5) "index"
}
bool(true)
string(41) "API page uses different permissions group"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(10) "api/System"
  [1]=>
  string(5) "index"
}
bool(true)
string(48) "Closed site request allowed for sign in endpoint"
bool(true)
string(41) "Render CLI, API or page without interface"
string(38) "cs\App\Router::execute_router() called"
string(38) "cs\App\Router::execute_router() called"
string(38) "cs\App\Router::execute_router() called"
string(30) "Admin page should render title"
string(39) "cs\Page::title('Administration') called"
string(31) "cs\Page::title('System') called"
string(38) "cs\App\Router::execute_router() called"
string(50) "Getting $App->controller_path property should work"
array(3) {
  [0]=>
  string(1) "x"
  [1]=>
  string(1) "y"
  [2]=>
  string(1) "z"
}
string(28) "Other properties not allowed"
bool(false)
string(13) "Render blocks"
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(5) "Block"
  [1]=>
  int(%d)
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(23) "System/App/block_render"
  [1]=>
  array(2) {
    ["index"]=>
    int(%d)
    ["blocks_array"]=>
    &array(4) {
      ["top"]=>
      string(0) ""
      ["left"]=>
      string(0) ""
      ["right"]=>
      string(0) ""
      ["bottom"]=>
      string(0) ""
    }
  }
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(5) "Block"
  [1]=>
  int(%d)
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(23) "System/App/block_render"
  [1]=>
  array(2) {
    ["index"]=>
    int(%d)
    ["blocks_array"]=>
    &array(4) {
      ["top"]=>
      string(0) ""
      ["left"]=>
      string(18) "HTML block content"
      ["right"]=>
      string(0) ""
      ["bottom"]=>
      string(0) ""
    }
  }
}
string(37) "cs\User::get_permission() called with"
array(2) {
  [0]=>
  string(5) "Block"
  [1]=>
  int(%d)
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(23) "System/App/block_render"
  [1]=>
  array(2) {
    ["index"]=>
    int(%d)
    ["blocks_array"]=>
    &array(4) {
      ["top"]=>
      string(0) ""
      ["left"]=>
      string(18) "HTML block content"
      ["right"]=>
      string(22) "Raw HTML block content"
      ["bottom"]=>
      string(0) ""
    }
  }
}
string(30) "cs\Page::replace() called with"
array(2) {
  [0]=>
  string(%d) "<!--block#%d-->"
  [1]=>
  string(46) "<h1>Custom block title</h1>
Some content here
"
}
string(0) ""
string(18) "HTML block content"
string(22) "Raw HTML block content"
string(0) ""
