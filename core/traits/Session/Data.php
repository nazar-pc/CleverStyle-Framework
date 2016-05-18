<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Session;
use
	cs\User;

trait Data {
	/**
	 * Get data, stored with session
	 *
	 * @param string      $item
	 * @param null|string $session_id
	 *
	 * @return false|mixed
	 *
	 */
	function get_data ($item, $session_id = null) {
		$session_data = $this->get_data_internal($session_id);
		return isset($session_data['data'][$item]) ? $session_data['data'][$item] : false;
	}
	/*
	 * @param null|string $session_id
	 *
	 * @return array|false
	 */
	protected function get_data_internal ($session_id) {
		$session_id = $session_id ?: $this->session_id;
		return is_md5($session_id) ? $this->get_internal($session_id) : false;
	}
	/**
	 * Store data with session
	 *
	 * @param string      $item
	 * @param mixed       $value
	 * @param null|string $session_id
	 *
	 * @return bool
	 *
	 */
	function set_data ($item, $value, $session_id = null) {
		$session_data = $this->get_data_internal($session_id);
		/**
		 * If there is no session yet - let's create one
		 */
		if (!$session_data) {
			$session_id   = $this->add(User::GUEST_ID);
			$session_data = $this->get_data_internal($session_id);
		}
		if (!isset($session_data['data'])) {
			return false;
		}
		$session_data['data'][$item] = $value;
		return $this->update($session_data) && $this->cache->del($session_id);
	}
	/**
	 * Delete data, stored with session
	 *
	 * @param string      $item
	 * @param null|string $session_id
	 *
	 * @return bool
	 *
	 */
	function del_data ($item, $session_id = null) {
		$session_data = $this->get_data_internal($session_id);
		if (!isset($session_data['data'][$item])) {
			return true;
		}
		return $this->update($session_data) && $this->cache->del($session_id);
	}
}
