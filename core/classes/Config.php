<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

/**
 * Provides next events:
 *  System/Config/init/before
 *
 *  System/Config/init/after
 *
 * @method static Config instance($check = false)
 */
class Config {
	use	Singleton;
	/**
	 * Most of general configuration properties
	 *
	 * @var mixed[]
	 */
	public $core		= [];
	/**
	 * Configuration of databases, except the main database, parameters of which are stored in configuration file
	 *
	 * @var mixed[]
	 */
	public $db			= [];
	/**
	 * Configuration of storages, except the main storage, parameters of which are stored in configuration file
	 *
	 * @var mixed[]
	 */
	public $storage		= [];
	/**
	 * Internal structure of components parameters
	 *
	 * @var mixed[]
	 */
	public $components	= [];
	/**
	 * Replacing rules, that are used to replace text on pages
	 *
	 * @var mixed[]
	 */
	public $replace		= [];
	/**
	 * Replacing rules, they are applied to current route, every rule is applied only once
	 *
	 * @var mixed[]
	 */
	public $routing		= [];
	/**
	 * Array of all domains, which allowed to access the site
	 *
	 * Contains keys:
	 * * count - Total count
	 * * http - Insecure (http) domains
	 * * https - Secure (https) domains
	 *
	 * @var array
	 */
	public $mirrors = [
		'count' => 0,
		'http'  => [],
		'https' => []
	];
	/**
	 * Initialization state
	 *
	 * @var bool
	 */
	protected	$init	= false;
	/**
	 * Loading of configuration, initialization of $Config, $Cache, $L and Page objects, Routing processing
	 */
	protected function construct () {
		/**
		 * Reading settings from cache and defining missing data
		 */
		$config = Cache::instance()->config;
		/**
		 * Cache reloading, if necessary
		 */
		if (!is_array($config)) {
			$this->load_config_from_db();
		} else {
			foreach ($config as $part => $value) {
				$this->$part = $value;
			}
			unset($part, $value);
		}
		Event::instance()->fire('System/Config/init/before');
		/**
		 * System initialization with current configuration
		 */
		$this->init();
		Event::instance()->fire('System/Config/init/after');
		if (!file_exists(MODULES.'/'.$this->core['default_module'])) {
			$this->core['default_module']	= 'System';
			$this->save();
		}
	}
	/**
	 * Engine initialization (or reinitialization if necessary)
	 */
	protected function init () {
		Language::instance()->init();
		$Page	= Page::instance();
		$Page->init(
			get_core_ml_text('name'),
			$this->core['theme']
		);
		if (!$this->init) {
			$Page->replace($this->replace['in'], $this->replace['out']);
			$this->init = true;
			if ($this->check_ip($this->core['ip_black_list'])) {
				error_code(403);
				$Page->error();
				return;
			}
		}
		/**
		 * Setting system timezone
		 */
		date_default_timezone_set($this->core['timezone']);
		$this->fill_mirrors();
	}
	/**
	 * Is used to fill `$this->mirrors` using current configuration
	 */
	protected function fill_mirrors () {
		foreach ($this->core['url'] as $i => $address) {
			list($protocol, $urls) = explode('://', $address, 2);
			$urls                           = explode(';', $urls);
			$this->mirrors[$protocol][] = $urls[0];
		}
		$this->mirrors['count'] = count($this->mirrors['http']) + count($this->mirrors['https']);
	}
	/**
	 * Check user's IP address matches with elements of given list
	 *
	 * @param string[]	$ips
	 *
	 * @return bool
	 */
	protected function check_ip ($ips) {
		if (!$ips || !is_array($ips)) {
			return false;
		}
		/**
		 * @var _SERVER $_SERVER
		 */
		foreach ($ips as $ip) {
			if ($ip) {
				$char = mb_substr($ip, 0, 1);
				if ($char != mb_substr($ip, -1)) {
					$ip = "/$ip/";
				}
				if (
					_preg_match($ip, $_SERVER->remote_addr) ||
					(
						$_SERVER->ip &&
						_preg_match($ip, $_SERVER->ip)
					)
				) {
					return true;
				}
			}
		}
		return false;
	}
	/**
	 * Updating information about set of available themes
	 */
	function reload_themes () {
		$this->core['themes']	= get_files_list(THEMES, false, 'd');
		asort($this->core['themes']);
	}
	/**
	 * Updating information about set of available languages
	 */
	function reload_languages () {
		$this->core['languages'] = array_unique(
			array_merge(
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
				_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
			)
		);
		asort($this->core['languages']);
	}
	/**
	 * Reloading of settings cache
	 *
	 * @return bool
	 */
	protected function load_config_from_db () {
		$result = DB::instance()->qf([
			"SELECT
				`core`,
				`db`,
				`storage`,
				`components`,
				`replace`,
				`routing`
			FROM `[prefix]config`
			WHERE `domain` = '%s'
			LIMIT 1",
			DOMAIN
		]);
		if (is_array($result)) {
			foreach ($result as $part => $value) {
				$this->$part = _json_decode($value);
			}
			unset($part, $value);
		} else {
			return false;
		}
		$this->reload_themes();
		$this->reload_languages();
		$this->apply_internal(false);
		return true;
	}
	/**
	 * Applying settings without saving changes into db
	 *
	 * @return bool
	 */
	function apply () {
		return $this->apply_internal();
	}
	/**
	 * Applying settings without saving changes into db
	 *
	 * @param bool $cache_not_saved_mark
	 *
	 * @return bool
	 */
	protected function apply_internal ($cache_not_saved_mark = true) {
		if ($cache_not_saved_mark) {
			$this->core['cache_not_saved'] = true;
		} else {
			unset($this->core['cache_not_saved']);
		}
		$Cache         = Cache::instance();
		$Cache->config = [
			'core'       => $this->core,
			'db'         => $this->db,
			'storage'    => $this->storage,
			'components' => $this->components,
			'replace'    => $this->replace,
			'routing'    => $this->routing
		];
		unset($Cache->{'languages'});
		$L = Language::instance();
		if ($this->core['multilingual'] && User::instance(true)) {
			$L->change(User::instance()->language);
		} else {
			$L->change($this->core['language']);
		}
		$this->init();
		return true;
	}
	/**
	 * Saving settings
	 *
	 * @return bool
	 */
	function save () {
		if (isset($this->core['cache_not_saved'])) {
			unset($this->core['cache_not_saved']);
		}
		$cdb	= DB::instance()->db_prime(0);
		if ($cdb->q(
			"UPDATE `[prefix]config`
			SET
				`core`			= '%s',
				`db`			= '%s',
				`storage`		= '%s',
				`components`	= '%s',
				`replace`		= '%s',
				`routing`		= '%s'
			WHERE `domain` = '%s'
			LIMIT 1",
			_json_encode($this->core),
			_json_encode($this->db),
			_json_encode($this->storage),
			_json_encode($this->components),
			_json_encode($this->replace),
			_json_encode($this->routing),
			DOMAIN
		)) {
			$this->apply_internal(false);
			return true;
		}
		return false;
	}
	/**
	 * Canceling of applied settings
	 */
	function cancel () {
		unset(Cache::instance()->config);
		$this->load_config_from_db();
		$this->apply_internal(false);
	}
	/**
	 * Get base url of current mirror including language suffix
	 *
	 * @return string
	 */
	function base_url () {
		if (Route::instance()->mirror_index === -1) {
			return '';
		}
		/**
		 * @var _SERVER $_SERVER
		 */
		$base_url	= "$_SERVER->protocol://$_SERVER->host";
		$L			= Language::instance();
		if ($L->url_language()) {
			$base_url	.= "/$L->clang";
		}
		return $base_url;
	}
	/**
	 * Get base url of main domain
	 *
	 * @return string
	 */
	function core_url () {
		/**
		 * @var _SERVER $_SERVER
		 */
		return "$_SERVER->protocol://$_SERVER->host";
	}
	/**
	 * Get object for getting db and storage configuration of module
	 *
	 * @param string $module_name
	 *
	 * @return Config\Module_Properties
	 */
	function module ($module_name) {
		if (!isset($this->components['modules'][$module_name])) {
			return False_class::instance();
		}
		return new Config\Module_Properties($this->components['modules'][$module_name], $module_name);
	}
	/**
	 * Allows to check ability to be admin user (can be limited by IP)
	 *
	 * @return bool
	 */
	function can_be_admin () {
		return !$this->core['ip_admin_list_only'] || $this->check_ip($this->core['ip_admin_list']);
	}
}
