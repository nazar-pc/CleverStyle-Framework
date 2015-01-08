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
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Trigger,
	cs\User,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Shipping_types instance($check = false)
 *
 * Provides next triggers:<br>
 *  Shop/Shipping_types/get_for_user<code>
 *  [
 *   'data' => &$data,
 *   'user' => $user
 *  ]</code>
 */
class Shipping_types {
	use
		CRUD,
		Singleton;

	protected $data_model          = [
		'id'             => 'int',
		'price'          => 'float',
		'phone_needed'   => 'int:0..1',
		'address_needed' => 'int:0..1',
		'title'          => 'ml:string',
		'description'    => 'ml:html'
	];
	protected $data_model_ml_group = 'Shop/shipping_types';
	protected $table               = '[prefix]shop_shipping_types';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Shop/shipping_types');
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
	 * Get shipping type
	 *
	 * @param int|int[] $id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get($i);
			}
			return $id;
		}
		$L  = Language::instance();
		$id = (int)$id;
		return $this->cache->get("$id/$L->clang", function () use ($id) {
			return $this->read_simple($id);
		});
	}
	/**
	 * Get shipping type data for specific user (price might be adjusted, some shipping types may be restricted and so on)
	 *
	 * @param $id
	 * @param $user
	 *
	 * @return array|bool
	 */
	function get_for_user ($id, $user = false) {
		if (is_array($id)) {
			foreach ($id as $index => &$i) {
				$i = $this->get_for_user($i, $user);
				if ($i === false) {
					unset($id[$index]);
				}
			}
			return $id;
		}
		$user = (int)$user ?: User::instance()->id;
		$data = $this->get($id);
		if (!Trigger::instance()->run('Shop/Shipping_type/get_for_user', [
			'data' => &$data,
			'user' => $user
		])
		) {
			return false;
		}
		return $data;
	}
	/**
	 * Get array of all shipping types
	 *
	 * @return int[] Array of shipping types ids
	 */
	function get_all () {
		return $this->cache->get('all', function () {
			return $this->db()->qfas(
				"SELECT `id`
				FROM `$this->table`"
			) ?: [];
		});
	}
	/**
	 * Add new shipping type
	 *
	 * @param float  $price
	 * @param int    $phone_needed
	 * @param int    $address_needed
	 * @param string $title
	 * @param string $description
	 *
	 * @return bool|int Id of created item on success of <b>false</> on failure
	 */
	function add ($price, $phone_needed, $address_needed, $title, $description) {
		$id = $this->create_simple([
			$price,
			$phone_needed,
			$address_needed,
			$title,
			$description
		]);
		if ($id) {
			unset($this->cache->all);
		}
		return $id;
	}
	/**
	 * Set data of specified shipping type
	 *
	 * @param int    $id
	 * @param float  $price
	 * @param int    $phone_needed
	 * @param int    $address_needed
	 * @param string $title
	 * @param string $description
	 *
	 * @return bool
	 */
	function set ($id, $price, $phone_needed, $address_needed, $title, $description) {
		$id     = (int)$id;
		$result = $this->update_simple([
			$id,
			$price,
			$phone_needed,
			$address_needed,
			$title,
			$description
		]);
		if ($result) {
			$L = Language::instance();
			unset(
				$this->cache->{"$id/$L->clang"},
				$this->cache->all
			);
		}
		return $result;
	}
	/**
	 * Delete specified shipping type
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id     = (int)$id;
		$result = $this->delete_simple($id);
		if ($result) {
			unset(
				$this->cache->$id,
				$this->cache->all
			);
		}
		return $result;
	}
}
