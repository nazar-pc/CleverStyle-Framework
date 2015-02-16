<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use            h;
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
 * @property string $action    Form action
 */
class Index {
	use    Singleton;
	/**
	 * @var string
	 */
	public $Content;

	public $form               = false;
	public $file_upload        = false;
	public $form_attributes    = [
		'class' => 'uk-form'
	];
	public $buttons            = true;
	public $save_button        = true;
	public $apply_button       = true;
	public $cancel_button_back = false;
	public $custom_buttons     = '';
	/**
	 * Like Config::$route property, but excludes numerical items
	 *
	 * @var string[]
	 */
	public $route_path = [];
	/**
	 * Like Config::$route property, but only includes numerical items (opposite to route_path property)
	 *
	 * @var int[]
	 */
	public    $route_ids = [];
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
	protected $path_required     = false;
	protected $sub_path_required = false;
	/**
	 * Detecting module folder including of admin/api request type, including prepare file, including of plugins
	 */
	function construct () {
		$Config = Config::instance();
		$User   = User::instance();
		$api    = api_path();
		/**
		 * If site is closed, user is not admin, and it is not request for sign in
		 */
		if (
			!$Config->core['site_mode'] &&
			!(
				$User->admin() ||
				(
					$api && $Config->route === ['user', 'sign_in']
				)
			)
		) {
			return;
		}
		$this->module = current_module();
		$admin_path   = MODULES."/$this->module/admin";
		$api_path     = MODULES."/$this->module/api";
		if (
			admin_path() &&
			file_exists($admin_path) &&
			(
				file_exists("$admin_path/index.php") ||
				file_exists("$admin_path/index.json")
			)
		) {
			if (!$this->set_permission_group("admin/$this->module")) {
				error_code(403);
				throw new \ExitException;
			}
			$this->working_directory = $admin_path;
			$this->form              = true;
			$this->in_admin          = true;
		} elseif (
			$api &&
			file_exists($api_path)
		) {
			if (!$this->set_permission_group("api/$this->module")) {
				error_code(403);
				throw new \ExitException;
			}
			$this->working_directory = $api_path;
			$this->in_api            = true;
		} elseif (
			!$api &&
			!admin_path() &&
			file_exists(MODULES."/$this->module")
		) {
			if (!$this->set_permission_group($this->module)) {
				error_code(403);
				throw new \ExitException;
			}
			$this->working_directory = MODULES."/$this->module";
		} else {
			error_code(404);
			throw new \ExitException;
		}
		unset($admin_path, $api_path);
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
		}
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
	protected function normalize_route () {
		/**
		 * Separate numeric and other parts of route
		 */
		foreach (Config::instance()->route as $item) {
			if (is_numeric($item)) {
				$this->route_ids[] = $item;
			} else {
				$this->route_path[] = $item;
			}
		}
		unset($item);
		if (!file_exists("$this->working_directory/index.json")) {
			return;
		}
		$structure = file_get_json("$this->working_directory/index.json");
		if (!$structure) {
			return;
		}
		$this->path_required = true;
		/**
		 * First level path routing
		 */
		$path = @$this->route_path[0];
		/**
		 * If path not specified - take first from structure
		 */
		if (!$path) {
			if (api_path()) {
				error_code(404);
				return;
			}
			$path = isset($structure[0]) ? $structure[0] : array_keys($structure)[0];
		} elseif (!isset($structure[$path]) && !in_array($path, $structure)) {
			error_code(404);
			return;
		}
		if (!$this->check_permission($path)) {
			error_code(403);
		}
		$this->route_path[0] = $path;
		/**
		 * If there is second level routing in structure - handle that
		 */
		if (!isset($structure[$path]) || empty($structure[$path])) {
			return;
		}
		$this->sub_path_required = true;
		$sub_path                = @$this->route_path[1];
		/**
		 * If sub path not specified - take first from structure
		 */
		if (!$sub_path) {
			if (api_path()) {
				error_code(404);
				return;
			}
			$sub_path = array_shift($structure[$path]);
		} elseif (!in_array($sub_path, $structure[$path])) {
			error_code(404);
			return;
		}
		if (!$this->check_permission("$path/$sub_path")) {
			error_code(403);
		}
		$this->route_path[1] = $sub_path;
	}
	/**
	 * Include files necessary for module page rendering
	 *
	 * @param string $path
	 * @param string $sub_path
	 */
	protected function files_router ($path, $sub_path) {
		if (!$this->files_router_handler($this->working_directory, 'index', !$path)) {
			return;
		}
		if (!$this->path_required || !$this->files_router_handler($this->working_directory, $path, !$sub_path)) {
			return;
		}
		if (!$this->sub_path_required || !$this->files_router_handler("$this->working_directory/$path", $sub_path)) {
			return;
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
	 *
	 * @param string $path
	 * @param string $sub_path
	 */
	protected function controller_router ($path, $sub_path) {
		$controller_class = "cs\\modules\\$this->module\\".($this->in_admin ? 'admin\\' : ($this->in_api ? 'api\\' : '')).'Controller';
		if (!$this->controller_router_handler($controller_class, 'index', !$path)) {
			return;
		}
		if (!$this->path_required || !$this->controller_router_handler($controller_class, $path, !$sub_path)) {
			return;
		}
		if (!$this->sub_path_required || !$this->controller_router_handler($controller_class, $path.'_'.$sub_path)) {
			return;
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
	protected function controller_router_handler_internal ($controller_class, $method_name, $required) {
		$included =
			method_exists($controller_class, $method_name) &&
			$controller_class::$method_name($this->route_ids, $this->route_path) !== false;
		if (!api_path()) {
			return;
		}
		$included =
			method_exists($controller_class, $method_name.'_'.$this->request_method) &&
			$controller_class::{$method_name.'_'.$this->request_method}($this->route_ids, $this->route_path) !== false ||
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
			$methods = _strtoupper(_substr($methods, strlen($method_name) + 1, -4));
			$methods = implode(', ', $methods);
			_header("Allow: $methods");
			error_code(405);
		} else {
			error_code(404);
		}
	}
	/**
	 * Page generation, blocks processing, adding of form with save/apply/cancel/reset and/or custom users buttons
	 */
	protected function render_complete_page () {
		$Page = Page::instance();
		if ($this->in_api) {
			$Page->content($this->Content);
			return;
		}
		$this->render_blocks();
		if ($this->form) {
			$this->form_wrapper();
		}
		$Page->content($this->Content);
	}
	/**
	 * Wraps `cs\Index::$Content` with form and adds form buttons to the end of content
	 */
	protected function form_wrapper () {
		$Config        = Config::instance();
		$L             = Language::instance();
		$this->Content = h::form(
			$this->Content.
			//Apply button
			($this->apply_button && $this->buttons ?
				$this->form_button('apply', !Cache::instance()->cache_state())
				: '').
			//Save button
			($this->save_button && $this->buttons ?
				$this->form_button('save')
				: '').
			//Cancel button
			($this->apply_button && $this->buttons && !$this->cancel_button_back ?
				$this->form_button('cancel', !@$Config->core['cache_not_saved'])
				: '').
			($this->cancel_button_back ?
				h::{'button.uk-button'}(
					$L->cancel,
					[
						'name'    => 'cancel',
						'type'    => 'button',
						'onclick' => 'history.go(-1);'
					]
				)
				: '').
			$this->custom_buttons,
			array_merge(
				[
					'enctype' => $this->file_upload ? 'multipart/form-data' : false,
					'action'  => $this->get_action()
				],
				$this->form_attributes
			)
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
	 * Get form action based on current module, path and other parameters
	 *
	 * @return string
	 */
	protected function get_action () {
		if ($this->action === null) {
			$this->action = ($this->in_admin ? 'admin/' : '')."$this->module";
			if (isset($this->route_path[0])) {
				$this->action .= '/'.$this->route_path[0];
				if (isset($this->route_path[1])) {
					$this->action .= '/'.$this->route_path[1];
				}
			}
		}
		return $this->action ?: '';
	}
	/**
	 * Blocks processing
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
			if (
				!$block['active'] ||
				$block['start'] > time() ||
				($block['expire'] && $block['expire'] < time()) ||
				!(User::instance()->get_permission('Block', $block['index']))
			) {
				continue;
			}
			if (Event::instance()->fire(
				'System/Index/block_render',
				[
					'index'        => $block['index'],
					'blocks_array' => &$blocks_array
				]
			)
			) {
				$block['title'] = $this->ml_process($block['title']);
				switch ($block['type']) {
					default:
						$content = ob_wrapper(function () use ($block) {
							include BLOCKS."/block.$block[type].php";
						});
						break;
					case 'html':
					case 'raw_html':
						$content = $this->ml_process($block['content']);
						break;
				}
				$template = TEMPLATES.'/blocks/block.'.(
					file_exists(TEMPLATES."/blocks/block.$block[template]") ? $block['template'] : 'default.html'
					);
				$content  = str_replace(
					[
						'<!--id-->',
						'<!--title-->',
						'<!--content-->'
					],
					[
						$block['index'],
						$block['title'],
						$content
					],
					ob_wrapper(function () use ($template) {
						include $template;
					})
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
		}
		$Page->Top .= $blocks_array['top'];
		$Page->Left .= $blocks_array['left'];
		$Page->Right .= $blocks_array['right'];
		$Page->Bottom .= $blocks_array['bottom'];
	}
	protected function ml_process ($text) {
		return Text::instance()->process(Config::instance()->module('System')->db('texts'), $text, true, true);
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
	 * Getter for `action` property (no other properties supported currently)
	 *
	 * @param string $property
	 *
	 * @return bool|string
	 */
	function __get ($property) {
		if ($property == 'action') {
			return $this->get_action();
		}
		return false;
	}
	/**
	 * Getter for `action` property (no other properties supported currently)
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
			/**
			 * If user is not admin and it is not request for sign in
			 */
			if (
				!User::instance()->admin() &&
				!(
					api_path() && $Config->route === ['user', 'sign_in']
				)
			) {
				code_header(503);
				return;
			}
			/**
			 * Warning about closed site
			 */
			if (!$this->in_api) {
				$Page->warning(get_core_ml_text('closed_title'));
			}
		}
		if (error_code()) {
			$Page->error();
		}
		/**
		 * Add generic Home or Module Name title
		 */
		if (!$this->in_api) {
			$L = Language::instance();
			if ($this->in_admin()) {
				$Page->title($L->administration);
			}
			$Page->title(
				$L->{home_page() ? 'home' : $this->module}
			);
		}
		Event::instance()->fire('System/Index/preload');
		/**
		 * If module consists of index.html only
		 */
		if (!$this->in_admin && !$this->in_api && $this->module && file_exists(MODULES."/$this->module/index.html")) {
			ob_start();
			_include(MODULES."/$this->module/index.html", false, false);
			$Page->content(ob_get_clean());
		} elseif (!error_code()) {
			$this->normalize_route();
			if (!error_code()) {
				if (file_exists("$this->working_directory/Controller.php")) {
					$this->controller_router(@$this->route_path[0], @$this->route_path[1]);
				} else {
					$this->files_router(@$this->route_path[0], @$this->route_path[1]);
				}
			}
		}
		$this->render_complete_page();
		if (error_code()) {
			$Page->error();
		}
		Event::instance()->fire('System/Index/postload');
	}
}
