<?php
namespace cs;
class Error {
	public		$error					= true;	//Process errors
	protected	$num					= 0,	//Number of occured errors
				$errors_list_all		= [],	//Array of all errors
				$errors_list_display	= [];	//Array of non-critical errors to show to user
	function __construct () {
		global $Error;
		$Error = $this;
		set_error_handler([$Error, 'trigger']);
	}
	/**
	 * Is used as error handler
	 *
	 * @param      $level	Error level
	 * @param null $string	Error message
	 */
	function trigger ($level, $string = null) {
		if (!$this->error) {
			return;
		}
		$string = xap($string);
		global $Config, $Index, $Page;
		$dump = 'null';
		$debug_backtrace	= debug_backtrace();
		if (isset($debug_backtrace[0]['file'], $debug_backtrace[0]['file'])) {
			$file	= $debug_backtrace[0]['file'];
			$line	= $debug_backtrace[0]['line'];
		} else {
			$file	= $debug_backtrace[1]['file'];
			$line	= $debug_backtrace[1]['line'];
		}
		if ((is_object($Config) && $Config->core['on_error_globals_dump']) || (!is_object($Config) && defined('DEBUG'))) {
			global $Core;
			$objects_array		= [];
			if (is_object($Core)) {
				foreach ($Core->Loaded as $object => $data) {
					if (!isset($GLOBALS[$object])) {
						continue;
					}
					$objects_array[$object] = print_r($GLOBALS[$object], true);
				}
				unset($object, $data);
			}
			$dump = _json_encode([
				'Objects'			=> $objects_array,
				'debug_backtrace'	=> $debug_backtrace
			]);
			unset($debug_backtrace, $objects_array);
		}
		switch ($level) {
			case E_USER_ERROR:
			case E_ERROR:
				++$this->num;
				$this->errors_list_all[]		=	'E %time% ['.MICROTIME.'] '.$string.
													' Occured: '.$file.':'.$line.' Dump: '.$dump."\n";
				define('ERROR_PAGE', 500);
				if (is_object($Index)) {
					$Index->__finish();
				} else {
					$Page->error_page();
				}
			break;
			case E_USER_WARNING:
			case E_WARNING:
				++$this->num;
				$this->errors_list_all[]		=	'W %time% ['.MICROTIME.'] '.$string.
													' Occured: '.$file.':'.$line.' Dump: '.$dump."\n";
			break;
			default:
				$this->errors_list_all[]		=	'N %time% ['.MICROTIME.'] '.$string.
													' Occured: '.$file.':'.$line.' Dump: '.$dump."\n";
				$this->errors_list_display[]	= $string;
			break;
		}
	}
	/**
	 * Get number of occured errors
	 *
	 * @return int
	 */
	function num () {
        return $this->num;
    }
	/**
 	 * Displaying errors
	 */
	function display () {
		global $User;
		if ($User->is('admin')) {
			if (!empty($this->errors_list_all)) {
				$this->errors_list_all		= str_replace('%time%', date('H:i:s', TIME), $this->errors_list_all);
				global $Page;
				foreach ($this->errors_list_all as $error) {
					$Page->warning($error);
				}
			}
		} else {
			if (!empty($this->errors_list_display)) {
				$this->errors_list_display	= str_replace('%time%', date('H:i:s', TIME), $this->errors_list_display);
				global $Page;
				foreach ($this->errors_list_display as $error) {
					$Page->warning($error);
				}
				$this->errors_list_display = [];
			}
		}
	}
	/**
	 * Cloning restriction
	 *
	 * @final
	 */
	function __clone () {}
	/**
 	 * Writing occured errors to the log file
	 */
	function __finish () {
		if (!empty($this->errors_list_all)) {
			$this->errors_list_all		= str_replace('%time%', date('H:i:s', TIME), $this->errors_list_all);
			file_put_contents(LOGS.'/'.date('d-m-Y', TIME).'_'.strtr(date_default_timezone_get(), '/', '_'), implode("\n", $this->errors_list_all)."\n", LOCK_EX | FILE_APPEND);
			$this->errors_list_all = [];
		}
	}
}