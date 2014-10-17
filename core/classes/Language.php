<?php
/**
 * @package        CleverStyle CMS
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;

use
	JsonSerializable;

/**
 * Provides next triggers:
 *  System/general/languages/load
 *  [
 *   'clanguage'        => clanguage
 *   'clang'            => clang
 *   'cregion'          => cregion
 *   'clanguage_en'     => clanguage_en
 *  ]
 *
 * @method static Language instance($check = false)
 */
class Language implements JsonSerializable {
	use Singleton;
	/**
	 * Current language
	 *
	 * @var string
	 */
	public $clanguage;
	/**
	 * callable for time processing
	 *
	 * @var callable
	 */
	public $time = null;
	/**
	 * For single initialization
	 *
	 * @var bool
	 */
	protected $init = false;
	/**
	 * Local cache of translations
	 *
	 * @var array
	 */
	protected $translate      = [];
	/**
	 * Whether it is possible to change language (may be fixed to some concrete language)
	 *
	 * @var bool
	 */
	protected $fixed_language = false;
	protected $changed_once = false;
	/**
	 * Set basic language
	 */
	protected function construct () {
		$Core					= Core::instance();
		$this->fixed_language	= $Core->fixed_language;
		$this->change($Core->language);
	}
	/**
	 * Scanning of aliases for defining of current language
	 *
	 * @param array $active_languages
	 *
	 * @return bool|string
	 */
	protected function scan_aliases ($active_languages) {
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return false;
		}
		$Cache = Cache::instance();
		if (($aliases = $Cache->{'languages/aliases'}) === false) {
			$aliases = [];
			$aliases_list = _strtolower(get_files_list(LANGUAGES.'/aliases'));
			foreach ($aliases_list as $alias) {
				$aliases[$alias] = file_get_contents(LANGUAGES."/aliases/$alias");
			}
			unset($aliases_list, $alias);
			$Cache->{'languages/aliases'} = $aliases;
		}
		$accept_languages = str_replace(
			'-',
			'_',
			explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		);
		foreach (_strtolower($_SERVER) as $i => $v) {
			if (preg_match('/.*locale/i', $i)) {
				$accept_languages[] = strtolower($v);
			}
		}
		unset($i, $v);
		foreach ($accept_languages as $language) {
			$language = explode(';', $language, 2)[0];
			if (@in_array($aliases[$language], $active_languages)) {
				return $aliases[$language];
			}
		}
		return false;
	}
	/**
	 * Get translation
	 *
	 * @param string      $item
	 * @param bool|string $language If specified - translation for specified language will be returned, otherwise for current
	 *
	 * @return string
	 */
	function get ($item, $language = false) {
		$language = $language ?: $this->clanguage;
		if (isset($this->translate[$language])) {
			return @$this->translate[$language][$item] ?: ucfirst(str_replace('_', ' ', $item));
		}
		$current_language = $this->clanguage;
		$current_fixed_language = $this->fixed_language;
		$this->fixed_language = false;
		$this->change($language);
		$return = $this->get($item);
		$this->change($current_language);
		$this->fixed_language = $current_fixed_language;
		return $return;
	}
	/**
	 * Set translation
	 *
	 * @param array|string $item Item string, or key-value array
	 * @param null|string  $value
	 *
	 * @return void
	 */
	function set ($item, $value = null) {
		$translate = &$this->translate[$this->clanguage];
		if (is_array($item)) {
			$translate = $item + ($translate ?: []);
		} else {
			$translate[$item] = $value;
		}
	}
	/**
	 * Get translation
	 *
	 * @param string $item
	 *
	 * @return string
	 */
	function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Set translation
	 *
	 * @param array|string $item
	 * @param null|string  $value
	 *
	 * @return string
	 */
	function __set ($item, $value = null) {
		$this->set($item, $value);
	}
	/**
	 * Change language
	 *
	 * @param string $language
	 *
	 * @return bool
	 */
	function change ($language) {
		if ($this->fixed_language && $this->changed_once) {
			return false;
		}
		$this->changed_once = true;
		if ($language == $this->clanguage) {
			return true;
		}
		$Config = Config::instance(true);
		if (!$language && $Config->core['multilingual']) {
			$language = $this->scan_aliases($Config->core['active_languages']) ?: $language;
		}
		if (
			!$Config ||
			$language == $Config->core['language'] ||
			(
				$Config->core['multilingual'] &&
				in_array($language, $Config->core['active_languages'])
			)
		) {
			$previous_language = $this->clanguage;
			$this->clanguage = $language;
			$return = false;
			$Cache = Cache::instance();
			/**
			 * If translations in cache
			 */
			if ($translate = $Cache->{"languages/$language"}) {
				$this->set($translate);
				$return = true;
				/**
				 * Otherwise check for system translations
				 */
			} elseif (file_exists(LANGUAGES."/$language.json")) {
				/**
				 * Set system translations
				 */
				$translate = &$this->translate[$language];
				$load_previous_translation = false;
				if (!$translate && $previous_language) {
					$load_previous_translation = true;
				}
				$this->set(file_get_json_nocomments(LANGUAGES."/$language.json"));
				$translate['clanguage'] = $language;
				if (!isset($translate['clang'])) {
					$translate['clang'] = mb_strtolower(mb_substr($language, 0, 2));
				}
				if (!isset($translate['cregion'])) {
					$translate['cregion'] = $translate['clang'];
				}
				if (!isset($translate['clanguage_en'])) {
					$translate['clanguage_en'] = $language;
				}
				$translate['clocale'] = $this->clang.'_'.mb_strtoupper($this->cregion);
				/**
				 * Set modules' translations
				 */
				foreach (get_files_list(MODULES, false, 'd') as $module) {
					if (file_exists(MODULES."/$module/languages/$language.json")) {
						$this->set(
							file_get_json_nocomments(MODULES."/$module/languages/$language.json") ?: []
						);
					}
				}
				unset($module);
				/**
				 * Set plugins' translations
				 */
				foreach (get_files_list(PLUGINS, false, 'd') as $plugin) {
					if (file_exists(PLUGINS."/$plugin/languages/$language.json")) {
						$this->set(
							file_get_json_nocomments(PLUGINS."/$plugin/languages/$language.json") ?: []
						);
					}
				}
				unset($plugin);
				Trigger::instance()->run(
					'System/general/languages/load',
					[
						'clanguage'    => $language,
						'clang'        => $this->clang,
						'cregion'      => $this->cregion,
						'clanguage_en' => $this->clanguage_en
					]
				);
				if ($load_previous_translation) {
					$translate = $translate + $this->translate[$previous_language];
				}
				$Cache->{"languages/$language"} = $translate;
				$return = true;
			}
			_include(LANGUAGES."/$language.php", false, false);
			header("Content-Language: $translate[content_language]");
			return $return;
		}
		return false;
	}
	/**
	 * Time formatting according to the current language (adding correct endings)
	 *
	 * @param int    $in          time (number)
	 * @param string $type        Type of formatting<br>
	 *                            s - seconds<br>m - minutes<br>h - hours<br>d - days<br>M - months<br>y - years
	 *
	 * @return string
	 */
	function time ($in, $type) {
		if (is_callable($this->time)) {
			$time = $this->time;
			return $time($in, $type);
		} else {
			switch ($type) {
				case 's':
					return "$in $this->seconds";
					break;
				case 'm':
					return "$in $this->minutes";
					break;
				case 'h':
					return "$in $this->hours";
					break;
				case 'd':
					return "$in $this->days";
					break;
				case 'M':
					return "$in $this->months";
					break;
				case 'y':
					return "$in $this->years";
					break;
			}
		}
		return $in;
	}
	/**
	 * Allows to use formatted strings in translations
	 *
	 * @see format()
	 *
	 * @param string $item
	 * @param array  $arguments
	 *
	 * @return string
	 */
	function __call ($item, $arguments) {
		return $this->format($item, $arguments);
	}
	/**
	 * Allows to use formatted strings in translations
	 *
	 * @param string   $item
	 * @param string[] $arguments
	 *
	 * @return string
	 */
	function format ($item, $arguments) {
		return vsprintf($this->get($item), $arguments);
	}
	/**
	 * Formatting data according to language locale (translating months names, days of week, etc.)
	 *
	 * @param string|string[] $data
	 * @param bool            $short_may      When in date() or similar functions "M" format option is used, third month "May"
	 *                                        have the same short textual representation as full, so, this option allows to
	 *                                        specify, which exactly form of representation do you want
	 *
	 * @return string|string[]
	 */
	function to_locale ($data, $short_may = false) {
		if (is_array($data)) {
			foreach ($data as &$item) {
				$item = $this->to_locale($item, $short_may);
			}
			return $data;
		}
		if ($short_may) {
			$data = str_replace('May', 'MaY', $data);
		}
		$from = [
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December',
			'Jan',
			'Feb',
			'Mar',
			'Apr',
			'MaY',
			'Jun',
			'Jul',
			'Aug',
			'Sep',
			'Oct',
			'Nov',
			'Dec',
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday',
			'Sun',
			'Mon',
			'Tue',
			'Wed',
			'Thu',
			'Fri',
			'Sat'
		];
		foreach ($from as $f) {
			$data = str_replace($f, $this->get("l_$f"), $data);
		}
		return $data;
	}
	/**
	 * Implementation of JsonSerializable interface
	 *
	 * @return string[]
	 */
	function jsonSerialize () {
		return $this->translate[$this->clanguage];
	}
}
