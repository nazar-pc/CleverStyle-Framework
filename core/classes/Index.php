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
 *      'index'           => $index,        //Block index
 *      'blocks_array'    => &$blocks_array //Reference to array in form ['top' => '', 'left' => '', 'right' => '', 'bottom' => '']
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
	use
		Singleton,
		Index\Router;
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
		$Config     = Config::instance();
		$Route      = Route::instance();
		$this->path = &$Route->path;
		$this->ids  = &$Route->ids;
		if ($this->closed_site($Config, api_path())) {
			return;
		}
		$this->module            = current_module();
		$this->working_directory = MODULES."/$this->module";
		if (admin_path()) {
			$this->working_directory .= '/admin';
		} elseif (api_path()) {
			$this->working_directory .= '/api';
		}
		if (!is_dir($this->working_directory)) {
			error_code(404);
			throw new \ExitException;
		}
		if (!$this->check_permission('index')) {
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
		if (!preg_match('/^[a-z_]+$/', $this->request_method)) {
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
			Route::instance()->route !== ['user', 'sign_in'];
	}
	/**
	 * Check whether user allowed to access to specified label
	 *
	 * @param string $label
	 *
	 * @return bool
	 */
	protected function check_permission ($label) {
		$permission_group = $this->module;
		if (admin_path()) {
			$permission_group = "admin/$permission_group";
		} elseif (api_path()) {
			$permission_group = "api/$permission_group";
		}
		return User::instance()->get_permission($permission_group, $label);
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
		$this->execute_router();
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
