<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Request;
use
	cs\Config;

trait Cookie {
	/**
	 * Cookie array, similar to `$_COOKIE`, but also contains un-prefixed keys according to system configuration
	 *
	 * @var array
	 */
	public $cookie;
	/**
	 * @param array $cookie Typically `$_COOKIE`
	 */
	function init_cookie ($cookie = []) {
		$this->cookie = $cookie;
		/**
		 * Fill un-prefixed keys according to system configuration
		 */
		$prefix        = Config::instance()->core['cookie_prefix'];
		$prefix_length = strlen($prefix);
		if ($prefix_length) {
			foreach ($cookie as $key => $value) {
				if (strpos($key, $prefix) === 0) {
					$this->cookie[substr($key, $prefix_length)] = $value;
				}
			}
		}
	}
	/**
	 * Get cookie by name
	 *
	 * @param string $name
	 *
	 * @return null|string Cookie content if exists or `null` otherwise
	 */
	function cookie ($name) {
		return @$this->cookie[$name];
	}
}
