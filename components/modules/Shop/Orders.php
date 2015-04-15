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
	cs\Event,
	cs\Language,
	cs\User,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static Orders instance($check = false)
 *
 * Provides next events:<br>
 *  Shop/Orders/get<code>
 *  [
 *   'data' => &$data
 *  ]</code>
 *
 *  Shop/Orders/add<code>
 *  [
 *   'id' => $id
 *  ]</code>
 *
 *  Shop/Orders/set<code>
 *  [
 *   'id' => $id
 *  ]</code>
 *
 *  Shop/Orders/set_status<code>
 *  [
 *   'id'      => $id,
 *   'status'  => $status,
 *   'comment' => $comment
 *  ]</code>
 *
 *  Shop/Orders/del<code>
 *  [
 *   'id' => $id
 *  ]</code>
 *
 *  System/payment/methods<code>
 *  [
 *   'items'    => &$payment_methods,
 *   'currency' => $currency
 *  ]</code>
 *
 *  Shop/Orders/Cart/recalculate<code>
 *  [
 *   'items'    => &$items,   // Array of array elements [id => item_id, units => units, price => total_price]
 *   'shipping' => &$shipping // Array in form [type => shipping_type_id, price => shipping_type_price]
 *  ]</code>
 */
class Orders {
	use CRUD_helpers {
		search as crud_search;
	}
	use
		Singleton;

	const PAYMENT_METHOD_CASH = 'shop:cash';

