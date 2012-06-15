<?php
class Config {
	public	$core			= [],
			$db				= [],
			$storage		= [],
			$components		= [],
			$replace		= [],
			$routing		= [],
			$admin_parts	= [				//Столбцы в БД в таблице конфигурации движка
				'core',
				'db',
				'storage',
				'components',
				'replace',
				'routing'
			],
			$server			= [				//Массив некоторых настроек адресов, зеркал и прочего
				'url'			=> '',		//Сырой путь страницы (тот, который вводит пользователь в строке адреса)
				'host'			=> '',		//Host
				'current_url'	=> '',		//Скорректированный полный путь страницы (рекомендуемый к использованию)
				'protocol'		=> '',		//Протокол страницы (http/https)
				'base_url'		=> '',		//Адрес главной страницы текущего зеркала с учётом префикса протокола (http/https)
				'mirrors'	=> [			//Массив всех адресов, по которым разрешен доступ к сайту
					'count'		=> 0,		//Общее количество
					'http'		=> [],		//Небезопасные адреса
					'https'		=> []		//Безопасные адреса
				],
				'referer'		=> [
					'url'		=> '',
					'host'		=> '',
					'protocol'	=> '',
					'local'		=> false
				],
				'ajax'			=> false,	//Is this page request via AJAX
				'mirror_index'	=> -1		//Индекс текущего адреса сайта в списке зеркал ('-1' - не зеркало, а основной домен)
			],
			$can_be_admin		= true;		//Alows to check ability to be admin user (can be limited by IP)
	protected	$init = false;

