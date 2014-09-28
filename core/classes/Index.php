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
 *  System/Index/mainmenu
 *  [
 *  	'path'	=> &$path,					//Reference to module path, can be changed by corresponding module
 *  	'title'	=> &title,					//Reference to module title, can be changed by corresponding module
 *  	'hide'	=> &$hide					//Reference to hide property, if set this to true (false by default) - menu element will not be displayed
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

	public	$main_menu_auto			= true;
	public	$main_sub_menu_auto		= false;
	public	$main_menu_more_auto	= false;

	public	$main_menu				= [];
	public	$main_sub_menu			= [];
	public	$main_menu_more			= [];

	public	$form					= false;
	public	$file_upload			= false;
	public	$form_atributes			= [];
	public	$action					= null;
	public	$buttons				= true;
	public	$save_button			= true;
	public	$apply_button			= true;
	public	$cancel_button			= ' disabled';
	public	$cancel_button_back		= false;
	public	$reset_button			= true;
	public	$post_buttons			= '';

	public $init_auto				= true;
	public $generate_auto			= true;
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

	protected	$post_title			= '';	//Appends to the end of title
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
	protected	$in_api				= false;
	protected	$in_admin			= false;
	protected	$request_method		= null;
	protected	$working_directory	= '';
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
			if (!($User->admin() && $User->get_permission($this->permission_group = "admin/$this->module", 'index'))) {
				error_code(403);
				exit;
			}
			$this->working_directory = $admin_path;
			$this->form		= true;
			$this->in_admin = true;
		} elseif ($api && file_exists($api_path)) {
			if (!$User->get_permission($this->permission_group = "api/$this->module", 'index')) {
				error_code(403);
				exit;
			}
			$this->working_directory = $api_path;
			$this->in_api		= true;
		} elseif (!admin_path() && !$api && file_exists(MODULES."/$this->module")) {
			if (!$User->get_permission($this->permission_group = $this->module, 'index')) {
				error_code(403);
				exit;
			}
			$this->working_directory = MODULES."/$this->module";
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
		if (preg_match('/[a-z_\-]+/i', $_SERVER['REQUEST_METHOD'])) {
			$this->request_method	= strtolower($_SERVER['REQUEST_METHOD']);
		}
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
		$User		= User::instance();
		$api		= api_path();
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
		$rc		= &$this->route_path;
		$working_directory = $this->working_directory;
		$structure_file = $Config->core['simple_admin_mode'] && file_exists("$working_directory/index_simple.json") ? 'index_simple.json' : 'index.json';
		if (file_exists("$working_directory/$structure_file")) {
			$this->structure	= file_get_json("$working_directory/$structure_file");
			if (is_array($this->structure)) {
				foreach ($this->structure as $item => $value) {
					if (!is_array($value)) {
						$item	= $value;
					}
					if ($User->get_permission($this->permission_group, $item)) {
						$this->parts[] = $item;
						if (isset($rc[0]) && $item == $rc[0] && is_array($value)) {
							foreach ($value as $subpart) {
								if ($User->get_permission($this->permission_group, "$item/$subpart")) {
									$this->subparts[] = $subpart;
								} elseif (isset($rc[1]) && $rc[1] == $subpart) {
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
		} elseif ($api && !file_exists("$working_directory/index.php") && !file_exists("$working_directory/index.$this->request_method.php")) {
			error_code(404);
			return;
		}
		unset($structure_file);
		_include_once("$working_directory/index.php", false);
		if ($api && $this->request_method) {
			_include_once("$working_directory/index.$this->request_method.php", false);
		}
		if (error_code()) {
			return;
		}
		if ($this->parts) {
			if (!isset($rc[0]) || $rc[0] == '') {
				if ($api) {
					return;
				}
				$rc[0] = $this->parts[0];
				if (isset($this->structure[$rc[0]]) && is_array($this->structure[$rc[0]])) {
					$this->subparts = $this->structure[$rc[0]];
				}
			} elseif ($rc[0] != '' && !empty($this->parts) && !in_array($rc[0], $this->parts)) {
				error_code(404);
				return;
			}
			/**
			 * Saving of changes
			 */
			if ($this->in_admin() && !_include_once("$working_directory/$rc[0]/save.php", false)) {
				_include_once("$working_directory/save.php", false);
			}
			if ($this->in_admin() && $this->title_auto) {
				$Page->title($L->administration);
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
			_include_once("$working_directory/$rc[0].php", false);
			if ($api && $this->request_method) {
				_include_once("$working_directory/$rc[0].$this->request_method.php", false);
			}
			if (error_code()) {
				return;
			}
			if ($this->subparts) {
				if (!isset($rc[1]) || ($rc[1] == '' && !empty($this->subparts))) {
					if ($api) {
						return;
					}
					$rc[1] = $this->subparts[0];
				} elseif ($rc[1] != '' && !empty($this->subparts) && !in_array($rc[1], $this->subparts)) {
					error_code(404);
					return;
				}
				if (!$this->in_api) {
					if (!home_page() && $this->title_auto) {
						$Page->title($L->$rc[1]);
					}
					if ($this->action === null) {
						$this->action = ($this->in_admin() ? 'admin/' : '')."$this->module/$rc[0]/$rc[1]";
					}
				}
				_include_once("$working_directory/$rc[0]/$rc[1].php", false);
				if ($api && $this->request_method) {
					_include_once("$working_directory/$rc[0]/$rc[1].$this->request_method.php", false);
				}
				if (error_code()) {
					return;
				}
			} elseif (!$this->in_api && $this->action === null) {
				$this->action = ($this->in_admin() ? 'admin/' : '')."$this->module/$rc[0]";
			}
			unset($rc);
			if ($this->post_title && $this->title_auto) {
				$Page->title($this->post_title);
			}
		} elseif (!$this->in_api) {
			if ($this->in_admin()) {
				$Page->title($L->administration);
			}
			if (!$this->in_api && $this->title_auto) {
				$Page->title($L->{home_page() ? 'home' : $this->module});
			}
			if ($this->action === null) {
				$this->action = $Config->server['relative_address'];
			}
			if ($this->in_admin()) {
				_include_once("$working_directory/save.php", false);
			}
		}
	}
	/**
	 * Rendering of data for main menu
	 */
	protected function main_menu () {
		$Config			= Config::instance();
		$L				= Language::instance();
		$User			= User::instance();
		if ($User->admin() || ($Config->can_be_admin && $Config->core['ip_admin_list_only'])) {
			if (DEBUG) {
				$this->main_menu[]	= [
					mb_substr($L->debug, 0, 1),
					[
						 'onClick'		=> 'cs.debug_window();',
						 'data-title'	=> $L->debug
					]
				];
			}
			$this->main_menu[]	= [
				mb_substr($L->administration, 0, 1),
				[
					 'href'			=> 'admin',
					 'data-title'	=> $L->administration
				]
			];
		}
		$this->main_menu[]	= [
			$L->home,
			[
				 'href'		=> '/',
				 'title'	=> $L->home
			]
		];
		foreach ($Config->components['modules'] as $module => $module_data) {
			if (
				$module_data['active'] == 1 &&
				$module != $Config->core['default_module'] &&
				$module != 'System' &&
				$User->get_permission($module, 'index') &&
				(
					(
						file_exists(MODULES."/$module/index.php") && filesize(MODULES."/$module/index.php")
					) ||
					(
						file_exists(MODULES."/$module/index.html") && filesize(MODULES."/$module/index.html")
					) ||
					file_exists(MODULES."/$module/index.json")
				)
			) {
				$title			= $L->$module;
				$path			= path($title);
				$hide			= false;
				Trigger::instance()->run(
					'System/Index/mainmenu',
					[
						'path'	=> &$path,
						'title'	=> &$title,
						'hide'	=> &$hide
					]
				);
				if ($hide) {
					continue;
				}
				$this->main_menu[]	= [
					$title,
					[
						'href'	=> $path,
						'title'	=> $title
					]
				];
			}
		}
	}
	/**
	 * Rendering of data for main sub menu
	 */
	protected function main_sub_menu () {
		if (!is_array($this->parts) || !$this->parts) {
			return;
		}
		$rc		= $this->route_path;
		$L		= Language::instance();
		foreach ($this->parts as $part) {
			$this->main_sub_menu[]	= [
				$L->$part,
				[
					'href'		=> ($this->in_admin() ? 'admin/' : '')."$this->module/$part",
					'class'		=> isset($rc[0]) && $rc[0] == $part ? 'uk-active' : ''
				]
			];
		}
	}
	/**
	 * Rendering of data for main menu more
	 */
	protected function main_menu_more () {
		if (!is_array($this->subparts) || !$this->subparts) {
			return;
		}
		$rc		= $this->route_path;
		$L		= Language::instance();
		foreach ($this->subparts as $subpart) {
			$this->main_menu_more[]	= [
				$L->$subpart,
				[
					'href'		=> ($this->in_admin() ? 'admin/' : '')."$this->module/$rc[0]/$subpart",
					'class'		=> $rc[1] == $subpart ? 'uk-active' : ''
				]
			];
		}
	}
	/**
	 * Module page generation, menus rendering, blocks processing, adding of form with save/apply/cancel/reset and/or custom users buttons
	 */
	protected function generate () {
		$Config	= Config::instance();
		$L		= Language::instance();
		$Page	= Page::instance();
		if ($this->in_api) {
			$Page->content($this->Content);
			return;
		}
		$this->main_menu_auto		&& $this->main_menu();
		$this->main_sub_menu_auto		&& $this->main_sub_menu();
		$this->main_menu_more_auto	&& $this->main_menu_more();
		$this->blocks_processing();
		if ($this->form) {
			$Page->content(
				h::form(
					$this->Content.
					//Apply button
					($this->apply_button && $this->buttons ?
						h::button(
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
						h::button(
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
						h::button(
							$L->cancel,
							[
								'name'			=> 'edit_settings',
								'id'			=> 'cancel_settings',
								'value'			=> 'cancel',
								'data-title'	=> $this->cancel_button_back ? false : $L->cancel_info,
								'type'			=> $this->cancel_button_back ? 'button' : 'submit',
								'onClick'		=> $this->cancel_button_back ? 'history.go(-1);' : false,
								'add'			=> $this->cancel_button_back ? '' : (isset($Config->core['cache_not_saved']) ? '' : $this->cancel_button)
							]
						)
					: '').
					//Reset button
					($this->buttons && $this->reset_button ?
						h::button(
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
						$this->form_atributes
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
	protected function blocks_processing () {
		$Config			= Config::instance();
		$Page			= Page::instance();
		$Text			= Text::instance();
		$blocks_array	= [
			'top'		=> '',
			'left'		=> '',
			'right'		=> '',
			'bottom'	=> ''
		];
		foreach ($Config->components['blocks'] as $block) {
			if (
				!$block['active'] ||
				($block['expire'] != 0 && $block['expire'] < TIME) ||
				$block['start'] > TIME ||
				!(User::instance()->get_permission('Block', $block['index']))
			) {
				continue;
			}
			$block['title']	= $Text->process($Config->module('System')->db('texts'), $block['title'], true, true);
			if (Trigger::instance()->run(
				'System/Index/block_render',
				[
					'block'			=> $block['index'],
					'blocks_array'	=> &$blocks_array
				]
			)) {
				switch ($block['type']) {
					default:
						$content = ob_wrapper(function () use ($block) {
							include BLOCKS."/block.$block[type].php";
						});
					break;
					case 'html':
					case 'raw_html':
						$content = $Text->process($Config->module('System')->db('texts'), $block['content'], true, true);
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
						_include($template);
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
		static $finished = false;
		if ($finished) {
			return;
		}
		$finished	= true;
		$Config		= Config::instance();
		$Page		= Page::instance();
		$api		= api_path();
		/**
		 * If site is closed, user is not admin, and it is not request for sign in
		 */
		if (
			!$Config->core['site_mode'] &&
			!(
				User::instance()->admin() ||
				(
					$api && $Config->route === ['user', 'sign_in']
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
		if (!$this->in_admin() && !$this->in_api && $this->module && file_exists(MODULES."/$this->module/index.html")) {
			ob_start();
			_include(MODULES."/$this->module/index.html", false, false);
			$Page->content(ob_get_clean());
			if ($this->title_auto) {
				$Page->title(Language::instance()->{home_page() ? 'home' : $this->module});
			}
		} elseif (!error_code()) {
			$this->init_auto	&& $this->init();
		}
		if ($this->generate_auto) {
			$this->generate();
		}
		if (error_code()) {
			$Page->error();
		} elseif (
			_getcookie('sign_out') &&
			!(
				$api &&
				$this->module == 'System' &&
				$Config->route == ['user', 'sign_out']
			)
		) {
			_setcookie('sign_out', '');
		}
		Trigger::instance()->run('System/Index/postload');
	}
}
