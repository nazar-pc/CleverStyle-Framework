<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	cs\Config\Options;

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
		Properties_getter,
		Singleton;
	const INIT_STATE_METHOD = 'init';
	const SYSTEM_MODULE     = 'System';
	const SYSTEM_THEME      = 'CleverStyle';
	/**
	 * @var Cache\Prefix
	 */
	protected $cache;
	/**
	 * Most of general configuration properties
	 *
	 * @var array
	 */
	public $core = [];
	/**
	 * @var array
	 */
	protected $core_internal = [];
	/**
	 * @var string
	 */
	protected $last_language;
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
	 * @var array[]
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
	 * Update multilingual options when language changes
	 */
	protected function init () {
		$this->read_core_update_multilingual();
		Event::instance()->on(
			'System/Language/change/after',
			function () {
				$this->read_core_update_multilingual();
			}
		);
	}
	/**
	 * Loading of configuration, initialization of $Config, $Cache, $L and Page objects, Routing processing
	 *
	 * @throws ExitException
	 */
	protected function construct () {
		// TODO: Change `config2` to `config` in 6.x
		$this->cache = Cache::prefix('config2');
		Event::instance()->fire('System/Config/init/before');
		$this->load_configuration();
		Event::instance()->fire('System/Config/init/after');
		if (!file_exists(MODULES.'/'.$this->core['default_module'])) {
			$this->core['default_module'] = self::SYSTEM_MODULE;
			$this->save();
		}
	}
	/**
	 * Reloading of settings cache
	 *
	 * @throws ExitException
	 */
	protected function load_configuration () {
		/**
		 * @var array[] $config
		 */
		$config = $this->cache->get(
			'source',
			function () {
				return $this->read(Core::instance()->domain);
			}
		);
		if (!$config) {
			throw new ExitException('Failed to load system configuration', 500);
		}
		$this->core_internal = $config['core'] + Options::get_defaults();
		$this->core          = $this->core_internal;
		$this->db            = $config['db'];
		$this->storage       = $config['storage'];
		$this->components    = $config['components'];
		date_default_timezone_set($this->core['timezone']);
		$this->fill_mirrors();
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
	protected function read_core_update_multilingual () {
		$language = Language::instance(true)->clanguage ?: @$this->core['language'];
		if (!$language || $language == $this->last_language) {
			return;
		}
		$this->last_language  = $language;
		$multilingual_options = $this->cache->get(
			$language,
			function () {
				$db_id                = $this->module('System')->db('texts');
				$Text                 = Text::instance();
				$multilingual_options = [];
				foreach (Options::get_multilingual() as $option) {
					$multilingual_options[$option] = $Text->process($db_id, $this->core_internal[$option], true);
				}
				return $multilingual_options;
			}
		);
		$this->core           = $multilingual_options + $this->core;
	}
	/**
	 * Get core options item
	 *
	 * @param string[]|string[][] $item
	 *
	 * @return mixed|mixed[]|null Core options items (or associative array of items) if exists or `null` otherwise (in case if `$item` is an array even one
	 *                            missing key will cause the whole thing to fail)
	 */
	public function core (...$item) {
		return $this->get_property_items('core', $item);
	}
	/**
	 * Applying settings without saving changes into db
	 *
	 * @return bool
	 *
	 * @throws ExitException
	 */
	public function apply () {
		$this->core = Options::apply_formatting($this->core) + Options::get_defaults();
		/**
		 * Update multilingual cache manually to avoid actually storing changes in database
		 */
		$multilingual_options_list = Options::get_multilingual();
		$multilingual_options      = [];
		foreach ($this->core as $option => $value) {
			if (in_array($option, $multilingual_options_list)) {
				$multilingual_options[$option] = $this->core[$option];
			} else {
				$this->core_internal[$option] = $value;
			}
		}
		$this->cache->set(Language::instance()->clanguage, $multilingual_options);
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
			$this->core_internal['cache_not_saved'] = true;
		} else {
			unset($this->core_internal['cache_not_saved']);
		}
		if (!$this->cache->set(
			'source',
			[
				'core'       => $this->core_internal,
				'db'         => $this->db,
				'storage'    => $this->storage,
				'components' => $this->components
			]
		)
		) {
			return false;
		}
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
	public function save () {
		unset($this->core_internal['cache_not_saved']);
		// TODO: Remove `modules/System/core_settings_defaults.json` file in 6.x
		$this->core = Options::apply_formatting($this->core) + Options::get_defaults();
		/**
		 * Persist multilingual options and copy the rest to `$this->core_internal` as is
		 */
		$multilingual_options_list = Options::get_multilingual();
		$db_id                     = $this->module('System')->db('texts');
		$Text                      = Text::instance();
		foreach ($this->core as $option => $value) {
			if (in_array($option, $multilingual_options_list)) {
				$this->core_internal[$option] = $Text->set($db_id, 'System/Config/core', $option, $this->core[$option]);
			} else {
				$this->core_internal[$option] = $value;
			}
		}
		if (!$this->update(Core::instance()->domain, $this->core_internal, $this->db, $this->storage, $this->components)) {
			return false;
		}
		return $this->apply_internal(false);
	}
	/**
	 * Whether configuration was applied (not saved) and can be canceled
	 *
	 * @return bool
	 */
	public function cancel_available () {
		return isset($this->core_internal['cache_not_saved']);
	}
	/**
	 * Canceling of applied settings
	 *
	 * @throws ExitException
	 */
	public function cancel () {
		$this->cache->del('/');
		$this->load_configuration();
	}
	/**
	 * Get base url of current mirror including language suffix
	 *
	 * @return string
	 */
	public function base_url () {
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
	public function core_url () {
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
	public function module ($module_name) {
		if (!isset($this->components['modules'][$module_name])) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return False_class::instance();
		}
		return new Config\Module_Properties($this->components['modules'][$module_name], $module_name);
	}
}
