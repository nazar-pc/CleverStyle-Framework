<?php
/**
 * @package CleverStyle Framework
 * @author  Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license 0BSD
 */
namespace cs;
use
	cs\App\Router;

/**
 * Provides next events:
 *  System/App/block_render
 *  [
 *      'index'        => $index,        //Block index
 *      'blocks_array' => &$blocks_array //Reference to array in form ['top' => '', 'left' => '', 'right' => '', 'bottom' => '']
 *  ]
 *
 *  System/App/render/before
 *
 *  System/App/execute_router/before
 *
 *  System/App/execute_router/after
 *
 *  System/App/render/after
 *
 * @property string[] $controller_path Path that will be used by controller to render page
 *
 * @method static $this instance($check = false)
 */
class App {
	use
		Singleton,
		Router;
	const INIT_STATE_METHOD = 'init';
	protected function init () {
		$this->init_router();
	}
	/**
	 * Executes blocks and module page generation
	 *
	 * @throws ExitException
	 */
	public function execute () {
		$Config  = Config::instance();
		$Request = Request::instance();
		if (!preg_match('/^[0-9a-z_]+$/i', $Request->method)) {
			throw new ExitException(400);
		}
		$this->handle_closed_site($Config, $Request);
		if (!$this->check_permission($Request, 'index')) {
			throw new ExitException(403);
		}
		Event::instance()->fire('System/App/render/before');
		$Page = Page::instance();
		$this->render($Request, $Page);
		Event::instance()->fire('System/App/render/after');
		$Page->render();
	}
	/**
	 * @param Config  $Config
	 * @param Request $Request
	 *
	 * @throws ExitException
	 */
	protected function handle_closed_site ($Config, $Request) {
		if ($Config->core['site_mode']) {
			return;
		}
		/**
		 * If site is closed
		 */
		if (!$this->allow_closed_site_request($Request)) {
			throw new ExitException(
				[
					$Config->core['closed_title'],
					$Config->core['closed_text']
				],
				503
			);
		}
		/**
		 * Warning about closed site for administrator
		 */
		Page::instance()->warning($Config->core['closed_title']);
	}
	/**
	 * Check if visitor is allowed to make current request to closed site
	 *
	 * @param Request $Request
	 *
	 * @return bool
	 */
	protected function allow_closed_site_request ($Request) {
		return
			User::instance()->admin() ||
			(
				$Request->api_path &&
				$Request->current_module == 'System' &&
				$Request->route == ['profile'] &&
				$Request->method == 'SIGN_IN'
			);
	}
	/**
	 * Check whether user allowed to access to specified label
	 *
	 * @param Request $Request
	 * @param string  $label
	 *
	 * @return bool
	 */
	protected function check_permission ($Request, $label) {
		if ($Request->cli_path) {
			return true;
		}
		$permission_group = $Request->current_module;
		if ($Request->admin_path) {
			$permission_group = "admin/$permission_group";
		} elseif ($Request->api_path) {
			$permission_group = "api/$permission_group";
		}
		return User::instance()->get_permission($permission_group, $label);
	}
	/**
	 * @param Request $Request
	 * @param Page    $Page
	 */
	protected function render ($Request, $Page) {
		if ($Request->api_path) {
			$Page->json(null);
			$this->execute_router($Request);
		} elseif ($Request->cli_path || !$Page->interface) {
			$this->execute_router($Request);
		} else {
			$this->render_title($Request, $Page);
			$this->execute_router($Request);
			$this->render_blocks($Page);
		}
	}
	/**
	 * Render page title
	 *
	 * @param Request $Request
	 * @param Page    $Page
	 */
	protected function render_title ($Request, $Page) {
		/**
		 * Add generic Home or Module name title
		 */
		$L = Language::instance();
		if ($Request->admin_path) {
			$Page->title($L->system_admin_administration);
		}
		$Page->title(
			$L->{$Request->home_page ? 'system_home' : $Request->current_module}
		);
	}
	/**
	 * Blocks rendering
	 *
	 * @param Page $Page
	 */
	protected function render_blocks ($Page) {
		/**
		 * @var array $blocks
		 */
		$blocks = Config::instance()->components['blocks'];
		/**
		 * It is frequent that there is no blocks - so, no need to to anything here
		 */
		if (!$blocks) {
			return;
		}
		$blocks_array = [
			'top'    => '',
			'left'   => '',
			'right'  => '',
			'bottom' => ''
		];
		foreach ($blocks as $block) {
			/**
			 * If there is no need to show block or it was rendered by even handler - skip further processing
			 */
			if (
				!$this->should_block_be_rendered($block) ||
				!Event::instance()->fire(
					'System/App/block_render',
					[
						'index'        => $block['index'],
						'blocks_array' => &$blocks_array
					]
				)
			) {
				/**
				 * Block was rendered by event handler
				 */
				continue;
			}
			$block['title'] = $this->ml_process($block['title']);
			switch ($block['type']) {
				default:
					$block['content'] = ob_wrapper(
						function () use ($block) {
							include BLOCKS."/block.$block[type].php";
						}
					);
					break;
				case 'html':
				case 'raw_html':
					$block['content'] = $this->ml_process($block['content']);
					break;
			}
			if ($block['position'] == 'floating') {
				$Page->replace(
					"<!--block#$block[index]-->",
					$block['content']
				);
			} else {
				$blocks_array[$block['position']] .= $block['content'];
			}
		}
		$Page->Top .= $blocks_array['top'];
		$Page->Left .= $blocks_array['left'];
		$Page->Right .= $blocks_array['right'];
		$Page->Bottom .= $blocks_array['bottom'];
	}
	/**
	 * Check whether to render block or not based on its properties (active state, when start to show, when it expires and permissions)
	 *
	 * @param array $block
	 *
	 * @return bool
	 */
	protected function should_block_be_rendered ($block) {
		return
			$block['active'] &&
			$block['start'] <= time() &&
			(
				!$block['expire'] ||
				$block['expire'] >= time()
			) &&
			User::instance()->get_permission('Block', $block['index']);
	}
	/**
	 * @param string $text
	 *
	 * @return string
	 */
	protected function ml_process ($text) {
		return Text::instance()->process(Config::instance()->module('System')->db('texts'), $text, true);
	}
	/**
	 * Getter for `controller_path` property (no other properties supported currently)
	 *
	 * @param string $property
	 *
	 * @return false|string[]
	 */
	public function __get ($property) {
		if ($property == 'controller_path') {
			return $this->controller_path;
		}
		return false;
	}
}