	protected $data_model            = [
		'id'                => 'int',
		'user'              => 'int',
		'date'              => 'int',
		'shipping_type'     => 'int',
		'shipping_cost'     => 'float',
		'shipping_username' => 'text',
		'shipping_phone'    => 'text',
		'shipping_address'  => 'text',
		'payment_method'    => 'text',
		'paid'              => 'int',
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
	 * @return array|false
	 */
	function get ($id) {
		$data = $this->read($id);
		if (!$data) {
			return false;
		}
		if (is_array($id)) {
			foreach ($data as &$d) {
				$d['for_payment'] = $this->get_for_payment($d);
			}
		} else {
			$data['for_payment'] = $this->get_for_payment($data);
		}
		if (!Event::instance()->fire(
			'Shop/Orders/get',
			[
				'data' => &$data
			]
		)
		) {
			return false;
		}
		return $data;
	}
	/**
	 * @param array $data
	 *
	 * @return number
	 */
	protected function get_for_payment ($data) {
		return
			array_sum(
				array_column(
					$this->get_items($data['id']),
					'price'
				)
			) + $data['shipping_cost'];
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
		return $this->db()->qfa(
			[
				"SELECT
				`id`,
				`date`,
				`status`,
				`comment`
				FROM `{$this->table}_history`
				WHERE `id` = '%d'",
				$id
			]
		) ?: [];
	}
	/**
	 * Get order items
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	function get_items ($id) {
		return $this->db()->qfa(
			[
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
			]
		) ?: [];
	}
	/**
	 * Get array of payment methods
	 *
	 * @return array [method => [title => title, description => description]]
	 */
	function get_payment_methods () {
		$currency        = Config::instance()->module('Shop')->currency;
		$payment_methods = [
			self::PAYMENT_METHOD_CASH => [
				'title'       => "$currency: ".Language::instance()->shop_cash,
				'description' => ''
			]
		];
		Event::instance()->fire(
			'System/payment/methods',
			[
				'methods'  => &$payment_methods,
				'currency' => $currency
			]
		);
		return $payment_methods;
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
	 * @return array|false|string
	 */
	function search ($search_parameters = [], $page = 1, $count = 20, $order_by = 'date', $asc = false) {
		return $this->crud_search($search_parameters, $page, $count, $order_by, $asc);
	}
	/**
	 * Returns recalculated prices for items and shipping
	 *
	 * Using this event third party components may automatically apply different discounts for items and shipping based on user, number of units and other
	 * things
	 *
	 * @param array     $items Array in form of [id => units]
	 * @param int       $shipping_type
	 * @param false|int $user
	 *
	 * @return array   <pre>
	 *  [
	 *   'items'    => $items,   // Array of array elements [id => item_id, units => units, price => total_price]
	 *   'shipping' => $shipping // Array in form [type => shipping_type_id, price => shipping_type_price]
	 *  ]</pre>
	 */
	function get_recalculated_cart_prices ($items, $shipping_type, $user = false) {
		$Items         = Items::instance();
		$items         = array_map(
			function ($item, $units) use ($Items, $user) {
				$item = $Items->get_for_user($item, $user);
				if (!$item || $units < 1) {
					return false;
				}
				return [
					'id'    => $item['id'],
					'units' => (int)$units,
					'price' => $item['price'] * $units
				];
			},
			array_keys($items),
			array_values($items)
		);
		$items         = array_filter($items);
		$shipping_type = Shipping_types::instance()->get_for_user($shipping_type, $user);
		if (!$items || !$shipping_type) {
			return false;
		}
		$shipping = [
			'type'  => $shipping_type['id'],
			'price' => $shipping_type['price']
		];
		Event::instance()->fire(
			'Shop/Orders/Cart/recalculate',
			[
				'items'    => &$items,
				'shipping' => &$shipping
			]
		);
		return [
			'items'    => $items,
			'shipping' => $shipping
		];
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
	 * @param string $payment_method
	 * @param int    $paid
	 * @param int    $status
	 * @param string $comment
	 *
	 * @return false|int Id of created item on success of <b>false</> on failure
	 *
	 */
	function add ($user, $shipping_type, $shipping_cost, $shipping_username, $shipping_phone, $shipping_address, $payment_method, $paid, $status, $comment) {
		$id = $this->create(
			[
				$user,
				time(),
				$shipping_type,
				$shipping_cost,
				$shipping_username,
				$shipping_phone,
				$shipping_address,
				$payment_method,
				$paid == 1 ? time() : $paid,
				$status,
				$comment
			]
		);
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
				time(),
				$status,
				Order_statuses::instance()->get($status)['comment']
			);
			Event::instance()->fire(
				'Shop/Orders/add',
				[
					'id' => $id
				]
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
			$Items = Items::instance();
			$item  = $Items->get($item);
			$item['in_stock'] -= $units;
			$Items->set(
				$item['id'],
				$item['category'],
				$item['price'],
				max($item['in_stock'], 0),
				$item['soon'],
				$item['listed'],
				$item['attributes'],
				$item['images'],
				$item['videos'],
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
	 * @param string $payment_method
	 * @param int    $paid
	 * @param int    $status
	 * @param string $comment
	 *
	 * @return bool
	 */
	function set (
		$id,
		$user,
		$shipping_type,
		$shipping_cost,
		$shipping_username,
		$shipping_phone,
		$shipping_address,
		$payment_method,
		$paid,
		$status,
		$comment
	) {
		$order  = $this->read($id);
		$result = $this->update(
			[
				$id,
				$user,
				$order['date'],
				$shipping_type,
				$shipping_cost,
				$shipping_username,
				$shipping_phone,
				$shipping_address,
				$payment_method,
				$paid == 1 ? ($order['paid'] ?: time()) : $paid,
				$status,
				$comment
			]
		);
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
				time(),
				$status,
				Order_statuses::instance()->get($status)['comment']
			);
			Event::instance()->fire(
				'Shop/Orders/set',
				[
					'id' => $id
				]
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
			$old_units        = array_column($items, 'units', 'item')[$item];
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
				$item['videos'],
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
			time(),
			$status,
			xap($comment, true)
		);
		Event::instance()->fire(
			'Shop/Orders/set_status',
			[
				'id'      => $id,
				'status'  => $status,
				'comment' => $comment
			]
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
		if ($this->delete($id)) {
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
			Event::instance()->fire(
				'Shop/Orders/del',
				[
					'id' => $id
				]
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
			$Items     = Items::instance();
			$old_units = array_column($items, 'units', 'item')[$item];
			$item      = $Items->get($item);
			$item['in_stock'] += $old_units;
			$Items->set(
				$item['id'],
				$item['category'],
				$item['price'],
				max($item['in_stock'], 0),
				$item['soon'],
				$item['listed'],
				$item['attributes'],
				$item['images'],
				$item['videos'],
				$item['tags']
			);
		}
		return $result;
	}
}
