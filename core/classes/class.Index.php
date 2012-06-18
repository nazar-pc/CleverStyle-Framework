<?php
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
				$action,
				$buttons			= true,
				$save_button		= true,
				$apply_button		= true,
				$cancel_button		= ' disabled',
				$cancel_button_back	= false,
				$reset_button		= true,
				$post_buttons		= '',

				$init_auto			= true,
				$generate_auto		= true,
				$stop				= false;	//Gives the ability to stop further processing

	protected	$preload			= [],
				$postload			= [],

				$structure			= [],
				$parts				= [],
				$subparts			= [],
				$permission_group,

				$admin				= false,
				$module				= false,
				$api				= false;

	function __construct () {
		global $Config, $User;
		$admin_path	= MODULES.DS.MODULE.DS.'admin';
		$api_path	= MODULES.DS.MODULE.DS.'api';
		if (
			ADMIN &&
			_file_exists($admin_path) && (_file_exists($admin_path.DS.'index.php') || _file_exists($admin_path.DS.'index.json'))
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
			&& _file_exists($api_path) && (_file_exists($api_path.DS.'index.php') || _file_exists($api_path.DS.'index.json'))
		) {
			if (!$User->get_user_permission($this->permission_group = MODULE.'/api', 'index')) {
				define('ERROR_PAGE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', $api_path);
			$this->api		= true;
		} elseif (_file_exists(MODULES.DS.MODULE)) {
			if (!$User->get_user_permission($this->permission_group = MODULE, 'index')) {
				define('ERROR_PAGE', 403);
				$this->__finish();
				return;
			}
			define('MFOLDER', MODULES.DS.MODULE);
			$this->module	= true;
		} else {
			define('ERROR_PAGE', 404);
			$this->__finish();
			return;
		}
		unset($admin_path, $api_path);
		//Plugins processing
		foreach ($Config->components['plugins'] as $plugin) {
			_include(PLUGINS.DS.$plugin.DS.'index.php', true, false);
		}
		_include(MFOLDER.DS.'prepare.php', true, false);
	}
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
	}
	protected function init () {
		global $Config, $L, $Page, $User;
		$rc = &$Config->routing['current'];
		if (_file_exists(MFOLDER.DS.'index.json')) {
			$this->structure	= _json_decode(_file_get_contents(MFOLDER.DS.'index.json'));
			if (is_array($this->structure)) {
				foreach ($this->structure as $item => $value) {
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
		_include(MFOLDER.DS.'index.php', true, false);
		if ($this->stop) {
			return;
		}
		$this->admin && $Page->title($L->administration);
		if (!$this->api) {
			$Page->title($L->{HOME ? 'home' : MODULE});
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
			if (!$this->api) {
				if (!HOME) {
					$Page->title($L->$rc[0]);
				}
			}
			if ($this->admin && !_include(MFOLDER.DS.$rc[0].DS.$this->savefile.'.php', true, false)) {
				_include(MFOLDER.DS.$this->savefile.'.php', true, false);
			}
			_include(MFOLDER.DS.$rc[0].'.php', true, false);
			if ($this->stop) {
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
					if (!HOME) {
						$Page->title($L->$rc[1]);
					}
					$this->action = ($this->admin ? 'admin/' : '').MODULE.'/'.$rc[0].'/'.$rc[1];
				}
				_include(MFOLDER.DS.$rc[0].DS.$rc[1].'.php', true, false);
				if ($this->stop) {
					return;
				}
			} elseif (!$this->api) {
				$this->action = ($this->admin ? 'admin/' : '').MODULE.'/'.$rc[0];
			}
			unset($rc);
			if ($this->post_title) {
				$Page->title($this->post_title);
			}
		} elseif (!$this->api) {
			$this->action = $Config->server['corrected_full_address'];
			_include(MFOLDER.DS.$this->savefile.'.php', true, false);
		}
	}
	protected function mainmenu () {
		global $Config, $L, $Page, $User;
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
	}
	protected function mainsubmenu () {
		if (!is_array($this->parts) || !$this->parts) {
			return;
		}
		global $Config, $L, $Page;
		foreach ($this->parts as $part) {
			$Page->mainsubmenu .= h::a(
				$L->$part,
				[
					'id'		=> $part.'_a',
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$part,
					'class'		=> isset($Config->routing['current'][0]) && $Config->routing['current'][0] == $part ? 'active' : ''
				]
			);
		}
	}
	protected function menumore () {
		if (!is_array($this->subparts) || !$this->subparts) {
			return;
		}
		global $Config, $L, $Page;
		foreach ($this->subparts as $subpart) {
			$Page->menumore .= h::a(
				$L->$subpart,
				[
					'id'		=> $subpart.'_a',
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$Config->routing['current'][0].'/'.$subpart,
					'class'		=> $Config->routing['current'][1] == $subpart ? 'active' : ''
				]
			);
		}
	}
	protected function generate () {
		global $Page, $Config, $L, $Cache;
		if ($this->api) {
			interface_off();
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
					(isset($Config->routing['current'][1]) ? h::input(
						[
							'type'	=> 'hidden',
							'name'	=> 'subpart',
							'value'	=> $Config->routing['current'][1]
						]
					) : '').
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
						'method'	=> 'post',
						'enctype'	=> $this->file_upload ? 'multipart/form-data' : false,
						'action'	=> $this->action,
						'id'		=> 'admin_form',
						'class'		=> 'cs-admin-form'
					]+$this->form_atributes
				), 1
			);
		} else {
			$Page->content($this->Content);
		}
	}
	protected function js_vars () {
		if (!$this->api) {
			global $Config, $Page, $User, $L;
			$Page->js(
				'var continue_transfer = "'.$L->continue_transfer.'",'.
					'base_url = "'.$Config->server['base_url'].'",'.
					'current_base_url = "'.$Config->server['base_url'].'/'.($this->admin ? 'admin/' : '').MODULE.'",'.
					'yes = "'.$L->yes.'",'.
					'no = "'.$L->no.'",'.
					($User->is('guest') ?
						'auth_error_connection = "'.$L->auth_error_connection.'",'.
							'reg_connection_error = "'.$L->reg_error_connection.'",'.
							'please_type_your_email = "'.$L->please_type_your_email.'",'.
							'please_type_correct_email = "'.$L->please_type_correct_email.'",'.
							'reg_success = "'.$L->reg_success.'",'.
							'reg_confirmation = "'.$L->reg_confirmation.'",'.
							'reg_error_connection = "'.$L->reg_error_connection.'",'.
							'rules_agree = "'.$L->rules_agree.'",'.
							'rules_text = "'.$Config->core['rules'].'",'.
							'reg_success = "'.$L->reg_success.'",'.
							'reg_success_confirmation = "'.$L->reg_success_confirmation.'",'
						: '').
					(defined('DEBUG') && DEBUG && ($User->is('admin') || ($Config->can_be_admin && $Config->core['ip_admin_list_only'])) ?
						'objects = "'.$L->objects.'",'.
							'user_data = "'.$L->user_data.'",'.
							'queries = "'.$L->queries.'",'.
							'cookies = "'.$L->cookies.'",'
						: '').
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
	protected function blocks_processing () {
		global $Page, $Config, $User, $Cache;
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
			$block_cache = $Cache->{'blocks/'.$block['index']};
			if (!is_array($block_cache) || $block_cache['expire'] < TIME - $block['update']) {
				$block_cache			= [];
				switch ($block['type']) {
					default:
						ob_start();
						_include(BLOCKS.DS.'block.'.$block['type'].'.php', false, false);
						$content = ob_get_clean();
					break;
					case 'html':
					case 'raw_html':
						$content = $block['data'];
					break;
				}
				$template				= _file_exists(TEMPLATES.DS.'blocks'.DS.'block.'.$block['template']) ?
														TEMPLATES.DS.'blocks'.DS.'block.'.$block['template'] :
														TEMPLATES.DS.'blocks'.DS.'block.default.html';
				$block_cache['content']	= str_replace(
					['<!--id-->', '<!--title-->', '<!--content-->'],
					[$block['index'], $block['title'], $content],
					_file_get_contents($template)
				);
				if ($block['update'] > 0) {
					$block_cache['expire']				= TIME - $block['update'];
					$Cache->{'blocks/'.$block['index']}	= $block_cache;
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
	function cancel ($system = true) {
		global $L, $Page, $Config;
		$system && $Config->cancel();
		$this->post_title = $L->changes_canceled;
		$Page->notice($L->changes_canceled);
	}
	/**
	 * Adding functions for executing before initialization processing of modules
	 *
	 * @param Closure[] $closure
	 * @param bool $remove_others	Allows to remove others closures for preload
	 *
	 * @return bool
	 */
	function preload ($closure, $remove_others = false) {
		if ($remove_others) {
			$this->preload = [];
		}
		if ($closure instanceof Closure) {
			$this->preload[] = $closure;
			return true;
		}
		return false;
	}
	/**
	 * Adding functions for executing after generating processing of modules
	 *
	 * @param Closure[] $closure
	 * @param bool $remove_others	Allows to remove others closures for postload
	 *
	 * @return bool
	 */
	function postload ($closure, $remove_others = false) {
		if ($remove_others) {
			$this->postload = [];
		}
		if ($closure instanceof Closure) {
			$this->postload[] = $closure;
			return true;
		}
		return false;
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
	function __finish () {
		if (defined('ERROR_PAGE')) {
			$this->form = false;
			global $Page;
			$this->js_vars();
			$Page->error_page();
		}
		closure_process($this->preload);
		if (!$this->admin && !$this->api && _file_exists(MFOLDER.DS.'index.html')) {
			global $Page;
			ob_start();
			_include(MFOLDER.DS.'index.html', false, false);
			$Page->content(ob_get_clean());
		} else {
			$this->init_auto	&& $this->init();
		}
		if ($this->stop) {
			return;
		}
		if ($this->generate_auto) {
			$this->js_vars();
			$this->generate();
		}
		closure_process($this->postload);
	}
}