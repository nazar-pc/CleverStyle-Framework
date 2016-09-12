<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	JsonSerializable,
	cs\Language\Prefix;

/**
 * Provides next events:
 *  System/Language/change/before
 *
 *  System/Language/change/after
 *
 *  System/Language/load
 *  [
 *   'clanguage'        => clanguage
 *   'clang'            => clang
 *   'cregion'          => cregion
 *  ]
 *
 * @method static $this instance($check = false)
 *
 * @property string $clanguage
 * @property string $clang
 * @property string $cregion
 * @property string $content_language
 * @property string $_datetime_long
 * @property string $_datetime
 * @property string $_date
 * @property string $_time
 */
class Language implements JsonSerializable {
	use
		Singleton;
	const INIT_STATE_METHOD = 'init';
	/**
	 * Callable for time processing
	 *
	 * @var callable
	 */
	public $time;
	/**
	 * @var string
	 */
	protected $current_language;
	/**
	 * Local cache of translations
	 *
	 * @var array
	 */
	protected $translation = [];
	/**
	 * Cache to optimize frequent calls
	 *
	 * @var array
	 */
	protected $localized_url = [];
	protected function init () {
		/**
		 * Initialization: set default language based on system configuration and request-specific parameters
		 */
		$Config = Config::instance(true);
		/**
		 * We need Config for initialization
		 */
		if (!$Config) {
			Event::instance()->once(
				'System/Config/init/after',
				function () {
					$this->init_internal();
				}
			);
		} else {
			$this->init_internal();
		}
		/**
		 * Change language when configuration changes
		 */
		Event::instance()->on(
			'System/Config/changed',
			function () {
				$this->init_internal();
			}
		);
	}
	protected function init_internal () {
		$Config   = Config::instance();
		$language = '';
		if ($Config->core['multilingual']) {
			$language = User::instance(true)->language;
			/**
			 * Highest priority - `-Locale` header
			 */
			/** @noinspection PhpParamsInspection */
			$language = $language ?: $this->check_locale_header($Config->core['active_languages']);
			/**
			 * Second priority - URL
			 */
			$language = $language ?: $this->url_language(Request::instance()->path);
			/**
			 * Third - `Accept-Language` header
			 */
			/** @noinspection PhpParamsInspection */
			$language = $language ?: $this->check_accept_header($Config->core['active_languages']);
		}
		$this->current_language = $language ?: $Config->core['language'];
	}
	/**
	 * Returns instance for simplified work with translations, when using common prefix
	 *
	 * @param string $prefix
	 *
	 * @return Prefix
	 */
	public static function prefix ($prefix) {
		return new Prefix($prefix);
	}
	/**
	 * Does URL have language prefix
	 *
	 * @param false|string $url Relative url, `Request::instance()->path` by default
	 *
	 * @return false|string If there is language prefix - language will be returned, `false` otherwise
	 */
	public function url_language ($url = false) {
		/**
		 * @var string $url
		 */
		$url = $url ?: Request::instance()->path;
		if (isset($this->localized_url[$url])) {
			return $this->localized_url[$url];
		}
		$aliases = $this->get_aliases();
		$clang   = explode('?', $url, 2)[0];
		$clang   = explode('/', trim($clang, '/'), 2)[0];
		if (isset($aliases[$clang])) {
			if (count($this->localized_url) > 100) {
				$this->localized_url = [];
			}
			return $this->localized_url[$url] = $aliases[$clang];
		}
		return false;
	}
	/**
	 * Checking Accept-Language header for languages that exists in configuration
	 *
	 * @param array $active_languages
	 *
	 * @return false|string
	 */
	protected function check_accept_header ($active_languages) {
		$aliases          = $this->get_aliases();
		$accept_languages = array_filter(
			explode(
				',',
				strtolower(
					str_replace('-', '_', Request::instance()->header('accept-language'))
				)
			)
		);
		foreach ($accept_languages as $language) {
			$language = explode(';', $language, 2)[0];
			if (@in_array($aliases[$language], $active_languages)) {
				return $aliases[$language];
			}
		}
		return false;
	}
	/**
	 * Check `*-Locale` header (for instance, `X-Facebook-Locale`) that exists in configuration
	 *
	 * @param string[] $active_languages
	 *
	 * @return false|string
	 */
	protected function check_locale_header ($active_languages) {
		$aliases = $this->get_aliases();
		/**
		 * For `X-Facebook-Locale` and other similar
		 */
		foreach (Request::instance()->headers ?: [] as $i => $v) {
			if (stripos($i, '-locale') !== false) {
				$language = strtolower($v);
				if (@in_array($aliases[$language], $active_languages)) {
					return $aliases[$language];
				}
				return false;
			}
		}
		return false;
	}
	/**
	 * Get languages aliases
	 *
	 * @return array|false
	 */
	protected function get_aliases () {
		return Cache::instance()->get(
			'languages/aliases',
			function () {
				$aliases = [];
				/**
				 * @var string[] $aliases_list
				 */
				$aliases_list = _strtolower(get_files_list(LANGUAGES.'/aliases'));
				foreach ($aliases_list as $alias) {
					$aliases[$alias] = trim(file_get_contents(LANGUAGES."/aliases/$alias"));
				}
				return $aliases;
			}
		);
	}
	/**
	 * Get translation
	 *
	 * @param bool|string  $item
	 * @param false|string $language If specified - translation for specified language will be returned, otherwise for current
	 * @param string       $prefix   Used by `\cs\Language\Prefix`, usually no need to use it directly
	 *
	 * @return string
	 */
	public function get ($item, $language = false, $prefix = '') {
		/**
		 * Small optimization, we can actually return value without translations
		 */
		if ($item == 'clanguage' && $this->current_language && $language === false && !$prefix) {
			return $this->current_language;
		}
		$language = $language ?: $this->current_language;
		if (isset($this->translation[$language])) {
			$translation = $this->translation[$language];
			if (isset($translation[$prefix.$item])) {
				return $translation[$prefix.$item];
			} elseif (isset($translation[$item])) {
				return $translation[$item];
			}
			return ucfirst(str_replace('_', ' ', $item));
		}
		$current_language = $this->current_language;
		$this->change($language);
		$return = $this->get($item, $this->current_language, $prefix);
		$this->change($current_language);
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
	public function set ($item, $value = null) {
		$translate = &$this->translation[$this->current_language];
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
	public function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Set translation
	 *
	 * @param array|string $item
	 * @param null|string  $value
	 */
	public function __set ($item, $value = null) {
		$this->set($item, $value);
	}
	/**
	 * Change language
	 *
	 * @param string $language
	 *
	 * @return bool
	 */
	public function change ($language) {
		/**
		 * Already set to specified language
		 */
		if ($language == $this->current_language && isset($this->translation[$language])) {
			return true;
		}
		$Config = Config::instance(true);
		/**
		 * @var string $language
		 */
		$language = $language ?: $Config->core['language'];
		if (!$this->can_be_changed_to($Config, $language)) {
			return false;
		}
		$Event = Event::instance();
		$Event->fire('System/Language/change/before');
		if (!isset($this->translation[$language])) {
			$this->translation[$language] = $this->get_translation($language);
		}
		/**
		 * Change current language to `$language`
		 */
		$this->current_language = $language;
		_include(LANGUAGES."/$language.php", false, false);
		$Request = Request::instance();
		if ($Request->regular_path && $Config->core['multilingual']) {
			Response::instance()->header('content-language', $this->content_language);
		}
		$Event->fire('System/Language/change/after');
		return true;
	}
	/**
	 * Check whether it is allowed to change to specified language according to configuration
	 *
	 * @param Config $Config
	 * @param string $language
	 *
	 * @return bool
	 */
	protected function can_be_changed_to ($Config, $language) {
		if (!$language) {
			return false;
		}
		return
			// Config not loaded yet
			!$Config->core ||
			// Set to language that is configured on system level
			$language == $Config->core['language'] ||
			// Set to active language
			(
				$Config->core['multilingual'] &&
				in_array($language, $Config->core['active_languages'])
			);
	}
	protected function get_translation ($language) {
		return Cache::instance()->get(
			"languages/$language",
			function () use ($language) {
				return $this->get_translation_internal($language);
			}
		);
	}
	/**
	 * Load translation from all over the system, set `$this->translation[$language]` and return it
	 *
	 * @param $language
	 *
	 * @return string[]
	 */
	protected function get_translation_internal ($language) {
		/**
		 * Get current system translations
		 */
		$translation = $this->get_translation_from_json(LANGUAGES."/$language.json");
		$translation = $this->fill_required_translation_keys($translation, $language);
		/**
		 * Set modules' translations
		 */
		foreach (get_files_list(MODULES, false, 'd', true) as $module_dir) {
			if (file_exists("$module_dir/languages/$language.json")) {
				$translation = $this->get_translation_from_json("$module_dir/languages/$language.json") + $translation;
			}
		}
		Event::instance()->fire(
			'System/Language/load',
			[
				'clanguage' => $language,
				'clang'     => $translation['clang'],
				'cregion'   => $translation['cregion']
			]
		);
		// TODO: Remove in 6.x
		Event::instance()->fire(
			'System/general/languages/load',
			[
				'clanguage' => $language,
				'clang'     => $translation['clang'],
				'cregion'   => $translation['cregion']
			]
		);
		/**
		 * Append translations from core language to fill potentially missing keys
		 */
		$core_language = Core::instance()->language;
		if ($language != $core_language) {
			$translation += $this->get_translation($core_language);
		}
		return $translation;
	}
	/**
	 * @param string $filename
	 *
	 * @return string[]
	 */
	protected function get_translation_from_json ($filename) {
		return $this->get_translation_from_json_internal(
			file_get_json_nocomments($filename)
		);
	}
	/**
	 * @param string[]|string[][] $translation
	 *
	 * @return string[]
	 */
	protected function get_translation_from_json_internal ($translation) {
		// Nested structure processing
		foreach ($translation as $item => $value) {
			if (is_array_assoc($value)) {
				unset($translation[$item]);
				foreach ($value as $sub_item => $sub_value) {
					$translation[$item.$sub_item] = $sub_value;
				}
				return $this->get_translation_from_json_internal($translation);
			}
		}
		return $translation;
	}
	/**
	 * Some required keys might be missing in translation, this functions tries to guess and fill them automatically
	 *
	 * @param string[] $translation
	 * @param string   $language
	 *
	 * @return string[]
	 */
	protected function fill_required_translation_keys ($translation, $language) {
		$translation += [
			'clanguage' => $language,
			'clang'     => mb_strtolower(mb_substr($language, 0, 2)),
			'clocale'   => $translation['clang'].'_'.mb_strtoupper($translation['cregion'])
		];
		$translation += [
			'content_language' => $translation['clang'],
			'cregion'          => $translation['clang']
		];
		return $translation;
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
	public function time ($in, $type) {
		if (is_callable($this->time)) {
			$time = $this->time;
			return $time($in, $type);
		} else {
			switch ($type) {
				case 's':
					return "$in $this->system_time_seconds";
				case 'm':
					return "$in $this->system_time_minutes";
				case 'h':
					return "$in $this->system_time_hours";
				case 'd':
					return "$in $this->system_time_days";
				case 'M':
					return "$in $this->system_time_months";
				case 'y':
					return "$in $this->system_time_years";
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
	public function __call ($item, $arguments) {
		return $this->format($item, $arguments);
	}
	/**
	 * Allows to use formatted strings in translations
	 *
	 * @param string       $item
	 * @param string[]     $arguments
	 * @param false|string $language If specified - translation for specified language will be returned, otherwise for current
	 * @param string       $prefix   Used by `\cs\Language\Prefix`, usually no need to use it directly
	 *
	 * @return string
	 */
	public function format ($item, $arguments, $language = false, $prefix = '') {
		return vsprintf($this->get($item, $language, $prefix), $arguments);
	}
	/**
	 * Formatting date according to language locale (translating months names, days of week, etc.)
	 *
	 * @param string|string[] $data
	 * @param bool            $short_may When in date() or similar functions "M" format option is used, third month "May" have the same short textual
	 *                                   representation as full, so, this option allows to specify, which exactly form of representation do you want
	 *
	 * @return string|string[]
	 */
	public function to_locale ($data, $short_may = false) {
		if (is_array($data)) {
			foreach ($data as &$item) {
				$item = $this->to_locale($item, $short_may);
			}
			return $data;
		}
		if ($short_may) {
			$data = str_replace('May', 'May_short', $data);
		}
		$from = [
			'January',
			'February',
			'March',
			'April',
			'May_short',
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
			'May',
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
	public function jsonSerialize () {
		return $this->translation[$this->current_language];
	}
}
