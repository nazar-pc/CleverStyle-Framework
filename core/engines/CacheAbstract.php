<?php
abstract class CacheAbstract {
	/**
	 * @abstract
	 *
	 * @param string $item
	 *
	 * @return bool|mixed
	 */
	abstract function get ($item);
	/**
	 * @abstract
	 *
	 * @param string $item
	 * @param mixed $data
	 *
	 * @return bool
	 */
	abstract function set ($item, $data);
	/**
	 * @abstract
	 *
	 * @param string $item
	 *
	 * @return bool
	 */
	abstract function del ($item);
	/**
	 * @abstract
	 *
	 * @return bool
	 */
	abstract function clean ();
}