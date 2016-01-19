<?php
/**
 * @package   CleverStyle CMS
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\Cache;
use
	cs\Core;

/**
 * Provides cache functionality based on Memcached.
 * Support optionally base configuration option Core::instance()->memcached_host and Core::instance()->memcached_port
 */
class Memcached extends _Abstract_with_namespace {
	/**
	 * @var \Memcached
	 */
	protected $memcached;
	function __construct () {
		if (!extension_loaded('memcached')) {
			return;
		}
		$this->memcached = new \Memcached(DOMAIN);
		$Core            = Core::instance();
		$this->memcached->addServer($Core->memcached_host ?: '127.0.0.1', $Core->memcached_port ?: 11211);
	}
	/**
	 * @inheritdoc
	 */
	protected function available_internal () {
		return (bool)$this->memcached;
	}
	/**
	 * @inheritdoc
	 */
	protected function get_internal ($item) {
		return $this->memcached->get($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function set_internal ($item, $data) {
		return $this->memcached->set($item, $data);
	}
	/**
	 * @inheritdoc
	 */
	protected function del_internal ($item) {
		return $this->memcached->delete($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function increment_internal ($item) {
		return $this->memcached->increment($item);
	}
	/**
	 * @inheritdoc
	 */
	protected function clean_internal () {
		return $this->memcached->flush();
	}
	/**
	 * Close connections to memcached servers
	 */
	function __destruct () {
		if ($this->memcached) {
			$this->memcached->quit();
		}
	}
}
