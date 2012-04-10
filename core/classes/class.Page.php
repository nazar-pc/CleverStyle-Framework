<?php
class Page {
	public		$Content, $interface = true,
				$Html = '', $Keywords = '', $Description = '', $Title = [],
				$debug_info = '',
				$Head		= '',
				$pre_Body	= '',
					$Header	= '',
						$mainmenu = '', $mainsubmenu = '', $menumore = '',
					$Left	= '',
					$Top	= '',
					$Right	= '',
					$Bottom	= '',
					$Footer	= '',
				$post_Body	= '',
				$level		= [					//Number of tabs by default for margins the substitution
					'Head'				=> 2,	//of values into template
					'pre_Body'			=> 2,
					'Header'			=> 4,
					'mainmenu'			=> 3,
					'mainsubmenu'		=> 3,
					'menumore'			=> 3,
					'user_avatar_text'	=> 5,
					'user_info'			=> 5,
					'debug_info'		=> 3,
					'Left'				=> 3,
					'Top'				=> 3,
					'Content'			=> 8,
					'Bottom'			=> 3,
					'Right'				=> 3,
					'Footer'			=> 4,
					'post_Body'			=> 2
				];

	protected	$init = false,										//For single initialization
				$secret,											//Secret random phrase for separating internal
																	//function calling from external ones
				$theme, $color_scheme, $pcache_basename, $includes,
				$user_avatar_image, $user_avatar_text, $user_info,
				$core_js	= [0 => '', 1 => ''],
				$core_css	= [0 => '', 1 => ''],
				$js			= [0 => '', 1 => ''],
				$css		= [0 => '', 1 => ''],
				$Search		= [],
				$Replace	= [];

