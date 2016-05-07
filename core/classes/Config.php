<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * Provides next events:
 *  System/Config/init/before
 *
 *  System/Config/init/after
 *
 *  System/Config/changed
 *
 * @method static $this instance($check = false)
 */
class Config {
	use
		CRUD,
		Singleton;
	const SYSTEM_MODULE = 'System';
	const SYSTEM_THEME  = 'CleverStyle';
	/**
	 * Most of general configuration properties
	 *
	 * @var mixed[]
	 */
	public $core = [];
	/**
	 * Configuration of databases, except the main database, parameters of which are stored in configuration file
	 *
	 * @var mixed[]
	 */
	public $db = [];
	/**
	 * Configuration of storages, except the main storage, parameters of which are stored in configuration file
	 *
	 * @var mixed[]
	 */
	public $storage = [];
	/**
	 * Internal structure of components parameters
	 *
	 * @var mixed[]
	 */
	public $components = [];
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
	public    $mirrors;
	protected $data_model = [
		'domain'     => 'text',
		'core'       => 'json',
		'db'         => 'json',
		'storage'    => 'json',
		'components' => 'json'
	];
	protected $table      = '[prefix]config';
	protected function cdb () {
		return 0;
	}
	/**
	 * Loading of configuration, initialization of $Config, $Cache, $L and Page objects, Routing processing
	 *
	 * @throws ExitException
	 */
	protected function construct () {
		Event::instance()->fire('System/Config/init/before');
		$this->load_configuration();
		date_default_timezone_set($this->core['timezone']);
		$this->fill_mirrors();
		Event::instance()->fire('System/Config/init/after');
		if (!file_exists(MODULES.'/'.$this->core['default_module'])) {
			$this->core['default_module'] = self::SYSTEM_MODULE;
			$this->save();
		}
	}
	/**
	 * Is used to fill `$this->mirrors` using current configuration
	 */
	protected function fill_mirrors () {
		$this->mirrors = [
			'count' => 0,
			'http'  => [],
			'https' => []
		];
		foreach ($this->core['url'] as $i => $address) {
			list($protocol, $urls) = explode('://', $address, 2);
			$urls                       = explode(';', $urls);
			$this->mirrors[$protocol][] = $urls[0];
		}
		$this->mirrors['count'] = count($this->mirrors['http']) + count($this->mirrors['https']);
	}
	/**
	 * Reloading of settings cache
	 *
	 * @return bool
	 *
	 * @throws ExitException
	 */
	protected function load_configuration () {
		$config = Cache::instance()->get(
			'config',
			function () {
				return $this->read(Core::instance()->domain);
			}
		);
		if (!$config) {
			throw new ExitException('Failed to load system configuration', 500);
		}
		foreach ($config as $part => $value) {
			$this->$part = $value;
		}
		return $this->apply_internal(false);
	}
	/**
	 * Applying settings without saving changes into db
	 *
	 * @return bool
	 *
	 * @throws ExitException
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
	 *
	 * @throws ExitException
	 */
	protected function apply_internal ($cache_not_saved_mark = true) {
		if ($cache_not_saved_mark) {
			$this->core['cache_not_saved'] = true;
		} else {
			unset($this->core['cache_not_saved']);
		}
		$Cache = Cache::instance();
		if (!$Cache->set(
			'config',
			[
				'core'       => $this->core,
				'db'         => $this->db,
				'storage'    => $this->storage,
				'components' => $this->components
			]
		)
		) {
			return false;
		}
		$Cache->del('languages');
		date_default_timezone_set($this->core['timezone']);
		$this->fill_mirrors();
		Event::instance()->fire('System/Config/changed');
		return true;
	}
	/**
	 * Saving settings
	 *
	 * @return bool
	 *
	 * @throws ExitException
	 */
	function save () {
		if ($this->cancel_available()) {
			unset($this->core['cache_not_saved']);
		}
		$core_settings_keys = file_get_json(MODULES.'/System/core_settings_keys.json');
		foreach ($this->core as $key => $value) {
			if (!in_array($key, $core_settings_keys)) {
				unset($this->core[$key]);
			}
		}
		if (!$this->update(Core::instance()->domain, $this->core, $this->db, $this->storage, $this->components)) {
			return false;
		}
		return $this->apply_internal(false);
	}
	/**
	 * Whether configuration was applied (not saved) and can be canceled
	 *
	 * @return bool
	 */
	function cancel_available () {
		return isset($this->core['cache_not_saved']);
	}
	/**
	 * Canceling of applied settings
	 *
	 * @return bool
	 *
	 * @throws ExitException
	 */
	function cancel () {
		Cache::instance()->del('config');
		return $this->load_configuration();
	}
	/**
	 * Get base url of current mirror including language suffix
	 *
	 * @return string
	 */
	function base_url () {
		if (Request::instance()->mirror_index === -1) {
			return '';
		}
		$base_url = $this->core_url();
		if ($this->core['multilingual']) {
			$L = Language::instance();
			$base_url .= "/$L->clang";
		}
		return $base_url;
	}
	/**
	 * Get base url of main domain
	 *
	 * @return string
	 */
	function core_url () {
		$Request = Request::instance();
		return "$Request->scheme://$Request->host";
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
}
