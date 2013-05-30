<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
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
 *  System/Index/preload
 *
 *  System/Index/postload
 */
class Index {
	public		$Content,

				$menu_auto			= true,
				$submenu_auto		= false,
				$menumore_auto		= false,

				$savefile			= 'save',
				$form				= false,
				$file_upload		= false,
				$form_atributes		= [],
				$action				= null,
				$buttons			= true,
				$save_button		= true,
				$apply_button		= true,
				$cancel_button		= ' disabled',
				$cancel_button_back	= false,
				$reset_button		= true,
				$post_buttons		= '',

				$init_auto			= true,
				$generate_auto		= true,
				$title_auto			= true,
				$stop				= false;	//Gives the ability to stop further processing

	protected	$post_title			= '',		//Appends to the end of title
				$structure			= [],
				$parts				= [],
				$subparts			= [],
				$permission_group,

				$admin				= false,
				$module				= false,
				$api				= false;
	/**
	 * Detecting module folder including of admin/api request type, including prepare file, including of plugins
	 */
	function __construct () {
		global $Config, $User, $Index;
		/**
		 * If site is closed, user is not admin, and it is not request for log in
		 */
		if (
			!$Config->core['site_mode'] &&
			!(
				$User->admin() ||
				(
					API && $Config->route === ['user', 'login']
				)
			)
		) {
			return;
		}
		$Index = $this;
		$admin_path	= MODULES.'/'.MODULE.'/admin';
		$api_path	= MODULES.'/'.MODULE.'/api';
		if (
			ADMIN &&
			file_exists($admin_path) && (file_exists($admin_path.'/index.php') || file_exists($admin_path.'/index.json'))
		) {
			if (!($User->admin() && $User->get_user_permission($this->permission_group = 'admin/'.MODULE, 'index'))) {
				define('ERROR_CODE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', $admin_path);
			$this->form		= true;
			$this->admin	= true;
		} elseif (
			API
			&& file_exists($api_path) && (file_exists($api_path.'/index.php') || file_exists($api_path.'/index.json'))
		) {
			if (!$User->get_user_permission($this->permission_group = 'api/'.MODULE, 'index')) {
				define('ERROR_CODE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', $api_path);
			$this->api		= true;
		} elseif (file_exists(MODULES.'/'.MODULE)) {
			if (!$User->get_user_permission($this->permission_group = MODULE, 'index')) {
				define('ERROR_CODE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', MODULES.'/'.MODULE);
			$this->module	= true;
		} else {
			define('ERROR_CODE', 404);
			$this->__finish();
			return;
		}
		unset($admin_path, $api_path);
		/**
		 * Plugins processing
		 */
		foreach ($Config->components['plugins'] as $plugin) {
			_include_once(PLUGINS.'/'.$plugin.'/index.php', false);
		}
		_include_once(MFOLDER.'/prepare.php', false);
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
		global $Config, $L, $Page, $User;
		$rc = &$Config->route;
		if ($Config->core['simple_admin_mode'] && file_exists(MFOLDER.'/index_simple.json')) {
			$structure_file	= 'index_simple.json';
		} else {
			$structure_file	= 'index.json';
		}
		if (file_exists(MFOLDER.'/'.$structure_file)) {
			$this->structure	= _json_decode(file_get_contents(MFOLDER.'/'.$structure_file));
			if (is_array($this->structure)) {
				foreach ($this->structure as $item => $value) {
					if (!is_array($value)) {
						$item	= $value;
					}
					if ($User->get_user_permission($this->permission_group, $item)) {
						$this->parts[] = $item;
						if (isset($rc[0]) && $item == $rc[0] && is_array($value)) {
							foreach ($value as $subpart) {
								if ($User->get_user_permission($this->permission_group, $item.'/'.$subpart)) {
									$this->subparts[] = $subpart;
								} elseif (isset($rc[1]) && $rc[1] == $subpart) {
									define('ERROR_CODE', 403);
									$this->__finish();
									return;
								}
							}
						}
					} elseif ($rc[0] == $item) {
						define('ERROR_CODE', 403);
						$this->__finish();
						return;
					}
				}
				unset($item, $value, $subpart);
			}
		}
		_include_once(MFOLDER.'/index.php', false);
		if ($this->stop || defined('ERROR_CODE')) {
			return;
		}
		if ($this->parts) {
			if (!isset($rc[0]) || $rc[0] == '') {
				if (API) {
					__finish();
				}
				$rc[0] = $this->parts[0];
				if (isset($this->structure[$rc[0]]) && is_array($this->structure[$rc[0]])) {
					$this->subparts = $this->structure[$rc[0]];
				}
			} elseif ($rc[0] != '' && !empty($this->parts) && !in_array($rc[0], $this->parts)) {
				define('ERROR_CODE', 404);
				$this->__finish();
				return;
			}
			/**
			 * Saving of changes
			 */
			if ($this->admin && !_include_once(MFOLDER.'/'.$rc[0].'/'.$this->savefile.'.php', false)) {
				_include_once(MFOLDER.'/'.$this->savefile.'.php', false);
			}
			$this->admin && $this->title_auto && $Page->title($L->administration);
			if (!$this->api && $this->title_auto) {
				$Page->title($L->{HOME ? 'home' : MODULE});
			}
			if (!$this->api) {
				if (!HOME && $this->title_auto) {
					$Page->title($L->$rc[0]);
				}
			}
			/**
			 * Warning if site is closed
			 */
			if (!$Config->core['site_mode']) {
				$Page->warning(get_core_ml_text('closed_title'));
			}
			_include_once(MFOLDER.'/'.$rc[0].'.php', false);
			if ($this->stop || defined('ERROR_CODE')) {
				return;
			}
			if ($this->subparts) {
				if (!isset($rc[1]) || ($rc[1] == '' && !empty($this->subparts))) {
					if (API) {
						__finish();
					}
					$rc[1] = $this->subparts[0];
				} elseif ($rc[1] != '' && !empty($this->subparts) && !in_array($rc[1], $this->subparts)) {
					define('ERROR_CODE', 404);
					$this->__finish();
					return;
				}
				if (!$this->api) {
					if (!HOME && $this->title_auto) {
						$Page->title($L->$rc[1]);
					}
					if ($this->action === null) {
						$this->action = ($this->admin ? 'admin/' : '').MODULE.'/'.$rc[0].'/'.$rc[1];
					}
				}
				_include_once(MFOLDER.'/'.$rc[0].'/'.$rc[1].'.php', false);
				if ($this->stop || defined('ERROR_CODE')) {
					return;
				}
			} elseif (!$this->api && $this->action === null) {
				$this->action = ($this->admin ? 'admin/' : '').MODULE.'/'.$rc[0];
			}
			unset($rc);
			if ($this->post_title && $this->title_auto) {
				$Page->title($this->post_title);
			}
		} elseif (!$this->api) {
			$this->admin && $Page->title($L->administration);
			if (!$this->api && $this->title_auto) {
				$Page->title($L->{HOME ? 'home' : MODULE});
			}
			if ($this->action === null) {
				$this->action = $Config->server['relative_address'];
			}
			_include_once(MFOLDER.'/'.$this->savefile.'.php', false);
		}
	}
	/**
	 * Rendering of main menu
	 */
	protected function mainmenu () {
		global $Config, $L, $Page, $User, $Core;
		if ($User->admin() || ($Config->can_be_admin && $Config->core['ip_admin_list_only'])) {
			if (defined('DEBUG') && DEBUG) {
				$Page->mainmenu .= h::a(
					mb_substr($L->debug, 0, 1),
					[
						 'onClick'	=> 'debug_window();',
						 'title'	=> $L->debug
					]
				);
			}
			$Page->mainmenu .= h::a(
				mb_substr($L->administration, 0, 1),
				[
					 'href'		=> 'admin',
					 'title'	=> $L->administration
				]
			);
		}
		$Page->mainmenu .= h::a(
			$L->home,
			[
				 'href'		=> '/',
				 'title'	=> $L->home
			]
		);
		foreach ($Config->components['modules'] as $module => $mdata) {
			if (
				$mdata['active'] == 1 &&
				$module != $Config->core['default_module'] &&
				$module != 'System' &&
				$User->get_user_permission($module, 'index') &&
				(
					(
						file_exists(MODULES.'/'.$module.'/index.php') && filesize(MODULES.'/'.$module.'/index.php')
					) ||
					(
						file_exists(MODULES.'/'.$module.'/index.html') && filesize(MODULES.'/'.$module.'/index.html')
					) ||
					file_exists(MODULES.'/'.$module.'/index.json')
				)
			) {
				$path			= $module;
				$title			= $L->$module;
				$hide			= false;
				$Core->run_trigger(
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
				$Page->mainmenu	.= h::a(
					$title,
					[
						'href'	=> $path,
						'title'	=> $title
					]
				);
			}
		}
	}
	/**
	 * Rendering of main submenu
	 */
	protected function mainsubmenu () {
		if (!is_array($this->parts) || !$this->parts) {
			return;
		}
		global $Config, $L, $Page;
		foreach ($this->parts as $part) {
			$Page->mainsubmenu .= h::a(
				$L->$part,
				[
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$part,
					'class'		=> isset($Config->route[0]) && $Config->route[0] == $part ? 'active' : ''
				]
			);
		}
	}
	/**
	 * Rendering of additional menu
	 */
	protected function menumore () {
		if (!is_array($this->subparts) || !$this->subparts) {
			return;
		}
		global $Config, $L, $Page;
		foreach ($this->subparts as $subpart) {
			$Page->menumore .= h::a(
				$L->$subpart,
				[
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$Config->route[0].'/'.$subpart,
					'class'		=> $Config->route[1] == $subpart ? 'active' : ''
				]
			);
		}
	}
	/**
	 * Module page generation, menus rendering, blocks processing, adding of form with save/apply/cancel/reset and/or custom users buttons
	 */
	protected function generate () {
		global $Page, $Config, $L, $Cache;
		if ($this->api) {
			$Page->content($this->Content);
			return;
		}
		$this->menu_auto		&& $this->mainmenu();
		$this->submenu_auto		&& $this->mainsubmenu();
		$this->menumore_auto	&& $this->menumore();
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
								'add'			=> $Cache->cache_state() ? '' : ' disabled'
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
								'data-title'	=> $this->cancel_button_back ? '' : $L->cancel_info,
								'type'			=> $this->cancel_button_back ? 'button' : 'submit',
								'onClick'		=> $this->cancel_button_back ? 'history.go(-1);' : '',
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
							'action'	=> $this->action,
							'class'		=> 'cs-fullwidth-form'
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
	 * Adds JavaScript variables with some system configuration information
	 *
	 * @return Index
	 */
	protected function js_vars () {
		if (!$this->api) {
			global $Config, $Page, $User, $L, $Core;
			$Page->js(
				'var	base_url = "'.$Config->base_url()."\",\n".
				'	current_base_url = "'.$Config->base_url().'/'.($this->admin ? 'admin/' : '').MODULE."\",\n".
				'	public_key = "'.$Core->public_key."\",\n".
				($User->guest() ?
					'	rules_text = "'.get_core_ml_text('rules')."\",\n"
				: '').
				'	module = "'.MODULE."\",\n".
				'	in_admin = '.(int)$this->admin.",\n".
				'	is_admin = '.(int)$User->admin().",\n".
				'	is_user = '.(int)$User->user().",\n".
				'	is_guest = '.(int)$User->guest().",\n".
				'	debug = '.(int)DEBUG.",\n".
				'	cookie_prefix = "'.$Config->core['cookie_prefix']."\",\n".
				'	cookie_domain = "'.$Config->core['cookie_domain']."\",\n".
				'	cookie_path = "'.$Config->core['cookie_path']."\",\n".
				'	protocol = "'.$Config->server['protocol']."\",\n".
				'	route = '._json_encode($Config->route).';',
				'code'
			);
			if (!$Config->core['cache_compress_js_css']) {
				$Page->js(
					'var	L = '.$L->get_json().';',
					'code'
				);
			}
		}
		return $this;
	}
	/**
	 * Blocks processing
	 */
	protected function blocks_processing () {
		global $Page, $Config, $User, $Text, $Core;
		$blocks_array = [
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
				!($User->get_user_permission('Block', $block['index']))
			) {
				continue;
			}
			if ($Core->run_trigger(
				'System/Index/block_render',
				[
					'block'			=> $block['index'],
					'blocks_array'	=> &$blocks_array
				]
			)) {
				switch ($block['type']) {
					default:
						$content = ob_wrapper(function () use ($block) {
							include BLOCKS.'/block.'.$block['type'].'.php';
						});
					break;
					case 'html':
					case 'raw_html':
						$content = $Text->process($Config->module('System')->db('texts'), $block['content'], true, true);
					break;
				}
				$template	= TEMPLATES.'/blocks/block.'.(
					file_exists(TEMPLATES.'/blocks/block.'.$block['template']) ? $block['template'] : 'default.html'
				);
				$content	= str_replace(
					[
						'<!--id-->',
						'<!--title-->',
						'<!--content-->'
					],
					[
						$block['index'],
						$Text->process($Config->module('System')->db('texts'), $block['title'], true, true),
						$content
					],
					ob_wrapper(function () use ($template) {
						_include($template);
					})
				);
				if ($block['position'] == 'floating') {
					$Page->replace(
						'<!--block#'.$block['index'].'-->',
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
		global $L, $Page, $Config;
		if ($result || ($result === null && $Config->save())) {
			$this->post_title = $L->changes_saved;
			$Page->notice($L->changes_saved);
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
		global $L, $Page, $Config;
		if ($result || ($result === null && $Config->apply())) {
			$this->post_title = $L->changes_applied;
			$Page->notice($L->changes_applied.$L->check_applied);
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
		global $L, $Page, $Config;
		if ($system) {
			$Config->cancel();
		}
		$this->post_title = $L->changes_canceled;
		$Page->notice($L->changes_canceled);
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
	/**
	 * Executes plugins processing, blocks and module page generation
	 */
	function __finish () {
		global $Config, $User, $Core, $Page;
		/**
		 * If site is closed, user is not admin, and it is not request for log in
		 */
		if (
			!$Config->core['site_mode'] &&
			!(
				$User->admin() ||
				(
					API && $Config->route === ['user', 'login']
				)
			)
		) {
			return;
		}
		if (defined('ERROR_CODE')) {
			$this->js_vars();
			$Page->error();
		}
		$Core->run_trigger('System/Index/preload');
		if (!$this->admin && !$this->api && file_exists(MFOLDER.'/index.html')) {
			global $L;
			ob_start();
			_include(MFOLDER.'/index.html', false, false);
			$Page->content(ob_get_clean());
			if ($this->title_auto) {
				$Page->title($L->{HOME ? 'home' : MODULE});
			}
		} else {
			$this->init_auto	&& $this->init();
		}
		if ($this->generate_auto) {
			$this->js_vars()->generate();
		}
		if ($this->stop) {
			if (!(
				API &&
				MODULE == 'System' &&
				_getcookie('logout') &&
				$Config->route == ['user', 'logout']
			)) {
				_setcookie('logout', '');
			}
			return;
		}
		if (defined('ERROR_CODE')) {
			$Page->error();
		} elseif (!(
			API &&
			MODULE == 'System' &&
			_getcookie('logout') &&
			$Config->route == ['user', 'logout']
		)) {
			_setcookie('logout', '');
		}
		$Core->run_trigger('System/Index/postload');
	}
}
/**
 * For IDE
 */
if (false) {
	global $Index;
	$Index = new Index;
}