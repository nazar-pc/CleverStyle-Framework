<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			Closure;
/**
 * Provides next triggers:
 *  System/general/languages/load
 *  [
 *   'clanguage'		=> clanguage
 *   'clang'			=> clang
 *   'clanguage_en'		=> clanguage_en
 *   'content_language'	=> content_language
 *   'locale'			=> locale
 *  ]
 */
defined('FIXED_LANGUAGE') || define('FIXED_LANGUAGE', false);
class Language {
	public		$clanguage,								//Current language
				$time = null;							//Closure for time processing
	protected	$init = false,							//For single initialization
				$translate = [],						//Local cache of translations
				$need_to_rebuild_cache = false;			//Necessity for cache rebuilding
	/**
	 * Set basic language
	 */
	function __construct () {
		global $Core, $L;
		$L	= $this;
		$this->change($Core->language);
	}
	/**
	 * Initialization: defining current language, loading translation
	 *
	 * @param string[]	$active_languages
	 * @param string	$language
	 *
	 * @return void
	 */
	function init ($active_languages, $language) {
		if ($this->init) {
			return;
		}
		global $Cache, $Config, $Core;
		if (!FIXED_LANGUAGE) {
			$language	= $this->scan_aliases($active_languages) ?: $language;
			if ($Config->core['multilingual']) {
				$language	= _getcookie('language') && in_array(_getcookie('language'), $active_languages) ? _getcookie('language') : $language;
			}
			$this->change($language);
			unset($language);
		}
		if ($this->need_to_rebuild_cache) {
			if (!empty($Config->components['modules'])) {
				foreach ($Config->components['modules'] as $module => $mdata) {
					if ($mdata['active'] != -1 && file_exists(MODULES.'/'.$module.'/languages/'.$this->clanguage.'.json')) {
						$this->translate	= array_merge(
							$this->translate,
							_json_decode_nocomments(file_get_contents(MODULES.'/'.$module.'/languages/'.$this->clanguage.'.json')) ?: []
						);
					}
				}
				unset($module, $mdata);
			}
			if (!empty($Config->components['plugins'])) {
				foreach ($Config->components['plugins'] as $plugin) {
					if (file_exists(PLUGINS.'/'.$plugin.'/languages/'.$this->clanguage.'.json')) {
						$this->translate	= array_merge(
							$this->translate,
							_json_decode_nocomments(file_get_contents(PLUGINS.'/'.$plugin.'/languages/'.$this->clanguage.'.json')) ?: []
						);
					}
				}
				unset($plugin);
			}
			$Core->run_trigger(
				'System/general/languages/load',
				[
					'clanguage'			=> $this->clanguage,
					'clang'				=> $this->clang,
					'clanguage_en'		=> $this->clanguage_en,
					'content_language'	=> $this->content_language,
					'locale'			=> $this->locale
				]
			);
			$Cache->{'languages/'.$this->clanguage} = $this->translate;
			$this->need_to_rebuild_cache = false;
		}
		$this->init = true;
	}
	/**
	 * Scanning of aliases for defining of current language
	 *
	 * @param array			$active_languages
	 *
	 * @return bool|string
	 */
	protected function scan_aliases ($active_languages) {
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return false;
		}
		global $Cache;
		if (($aliases = $Cache->{'languages/aliases'}) === false) {
			$aliases		= [];
			$aliases_list	= _strtolower(get_files_list(LANGUAGES.'/aliases'));
			foreach ($aliases_list as $alias) {
				$aliases[$alias] = file_get_contents(LANGUAGES.'/aliases/'.$alias);
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
				$accept_languages[]	= strtolower($v);
			}
		}
		unset($i, $v);
		foreach ($accept_languages as $language) {
			$language = explode(';', $language, 2)[0];
			if (isset($aliases[$language]) && in_array($aliases[$language], $active_languages)) {
				_setcookie('language', $aliases[$language]);
				return $aliases[$language];
			}
		}
		return false;
	}
	/**
	 * Get translation
	 *
	 * @param string		$item
	 * @param bool|string	$language	If specified - translation for specified language will be returned, otherwise for current
	 *
	 * @return string
	 */
	function get ($item, $language = false) {
		if (!$language) {
			return isset($this->translate[$item]) ? $this->translate[$item] : ucfirst(str_replace('_', ' ', $item));
		}
		global $Cache;
		if ($translate = $Cache->{'languages/'.$language}) {
			return isset($translate[$item]) ? $translate[$item] : ucfirst(str_replace('_', ' ', $item));
		}
		unset($translate);
		$current_language	= $this->clanguage;
		$this->change($language);
		$return				= isset($this->translate[$item]) ? $this->translate[$item] : ucfirst(str_replace('_', ' ', $item));
		$this->change($current_language);
		return $return;
	}
	/**
	 * Set translation
	 *
	 * @param array|string	$item	Item string, or key-value array
	 * @param null|string	$value
	 *
	 * @return void
	 */
	function set ($item, $value = null) {
		if (is_array($item)) {
			foreach ($item as $i => &$v) {
				$this->set($i, $v);
			}
		} else {
			$this->translate[$item] = $value;
		}
	}
	/**
	 * Get translation
	 *
	 * @param string	$item
	 *
	 * @return string
	 */
	function __get ($item) {
		return $this->get($item);
	}
	/**
	 * Set translation
	 *
	 * @param array|string	$item
	 * @param null|string	$value
	 *
	 * @return string
	 */
	function __set ($item, $value = null) {
		$this->set($item, $value);
	}
	/**
	 * Change language
	 *
	 * @param string	$language
	 *
	 * @return bool
	 */
	function change ($language) {
		if (empty($language)) {
			return false;
		}
		if ($language == $this->clanguage) {
			return true;
		}
		global $Config, $User;
		if (
			!is_object($Config) ||
			(
				(
					$Config->core['multilingual'] ||
					!$this->init ||
					(
						is_object($User) && $User->admin()
					)
				) &&
				in_array($language, $Config->core['active_languages'])
			)
		) {
			global $Cache;
			$this->clanguage = $language;
			if ($translate = $Cache->{'languages/'.$this->clanguage}) {
				$this->set($translate);
				header('Content-Language: '.$this->translate['content_language']);
				return true;
			} elseif (file_exists(LANGUAGES.'/'.$this->clanguage.'.json')) {
				$this->translate				= _json_decode_nocomments(file_get_contents(LANGUAGES.'/'.$this->clanguage.'.json'));
				$this->translate['clanguage']	= $this->clanguage;
				if (!isset($this->translate['clang'])) {
					$this->translate['clang']		= mb_strtolower(mb_substr($this->clanguage, 0, 2));
				}
				if (!isset($this->translate['clanguage_en'])) {
					$this->translate['clanguage_en']	= $this->clanguage;
				}
				if (!isset($this->translate['locale'])) {
					$this->translate['locale']			= $this->clang.'_'.strtoupper($this->clang);
				}
				header('Content-Language: '.$this->translate['content_language']);
				$this->need_to_rebuild_cache	= true;
				if ($this->init) {
					$this->init($Config->core['active_languages'], $language);
				}
				return true;
			} elseif (_include(LANGUAGES.'/'.$this->clanguage.'.php', false, false)) {
				header('Content-language: '.$this->translate['clang']);
				return true;
			}
		}
		return false;
	}
	/**
	 * Time formatting according to the current language (adding correct endings)
	 *
	 * @param int		$in		time (number)
	 * @param string	$type	Type of formatting<br>
	 * 							s - seconds<br>m - minutes<br>h - hours<br>d - days<br>M - months<br>y - years
	 *
	 * @return string
	 */
	function time ($in, $type) {
		if ($this->time instanceof Closure) {
			return $this->time->__invoke($in, $type);
		} else {
			global $L;
			switch ($type) {
				case 's':
					return $in.' '.$L->seconds;
				break;
				case 'm':
					return $in.' '.$L->minutes;
				break;
				case 'h':
					return $in.' '.$L->hours;
				break;
				case 'd':
					return $in.' '.$L->days;
				break;
				case 'M':
					return $in.' '.$L->months;
				break;
				case 'y':
					return $in.' '.$L->years;
				break;
			}
		}
		return $in;
	}
	/**
	 * Allows to use formatted strings in translations
	 *
	 * @see format()
	 * @param string	$name
	 * @param array		$arguments
	 *
	 * @return string
	 */
	function __call ($name, $arguments) {
		return $this->format($name, $arguments);
	}
	/**
	 * Allows to use formatted strings in translations
	 *
	 * @param string	$name
	 * @param array		$arguments
	 *
	 * @return string
	 */
	function format ($name, $arguments) {
		return vsprintf($this->get($name), $arguments);
	}
	/**
	 * Formatting data according to language locale (translating months names, days of week, etc.)
	 *
	 * @param string|string[]	$data
	 * @param bool				$short_may	When in date() or similar functions "M" format option is used, third month "May"
	 * 										have the same short textual representation as full, so, this option allows to
	 * 										specify, which exactly form of representation do you want
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
			$data = str_replace($f, $this->get('l_'.$f), $data);
		}
		return $data;
	}
	/**
	 * Get all translations in JSON format
	 *
	 * @return string
	 */
	function get_json () {
		return _json_encode($this->translate);
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
}
/**
 * For IDE
 */
if (false) {
	global $L;
	$L = new Language;
}