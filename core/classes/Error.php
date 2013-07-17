<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;
class Error {
	use	Singleton;

	public		$error			= true;	//Process errors
	protected	$num			= 0,	//Number of occurred errors
				$errors_list	= [];	//Array of errors for displaying
	/**
	 * Setting error handler
	 */
	function construct () {
		set_error_handler([$this, 'trigger']);
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
		$dump				= 'null';
		$debug_backtrace	= debug_backtrace();
		if (isset($debug_backtrace[0]['file'], $debug_backtrace[0]['file'])) {
			$file	= $debug_backtrace[0]['file'];
			$line	= $debug_backtrace[0]['line'];
		} else {
			$file	= $debug_backtrace[1]['file'];
			$line	= $debug_backtrace[1]['line'];
		}
		if (DEBUG) {
			$dump	= _json_encode($debug_backtrace);
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
				if (Index::instance(true)) {
					Index::instance()->__finish();
				} elseif (Page::instance(true)) {
					Page::instance()->error();
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
			if (Index::instance(true)) {
				Index::instance()->__finish();
			} elseif (Page::instance(true)) {
				Page::instance()->error();
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
		if (User::instance()->admin() || DEBUG) {
			if (!empty($this->errors_list_all)) {
				foreach ($this->errors_list as $error) {
					Page::instance()->warning($error);
				}
			}
		}
	}
}