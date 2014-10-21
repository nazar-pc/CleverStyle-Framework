<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
/**
 * Provides next triggers:
 *  System/Index/block_render
 *  [
 * 		'index'			=> $index,			//Block index
 *  	'blocks_array'	=> &$blocks_array	//Reference to array in form ['top' => '', 'left' => '', 'right' => '', 'bottom' => '']
 *  ]
 *
 *  System/Index/construct
 *
 *  System/Index/preload
 *
 *  System/Index/postload
 *
 * @method static Index instance($check = false)
 */
class Index {
	use	Singleton;
	/**
	 * @var string
	 */
	public	$Content;

	public	$form					= false;
	public	$file_upload			= false;
	public	$form_attributes		= [
		'class'	=> 'uk-form'
	];
	public	$action					= null;
	public	$buttons				= true;
	public	$save_button			= true;
	public	$apply_button			= true;
	public	$cancel_button			= ' disabled';
	public	$cancel_button_back		= false;
	public	$reset_button			= true;
	public	$post_buttons			= '';

	public $init_auto				= true;
	public $title_auto				= true;
	/**
	 * Like Config::$route property, but excludes numerical items
	 *
	 * @var string[]
	 */
	public	$route_path	= [];
	/**
	 * Like Config::$route property, but only includes numerical items (opposite to route_path property)
	 *
	 * @var int[]
	 */
	public	$route_ids	= [];
	/**
	 * Appends to the end of title
	 *
	 * @var string
	 */
	protected	$post_title			= '';
	protected	$structure			= [];
	protected	$parts				= [];
	protected	$subparts			= [];
	protected	$permission_group;
	/**
	 * Name of current module
	 *
	 * @var string
	 */
	protected	$module;
	/**
	 * Whether current page is api
	 *
	 * @var bool
	 */
	protected	$in_api				= false;
	/**
	 * Whether current page is administration and user is admin
	 *
	 * @var bool
	 */
	protected	$in_admin			= false;
	protected	$request_method		= null;
	protected	$working_directory	= '';
	protected 	$called_once		= false;
	/**
	 * Detecting module folder including of admin/api request type, including prepare file, including of plugins
	 */
	function construct () {
		$Config		= Config::instance();
		$User		= User::instance();
		$api		= api_path();
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
		$this->module	= current_module();
		$admin_path		= MODULES."/$this->module/admin";
		$api_path		= MODULES."/$this->module/api";
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
				exit;
			}
			$this->working_directory	= $admin_path;
			$this->form					= true;
			$this->in_admin				= true;
		} elseif (
			$api &&
			file_exists($api_path)
		) {
			if (!$this->set_permission_group("api/$this->module")) {
				error_code(403);
				exit;
			}
			$this->working_directory	= $api_path;
			$this->in_api				= true;
		} elseif (
			!admin_path() &&
			!$api &&
			file_exists(MODULES."/$this->module")
		) {
			if (!$this->set_permission_group($this->module)) {
				error_code(403);
				exit;
			}
			$this->working_directory	= MODULES."/$this->module";
		} else {
			error_code(404);
			exit;
		}
		unset($admin_path, $api_path);
		Trigger::instance()->run('System/Index/construct');
		/**
		 * Plugins processing
		 */
		foreach ($Config->components['plugins'] as $plugin) {
			_include_once(PLUGINS."/$plugin/index.php", false);
		}
		_include_once("$this->working_directory/prepare.php", false);
		$this->request_method	= strtolower($_SERVER['REQUEST_METHOD']);
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
		$this->permission_group	= $permission_group;
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
	 * @param string	$add
	 * @param bool|int	$level
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
	 * Initialization: loading of module structure, including of necessary module files, inclusion of save file
	 */
	protected function init () {
		$Config		= Config::instance();
		$L			= Language::instance();
		$Page		= Page::instance();
		/**
		 * Some routing preparations
		 */
		$rc_path	= &$this->route_path;
		$rc_ids		= &$this->route_ids;
		foreach ($Config->route as &$item) {
			if (is_numeric($item)) {
				$rc_ids[]	= &$item;
			} else {
				$rc_path[]	= &$item;
			}
		}
		unset($item, $rc_path, $rc_ids);
		$rc					= &$this->route_path;
		$working_directory	= $this->working_directory;
		$structure_file		=
			$Config->core['simple_admin_mode'] &&
			file_exists("$working_directory/index_simple.json")
				? 'index_simple.json'
				: 'index.json';
		if (file_exists("$working_directory/$structure_file")) {
			$this->structure	= file_get_json("$working_directory/$structure_file");
			if (is_array($this->structure)) {
				foreach ($this->structure as $item => $value) {
					if (!is_array($value)) {
						$item	= $value;
					}
					if ($this->check_permission($item)) {
						$this->parts[] = $item;
						if (@$rc[0] == $item && is_array($value)) {
							foreach ($value as $subpart) {
								if ($this->check_permission("$item/$subpart")) {
									$this->subparts[] = $subpart;
								} elseif (@$rc[1] == $subpart) {
									error_code(403);
									return;
								}
							}
						}
					} elseif ($rc[0] == $item) {
						error_code(403);
						return;
					}
				}
				unset($item, $value, $subpart);
			}
		}
		unset($structure_file);
		if (!$this->include_handler($working_directory, 'index', false)) {
			return;
		}
		if ($this->parts) {
			if (!$this->path_check($rc[0], $this->parts, $this->structure, $this->subparts)) {
				return;
			}
			/**
			 * Saving of changes
			 */
			if ($this->in_admin) {
				_include_once("$working_directory/$rc[0]/save.php", false) ||
				_include_once("$working_directory/save.php", false);
				if ($this->title_auto) {
					$Page->title($L->administration);
				}
			}
			if (!$this->in_api && $this->title_auto) {
				$Page->title($L->{home_page() ? 'home' : $this->module});
			}
			if (!$this->in_api) {
				if (!home_page() && $this->title_auto) {
					$Page->title($L->$rc[0]);
				}
			}
			/**
			 * Warning if site is closed
			 */
			if (!$Config->core['site_mode']) {
				$Page->warning(get_core_ml_text('closed_title'));
			}
			if (!$this->include_handler($working_directory, $rc[0], !$this->subparts)) {
				return;
			}
			if ($this->subparts) {
				if (!$this->path_check($rc[1], $this->subparts)) {
					return;
				}
				if (!$this->in_api) {
					if (!home_page() && $this->title_auto) {
						$Page->title($L->$rc[1]);
					}
					if ($this->action === null) {
						$this->action = ($this->in_admin ? 'admin/' : '')."$this->module/$rc[0]/$rc[1]";
					}
				}
				if (!$this->include_handler("$working_directory/$rc[0]", $rc[1])) {
					return;
				}
			} elseif (!$this->in_api && $this->action === null) {
				$this->action = ($this->in_admin ? 'admin/' : '')."$this->module/$rc[0]";
			}
			unset($rc);
			if ($this->post_title && $this->title_auto) {
				$Page->title($this->post_title);
			}
		} elseif (!$this->in_api) {
			if ($this->in_admin) {
				$Page->title($L->administration);
			}
			if (!$this->in_api && $this->title_auto) {
				$Page->title($L->{home_page() ? 'home' : $this->module});
			}
			if ($this->action === null) {
				$this->action = $Config->server['relative_address'];
			}
			if ($this->in_admin) {
				_include_once("$working_directory/save.php", false);
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
	protected function include_handler ($dir, $basename, $required = true) {
		$this->include_handler_internal($dir, $basename, $required);
		return !error_code();
	}
	protected function include_handler_internal ($dir, $basename, $required) {
		$included	= _include_once("$dir/$basename.php", false) !== false;
		if (!api_path()) {
			return;
		}
		$included = _include_once("$dir/$basename.$this->request_method.php", false) !== false || $included;
		if ($included || !$required) {
			return;
		}
		if ($methods = get_files_list($dir, "/$basename\\.[a-z]+\\.php$/")) {
			$methods = _strtoupper(_substr($methods, strlen($basename) + 1, -4));
			$methods = implode(', ', $methods);
			header("Allow: $methods");
			error_code(405);
		} else {
			error_code(404);
		}
	}
	/**
	 * Check, whether current path part exists and if not - try to select first from possible if any, and set possible nested parts
	 *
	 * @param string          $path
	 * @param string[]        $possible_parts
	 * @param null|string[][] $structure
	 * @param null|string[]   $nested_parts
	 *
	 * @return bool
	 */
	protected function path_check (&$path, $possible_parts, $structure = null, &$nested_parts = null) {
		/**
		 * Index not found or empty
		 */
		if (!@$path) {
			if (api_path()) {
				return false;
			}
			$path	= $possible_parts[0];
			/**
			 * If there is structure - set nested possible parts
			 */
			if ($structure) {
				$nested_parts	= @$structure[$path];
			}
		} elseif (!in_array($path, $possible_parts)) {
			error_code(404);
			return false;
		}
		return true;
	}
	/**
	 * Module page generation, blocks processing, adding of form with save/apply/cancel/reset and/or custom users buttons
	 */
	protected function render () {
		$Config	= Config::instance();
		$L		= Language::instance();
		$Page	= Page::instance();
		if ($this->in_api) {
			$Page->content($this->Content);
			return;
		}
		$this->render_blocks();
		if ($this->form) {
			$Page->content(
				h::form(
					$this->Content.
					//Apply button
					($this->apply_button && $this->buttons ?
						h::{'button.uk-button'}(
							$L->apply,
							[
								'name'			=> 'edit_settings',
								'data-title'	=> $L->apply_info,
								'id'			=> 'apply_settings',
								'type'			=> 'submit',
								'value'			=> 'apply',
								'add'			=> Cache::instance()->cache_state() ? '' : ' disabled'
							]
						)
					: '').
					//Save button
					($this->save_button && $this->buttons ?
						h::{'button.uk-button'}(
							$L->save,
							[
								'name'			=> 'edit_settings',
								'data-title'	=> $L->save_info,
								'id'			=> 'save_settings',
								'type'			=> 'submit',
								'value'			=> 'save'
							]
						)
					: '').
					//Cancel button (cancel changes or returns to the previous page)
					(($this->apply_button && $this->buttons) || $this->cancel_button_back ?
						h::{'button.uk-button'}(
							$L->cancel,
							[
								'name'			=> 'edit_settings',
								'id'			=> 'cancel_settings',
								'value'			=> 'cancel',
								'data-title'	=> $this->cancel_button_back ? false : $L->cancel_info,
								'type'			=> $this->cancel_button_back ? 'button' : 'submit',
								'onClick'		=> $this->cancel_button_back ? 'history.go(-1);' : false,
								'add'			=> $this->cancel_button_back ? '' : (@$Config->core['cache_not_saved'] ? '' : $this->cancel_button)
							]
						)
					: '').
					//Reset button
					($this->buttons && $this->reset_button ?
						h::{'button.uk-button'}(
							$L->reset,
							[
								'id'			=> 'reset_settings',
								'data-title'	=> $L->reset_info,
								'type'			=> 'reset'
							]
						)
					: '').
					$this->post_buttons,
					array_merge(
						[
							'enctype'	=> $this->file_upload ? 'multipart/form-data' : false,
							'action'	=> $this->action
						],
						$this->form_attributes
					)
				)
			);
		} elseif ($this->Content) {
			$Page->content($this->Content);
		}
	}
	/**
	 * Blocks processing
	 */
	protected function render_blocks () {
		$blocks			= Config::instance()->components['blocks'];
		/**
		 * It is frequent that there is no blocks - so, no need to to anything here
		 */
		if (!$blocks) {
			return;
		}
		$Page			= Page::instance();
		$blocks_array	= [
			'top'		=> '',
			'left'		=> '',
			'right'		=> '',
			'bottom'	=> ''
		];
		foreach ($blocks as $block) {
			if (
				!$block['active'] ||
				($block['expire'] && $block['expire'] < TIME) ||
				$block['start'] > TIME ||
				!(User::instance()->get_permission('Block', $block['index']))
			) {
				continue;
			}
			if (Trigger::instance()->run(
				'System/Index/block_render',
				[
					'index'			=> $block['index'],
					'blocks_array'	=> &$blocks_array
				]
			)) {
				$block['title']	= $this->ml_process($block['title']);
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
				$template	= TEMPLATES.'/blocks/block.'.(
					file_exists(TEMPLATES."/blocks/block.$block[template]") ? $block['template'] : 'default.html'
				);
				$content	= str_replace(
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
		$Page->Top		.= $blocks_array['top'];
		$Page->Left		.= $blocks_array['left'];
		$Page->Right	.= $blocks_array['right'];
		$Page->Bottom	.= $blocks_array['bottom'];
	}
	protected function ml_process ($text) {
		return Text::instance()->process(Config::instance()->module('System')->db('texts'), $text, true, true);
	}
	/**
	 * Saving changes and/or showing resulting message of saving changes
	 *
	 * @param bool|null	$result	If bool - result will be shown only, otherwise works similar to the $Config->save() and shows resulting message
	 *
	 * @return bool
	 */
	function save ($result = null) {
		$L		= Language::instance();
		$Page	= Page::instance();
		if ($result || ($result === null && Config::instance()->save())) {
			$this->post_title = $L->changes_saved;
			$Page->success($L->changes_saved);
			return true;
		} else {
			$this->post_title = $L->changes_save_error;
			$Page->warning($L->changes_save_error);
			return false;
		}
	}
	/**
	 * Applying changes and/or showing resulting message of applying changes
	 *
	 * @param bool|null|string	$result	If bool - result will be shown only, otherwise works similar to the $Config->apply() and shows resulting message
	 *
	 * @return bool
	 */
	function apply ($result = null) {
		$L		= Language::instance();
		$Page	= Page::instance();
		if ($result || ($result === null && Config::instance()->apply())) {
			$this->post_title = $L->changes_applied;
			$Page->success($L->changes_applied.$L->check_applied);
			return true;
		} else {
			$this->post_title = $L->changes_apply_error;
			$Page->warning($L->changes_apply_error);
			return false;
		}
	}
	/**
	 * Changes canceling and/or showing result of canceling changes
	 *
	 * @param bool	$system	If <b>true,/b> - cancels changes of system configuration, otherwise shows message about successful canceling
	 */
	function cancel ($system = true) {
		if ($system) {
			Config::instance()->cancel();
		}
		$L					= Language::instance();
		$this->post_title	= $L->changes_canceled;
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
	 * Executes plugins processing, blocks and module page generation
	 */
	function __finish () {
		/**
		 * Protection from double calling
		 */
		if ($this->called_once) {
			return;
		}
		$this->called_once	= true;
		$Config				= Config::instance();
		$Page				= Page::instance();
		/**
		 * If site is closed, user is not admin, and it is not request for sign in
		 */
		if (
			!$Config->core['site_mode'] &&
			!(
				User::instance()->admin() ||
				(
					api_path() && $Config->route === ['user', 'sign_in']
				)
			)
		) {
			code_header(503);
			return;
		}
		if (error_code()) {
			$Page->error();
		}
		Trigger::instance()->run('System/Index/preload');
		/**
		 * If module consists of index.html only
		 */
		if (!$this->in_admin && !$this->in_api && $this->module && file_exists(MODULES."/$this->module/index.html")) {
			ob_start();
			_include(MODULES."/$this->module/index.html", false, false);
			$Page->content(ob_get_clean());
			if ($this->title_auto) {
				$Page->title(Language::instance()->{home_page() ? 'home' : $this->module});
			}
		} elseif (!error_code()) {
			$this->init_auto	&& $this->init();
		}
		$this->render();
		if (error_code()) {
			$Page->error();
		}
		Trigger::instance()->run('System/Index/postload');
	}
}
