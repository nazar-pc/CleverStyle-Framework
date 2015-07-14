<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;
/**
 * Provides next events:
 *  System/Index/block_render
 *  [
 *      'index'           => $index,           //Block index
 *      'blocks_array'    => &$blocks_array    //Reference to array in form ['top' => '', 'left' => '', 'right' => '', 'bottom' => '']
 *  ]
 *
 *  System/Index/construct
 *
 *  System/Index/preload
 *
 *  System/Index/postload
 *
 * @method static Index instance($check = false)
 *
 * @property string   $action             Form action
 * @property string[] $controller_path    Path that will be used by controller to render page
 */
class Index {
	use    Singleton;
	/**
	 * @var string
	 */
	public $Content;

	public    $form               = false;
	public    $file_upload        = false;
	public    $form_attributes    = [
		'class' => 'uk-form'
	];
	public    $buttons            = true;
	public    $save_button        = true;
	public    $apply_button       = false;
	public    $cancel_button_back = false;
	public    $custom_buttons     = '';
	protected $action;
	/**
	 * Appends to the end of title
	 *
	 * @var string
	 */
	protected $append_to_title = '';
	protected $permission_group;
	/**
	 * Name of current module
	 *
	 * @var string
	 */
	protected $module;
	/**
	 * Whether current page is api
	 *
	 * @var bool
	 */
	protected $in_api = false;
	/**
	 * Whether current page is administration and user is admin
	 *
	 * @var bool
	 */
	protected $in_admin          = false;
	protected $request_method;
	protected $working_directory = '';
	protected $called_once       = false;
	/**
	 * Reference to Route::instance()->route
	 *
	 * @var array
	 */
	protected $route = [];
	/**
	 * Reference to Route::instance()->path
	 *
	 * @var string[]
	 */
	protected $path = [];
	/**
	 * Reference to Route::instance()->ids
	 *
	 * @var int[]
	 */
	protected $ids = [];
	/**
	 * Path that will be used by controller to render page
	 *
	 * @var string[]
	 */
	protected $controller_path = ['index'];
	/**
	 * Detecting module folder including of admin/api request type, including prepare file, including of plugins
	 *
	 * @throws \ExitException
	 */
	function construct () {
		$Config      = Config::instance();
		$Route       = Route::instance();
		$this->route = &$Route->route;
		$this->path  = &$Route->path;
		$this->ids   = &$Route->ids;
		if ($this->closed_site($Config, api_path())) {
			return;
		}
		$this->module            = current_module();
		$this->working_directory = MODULES."/$this->module";
		$permission_group        = $this->module;
		if (admin_path()) {
			$this->working_directory .= '/admin';
			$permission_group = "admin/$permission_group";
		} elseif (api_path()) {
			$this->working_directory .= '/api';
			$permission_group = "api/$permission_group";
		}
		if (!is_dir($this->working_directory)) {
			error_code(404);
			throw new \ExitException;
		}
		if (!$this->set_permission_group($permission_group)) {
			error_code(403);
			throw new \ExitException;
		}
		$this->in_admin = admin_path();
		$this->form     = $this->in_admin;
		$this->in_api   = api_path();
		Event::instance()->fire('System/Index/construct');
		/**
		 * Plugins processing
		 */
		foreach ($Config->components['plugins'] as $plugin) {
			_include(PLUGINS."/$plugin/index.php", false, false);
		}
		_include("$this->working_directory/prepare.php", false, false);
		/**
		 * @var _SERVER $_SERVER
		 */
		$this->request_method = strtolower($_SERVER->request_method);
		if (!preg_match('/^[a-z]+$/', $this->request_method)) {
			error_code(400);
			throw new \ExitException;
		}
	}
	/**
	 * Check if site is closed (taking user into account)
	 *
	 * @param Config $Config
	 * @param bool   $in_api
	 *
	 * @return bool Whether user is not admin and this is not request for sign in (we allow to sign in on disabled site)
	 */
	protected function closed_site ($Config, $in_api) {
		if (
			$Config->core['site_mode'] ||
			User::instance()->admin()
		) {
			return false;
		}
		return
			!$in_api ||
			$this->module != 'System' ||
			$this->route !== ['user', 'sign_in'];
	}
	/**
	 * Store permission group for further checks, check whether user allowed to access `index` permission label of this group
	 *
	 * @param string $permission_group
	 *
	 * @return bool
	 */
	protected function set_permission_group ($permission_group) {
		if (strpos($permission_group, 'admin/') === 0 && !User::instance()->admin()) {
			return false;
		}
		$this->permission_group = $permission_group;
		return $this->check_permission('index');
	}
	/**
	 * Check whether user allowed to access to specified label
	 *
	 * @param string $label
	 *
	 * @return bool
	 */
	protected function check_permission ($label) {
		return User::instance()->get_permission($this->permission_group, $label);
	}
	/**
	 * Adding of content on the page
	 *
	 * @param string   $add
	 * @param bool|int $level
	 *
	 * @return Index
	 */
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
		return $this;
	}
	/**
	 * Normalize route path and fill `cs\Index::$route_path` and `cs\Index::$route_ids` properties
	 */
	protected function check_and_normalize_route () {
		if (!file_exists("$this->working_directory/index.json")) {
			return;
		}
		$structure = file_get_json("$this->working_directory/index.json");
		if (!$structure) {
			return;
		}
		for ($nesting_level = 0; $structure; ++$nesting_level) {
			/**
			 * Next level of routing path
			 */
			$path = @$this->path[$nesting_level];
			/**
			 * If path not specified - take first from structure
			 */
			$code = $this->check_and_normalize_route_internal($path, $structure);
			if ($code !== 200) {
				error_code($code);
				return;
			}
			$this->path[$nesting_level] = $path;
			/**
			 * Fill paths array intended for controller's usage
			 */
			$this->controller_path[] = $path;
			/**
			 * If nested structure is not available - we'll not go into next iteration of this cycle
			 */
			$structure = @$structure[$path];
		}
	}
	/**
	 * @param string $path
	 * @param array  $structure
	 *
	 * @return int HTTP status code
	 */
	protected function check_and_normalize_route_internal (&$path, $structure) {
		/**
		 * If path not specified - take first from structure
		 */
		if (!$path) {
			$path = isset($structure[0]) ? $structure[0] : array_keys($structure)[0];
			/**
			 * We need exact paths for API request (or `_` ending if available) and less strict mode for other cases that allows go deeper automatically
			 */
			if ($path !== '_' && api_path()) {
				return 404;
			}
		} elseif (!isset($structure[$path]) && !in_array($path, $structure)) {
			return 404;
		}
		if (!$this->check_permission($path)) {
			return 403;
		}
		return 200;
	}
	/**
	 * Include files necessary for module page rendering
	 */
	protected function files_router () {
		foreach ($this->controller_path as $index => $path) {
			/**
			 * Starting from index 2 we need to maintain slash-separated string that includes all paths from index 1 and till current
			 */
			if ($index > 1) {
				$path = implode('/', array_slice($this->controller_path, 1, $index));
			}
			$next_exists = isset($this->controller_path[$index + 1]);
			if (!$this->files_router_handler($this->working_directory, $path, !$next_exists)) {
				return;
			}
		}
	}
	/**
	 * Include files that corresponds for specific paths in URL
	 *
	 * @param string $dir
	 * @param string $basename
	 * @param bool   $required
	 *
	 * @return bool
	 */
	protected function files_router_handler ($dir, $basename, $required = true) {
		$this->files_router_handler_internal($dir, $basename, $required);
		return !error_code();
	}
	protected function files_router_handler_internal ($dir, $basename, $required) {
		$included = _include("$dir/$basename.php", false, false) !== false;
		if (!api_path()) {
			return;
		}
		$included = _include("$dir/$basename.$this->request_method.php", false, false) !== false || $included;
		if ($included || !$required) {
			return;
		}
		if ($methods = get_files_list($dir, "/^$basename\\.[a-z]+\\.php$/")) {
			$methods = _strtoupper(_substr($methods, strlen($basename) + 1, -4));
			$methods = implode(', ', $methods);
			_header("Allow: $methods");
			error_code(405);
		} else {
			error_code(404);
		}
	}
	/**
	 * Call methods necessary for module page rendering
	 */
	protected function controller_router () {
		$suffix = '';
		if ($this->in_admin) {
			$suffix = '\\admin';
		} elseif ($this->in_api) {
			$suffix = '\\api';
		}
		$controller_class = "cs\\modules\\$this->module$suffix\\Controller";
		foreach ($this->controller_path as $index => $path) {
			/**
			 * Starting from index 2 we need to maintain underscore-separated string that includes all paths from index 1 and till current
			 */
			if ($index > 1) {
				$path = implode('_', array_slice($this->controller_path, 1, $index));
			}
			$next_exists = isset($this->controller_path[$index + 1]);
			if (!$this->controller_router_handler($controller_class, $path, !$next_exists)) {
				return;
			}
		}
	}
	/**
	 * Call methods that corresponds for specific paths in URL
	 *
	 * @param string $controller_class
	 * @param string $method_name
	 * @param bool   $required
	 *
	 * @return bool
	 */
	protected function controller_router_handler ($controller_class, $method_name, $required = true) {
		$method_name = strtr($method_name, '.', '_');
		$this->controller_router_handler_internal($controller_class, $method_name, $required);
		return !error_code();
	}
	/**
	 * @param string $controller_class
	 * @param string $method_name
	 * @param bool   $required
	 */
	protected function controller_router_handler_internal ($controller_class, $method_name, $required) {
		$included =
			method_exists($controller_class, $method_name) &&
			$controller_class::$method_name($this->ids, $this->path) !== false;
		if (!api_path()) {
			return;
		}
		$included =
			method_exists($controller_class, $method_name.'_'.$this->request_method) &&
			$controller_class::{$method_name.'_'.$this->request_method}($this->ids, $this->path) !== false ||
			$included;
		if ($included || !$required) {
			return;
		}
		$methods = array_filter(
			get_class_methods($controller_class),
			function ($method) use ($method_name) {
				return preg_match("/^{$method_name}_[a-z]+$/", $method);
			}
		);
		if ($methods) {
			$methods = _strtoupper(_substr($methods, strlen($method_name) + 1));
			$methods = implode(', ', $methods);
			_header("Allow: $methods");
			error_code(405);
		} else {
			error_code(404);
		}
	}
	/**
	 * Get form action based on current module, path and other parameters
	 *
	 * @return string
	 */
	protected function get_action () {
		if ($this->action === null) {
			$this->action = ($this->in_admin ? 'admin/' : '')."$this->module";
			if (isset($this->path[0])) {
				$this->action .= '/'.$this->path[0];
				if (isset($this->path[1])) {
					$this->action .= '/'.$this->path[1];
				}
			}
		}
		return $this->action ?: '';
	}
	/**
	 * Page generation, blocks processing, adding of form with save/apply/cancel/reset and/or custom users buttons
	 *
	 * @throws \ExitException
	 */
	protected function render_page () {
		$this->render_title();
		$this->render_content();
		$Page = Page::instance();
		if (!$this->in_api) {
			if ($this->form) {
				$this->form_wrapper();
			}
			$this->render_blocks();
		}
		$Page->content($this->Content);
	}
	/**
	 * Render page title
	 */
	protected function render_title () {
		$Page = Page::instance();
		/**
		 * Add generic Home or Module name title
		 */
		if (!$this->in_api) {
			$L = Language::instance();
			if ($this->in_admin) {
				$Page->title($L->administration);
			}
			$Page->title(
				$L->{home_page() ? 'home' : $this->module}
			);
		}
	}
	/**
	 * Render page content (without blocks, just module content)
	 *
	 * @throws \ExitException
	 */
	protected function render_content () {
		$Page = Page::instance();
		/**
		 * If module consists of index.html only
		 */
		if (file_exists("$this->working_directory/index.html")) {
			ob_start();
			_include("$this->working_directory/index.html", false, false);
			$Page->content(ob_get_clean());
			return;
		}
		$this->check_and_normalize_route();
		if (!error_code()) {
			$router = file_exists("$this->working_directory/Controller.php") ? 'controller_router' : 'files_router';
			$this->$router();
		}
		if (error_code()) {
			$Page->error();
		}
	}
	/**
	 * Wraps `cs\Index::$Content` with form and adds form buttons to the end of content
	 */
	protected function form_wrapper () {
		/**
		 * Render buttons
		 */
		if ($this->buttons) {
			/**
			 * Apply button
			 */
			if ($this->apply_button) {
				$this->Content .= $this->form_button('apply', !Cache::instance()->cache_state());
			}
			/**
			 * Save button
			 */
			if ($this->save_button) {
				$this->Content .= $this->form_button('save');
			}
			/**
			 * If cancel button does not work as back button - render it here
			 */
			if ($this->apply_button && !$this->cancel_button_back) {
				$this->Content .= $this->form_button('cancel', !@Config::instance()->core['cache_not_saved']);
			}
		}
		/**
		 * If cancel button works as back button - render it here
		 */
		if ($this->cancel_button_back) {
			$this->Content .= h::{'button.uk-button'}(
				Language::instance()->cancel,
				[
					'name'    => 'cancel',
					'type'    => 'button',
					'onclick' => 'history.go(-1);'
				]
			);
		}
		$this->Content = h::form(
			$this->Content.
			$this->custom_buttons,
			$this->form_attributes + [
				'enctype' => $this->file_upload ? 'multipart/form-data' : false,
				'action'  => $this->get_action()
			]
		);
	}
	/**
	 * Simple wrapper for form buttons
	 *
	 * @param string $name
	 * @param bool   $disabled
	 *
	 * @return string
	 */
	protected function form_button ($name, $disabled = false) {
		$L = Language::instance();
		return h::{'button.uk-button'}(
			$L->$name,
			[
				'name'       => $name,
				'type'       => 'submit',
				'data-title' => $L->{$name.'_info'},
				$disabled ? 'disabled' : false
			]
		);
	}
	/**
	 * Blocks rendering
	 */
	protected function render_blocks () {
		$blocks = Config::instance()->components['blocks'];
		/**
		 * It is frequent that there is no blocks - so, no need to to anything here
		 */
		if (!$blocks) {
			return;
		}
		$Page         = Page::instance();
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
					'System/Index/block_render',
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
			/**
			 * Template file will have access to `$block` variable, so it can use that
			 */
			$content = str_replace(
				[
					'<!--id-->',
					'<!--title-->',
					'<!--content-->'
				],
				[
					$block['index'],
					$block['title'],
					$block['content']
				],
				ob_wrapper(
					function () use ($block) {
						$template = file_exists(TEMPLATES."/blocks/block.$block[template]") ? $block['template'] : 'default.html';
						include TEMPLATES."/blocks/block.$template";
					}
				)
			);
			if ($block['position'] == 'floating') {
				$Page->replace(
					"<!--block#$block[index]-->",
					$content
				);
			} else {
				$blocks_array[$block['position']] .= $content;
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
	 * Saving changes and/or showing resulting message of saving changes
	 *
	 * @param bool|null $result If bool - result will be shown only, otherwise works similar to the $Config->save() and shows resulting message
	 *
	 * @return bool
	 */
	function save ($result = null) {
		$L    = Language::instance();
		$Page = Page::instance();
		if ($result || ($result === null && Config::instance()->save())) {
			$this->append_to_title = $L->changes_saved;
			$Page->success($L->changes_saved);
			return true;
		} else {
			$this->append_to_title = $L->changes_save_error;
			$Page->warning($L->changes_save_error);
			return false;
		}
	}
	/**
	 * Applying changes and/or showing resulting message of applying changes
	 *
	 * @param bool|null|string $result If bool - result will be shown only, otherwise works similar to the $Config->apply() and shows resulting message
	 *
	 * @return bool
	 */
	function apply ($result = null) {
		$L    = Language::instance();
		$Page = Page::instance();
		if ($result || ($result === null && Config::instance()->apply())) {
			$this->append_to_title = $L->changes_applied;
			$Page->success($L->changes_applied.$L->check_applied);
			return true;
		} else {
			$this->append_to_title = $L->changes_apply_error;
			$Page->warning($L->changes_apply_error);
			return false;
		}
	}
	/**
	 * Changes canceling and/or showing result of canceling changes
	 *
	 * @param bool $system If <b>true,/b> - cancels changes of system configuration, otherwise shows message about successful canceling
	 */
	function cancel ($system = true) {
		if ($system) {
			Config::instance()->cancel();
		}
		$L                     = Language::instance();
		$this->append_to_title = $L->changes_canceled;
		Page::instance()->success($L->changes_canceled);
	}
	/**
	 * Whether current page is administration and user is admin
	 *
	 * @return bool
	 */
	function in_admin () {
		return $this->in_admin;
	}
	/**
	 * Getter for `action` and `controller_path` properties (no other properties supported currently)
	 *
	 * @param string $property
	 *
	 * @return false|string|string[]
	 */
	function __get ($property) {
		switch ($property) {
			case 'action':
				return $this->get_action();
			case 'controller_path';
				return $this->controller_path;
		}
		return false;
	}
	/**
	 * Setter for `action` property (no other properties supported currently)
	 *
	 * @param string $property
	 * @param string $value
	 */
	function __set ($property, $value) {
		if ($property == 'action') {
			$this->action = $value;
		}
	}
	/**
	 * Executes plugins processing, blocks and module page generation
	 *
	 * @throws \ExitException
	 */
	function __finish () {
		/**
		 * Protection from double calling
		 */
		if ($this->called_once) {
			return;
		}
		$this->called_once = true;
		$Config            = Config::instance();
		$Page              = Page::instance();
		/**
		 * If site is closed
		 */
		if (!$Config->core['site_mode']) {
			if ($this->closed_site($Config, $this->in_api)) {
				status_code(503);
				return;
			}
			/**
			 * Warning about closed site
			 */
			$Page->warning(get_core_ml_text('closed_title'));
		}
		if (error_code()) {
			$Page->error();
		}
		Event::instance()->fire('System/Index/preload');
		$this->render_page();
		Event::instance()->fire('System/Index/postload');
	}
}
