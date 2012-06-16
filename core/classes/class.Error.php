<?php
class Error {
	public		$error					= true;
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
		$debug_backtrace = debug_backtrace();
		if (is_object($Config) && $Config->core['on_error_objects_dump']) {
			ob_start();
			var_dump($GLOBALS);
			$dump = _json_encode([
				'GLOBALS'			=> ob_get_clean(),
				'debug_backtrace'	=> $debug_backtrace
			]);
		}
		switch ($level) {
			case E_USER_ERROR:
			case E_ERROR:
				++$this->num;
				$this->errors_list_all[]		=	'E '.date('H:i:s', TIME).' ['.MICROTIME.'] '.$string.
													' Occured: '.$debug_backtrace[1]['file'].':'.$debug_backtrace[1]['line'].' Dump: '.$dump."\n";
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
				$this->errors_list_all[]		=	'W '.date('H:i:s', TIME).' ['.MICROTIME.'] '.$string.
													' Occured: '.$debug_backtrace[1]['file'].':'.$debug_backtrace[1]['line'].' Dump: '.$dump."\n";
			break;
			default:
				$this->errors_list_all[]		=	'N '.date('H:i:s', TIME).' ['.MICROTIME.'] '.$string.
													' Occured: '.$debug_backtrace[1]['file'].':'.$debug_backtrace[1]['line'].' Dump: '.$dump."\n";
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
				global $Page;
				foreach ($this->errors_list_all as $error) {
					$Page->warning($error);
				}
				$this->errors_list_all = [];
			}
		} else {
			if (!empty($this->errors_list_display)) {
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
	 */
	function __clone () {}
	/**
 	 * Writing occured errors to the log file
	 */
	function __finish () {
		if (!empty($this->errors_list_all)) {
			_file_put_contents(LOGS.DS.date('d-m-Y', TIME), implode("\n", $this->errors_list_all)."\n", LOCK_EX | FILE_APPEND);
			$this->errors_list_all = [];
		}
	}
}