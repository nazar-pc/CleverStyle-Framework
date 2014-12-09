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
	 * Get order
	 *
	 * @param int|int[] $id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		return $this->read_simple($id);
	}
	/**
	 * Get array of all orders
	 *
	 * @return int[] Array of orders ids
	 */
	function get_all () {
		return $this->db()->qfas(
			"SELECT `id`
			FROM `$this->table`"
		) ?: [];
	}
	/**
	 * Get order statuses history
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	function get_statuses ($id) {
		return $this->db()->qfa([
			"SELECT
				`id`,
				`date`,
				`status`,
				`comment`
			FROM `{$this->table}_history`
			WHERE `id` = '%d'",
			$id
		]) ?: [];
	}
	/**
	 * Get order items
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	function get_items ($id) {
		return $this->db()->qfa([
			"SELECT
				`id`,
				`item`,
				`units`,
				`price`,
				`unit_price`
			FROM `{$this->table}_items`
			WHERE
				`id` = '%d'",
			$id
		]) ?: [];
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
		$id = $this->create_simple([
			$user,
			TIME,
			$shipping_type,
			$shipping_phone,
			$shipping_address,
			$status,
			$comment
		]);
		if ($id) {
			$this->db_prime()->q(
				"INSERT INTO `{$this->table}_history`
					(
						`id`,
						`date`,
						`status`,
						`comment`
					)
				VALUES
					(
						'%d',
						'%d',
						'%d',
						'%s'
					)",
				$id,
				TIME,
				$status,
				Order_statuses::instance()->get($status)['comment']
			);
		}
		return $id;
	}
	/**
	 * Add item to order
	 *
	 * @param int   $id    Order id
	 * @param int   $item  Item id
	 * @param int   $units How much units of this item ordered
	 * @param float $price Total price of all units (not more than `$units * $unit_price`, may include discount)
	 *
	 * @return bool
	 */
	function add_item ($id, $item, $units, $price) {
		$unit_price = Items::instance()->get($item)['price'];
		return $this->db_prime()->q(
			"INSERT INTO `{$this->table}_items`
				(
					`id`,
					`item`,
					`units`,
					`price`,
					`unit_price`
				)
			VALUES
				(
					'%d',
					'%d',
					'%d',
					'%s',
					'%s'
				)",
			$id,
			$item,
			$units,
			min($price, $units * $unit_price),
			$unit_price
		);
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
		$order  = $this->read_simple($id);
		$result = $this->update_simple([
			$id,
			$user,
			$order['date'],
			$shipping_type,
			$shipping_phone,
			$shipping_address,
			$status,
			$comment
		]);
		if ($result && $order['status'] != $status) {
			$this->db_prime()->q(
				"INSERT INTO `{$this->table}_history`
					(
						`id`,
						`date`,
						`status`,
						`comment`
					)
				VALUES
					(
						'%d',
						'%d',
						'%d',
						'%s'
					)",
				$id,
				TIME,
				$status,
				Order_statuses::instance()->get($status)['comment']
			);
		}
		return $result;
	}
	/**
	 * Set item in order (update units and/or price)
	 *
	 * @param int   $id    Order id
	 * @param int   $item  Item id
	 * @param int   $units How much units of this item ordered
	 * @param float $price Total price of all units (not more than `$units * $unit_price`, may include discount)
	 *
	 * @return bool
	 */
	function set_item ($id, $item, $units, $price) {
		$unit_price = Items::instance()->get($item)['price'];
		return $this->db_prime()->q(
			"UPDATE `{$this->table}_items`
			SET
				`units`	= '%d',
				`price`	= '%s'
			WHERE
				`id`	= '%d' AND
				`item`	= '%d'
			LIMIT 1",
			$units,
			min($price, $units * $unit_price),
			$id,
			$item
		);
	}
	/**
	 * Change order status and attach message for this change
	 *
	 * @param int    $id
	 * @param int    $status
	 * @param string $comment
	 */
	function set_status ($id, $status, $comment) {
		$this->db_prime()->q(
			"INSERT INTO `{$this->table}_history`
					(
						`id`,
						`date`,
						`status`,
						`comment`
					)
				VALUES
					(
						'%d',
						'%d',
						'%d',
						'%s'
					)",
			$id,
			TIME,
			$status,
			xap($comment, true)
		);
	}
	/**
	 * Delete specified order
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		if ($this->delete_simple($id)) {
			$this->db_prime()->q(
				"DELETE FROM `{$this->table}_history`
				WHERE `id` = '%d'",
				$id
			);
			$this->db_prime()->q(
				"DELETE FROM `{$this->table}_items`
				WHERE `id` = '%d'",
				$id
			);
			return true;
		}
		return false;
	}
	/**
	 * Delete item from order
	 *
	 * @param int $id   Order id
	 * @param int $item Item id
	 *
	 * @return bool
	 */
	function del_item ($id, $item) {
		return $this->db_prime()->q(
			"DELETE FROM `{$this->table}_items`
			WHERE
				`id`	= '%d' AND
				`item`	= '%d'
			LIMIT 1",
			$id,
			$item
		);
	}
}
