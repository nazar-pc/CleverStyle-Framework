<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
class Error {
	public		$error			= true;	//Process errors
	protected	$num			= 0,	//Number of occurred errors
				$errors_list	= [];	//Array of errors for displaying
	/**
	 * Setting error handler
	 */
	function __construct () {
		global $Error;
		$Error = $this;
		set_error_handler([$Error, 'trigger']);
	}
	/**
	 * Is used as error handler
	 *
	 * @param int			$level	Error level
	 * @param null|string	$string	Error message
	 */
	function trigger ($level, $string = null) {
		if (!$this->error) {
			return;
		}
		$string				= xap($string);
		global $Config, $Index, $Page;
		$dump				= 'null';
		$debug_backtrace	= debug_backtrace();
		if (isset($debug_backtrace[0]['file'], $debug_backtrace[0]['file'])) {
			$file	= $debug_backtrace[0]['file'];
			$line	= $debug_backtrace[0]['line'];
		} else {
			$file	= $debug_backtrace[1]['file'];
			$line	= $debug_backtrace[1]['line'];
		}
		if ((is_object($Config) && $Config->core['on_error_globals_dump']) || (!is_object($Config) && defined('DEBUG') && DEBUG)) {
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
			$dump				= _json_encode([
				'Objects'			=> $objects_array,
				'debug_backtrace'	=> $debug_backtrace
			]);
			unset($objects_array);
		}
		unset($debug_backtrace);
		$log_file			= LOGS.'/'.date('d-m-Y').'_'.strtr(date_default_timezone_get(), '/', '_');
		$time				= date('d-m-Y h:i:s').' ['.microtime(true).']';
		switch ($level) {
			case E_USER_ERROR:
			case E_ERROR:
				++$this->num;
				file_put_contents($log_file, "E $time $string Occurred: $file:$line Dump: $dump\n", LOCK_EX | FILE_APPEND);
				unset($dump);
				$this->errors_list[]	= "E $time $string Occurred: $file:$line";
				define('ERROR_CODE', 500);
				if (is_object($Index)) {
					$Index->__finish();
				} elseif (is_object($Page)) {
					$Page->page();
				} else {
					__finish();
				}
			break;
			case E_USER_WARNING:
			case E_WARNING:
				++$this->num;
				file_put_contents($log_file, "W $time $string Occurred: $file:$line Dump: $dump\n", LOCK_EX | FILE_APPEND);
				unset($dump);
				$this->errors_list[]	= "W $time $string Occurred: $file:$line";
			break;
			default:
				file_put_contents($log_file, "N $time $string Occurred: $file:$line Dump: $dump\n", LOCK_EX | FILE_APPEND);
				unset($dump);
				$this->errors_list[]	= "N $time $string Occurred: $file:$line";
			break;
		}
		if ($this->num >= 100) {
			if (is_object($Index)) {
				$Index->__finish();
			} elseif (is_object($Page)) {
				$Page->page();
			} else {
				__finish();
			}
		}
	}
	/**
	 * Get number of occurred errors
	 *
	 * @return int
	 */
	function num () {
        return $this->num;
    }
	/**
	 * Displaying of errors
	 */
	function display () {
		global $User;
		if ($User->admin() || (defined('DEBUG') && DEBUG)) {
			if (!empty($this->errors_list_all)) {
				global $Page;
				foreach ($this->errors_list as $error) {
					$Page->warning($error);
				}
			}
		}
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
	global $Error;
	$Error = new Error;
}