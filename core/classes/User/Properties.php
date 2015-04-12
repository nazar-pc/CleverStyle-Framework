<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\User;
use			cs\User;
/**
 * Class for getting of user information
 *
 * @property	int		$id
 * @property	string	$login
 * @property	string	$login_hash		sha224 hash
 * @property	string	$username
 * @property	string	$password_hash	sha512 hash
 * @property	string	$email
 * @property	string	$email_hash		sha224 hash
 * @property	string	$language
 * @property	string	$timezone
 * @property	int		$reg_date		unix timestamp
 * @property	string	$reg_ip			hex value, obtained by function ip2hex()
 * @property	string	$reg_key		random md5 hash, generated during registration
 * @property	int		$status			'-1' - not activated (for example after registration), 0 - inactive, 1 - active
 * @property	int		$block_until	unix timestamp
 * @property	int		$last_sign_in	unix timestamp
 * @property	string	$last_ip		hex value, obtained by function ip2hex()
 * @property	int		$last_online	unix timestamp
 * @property	string	$avatar
 */
class Properties {
	/**
	 * @var int
	 */
	protected	$id;
	/**
	 * Creating of object and saving user id inside
	 *
	 * @param int $user
	 */
	function __construct ($user) {
		$this->id	= $user;
	}
	/**
	 * Get data item of user
	 *
	 * @param string|string[]		$item
	 *
	 * @return false|string|mixed[]
	 */
	function get ($item) {
		return User::instance()->get($item, $this->id);
	}
	/**
	 * Set data item of specified user
	 *
	 * @param array|string	$item
	 * @param mixed|null	$value
	 *
	 * @return bool
	 */
	function set ($item, $value = null) {
		return User::instance()->set($item, $value, $this->id);
	}
	/**
	 * Get data item of user
	 *
	 * @param string|string[]		$item
	 *
	 * @return array|false|string
	 */
	function __get ($item) {
		return User::instance()->get($item, $this->id);
	}
	/**
	 * Get user avatar, if no one present - uses Gravatar
	 *
	 * @param int|null	$size	Avatar size, if not specified or resizing is not possible - original image is used
	 *
	 * @return string
	 */
	function avatar ($size = null) {
		return User::instance()->avatar($size, $this->id);
	}
	/**
	 * Get user name or login or email, depending on existing information
	 *
	 * @return string
	 */
	function username () {
		return $this->get('username') ?: ($this->get('login') ?: $this->get('email'));
	}
	/**
	 * Set data item of user
	 *
	 * @param array|string	$item
	 * @param mixed|null	$value
	 */
	function __set ($item, $value = null) {
		User::instance()->set($item, $value, $this->id);
	}
	/**
	 * Getting additional data item(s) of specified user
	 *
	 * @param string|string[]		$item
	 *
	 * @return false|string|mixed[]
	 */
	function get_data ($item) {
		return User::instance()->get_data($item, $this->id);
	}
	/**
	 * Setting additional data item(s) of specified user
	 *
	 * @param array|string	$item
	 * @param mixed|null	$value
	 *
	 * @return bool
	 */
	function set_data ($item, $value = null) {
		return User::instance()->set_data($item, $value, $this->id);
	}
	/**
	 * Deletion of additional data item(s) of specified user
	 *
	 * @param string|string[]		$item
	 *
	 * @return bool
	 */
	function del_data ($item) {
		return User::instance()->del_data($item, $this->id);
	}
}