	function __construct () {
		global $interface;
		$this->interface = (bool)$interface;
		unset($GLOBALS['interface']);
		$this->secret = uniqid();
	}
	function init ($name, $keywords, $description, $theme, $color_scheme) {
		$this->theme = $theme;
		$this->color_scheme = $color_scheme;
		if ($this->init) {
			return;
		}
		$this->init = true;
		$this->Title[0] = htmlentities($name, ENT_COMPAT, CHARSET);
		$this->Keywords = $keywords;
		$this->Description = $description;
	}
	function content ($add, $level = false) {
		if ($level !== false) {
			$this->Content .= h::level($add, $level);
		} else {
			$this->Content .= $add;
		}
	}
	//Загрузка и обработка темы оформления, подготовка шаблона
	protected function load($stop) {
		global $Config, $L;
		//Определение темы оформления
		if (
			is_object($Config) && $Config->core['allow_change_theme'] &&
			_getcookie('theme') && in_array(_getcookie('theme'), $Config->core['active_themes'])
		) {
			$this->theme = _getcookie('theme');
		}
		if (is_object($Config) && $Config->core['site_mode']) {
			if (
				$Config->core['allow_change_theme'] && _getcookie('color_scheme') &&
				in_array(_getcookie('color_scheme'), $Config->core['color_schemes'])
			) {
				$this->color_scheme = _getcookie('color_scheme');
			}
		}
		//Задание названия файлов кеша
		$this->pcache_basename = '_'.$this->theme.' '.$this->color_scheme.'_'.$L->clang.'.';
		//Загрузка шаблона
		if ($this->interface) {
			ob_start();
			if (
				is_object($Config) && !$stop && $Config->core['site_mode'] &&
				(_file_exists(THEMES.DS.$this->theme.DS.'index.html') || _file_exists(THEMES.DS.$this->theme.DS.'index.php'))
			) {
				_require(THEMES.DS.$this->theme.DS.'prepare.php', true, false);
				if (!_include(THEMES.DS.$this->theme.DS.'index.php', true, false)) {
					_include(THEMES.DS.$this->theme.DS.'index.html', true);
				}
			} elseif ($stop == 1 && _file_exists(THEMES.DS.$this->theme.DS.'closed.html')) {
				_include(THEMES.DS.$this->theme.DS.'closed.html', 1);
			} elseif ($stop == 2 && _file_exists(THEMES.DS.$this->theme.DS.'error.html')) {
				_include(THEMES.DS.$this->theme.DS.'error.html', 1);
			} else {
				echo	"<!doctype html>\n".
						"<html>\n".
						"	<head>\n".
						"<!--head-->\n".
						"	</head>\n".
						"	<body>\n".
						"<!--content-->\n".
						"	</body>\n".
						"</html>";
			}
			$this->Html = ob_get_clean();
		}
	}
	//Обработка шаблона и подготовка данных к выводу
	protected function prepare ($stop) {
		global $copyright, $L, $Config;
		//Загрузка настроек оформления и шаблона темы
		$this->load($stop);
		//Загрузка стилей и скриптов
		$this->load_includes();
		//Загрузка данных о пользователе
		$this->get_header_info();
		//Формирование заголовка
		if (!$stop) {
			foreach ($this->Title as $i => $v) {
				if (!trim($v)) {
					unset($this->Title[$i]);
				} else {
					$this->Title[$i] = trim($v);
				}
			}
			if (is_object($Config)) {
				$this->Title = $Config->core['title_reverse'] ? array_reverse($this->Title) : $this->Title;
				$this->Title = implode(' '.trim($Config->core['title_delimiter']).' ', $this->Title);
			} else {
				$this->Title = $this->Title[0];
			}
		}
		//Формирование содержимого <head>
		if ($this->core_css[1]) {
			$this->core_css[1] = h::style($this->core_css[1]);
		}
		if ($this->css[1]) {
			$this->css[1] = h::style($this->css[1]);
		}
		if ($this->core_js[1]) {
			$this->core_js[1] = h::script($this->core_js[1]);
		}
		if ($this->js[1]) {
			$this->js[1] = h::script($this->js[1]);
		}
		$this->Head =	h::title($this->Title).
						h::meta(array('name'		=> 'keywords',			'content'	=> $this->Keywords)).
						h::meta(array('name'		=> 'description',		'content'	=> $this->Description)).
						h::meta(array('name'		=> 'generator',			'content'	=> $copyright[0])).
						h::link(
							array(
								'rel'		=> 'shortcut icon',
								'href'		=> _file_exists(THEMES.'/'.$this->theme.'/'.$this->color_scheme.'/'.'img/favicon.ico') ?
												'themes/'.$this->theme.'/'.$this->color_scheme.'/img/favicon.ico' :
												_file_exists(THEMES.'/'.$this->theme.'/img/favicon.ico') ?
												'themes/'.$this->theme.'/img/favicon.ico' :
												'includes/img/favicon.ico'
						)).
						(is_object($Config) ? h::base($Config->server['base_url']) : '').
						$this->Head.
						implode('', $this->core_css).
						implode('', $this->css).
						implode('', $this->core_js).
						implode('', $this->js);
		$this->Footer .= $this->footer($stop);
		//Подстановка контента в шаблон
		$construct['in'] = array(
								'<!--html_lang-->',
								'<!--head-->',
								'<!--pre_Body-->',
								'<!--header-->',
								'<!--main-menu-->',
								'<!--main-submenu-->',
								'<!--menu-more-->',
								'<!--user_avatar_image-->',
								'<!--user_avatar_text-->',
								'<!--user_info-->',
								'<!--left_blocks-->',
								'<!--top_blocks-->',
								'<!--content-->',
								'<!--bottom_blocks-->',
								'<!--right_blocks-->',
								'<!--footer-->',
								'<!--post_Body-->'
							);
		$construct['out'] = array(
									$L->clang,
									h::level($this->Head, $this->level['Head']),
									h::level($this->pre_Body, $this->level['pre_Body']),
									h::level($this->Header, $this->level['Header']),
									h::level($this->mainmenu, $this->level['mainmenu']),
									h::level($this->mainsubmenu, $this->level['mainsubmenu']),
									h::level($this->menumore, $this->level['menumore']),
									$this->user_avatar_image,
									h::level($this->user_avatar_text, $this->level['user_avatar_text']),
									h::level($this->user_info, $this->level['user_info']),
									h::level($this->Left, $this->level['Left']),
									h::level($this->Top, $this->level['Top']),
									h::level($this->Content, $this->level['Content']),
									h::level($this->Bottom, $this->level['Bottom']),
									h::level($this->Right, $this->level['Right']),
									h::level($this->Footer, $this->level['Footer']),
									h::level($this->post_Body, $this->level['post_Body'])
								 );
		$this->Html = str_replace($construct['in'], $construct['out'], $this->Html);
	}
	//Задание елементов замены в исходном коде
	/**
	 * @param array|string $search
	 * @param array|string $replace
	 */
	function replace ($search, $replace = '') {
		if (is_array($search)) {
			foreach ($search as $i => $val) {
				$this->Search[] = '/'.trim($val, '/').'/';
				$this->Replace[] = is_array($replace) ? $replace[$i] : $replace;
			}
		} else {
			if (mb_substr($search, 0, 1) != '/') {
				$search = '/'.$search.'/';
			}
			$this->Search[] = $search;
			$this->Replace[] = $replace;
		}
	}
	//Добавление ссылок на подключаемые JavaScript файлы
	function js ($add, $mode = 'file', $secret = false) {
		if (is_array($add)) {
			foreach ($add as $script) {
				if ($script) {
					$this->js($script, $mode, $secret);
				}
			}
		} elseif ($add) {
			if ($secret == $this->secret) {
				if ($mode == 'file') {
					$this->core_js[0] .= h::script(array('type'	=> 'text/javascript', 'src'	=> $add, 'level'	=> false))."\n";
				} elseif ($mode == 'code') {
					$this->core_js[1] .= $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->js[0] .= h::script(array('type'	=> 'text/javascript', 'src'	=> $add, 'level'	=> false))."\n";
				} elseif ($mode == 'code') {
					$this->js[1] .= $add."\n";
				}
			}
		}
	}
	//Добавление ссылок на подключаемые CSS стили
	function css ($add, $mode = 'file', $secret = false) {
		if (is_array($add)) {
			foreach ($add as $style) {
				if ($style) {
					$this->css($style, $mode, $secret);
				}
			}
		} elseif ($add) {
			if ($secret == $this->secret) {
				if ($mode == 'file') {
					$this->core_css[0] .= h::link(array('type'	=> 'text/css', 'href'	=> $add, 'rel'	=> 'stylesheet'));
				} elseif ($mode == 'code') {
					$this->core_css[1] = $add."\n";
				}
			} else {
				if ($mode == 'file') {
					$this->css[0] .= h::link(array('type'	=> 'text/css', 'href'	=> $add, 'rel'	=> 'stylesheet'));
				} elseif ($mode == 'code') {
					$this->css[1] = $add."\n";
				}
			}
		}
	}
	//Добавление данных в заголовок страницы (для избежания случайной перезаписи всего заголовка)
	function title ($add) {
		$this->Title[] = htmlentities($add, ENT_COMPAT, CHARSET);
	}
	//Подключение JavaScript и CSS файлов
	protected function load_includes () {
		global $Config;
		if (!is_object($Config)) {
			return;
		}
		if ($Config->core['cache_compress_js_css']) {
			//Проверка текущего кеша
			if (
				!_file_exists(PCACHE.DS.$this->pcache_basename.'css') ||
				!_file_exists(PCACHE.DS.$this->pcache_basename.'js') ||
				!_file_exists(PCACHE.DS.'pcache_key')
			) {
				$this->rebuild_cache();
			}
			$key = _file_get_contents(PCACHE.DS.'pcache_key');
			//Подключение CSS стилей
			$css_list = get_list(PCACHE, '/^[^_](.*)\.css$/i', 'f', 'storages/pcache');
			if (DS != '/') {
				$css_list = str_replace(DS, '/', $css_list);
			}
			$css_list = array_merge(array('storages/pcache/'.$this->pcache_basename.'css'), $css_list);
			foreach ($css_list as &$file) {
				$file .= '?'.$key;
			}
			unset($file);
			$this->css($css_list, 'file', $this->secret);
			//Подключение JavaScript
			$js_list = get_list(PCACHE, '/^[^_](.*)\.js$/i', 'f', 'storages/pcache');
			if (DS != '/') {
				$js_list = str_replace(DS, '/', $js_list);
			}
			$js_list = array_merge(array('storages/pcache/'.$this->pcache_basename.'js'), $js_list);
			foreach ($js_list as &$file) {
				$file .= '?'.$key;
			}
			unset($file);
			$this->js($js_list, 'file', $this->secret);
		} else {
			$this->get_includes_list();
			//Подключение CSS стилей
			foreach ($this->includes['css'] as $file) {
				$this->css($file, 'file', $this->secret);
			}
			//Подключение JavaScript
			foreach ($this->includes['js'] as $file) {
				$this->js($file, 'file', $this->secret);
			}
		}
	}
	//Загрузка списка JavaScript и CSS файлов
	protected function get_includes_list ($for_cache = false) {
		$theme_folder	= THEMES.DS.$this->theme;
		$scheme_folder	= $theme_folder.DS.'schemes'.DS.$this->color_scheme;
		$theme_pfolder	= 'themes/'.$this->theme;
		$scheme_pfolder	= $theme_pfolder.'/schemes/'.$this->color_scheme;
		$this->includes = array(
			'css' => array_merge(
				(array)get_list(INCLUDES.DS.'css',			'/(.*)\.css$/i',	'f', $for_cache ? true : 'includes/css',			true, false, '!include'),
				(array)get_list($theme_folder.DS.'css',		'/(.*)\.css$/i',	'f', $for_cache ? true : $theme_pfolder.'/css',		true, false, '!include'),
				(array)get_list($scheme_folder.DS.'css',	'/(.*)\.css$/i',	'f', $for_cache ? true : $scheme_pfolder.'/css',	true, false, '!include')
			),
			'js' => array_merge(
				(array)get_list(INCLUDES.DS.'js',			'/(.*)\.js$/i',		'f', $for_cache ? true : 'includes/js',				true, false, '!include'),
				(array)get_list($theme_folder.DS.'js',		'/(.*)\.js$/i',		'f', $for_cache ? true : $theme_pfolder.'/js',		true, false, '!include'),
				(array)get_list($scheme_folder.DS.'js',		'/(.*)\.js$/i',		'f', $for_cache ? true : $scheme_pfolder.'/js',		true, false, '!include')
			)
		);
		unset($theme_folder, $scheme_folder, $theme_pfolder, $scheme_pfolder);
		if (!$for_cache && DS != '/') {
			$this->includes = str_replace(DS, '/', $this->includes);
		}
		sort($this->includes['css']);
		sort($this->includes['js']);
	}
	//Перестройка кеша JavaScript и CSS
	function rebuild_cache () {
		$this->get_includes_list(true);
		$key = '';
		foreach ($this->includes as $extension => &$files) {
			$temp_cache = '';
			foreach ($files as $file) {
				if (_file_exists($file)) {
					$current_cache = _file_get_contents($file);
					if ($extension == 'css') {
						$this->images_substitution($current_cache, $file);
					}
					$temp_cache .= $current_cache."\n";
					unset($current_cache);
				}
			}
			_file_put_contents(PCACHE.DS.$this->pcache_basename.$extension, gzencode($temp_cache, 9), LOCK_EX|FILE_BINARY);
			$key .= md5($temp_cache);
		}
		_file_put_contents(PCACHE.DS.'pcache_key', mb_substr(md5($key), 0, 5), LOCK_EX|FILE_BINARY);
	}
	//Подстановка изображений при сжатии CSS
	protected function images_substitution (&$data, $file) {
		_chdir(_dirname($file));
		preg_replace_callback(
			'/url\((.*?)\)/',
			function ($link) use (&$data) {
				$link[0] = trim($link[1], '\'" ');	//array(0 - фильтрованный адрес, 1 - исходные данные)
				$format = substr($link[0], -3);
				if ($format == 'peg' && substr($link[0], -4) == 'jpeg') {
					$format = 'jpg';
				}
				if (($format == 'jpg' || $format == 'png' || $format == 'gif') && _file_exists(_realpath($link[0]))) {
					$data = str_replace($link[1], 'data:image/'.$format.';base64,'.base64_encode(_file_get_contents(_realpath($link[0]))), $data);
				} elseif ($format == 'css' && _file_exists(_realpath($link[0]))) {
					$data = str_replace($link[1], 'data:text/'.$format.';base64,'.base64_encode(_file_get_contents(_realpath($link[0]))), $data);
				}
			},
			$data
		);
		_chdir(DIR);
	}
	//Генерирование информации о процессе загрузки страницы
	protected function footer ($stop) {
		global $copyright, $L, $db;
		if (!($copyright && is_array($copyright))) {
			exit;
		}
		$footer = h::div($copyright[1].h::br().$copyright[2], array('id'	=> 'copyright'));
		if (!$stop) {
			$footer =	h::div(
							$L->page_generated.' <!--generate time--> '.
							', '.(is_object($db) ? $db->queries : 0).' '.$L->queries_to_db.' '.$L->during.' '.format_time((is_object($db) ? round($db->time, 5) : 0)).
							', '.$L->peak_memory_usage.' <!--peak memory usage-->',
							array('id'	=> 'execution_info')
						).
						$footer;
		}
		return $footer;
	}
	//Сбор и отображение отладочных данных
	protected function debug () {
		global $Config, $L, $db;
		$span = h::{'span.ui-icon.ui-icon-triangle-1-e'}(
			array(
				 'style'	=> 'display: inline-block;',
				 'level'	=> 0
			)
		);
		//Объекты
		if ($Config->core['show_objects_data']) {
			global $Objects, $timeload, $loader_init_memory;
			$this->debug_info .= h::{'p#debug_objects_toggle.ui-widget-header.for_state_messages.center'}(
				$span.$L->objects
			);
			$debug_info =	h::p(
								$L->total_list.': '.implode(', ', array_keys($Objects->Loaded))
							).h::p(
								$L->loader
							).h::{'p.padding_left'}(
								$L->creation_duration.': '.
									format_time(round($timeload['loader_init'] - $timeload['start'], 5))
							).h::{'p.padding_left'}(
								$L->memory_usage.': '.
									format_filesize($loader_init_memory, 5)
							);
			$last = $timeload['loader_init'];
			foreach ($Objects->Loaded as $object => &$data) {
				$debug_info .=	h::p(
									$object
								).h::{'p.padding_left'}(
									$L->creation_duration.': '.
										format_time(round($data[0] - $last, 5))
								).h::{'p.padding_left'}(
									$L->time_from_start_execution.': '.
										format_time(round($data[0] - $timeload['start'], 5))
								).h::{'p.padding_left'}(
									$L->memory_usage.': '.
										format_filesize($data[1], 5)
								);
				$last = $data[0];
			}
			$this->debug_info .= h::{'div#debug_objects.padding_left'}(
				$debug_info,
				array('style' => 'display: none;')
			);
			unset($loader_init_memory, $last, $object, $data, $debug_info);
		}
		//Данные пользователя
		if ($Config->core['show_user_data']) {
			$this->debug_info .= h::{'p#debug_user_toggle.ui-widget-header.for_state_messages.center'}(
				$span.$L->user_data
			);
			global $loader_init_memory;
			$this->debug_info .= h::{'div#debug_user'}(
				'',//TODO Show user information
				array(
					'style' => 'display: none;'
				)
			);
			unset($loader_init_memory, $last, $object, $data);
		}
		//Запросы в БД
		if ($Config->core['show_queries']) {
			$this->debug_info .= h::{'p#debug_queries_toggle.ui-widget-header.for_state_messages.center'}(
				$span.$L->queries
			);
			$queries =	h::p(
				$L->false_connections.': '.h::b(implode(', ', $db->get_connections_list(false)) ?: $L->no)
			).
			h::p(
				$L->successful_connections.': '.h::b(implode(', ', $db->get_connections_list(true)) ?: $L->no)
			).
			h::p(
				$L->mirrors_connections.': '.h::b(implode(', ', $db->get_connections_list('mirror')) ?: $L->no)
			).
			h::p(
				$L->active_connections.': '.(count($db->get_connections_list()) ? '' : h::b($L->no))
			);
			$connections = $db->get_connections_list();
			foreach ($connections as $name => $database) {
				$name = $name != 0 ? $L->db.' '.$database->database : $L->core_db.' ('.$database->database.')';
				$queries .= h::{'p.padding_left'}(
					$name.
					', '.$L->duration_of_connecting_with_db.' '.$L->during.' '.round($database->connecting_time, 5).
					', '.$database->queries['num'].' '.$L->queries_to_db.' '.$L->during.' '.format_time(round($database->time, 5)).':'
				);
				foreach ($database->queries['text'] as $i => &$text) {
					$queries .= h::code(
						$text.
						h::br(2).
						'#'.h::i(format_time(round($database->queries['time'][$i], 5))).
						($error = (strtolower(substr($text, 0, 6)) == 'select' && !$database->queries['resource'][$i]) ? '('.$L->error.')' : ''),
						array(
							'class' => ($database->queries['time'][$i] > 0.1 ? 'ui-state-highlight ' : '').($error ? 'ui-state-error ' : '').'code_debug'
						)
					);
				}
				unset($error);
			}
			unset($connections, $name, $database, $i, $text);
			$this->debug_info .= h::{'div#debug_queries.padding_left'}(
				h::p(
					$L->total.' '.$db->queries.' '.$L->queries_to_db.' '.$L->during.' '.format_time(round($db->time, 5)).($db->queries ? ':' : '')
				).
				$queries,
				array(
					'style' => 'display: none; '
				)
			);
			unset($queries);
		}
		//TODO Storages information
		//Cookies
		if ($Config->core['show_cookies']) {
			$this->debug_info .= h::{'p#debug_cookies_toggle.ui-widget-header.for_state_messages.center'}(
				$span.$L->cookies
			);
			$debug_info = h::tr(
				h::td($L->key.':', array('style' => 'width: 20%;')).
				h::td($L->value, array('style' => 'width: 80%;'))
			);
			foreach ($_COOKIE as $i => $v) {
				$debug_info .= h::tr(
					h::td($i.':', array('style' => 'width: 20%;')).
					h::td(xap($v), array('style' => 'width: 80%;'))
				);
			}
			unset($i, $v);
			$this->debug_info .= h::{'div#debug_cookies'}(
				h::level(
					h::{'table.padding_left'}(
						$debug_info,
						array(
							 'style' => 'width: 100%'
						)
					)
				),
				array(
					'style'	=> 'display: none;'
				)
			);
			unset($debug_info);
		}
		$this->debug_info = preg_replace($this->Search, $this->Replace, $this->debug_info);
	}
	//Отображение уведомления
	function notice ($text) {
		$this->Top .= h::{'div.ui-state-highlight.ui-corner-all.ui-priority-primary.center.for_state_messages'}(
			$text
		);
	}
	//Отображение предупреждения
	function warning ($text) {
		$this->Top .= h::{'div.ui-state-error.ui-corner-all.ui-priority-primary.center.for_state_messages'}(
			$text
		);
	}
	//Error pages processing
	function error ($page) {//TODO Error pages processing

	}
	/**
	 * Substitutes header information about user, login/registration forms, etc.
	 */
	protected function get_header_info () {
		global $User, $L;
		if (is_object ($User) && $User->is('user')) {
			if ($User->avatar) {
				$this->user_avatar_image = 'url('.h::url($User->avatar, true).')';
			} else {
				$this->user_avatar_text = '?';
				$this->user_avatar_image = 'none';
			}
			$this->user_info = h::b($L->hello.', '.($User->username ?: $User->login ?: $User->email).'!').h::br();
		} else {
			$this->user_avatar_text = '?';
			$this->user_avatar_image = 'none';
			$this->user_info = h::{'div#anonym_header_form'}(
				h::b($L->hello.', '.$L->guest.'!').h::br().
					h::{'button#login_slide.compact'}(
						h::icon('check').$L->log_in
					).
					h::{'button#registration_slide.compact'}(
						h::icon('pencil').$L->register,
						array(
							 'data-title'	=> $L->quick_registration_form
						)
					)
			).
			h::{'div#register_header_form'}(
				h::{'input#register[tabindex=1]'}(
					array(
						 'placeholder'	=> $L->email_or
					)
				).
				h::{'select#register_list'}(
					array(
						 'in'			=> array_merge(array(''), (array)_mb_substr(get_list(MODULES.DS.'System'.DS.'registration', '/^.*?\.php$/i', 'f'), 0, -4))
					)
				).
				h::{'button#register_process.compact[tabindex=2]'}(h::icon('pencil').$L->register).
				h::{'button.compact.header_back[tabindex=3]'}(
					h::icon('carat-1-s'),
					array(
						 'data-title'	=> $L->back
					)
				).
				h::{'button.compact.restore_password[tabindex=4]'}(
					h::icon('help'),
					array(
						 'data-title'	=> $L->restore_password
					)
				),
				array(
					 'style'	=> 'display: none;'
				)
			).
			h::{'div#login_header_form'}(
				h::{'input#user_login[tabindex=1]'}(
					array(
						 'placeholder'	=> $L->login_or_email_or
					)
				).
				h::{'select#login_list'}(
					array(
						 'in'			=> array_merge(array(''), (array)_mb_substr(get_list(MODULES.DS.'System'.DS.'registration', '/^.*?\.php$/i', 'f'), 0, -4))
					)
				).
				h::{'input#user_password[type=password][tabindex=2]'}(
					array(
						 'placeholder'	=> $L->password
					)
				).
				h::{'icon#show_password.pointer'}('locked').
				h::{'button#login_process.compact[tabindex=3]'}(h::icon('check').$L->log_in).
				h::{'button.compact.header_back[tabindex=5]'}(
					h::icon('carat-1-s'),
					array(
						 'data-title'	=> $L->back
					)
				).
				h::{'button.compact.restore_password[tabindex=4]'}(
					h::icon('help'),
					array(
						 'data-title'	=> $L->restore_password
					)
				),
				array(
					 'style'	=> 'display: none;'
				)
			);
		}
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
	//Генерирование страницы
	function __finish () {
		global $Config;
		//Очистка вывода для избежания вывода нежелательных данных
		if (OUT_CLEAN) {
			ob_end_clean();
		}
		//Генерирование страницы в зависимости от ситуации
		//Для AJAX и API запросов не выводится весь интерфейс страницы, только основное содержание
		if (!$this->interface) {
			//Обработка замены контента
			echo preg_replace($this->Search, $this->Replace, $this->Content);
		} else {
			global $stop, $Error, $L, $timeload, $User;
			//Обработка шаблона, наполнение его содержимым
			$this->prepare($stop);
			//Обработка замены контента
			$this->Html = preg_replace($this->Search, $this->Replace, $this->Html);
			//Опеределение типа сжатия сжатия
			$ob = false;
			if (is_object($Config) && !zlib_autocompression() && $Config->core['gzip_compression'] && (is_object($Error) && !$Error->num())) {
				ob_start('ob_gzhandler');
				$ob = true;
			} elseif (is_object($Config) && $Config->core['zlib_compression'] && $Config->core['zlib_compression_level'] && zlib() && (is_object($Error) && !$Error->num())) {
				ini_set('zlib.output_compression', 'On');
				ini_set('zlib.output_compression_level', $Config->core['zlib_compression_level']);
			}
			$timeload['end'] = microtime(true);
			if (is_object($User) && $User->is('admin') && is_object($Config) && $Config->core['debug']) {
				$this->debug();
			}
			echo str_replace(
				[
					'<!--debug_info-->',
					'<!--generate time-->',
					'<!--peak memory usage-->'
				],
				[
					$this->debug_info ? h::level(
						h::{'div#debug'}(
							h::level($this->debug_info),
							[
								'data-dialog'	=> '{"autoOpen": false, "height": "400", "hide": "puff", "show": "scale", "width": "700"}',
								'title'			=> $L->debug,
								'style'			=> 'display: none;'
							]
						),
						$this->level['debug_info']
					) : '',
					format_time(round($timeload['end'] - $timeload['start'], 5)),
					format_filesize(memory_get_peak_usage(), 5)
				],
				$this->Html
			);
			if ($ob) {
				ob_end_flush();
			}
		}
		//Обработка замены контента и вывод сгенерированной страницы
	}
}