	//Инициализация параметров системы
	function __construct () {
		global $Cache;
		//Считывание настроек с кеша и определение недостающих данных
		$config = $Cache->config;
		if (is_array($config)) {
			$query = false;
			foreach ($this->admin_parts as $part) {
				if (isset($config[$part]) && !empty($config[$part])) {
					$this->$part = $config[$part];
				} else {
					$query = true;
					break;
				}
			}
			unset($part);
		} else {
			$query = true;
		}
		//Перестройка кеша при необходимости
		if ($query == true) {
			$this->load();
		} else {
			//Инициализация движка
			$this->init();
		}
		//Запуск роутинга адреса
		$this->routing();
	}
	//Инициализация движка (или реинициалицазия при необходимости)
	function init() {
		global $Cache, $L, $Error, $Page;
		if ($this->core['debug'] && !defined('DEBUG')) {
			define('DEBUG', true);
		}
		//Инициализация объекта кеша с использованием настроек движка
		$Cache->init();
		//Инициализация объекта языков с использованием настроек движка
		$L->init($this->core['active_languages'], $this->core['language']);
		//Инициализация объекта страницы с использованием настроек движка
		$Page->init($this->core['name'], $this->core['keywords'], $this->core['description'], $this->core['theme'], $this->core['color_scheme']);
		//Инициализация объекта обработки ошибок
		$Error->init();
		if (!$this->init) {
			$this->init = true;
			if ($this->check_ip($this->core['ip_black_list'])) {
				define('ERROR_PAGE', 403);
				$Error->page();
				__finish();
				return;
			}
		}
		//Установка часового пояса по-умолчанию
		date_default_timezone_set($this->core['timezone']);
	}
	protected function check_ip ($ips) {
		if (is_array($ips) && !empty($ips)) {
			$REMOTE_ADDR			= $_SERVER['REMOTE_ADDR'];
			$HTTP_X_FORWARDED_FOR	= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false;
			$HTTP_CLIENT_IP			= isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : false;
			foreach ($ips as $ip) {
				$char = mb_substr($ip, 0, 1);
				if ($char != mb_substr($ip, -1)) {
					$ip = '/'.$ip.'/';
				}
				if (
					preg_match($REMOTE_ADDR, $ip) ||
					($HTTP_X_FORWARDED_FOR && preg_match($HTTP_X_FORWARDED_FOR, $ip)) ||
					($HTTP_CLIENT_IP && preg_match($HTTP_CLIENT_IP, $ip))
				) {
					return true;
				}
			}
		}
		return false;
	}
	//Анализ и обработка текущего адреса страницы
	protected function routing () {
		$this->server['url']		= urldecode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		$this->server['url']		= null_byte_filter($this->server['url']);
		$this->server['host']		= $_SERVER['HTTP_HOST'];
		$this->server['protocol']	= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$core_url					= explode('://', $this->core['url'], 2);
		$core_url[1]				= explode(';', $core_url[1]);
		//$core_url = array(0 => протокол, 1 => array(список из домена и IP адресов))
		//Проверяем, сходится ли адрес с главным доменом
		$url_replace = false;
		if ($core_url[0] == $this->server['protocol']) {
			foreach ($core_url[1] as $url) {
				if (mb_strpos($this->server['url'], $url) === 0) {
					$this->server['base_url']	= $this->server['protocol'].'://'.$url;
					$url_replace				= $url;
					break;
				}
			}
		}
		$this->server['mirrors'][$core_url[0]] = array_merge($this->server['mirrors'][$core_url[0]], $core_url[1]);
		unset($core_url, $url);
		//If it  is not the main domain - try to find match in mirrors
		if ($url_replace === false && !empty($this->core['mirrors_url'])) {
			$mirrors_url = $this->core['mirrors_url'];
			foreach ($mirrors_url as $i => $mirror_url) {
				$mirror_url		= explode('://', $mirror_url, 2);
				$mirror_url[1]	= explode(';', $mirror_url[1]);
				//$mirror_url = array(0 => протокол, 1 => array(список из домена и IP адресов))
				if ($mirror_url[0] == $this->server['protocol']) {
					foreach ($mirror_url[1] as $url) {
						if (mb_strpos($this->server['url'], $url) === 0) {
							$this->server['base_url']		= $this->server['protocol'].'://'.$url;
							$url_replace					= $url;
							$this->server['mirror_index']	= $i;
							break 2;
						}
					}
				}
			}
			unset($mirrors_url, $mirror_url, $url, $i);
			//If match in mirrors was not found - mirror is not allowed!
			if ($this->server['mirror_index'] == -1) {
				global $Error, $L;
				$this->server['base_url'] = '';
				$Error->process($L->mirror_not_allowed, 'stop');
			}
		//If match was not found - mirror is not allowed!
		} elseif ($url_replace === false) {
			global $Error, $L;
			$this->server['base_url'] = '';
			$Error->process($L->mirror_not_allowed, 'stop');
		}
		if (!empty($this->core['mirrors_url'])) {
			$mirrors_url = $this->core['mirrors_url'];
			foreach ($mirrors_url as $mirror_url) {
				$mirror_url									= explode('://', $mirror_url, 2);
				$this->server['mirrors'][$mirror_url[0]]	= array_merge(
					isset($this->server['mirrors'][$mirror_url[0]]) ? $this->server['mirrors'][$mirror_url[0]] : [],
					isset($mirror_url[1]) ? explode(';', $mirror_url[1]) : []
				);
			}
			$this->server['mirrors']['count'] = count($this->server['mirrors']['http'])+count($this->server['mirrors']['https']);
			unset($mirrors_url, $mirror_url);
		}
		//Preparing page url without basic path
		$this->server['url'] = str_replace('//', '/', trim(str_replace($url_replace, '', $this->server['url']), ' /\\'));
		unset($url_replace);
		$r	= &$this->routing;
		$rc	= &$r['current'];
		//Obtaining page path in form of array
		$rc = explode('/', str_replace($r['in'], $r['out'], trim($this->server['url'], '/')));
		//If url looks like admin query
		if (isset($rc[0]) && mb_strtolower($rc[0]) == 'admin') {
			if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
				define('ERROR_PAGE', 403);
				global $Error;
				$Error->page();
				__finish();
				return;
			}
			if (!defined('ADMIN')) {
				define('ADMIN', true);
			}
			array_shift($rc);
		//If url looks like API query
		} elseif (isset($rc[0]) && mb_strtolower($rc[0]) == 'api') {
			if (!defined('API')) {
				define('API', true);
			}
			array_shift($rc);
		}
		if ($this->core['ip_admin_list_only'] && !$this->check_ip($this->core['ip_admin_list'])) {
			$this->can_be_admin = false;
		}
		!defined('ADMIN')	&& define('ADMIN', false);
		!defined('API')		&& define('API', false);
		//Определение модуля модуля
		if (isset($rc[0]) && in_array(mb_strtolower($rc[0]), _mb_strtolower(array_keys($this->components['modules'])))) {
			if (!defined('MODULE')) {
				define('MODULE', array_shift($rc));
			}
		} else {
			if (!defined('MODULE')) {
				define('MODULE', 'System');
				if (!ADMIN && !API && !isset($rc[1])) {
					define('HOME', true);
				}
			}
		}
		!defined('HOME')	&& define('HOME', false);
		//Скорректированный полный путь страницы (рекомендуемый к использованию)
		$this->server['current_url'] = (ADMIN ? 'admin/' : '').MODULE.(API ? 'api/' : '').'/'.implode('/', $rc);
		unset($rc, $r);
		if (isset($_SERVER['HTTP_REFERER'])) {
			$ref				= &$this->server['referer'];
			$referer			= explode('://', $ref['url'] = $_SERVER['HTTP_REFERER']);
			$referer[1]			= explode('/', $referer[1]);
			$referer[1]			= $referer[1][0];
			$ref['protocol']	= $referer[0];
			$ref['host']		= $referer[1];
			unset($referer);
			$ref['local']		= in_array($ref['host'], $this->server['mirrors'][$ref['protocol']]);
			unset($ref);
		}
		$this->server['ajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}
	//Обновление информации о текущем наборе тем оформления
	function reload_themes () {
		$this->core['themes'] = get_list(THEMES, false, 'd');
		asort($this->core['themes']);
		foreach ($this->core['themes'] as $theme) {
			$this->core['color_schemes'][$theme] = [];
			$this->core['color_schemes'][$theme] = get_list(THEMES.'/'.$theme.'/schemes', false, 'd');
			asort($this->core['color_schemes'][$theme]);
		}
	}
	//Обновление списка текущих языков
	function reload_languages () {
		$this->core['languages'] = array_unique(
			array_merge(
				_mb_substr(get_list(LANGUAGES, '/^lang\..*?\.php$/i', 'f'), 5, -4) ?: [],
				_mb_substr(get_list(LANGUAGES, '/^lang\..*?\.json$/i', 'f'), 5, -5) ?: []
			)
		);
		asort($this->core['languages']);
	}
	//Перестройка кеша настроек
	protected function load () {
		global $db;
		$query = [];
		foreach ($this->admin_parts as $part) {
			$query[] = '`'.$part.'`';
		}
		unset($part);
		$result = $db->qf('SELECT '.implode(', ', $query).' FROM `[prefix]config` WHERE `domain` = \''.DOMAIN.'\' LIMIT 1');
		if (isset($this->routing['current'])) {
			$current_routing = $this->routing['current'];
		}
		if (is_array($result)) {
			foreach ($this->admin_parts as $part) {
				$this->$part = _json_decode($result[$part]);
			}
			unset($part);
		} else {
			return false;
		}
		if (isset($current_routing)) {
			$this->routing['current'] = $current_routing;
			unset($current_routing);
		}
		$this->reload_themes();
		$this->reload_languages();
		$this->apply();
		return true;
	}
	//Применение изменений без сохранения в БД
	function apply () {
		return $this->apply_internal();
	}
	protected function apply_internal ($cache_not_saved_mark = true) {
		global $Error, $Cache;
		//If errors - cache updating must be stopped
		if ($Error->num()) {
			return false;
		}
		$this->init();
		$Config = [];
		foreach ($this->admin_parts as $part) {
			$Config[$part] = $this->$part;
		}
		unset($part);
		if (isset($Config['routing']['current'])) {
			unset($Config['routing']['current']);
		}
		if ($cache_not_saved_mark) {
			$Config['core']['cache_not_saved'] = $this->core['cache_not_saved'] = true;
		} else {
			unset($Config['core']['cache_not_saved'], $this->core['cache_not_saved']);
		}
		$Cache->config = $Config;
		return true;
	}
	//Сохранение и применение изменений
	function save ($parts = null) {
		global $db;
		if ($parts === null || empty($parts)) {
			$parts = $this->admin_parts;
		} elseif (!is_array($parts)) {
			$parts = (array)$parts;
		}
		$query = '';
		foreach ($parts as $part) {
			if (isset($this->$part)) {
				if ($part == 'routing') {
					$temp = $this->routing;
					unset($temp['current']);
					$query[] = '`'.$part.'` = '.$db->{0}->s(_json_encode($temp));
					continue;
				}
				$query[] = '`'.$part.'` = '.$db->{0}->s(_json_encode($this->$part));
			}
		}
		unset($parts, $part, $temp);
		if (!empty($query) && $db->{0}->q('UPDATE `[prefix]config` SET '.implode(', ', $query).' WHERE `domain` = \''.DOMAIN.'\' LIMIT 1')) {
			$this->apply_internal(false);
			return true;
		}
		return false;
	}
	//Отмена примененных изменений и перестройка кеша
	function cancel () {
		global $Cache;
		unset($Cache->config);
		$this->load();
		$this->apply_internal(false);
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}