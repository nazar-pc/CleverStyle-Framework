<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Config,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Orders instance($check = false)
 */
class Orders {
	use
		CRUD,
		Singleton;

	protected $data_model = [
		'id'               => 'int',
		'user'             => 'int',
		'date'             => 'int',
		'shipping_type'    => 'int',
		'shipping_phone'   => 'text',
		'shipping_address' => 'text',
		'status'           => 'int',
		'comment'          => 'text'
	];
	protected $table      = '[prefix]shop_orders';

	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Shop')->db('shop');
	}
	/**
	 * Get item
	 *
	 * @param int|int[] $id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		return $this->read_simple($id);
	}
	/**
	 * Add new order
	 *
	 * @param int    $user
	 * @param int    $shipping_type
	 * @param string $shipping_phone
	 * @param string $shipping_address
	 * @param int    $status
	 * @param string $comment
	 *
	 * @return bool|int Id of created item on success of <b>false</> on failure
	 *
	 */
	function add ($user, $shipping_type, $shipping_phone, $shipping_address, $status, $comment) {
		return $this->create_simple([
			$user,
			$shipping_type,
			$shipping_phone,
			$shipping_address,
			$status,
			$comment
		]);
	}
	/**
	 * Set data of specified order
	 *
	 * @param int    $id
	 * @param int    $user
	 * @param int    $shipping_type
	 * @param string $shipping_phone
	 * @param string $shipping_address
	 * @param int    $status
	 * @param string $comment
	 *
	 * @return bool
	 */
	function set ($id, $user, $shipping_type, $shipping_phone, $shipping_address, $status, $comment) {
		return $this->update_simple([
			$id,
			$user,
			$shipping_type,
			$shipping_phone,
			$shipping_address,
			$status,
			$comment
		]);
	}
	/**
	 * Delete specified item
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		return $this->delete_simple($id);
	}
}
