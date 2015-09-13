<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	Exception;

class ExitException extends Exception {
	/**
	 * @var bool
	 */
	protected $json = false;
	/**
	 * ExitException constructor.
	 *
	 * @param int|string     $message Error message (or code if no message)
	 * @param int            $code    HTTP status code
	 * @param Exception|null $previous
	 */
	function __construct ($message = '', $code = 0, Exception $previous = null) {
		$this->previous = $previous;
		$this->code     = $code;
		if (is_numeric($message) && !$code) {
			$this->code = $message;
		} else {
			$this->message = $message;
		}
	}
	/**
	 * @return bool
	 */
	function getJson () {
		return $this->json;
	}
	/**
	 * Specify that error should be in JSON format
	 *
	 * @return $this
	 */
	function setJson () {
		$this->json = true;
		return $this;
	}
}
