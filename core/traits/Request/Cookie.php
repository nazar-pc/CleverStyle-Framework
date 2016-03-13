<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;

trait Cookie {
	/**
	 * Cookie array, similar to `$_COOKIE`
	 *
	 * @var array
	 */
	public $cookie;
	/**
	 * @param array $cookie Typically `$_COOKIE`
	 */
	function init_cookie ($cookie = []) {
		$this->cookie = $cookie;
	}
}
