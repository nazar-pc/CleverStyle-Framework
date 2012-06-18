<?php
/**
 * Provides next triggers:<br>
 *  admin/System/general/languages/load<code>
 *  [
 *   'clanguage'	=> <i>clanguage</i><br>
 *   'clang'		=> <i>clang</i><br>
 *   'clanguage_en'	=> <i>clanguage_en</i><br>
 *   'clocale'		=> <i>clocale</i><br>
 *  ]</code>
 */
class Language {
	public		$clanguage,								//Current language
				$time = '';								//Closure for time processing
	protected	$init = false,							//For single initialization
				$translate = [],						//Local cache of translations
				$need_to_rebuild_cache = false;			//Necessity for cache rebuilding
	/**
	 * Set basic language
	 */
	function __construct () {
		global $LANGUAGE, $L;
		$L = $this;
		$this->change($LANGUAGE);
	}
	/**
	 * @param array  $active_languages
	 * @param string $language
	 *
	 * @return void
	 */
	function init ($active_languages, $language) {
		if ($this->init) {
			return;
		}
		$this->init = true;
		$this->change(
			_getcookie('language') && in_array(_getcookie('language'), $active_languages) ? _getcookie('language') : (
				$this->scan_aliases($active_languages) ?: $language
			)
		);
		if ($this->need_to_rebuild_cache) {
			global $Cache;
			global $Core;
			$Core->run_trigger(
				'admin/System/general/languages/load',
				[
					'clanguage'		=> $this->translate['clanguage'],
					'clang'			=> $this->translate['clang'],
					'clanguage_en'	=> $this->translate['clanguage_en'],
					'clocale'		=> $this->translate['clocale']
				]
			);
			$Cache->{'languages/'.$this->clanguage} = $this->translate;
			$this->need_to_rebuild_cache = false;
			$this->init = true;
		}
	}
	/**
	 * @param array $active_languages
	 *
	 * @return bool|string
	 */
	protected function scan_aliases ($active_languages) {
		global $Cache;
		if (($aliases = $Cache->{'languages/aliases'}) === false) {
			$aliases		= [];
			$aliases_list	= _strtolower(get_list(LANGUAGES.DS.'aliases'));
			foreach ($aliases_list as $alias) {
				$aliases[$alias] = _file_get_contents(LANGUAGES.DS.'aliases'.DS.$alias);
			}
			unset($aliases_list, $alias);
			$Cache->{'languages/aliases'} = $aliases;
		}
		$accept_languages = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
		foreach ($accept_languages as $index => $language) {
			$index = substr($index, 0, strpos($index, ';'));
			if (in_array($index, $aliases) && in_array($index, $active_languages)) {
				_setcookie('language', $language);
				return $language;
			}
		}
		return false;
	}
	/**
	 * Get translation
	 *
	 * @param string $item
	 *
	 * @return string
	 */
	function get ($item) {
		return isset($this->translate[$item]) ? $this->translate[$item] : ucfirst(str_replace('_', ' ', $item));
	}
	/**
	 * Set translation
	 *
	 * @param array|string $item
	 * @param null|string $value
	 *
	 * @return string
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
	 * @param null|string $value
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
		if (empty($language)) {
			return false;
		}
		if ($language == $this->clanguage) {
			return true;
		}
		global $Config;
		if (!is_object($Config) || ($Config->core['multilanguage'] && in_array($language, $Config->core['active_languages']))) {
			global $Cache, $Text;
			$this->clanguage = $language;
			if ($translate = $Cache->{'languages/'.$this->clanguage}) {
				$this->set($translate);
				if (!($Text instanceof Loader)) {
					$Text->language($this->clang);
				}
				return true;
			} elseif (_file_exists(LANGUAGES.'/lang.'.$this->clanguage.'.json')) {
				$data = _file(LANGUAGES.'/lang.'.$this->clanguage.'.json', FILE_SKIP_EMPTY_LINES);
				_include(LANGUAGES.'/lang.'.$this->clanguage.'.php', false, false);
				foreach ($data as $i => $line) {
					if (substr(ltrim($line), 0, 2) == '//') {
						unset($data[$i]);
					}
				}
				unset($i, $line);
				$this->translate = _json_decode(implode('', $data));
				$this->translate['clanguage'] = $this->clanguage;
				if(!isset($this->translate['clang'])) {
					$this->translate['clang'] = mb_strtolower(mb_substr($this->clanguage, 0, 2));
				}
				if(!isset($this->translate['clanguage_en'])) {
					$this->translate['clanguage_en'] = $this->clanguage;
				}
				if(!isset($this->translate['clocale'])) {
					$this->translate['clocale'] = $this->clang.'_'.mb_strtoupper($this->clang);
				}
				setlocale(LC_TIME | (defined('LC_MESSAGES') ? LC_MESSAGES : 0), $this->clocale);
				if (!($Text instanceof Loader)) {
					$Text->language($this->clang);
				}
				$this->need_to_rebuild_cache = true;
				if ($this->init) {
					$this->init($Config->core['active_languages'], $language);
				}
				return true;
			} elseif (_include(LANGUAGES.'/lang.'.$this->clanguage.'.php', false, false)) {
				return true;
			}
		}
		return false;
	}
	/**
	 * Time formatting according to the current language (adding correct endings)
	 *
	 * @param int $in		time in seconds
	 * @param string $type	Type of formatting<br>
	 * 						s - seconds<br>m - minutes<br>h - hours<br>d - days<br>M - months<br>y - years
	 *
	 * @return string
	 */
	function time ($in, $type) {
		if ($this->time instanceof Closure) {
			$tmp = $this->time;
			return $tmp($in, $type);
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
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 */
	function __call ($name, $arguments) {
		return $this->format($name, $arguments);
	}
	/**
	 * Allows to use formatted strings in translations
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 */
	function format ($name, $arguments) {
		return vsprintf($this->get($name), $arguments);
	}
	/**
	 * Cloning restriction
	 */
	function __clone () {}
}