<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
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

	protected $data_model            = [
		'id'                => 'int',
		'user'              => 'int',
		'date'              => 'int',
		'shipping_type'     => 'int',
		'shipping_cost'     => 'float',
		'shipping_username' => 'text',
		'shipping_phone'    => 'text',
		'shipping_address'  => 'text',
		'status'            => 'int',
		'comment'           => 'text'
	];
	protected $table                 = '[prefix]shop_orders';
	protected $reduce_in_stock_value = false;

	protected function construct () {
		$this->reduce_in_stock_value = Config::instance()->module('Shop')->automatically_reduce_in_stock_value;
	}
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
	 * Orders search
	 *
	 * @param mixed[] $search_parameters Array in form [attribute => value];
	 *                                   if `total_count => 1` element is present - total number of found rows will be returned instead of rows themselves
	 * @param int     $page
	 * @param int     $count
	 * @param string  $order_by
	 * @param bool    $asc
	 *
	 * @return array|bool|string
	 */
	function search ($search_parameters = [], $page = 1, $count = 20, $order_by = 'date', $asc = false) {
		if (!isset($this->data_model[$order_by])) {
			return false;
		}
		$where  = [];
		$params = [];
		foreach ($search_parameters as $key => $details) {
			if (isset($this->data_model[$key])) {
				$where[]  = "`$key` = '%s'";
				$params[] = $details;
			}
		}
		unset($key, $details);
		$where = $where ? 'WHERE '.implode(' AND ', $where) : '';
		if (@$search_parameters['total_count']) {
			return $this->db()->qfs([
				"SELECT COUNT(`id`)
				FROM `$this->table`
				$where",
				$params
			]);
		} else {
			$params[] = ($page - 1) * $count;
			$params[] = $count;
			$asc      = $asc ? 'ASC' : 'DESC';
			return $this->db()->qfas([
				"SELECT `id`
				FROM `$this->table`
				$where
				ORDER BY `$order_by` $asc
				LIMIT %d, %d",
				$params
			]);
		}
	}
	/**
	 * Add new order
	 *
	 * @param int    $user
	 * @param int    $shipping_type
	 * @param float  $shipping_cost
	 * @param string $shipping_username
	 * @param string $shipping_phone
	 * @param string $shipping_address
	 * @param int    $status
	 * @param string $comment
	 *
	 * @return bool|int Id of created item on success of <b>false</> on failure
	 *
	 */
	function add ($user, $shipping_type, $shipping_cost, $shipping_username, $shipping_phone, $shipping_address, $status, $comment) {
		$id = $this->create_simple([
			$user,
			TIME,
			$shipping_type,
			$shipping_cost,
			$shipping_username,
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
	 * @param int   $id         Order id
	 * @param int   $item       Item id
	 * @param int   $units      How much units of this item ordered
	 * @param float $price      Total price of all units (not more than `$units * $unit_price`, may include discount)
	 * @param float $unit_price Price of one item at the moment of order submission
	 *
	 * @return bool
	 */
	function add_item ($id, $item, $units, $price, $unit_price) {
		if (!$units) {
			return false;
		}
		$result = $this->db_prime()->q(
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
		if ($result && $this->reduce_in_stock_value) {
			$Items            = Items::instance();
			$item             = $Items->get($item);
			$item['in_stock'] = $item['in_stock'] - $units;
			$Items->set(
				$item['id'],
				$item['category'],
				$item['price'],
				max($item['in_stock'], 0),
				$item['soon'],
				$item['listed'],
				$item['attributes'],
				$item['images'],
				$item['tags']
			);
		}
		return $result;
	}
	/**
	 * Set data of specified order
	 *
	 * @param int    $id
	 * @param int    $user
	 * @param int    $shipping_type
	 * @param float  $shipping_cost
	 * @param string $shipping_username
	 * @param string $shipping_phone
	 * @param string $shipping_address
	 * @param int    $status
	 * @param string $comment
	 *
	 * @return bool
	 */
	function set ($id, $user, $shipping_type, $shipping_cost, $shipping_username, $shipping_phone, $shipping_address, $status, $comment) {
		$order  = $this->read_simple($id);
		$result = $this->update_simple([
			$id,
			$user,
			$order['date'],
			$shipping_type,
			$shipping_cost,
			$shipping_username,
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
	 * @param int   $id         Order id
	 * @param int   $item       Item id
	 * @param int   $units      How much units of this item ordered
	 * @param float $price      Total price of all units (not more than `$units * $unit_price`, may include discount)
	 * @param float $unit_price Price of one item at the moment of order submission
	 *
	 * @return bool
	 */
	function set_item ($id, $item, $units, $price, $unit_price) {
		if (!$units) {
			return $this->del_item($id, $item);
		}
		$items  = $this->get_items($id);
		$result = $this->db_prime()->q(
			"UPDATE `{$this->table}_items`
			SET
				`units`			= '%d',
				`price`			= '%s',
				`unit_price`	= '%s'
			WHERE
				`id`	= '%d' AND
				`item`	= '%d'
			LIMIT 1",
			$units,
			min($price, $units * $unit_price),
			$unit_price,
			$id,
			$item
		);
		if ($result && $this->reduce_in_stock_value) {
			$Items            = Items::instance();
			$old_units        = array_column($items, 'item', 'units')[$item];
			$item             = $Items->get($item);
			$item['in_stock'] = $item['in_stock'] + $old_units - $units;
			$Items->set(
				$item['id'],
				$item['category'],
				$item['price'],
				max($item['in_stock'], 0),
				$item['soon'],
				$item['listed'],
				$item['attributes'],
				$item['images'],
				$item['tags']
			);
		}
		return $result;
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
		$items  = $this->get_items($id);
		$result = $this->db_prime()->q(
			"DELETE FROM `{$this->table}_items`
			WHERE
				`id`	= '%d' AND
				`item`	= '%d'
			LIMIT 1",
			$id,
			$item
		);
		if ($result && $this->reduce_in_stock_value) {
			$Items            = Items::instance();
			$old_units        = array_column($items, 'item', 'units')[$item];
			$item             = $Items->get($item);
			$item['in_stock'] = $item['in_stock'] + $old_units;
			$Items->set(
				$item['id'],
				$item['category'],
				$item['price'],
				max($item['in_stock'], 0),
				$item['soon'],
				$item['listed'],
				$item['attributes'],
				$item['images'],
				$item['tags']
			);
		}
		return $result;
	}
}
