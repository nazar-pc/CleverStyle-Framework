<?php
class Index {
	public		$Content,

				$savecross			= false,

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
				$apply				= true,
				$cancel				= ' disabled',
				$cancel_back		= false,
				$reset				= true,
				$post_buttons		= '',

				$init_auto			= true,
				$generate_auto		= true,

				$admin				= false,
				$module				= false,
				$api				= false;

	protected	$preload			= [],
				$postload			= [],

				$structure			= [],
				$parts				= [],
				$subparts			= [],
				$triggers_reg		= false,
				$triggers,
				$permission_group;

	function __construct () {
		global $Config, $User;
		$admin_path	= MODULES.DS.MODULE.DS.'admin';
		$api_path	= MODULES.DS.MODULE.DS.'api';
		if (
			ADMIN && $User->is('admin') &&
			_file_exists($admin_path) && (_file_exists($admin_path.DS.'index.php') || _file_exists($admin_path.DS.'index.json'))
		) {
			if (!$User->permission($this->permission_group = MODULE.'/admin', 'index')) {
				define('ERROR_PAGE', 403);
				return;
			}
			define('MFOLDER', $admin_path);
			$this->form		= true;
			$this->admin	= true;
		} elseif (
			API
			&& _file_exists($api_path) && (_file_exists($api_path.DS.'index.php') || _file_exists($api_path.DS.'index.json'))
		) {
			if (!$User->permission($this->permission_group = MODULE.'/api', 'index')) {
				define('ERROR_PAGE', 403);
				return;
			}
			define('MFOLDER', $api_path);
			$this->api		= true;
		} else {
			if (!$User->permission($this->permission_group = MODULE, 'index')) {
				define('ERROR_PAGE', 403);
				return;
			}
			define('MFOLDER', MODULES.DS.MODULE);
			$this->module	= true;
		}
		unset($admin_path, $api_path);
		//Plugins processing
		foreach ($Config->components['plugins'] as $plugin) {
			_include(PLUGINS.DS.$plugin.DS.'index.php', true, false);
		}
		_include(MFOLDER.DS.'prepare.php', true, false);
	}
	protected function init () {
		global $Config, $L, $Page, $User;
		$rc = &$Config->routing['current'];
		if (_file_exists(MFOLDER.DS.'index.json')) {
			$this->structure	= _json_decode(_file_get_contents(MFOLDER.DS.'index.json'));
			if (is_array($this->structure)) {
				foreach ($this->structure as $item => $value) {
					if ($User->permission($this->permission_group, $item)) {
						$this->parts[] = $item;
						if (isset($rc[0]) && $item == $rc[0] && is_array($value)) {
							foreach ($value as $subpart) {
								if ($User->permission($this->permission_group, $item.'/'.$subpart)) {
									$this->subparts[] = $subpart;
								} elseif ($rc[1] == $subpart) {
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
		$this->admin && $Page->title($L->administration);
		if (!$this->api) {
			$Page->title($L->{HOME ? 'home' : MODULE});
		}
		if ($this->parts) {
			if (!isset($rc[0]) || ($rc[0] == '' && !empty($this->parts))) {
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
			} elseif (!$this->api) {
				$this->action = ($this->admin ? 'admin/' : '').MODULE.'/'.$rc[0];
			}
			unset($rc);
			if ($this->post_title) {
				$Page->title($this->post_title);
			}
		} elseif (!$this->api) {
			$this->action = $Config->server['current_url'];
			_include(MFOLDER.DS.$this->savefile.'.php', true, false);
		}
	}
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
	}
	protected function mainmenu () {
		global $Config, $L, $Page, $User;
		if ($User->is('admin')) {
			if ($Config->core['debug']) {
				$Page->mainmenu .= h::a(
					mb_substr($L->debug, 0, 1),
					array(
						 'onClick'	=> 'debug_window();',
						 'title'	=> $L->debug
					)
				);
			}
			$Page->mainmenu .= h::a(
				mb_substr($L->administration, 0, 1),
				array(
					 'href'		=> 'admin',
					 'title'	=> $L->administration
				)
			);
		}
		$Page->mainmenu .= h::a(
			$L->home,
			array(
				 'href'		=> '/',
				 'title'	=> $L->home
			)
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
				array(
					'id'		=> $part.'_a',
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$part,
					'class'		=> isset($Config->routing['current'][0]) && $Config->routing['current'][0] == $part ? 'active' : ''
				)
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
				array(
					'id'		=> $subpart.'_a',
					'href'		=> ($this->admin ? 'admin/' : '').MODULE.'/'.$Config->routing['current'][0].'/'.$subpart,
					'class'		=> $Config->routing['current'][1] == $subpart ? 'active' : '',
					'onClick'	=> $this->savecross && $this->form ? 'menuadmin(\''.$subpart.'\', false); return false;' : ''
				)
			);
		}
	}
	protected function generate () {
		global $Config, $L, $Page, $Cache;
		$this->menu_auto		&& $this->mainmenu();
		$this->submenu_auto		&& $this->mainsubmenu();
		$this->menumore_auto	&& $this->menumore();
		if (!$this->api) {
			global $User;
			$Page->js(
				'var save_before = "'.$L->save_before.'",'.
					'continue_transfer = "'.$L->continue_transfer.'",'.
					'base_url = "'.$Config->server['base_url'].'",'.
					'current_base_url = "'.$Config->server['base_url'].'/'.($this->admin ? 'admin/' : '').MODULE.
						(isset($Config->routing['current'][0]) ? '/'.$Config->routing['current'][0] : '').'",'.
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
					($Config->core['debug'] ?
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
					'routing = '._json_encode($Config->routing['current']).';',
				'code'
			);
		}
		if ($this->form) {
			$Page->content(
				h::form(
					$this->Content.
					(isset($Config->routing['current'][1]) ? h::input(
						array(
							'type'	=> 'hidden',
							'name'	=> 'subpart',
							'value'	=> $Config->routing['current'][1]
						)
					) : '').
					//Кнопка применить
					($this->apply && $this->buttons ?
						h::button(
							$L->apply,
							array(
								'name'			=> 'edit_settings',
								'data-title'	=> $L->apply_info,
								'id'			=> 'apply_settings',
								'type'			=> 'submit',
								'value'			=> 'apply',
								'add'			=> $Cache->cache ? '' : ' disabled'
							)
						)
					: '').
					//Кнопка сохранить
					($this->buttons ?
						h::button(
							$L->save,
							array(
								'name'			=> 'edit_settings',
								'data-title'	=> $L->save_info,
								'id'			=> 'save_settings',
								'type'			=> 'submit',
								'value'			=> 'save'
							)
						)
					: '').
					//Кнопка отмена (отменяет настройки или возвращает на предыдущую страницу)
					(($this->apply && $this->buttons) || $this->cancel_back ?
						h::button(
							$L->cancel,
							array(
								'name'			=> 'edit_settings',
								'id'			=> 'cancel_settings',
								'value'			=> 'cancel',
								'data-title'	=> $this->cancel_back ? '' : $L->cancel_info,
								'type'			=> $this->cancel_back ? 'button' : 'submit',
								'onClick'		=> $this->cancel_back ? 'history.go(-1);' : '',
								'add'			=> $this->cancel_back ? '' : $this->cancel
							)
						)
					: '').
					//Кнопка сбросить
					($this->buttons && $this->reset ?
						h::button(
							$L->reset,
							array(
								'id'			=> 'reset_settings',
								'data-title'	=> $L->reset_info,
								'type'			=> 'reset'
							)
						)
					: '').
					$this->post_buttons,
					array(
						'method'	=> 'post',
						'enctype'	=> $this->file_upload ? 'multipart/form-data' : false,
						'action'	=> $this->action,
						'id'		=> 'admin_form',
						'onReset'	=> 'save = 0;',
						'class'		=> 'admin_form'
					)+$this->form_atributes
				), 1
			);
		} else {
			$Page->content($this->Content);
		}
	}
	function save ($parts = null) {
		global $L, $Page, $Config;
		if ((($parts === null || is_array($parts) || in_array($parts, $Config->admin_parts)) && $Config->save($parts)) || $parts) {
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
		if (($parts === null && $Config->apply()) || $parts) {
			$this->post_title = $L->changes_applied;
			$Page->notice($L->changes_applied.$L->check_applied);
			$this->cancel = '';
			global $Page;
			$Page->js("\$(function(){save = true;});", 'code');
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
	 * @param bool $remove_others
	 */
	function preload ($closure, $remove_others = false) {
		if ($remove_others) {
			$this->preload = [];
		}
		$this->preload[] = $closure;
	}
	/**
	 * Adding functions for executing after generating processing of modules
	 *
	 * @param Closure[] $closure
	 * @param bool $remove_others
	 */
	function postload ($closure, $remove_others = false) {
		if ($remove_others) {
			$this->postload = [];
		}
		$this->postload[] = $closure;
	}
	/**
	 * Registration of triggers for actions
	 *
	 * @param array $trigger
	 * @param array|null $triggers
	 * @return bool
	 */
	function register_trigger ($trigger, &$triggers = null) {
		if ((!is_array($trigger) || empty($trigger)) && !($trigger instanceof Closure)) {
			return false;
		}
		if ($triggers === null) {
			$triggers = &$this->triggers;
		}
		if ($trigger instanceof Closure) {
			$triggers[] = $trigger;
			return true;
		}
		$return = true;
		foreach ($trigger as $item => $function) {
			if (!isset($triggers[$item])) {
				$triggers[$item] = [];
			}
			$return = $return && $this->register_trigger($function, $triggers[$item]);
		}
		return $return;
	}
	/**
	 * Running trigers for some actions
	 *
	 * @param string $action
	 * @param mixed $data
	 * @return bool
	 */
	function run_trigger ($action, $data = null) {
		if (!$this->triggers_reg) {
			global $Config;
			$modules = array_keys($Config->components['modules']);
			foreach ($modules as $module) {
				_include(MODULES.DS.$module.DS.'trigger.php', true, false);
			}
			unset($modules, $module);
			$plugins = get_list(PLUGINS, false, 'd');
			foreach ($plugins as $plugin) {
				_include(PLUGINS.DS.$plugin.DS.'trigger.php', true, false);
			}
			unset($plugins, $plugin);
			$this->triggers_reg = true;
		}
		$action = explode('/', $action);
		if (!is_array($action) || empty($action)) {
			return false;
		}
		$triggers = $this->triggers;
		foreach ($action as $item) {
			if (is_array($triggers) && isset($triggers[$item])) {
				$triggers = $triggers[$item];
			} else {
				return true;
			}
		}
		unset($action, $item);
		if (!is_array($triggers) || empty($triggers)) {
			return false;
		}
		$return = true;
		foreach ($triggers as $trigger) {
			if ($trigger instanceof Closure) {
				if ($data === null) {
					$return = $return && $trigger();
				} else {
					$return = $return && $trigger($data);
				}
			}
		}
		return $return;
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
	function __finish () {
		if (defined('ERROR_PAGE')) {
			$this->form = false;
			global $Error;
			$Error->page();
			return;
		}
		closure_process($this->preload);
		if (!$this->admin && !$this->api && _file_exists(MFOLDER.DS.'index.html')) {
			global $Page;
			$Page->content(_file_get_contents(MFOLDER.DS.'index.html'));
			return;
		}
		$this->init_auto		&& $this->init();
		$this->generate_auto	&& $this->generate();
		closure_process($this->postload);
	}
}