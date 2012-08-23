<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			\h;
/**
 * Provides next triggers:<br>
 *  System/Index/mainmenu<br>
 *  ['module'	=> <i>&$path</i>]		//Reference to module path, can be changed by corresponding module<br>
 *  System/Index/preload<br>
 *  System/Index/postload
 */
class Index {
	public		$Content,

				$menu_auto			= true,
				$submenu_auto		= false,
				$menumore_auto		= false,

				$savefile			= 'save',
				$post_title			= '',
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

	protected	$structure			= [],
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
				$User->is('admin') ||
				(
					API && $Config->routing['current'] === ['user', 'login']
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
			if (!($User->is('admin') && $User->get_user_permission($this->permission_group = MODULE.'/admin', 'index'))) {
				define('ERROR_PAGE', 403);
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
			if (!$User->get_user_permission($this->permission_group = MODULE.'/api', 'index')) {
				define('ERROR_PAGE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', $api_path);
			$this->api		= true;
		} elseif (file_exists(MODULES.'/'.MODULE)) {
			if (!$User->get_user_permission($this->permission_group = MODULE, 'index')) {
				define('ERROR_PAGE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', MODULES.'/'.MODULE);
			$this->module	= true;
		} else {
			define('ERROR_PAGE', 404);
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
	 */
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
	}
	/**
	 * Initialization: loading of module structure, including of necessary module files
	 */
	protected function init () {
		global $Config, $L, $Page, $User;
		$rc = &$Config->__get('routing')['current'];
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
									define('ERROR_PAGE', 403);
									$this->__finish();
									return;
								}
							}
						}
					} elseif ($rc[0] == $item) {
						define('ERROR_PAGE', 403);
						$this->__finish();
						return;
					}
				}
				unset($item, $value, $subpart);
			}
		}
		_include_once(MFOLDER.'/index.php', false);
		if ($this->stop || defined('EROOR_PAGE')) {
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
				define('ERROR_PAGE', 404);
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
			if ($this->stop || defined('EROOR_PAGE')) {
				return;
			}
			if ($this->subparts) {
				if (!isset($rc[1]) || ($rc[1] == '' && !empty($this->subparts))) {
					if (API) {
						__finish();
					}
					$rc[1] = $this->subparts[0];
				} elseif ($rc[1] != '' && !empty($this->subparts) && !in_array($rc[1], $this->subparts)) {
					define('ERROR_PAGE', 404);
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
				if ($this->stop || defined('EROOR_PAGE')) {
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
				$this->action = $Config->server['corrected_full_address'];
			}
			_include_once(MFOLDER.'/'.$this->savefile.'.php', false);
		}
	}
	/**
	 * Rendering of main menu
	 */
	protected function mainmenu () {
		global $Config, $L, $Page, $User, $Core;
		if ($User->is('admin') || ($Config->can_be_admin && $Config->core['ip_admin_list_only'])) {
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
				$Core->run_trigger(
					'System/Index/mainmenu',
					[
						'module'	=> &$path
					]
				);
				$Page->mainmenu	.= h::a(
					$L->$module,
					[
						'href'	=> '/'.$path,
						'title'	=> $L->$module
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
					'class'		=> isset($Config->routing['current'][0]) && $Config->routing['current'][0] == $part ? 'active' : ''
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
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$Config->routing['current'][0].'/'.$subpart,
					'class'		=> $Config->routing['current'][1] == $subpart ? 'active' : ''
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
					[
						'enctype'	=> $this->file_upload ? 'multipart/form-data' : false,
						'action'	=> $this->action,
						'class'		=> 'cs-fullwidth-form'
					]+$this->form_atributes
				)
			);
		} elseif ($this->Content) {
			$Page->content($this->Content);
		}
	}
	/**
	 * Adds JavaScript variables with some system configuration information
	 */
	protected function js_vars () {
		if (!$this->api) {
			global $Config, $Page, $User, $L, $Core;
			$Page->js(
				'var base_url = "'.$Config->server['base_url'].'",'.
					'current_base_url = "'.$Config->server['base_url'].'/'.($this->admin ? 'admin/' : '').MODULE.'",'.
					'public_key = "'.$Core->config('public_key').'",'.
					'yes = "'.$L->yes.'",'.
					'no = "'.$L->no.'",'.
					($User->is('guest') ?
							'auth_error_connection = "'.$L->auth_error_connection.'",'.
							'please_type_your_email = "'.$L->please_type_your_email.'",'.
							'reg_success = "'.$L->reg_success.'",'.
							'reg_confirmation = "'.$L->reg_confirmation.'",'.
							'reg_error_connection = "'.$L->reg_error_connection.'",'.
							'rules_agree = "'.$L->rules_agree.'",'.
							'rules_text = "'.get_core_ml_text('rules').'",'.
							'restore_password_confirmation = "'.$L->restore_password_confirmation.'",'
						: '').
					($User->is('user') ?
							'please_type_current_password = "'.$L->please_type_current_password.'",'.
							'please_type_new_password = "'.$L->please_type_new_password.'",'.
							'current_new_password_equal = "'.$L->current_new_password_equal.'",'.
							'password_changed_successfully = "'.$L->password_changed_successfully.'",'.
							'password_changing_error_connection = "'.$L->password_changing_error_connection.'",'
						: ''
					).
					'language = "'.$L->clanguage.'",'.
					'language_en = "'.$L->clanguage_en.'",'.
					'lang = "'.$L->clang.'",'.
					'module = "'.MODULE.'",'.
					'in_admin = '.(int)$this->admin.','.
					'debug = '.(int)(defined('DEBUG') && DEBUG).','.
					'session_id = "'.$User->get_session().'",'.
					'cookie_prefix = "'.$Config->core['cookie_prefix'].'",'.
					'cookie_domain = "'.$Config->core['cookie_domain'].'",'.
					'cookie_path = "'.$Config->core['cookie_path'].'",'.
					'protocol = "'.$Config->server['protocol'].'",'.
					'routing = '._json_encode($Config->routing['current']).';',
				'code'
			);
		}
	}
	/**
	 * Blocks processing
	 */
	protected function blocks_processing () {
		global $Page, $Config, $User, $Cache, $Text, $L;
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
			$block_cache = $Cache->{'blocks/'.$block['index'].'_'.$L->clang};
			if (!is_array($block_cache) || (isset($block_cache['expire']) && $block_cache['expire'] < TIME - $block['update'])) {
				$block_cache			= [];
				switch ($block['type']) {
					default:
						ob_start();
						_include(BLOCKS.'/block.'.$block['type'].'.php', false, false);
						$content = ob_get_clean();
					break;
					case 'html':
					case 'raw_html':
						$content = $Text->process($Config->module('System')->db('texts'), $block['content']);
					break;
				}
				$template				= file_exists(TEMPLATES.'/blocks/block.'.$block['template']) ?
														TEMPLATES.'/blocks/block.'.$block['template'] :
														TEMPLATES.'/blocks/block.default.html';
				$block_cache['content']	= str_replace(
					[
						'<!--id-->',
						'<!--title-->',
						'<!--content-->'
					],
					[
						$block['index'],
						$Text->process($Config->module('System')->db('texts'), $block['title']),
						$content
					],
					file_get_contents($template)
				);
				if ($block['update'] > 0) {
					$block_cache['expire']				= TIME + $block['update'];
					$Cache->{'blocks/'.$block['index'].'_'.$L->clang}	= $block_cache;
				}
			}
			if ($block['position'] == 'floating') {
				$Page->replace(
					'<!--block#'.$block['index'].'-->',
					$block_cache['content']
				);
			} else {
				$blocks_array[$block['position']] .= $block_cache['content'];
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
	 * @param bool|null|string	$parts	If bool - result will be shown only, otherwise works similar to the $Config->save() and shows resulting message
	 *
	 * @return bool
	 */
	function save ($parts = null) {
		global $L, $Page, $Config;
		if ($parts === true || (($parts === null || is_array($parts) || in_array($parts, $Config->admin_parts)) && $Config->save($parts))) {
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
	 * @param bool|null|string	$parts	If bool - result will be shown only, otherwise works similar to the $Config->apply() and shows resulting message
	 *
	 * @return bool
	 */
	function apply ($parts = null) {
		global $L, $Page, $Config;
		if ($parts === true || ($parts === null && $Config->apply())) {
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
		$system && $Config->cancel();
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
				$User->is('admin') ||
				(
					API && $Config->routing['current'] === ['user', 'login']
				)
			)
		) {
			return;
		}
		if (defined('ERROR_PAGE')) {
			$this->js_vars();
			$Page->error_page();
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
		if ($this->stop) {
			return;
		}
		if (defined('ERROR_PAGE')) {
			$Page->error_page();
		}
		if ($this->generate_auto) {
			$this->js_vars();
			$this->generate();
		}
		$Core->run_trigger('System/Index/postload');
	}
}