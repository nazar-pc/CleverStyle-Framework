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
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Shipping_types instance($check = false)
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
		return $this->create_simple([
			$price,
			$phone_needed,
			$address_needed,
			$title,
			$description
		]);
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
		$id = (int)$id;
		if ($this->update_simple([
			$id,
			$price,
			$phone_needed,
			$address_needed,
			$title,
			$description
		])
		) {
			$L = Language::instance();
			$this->cache->del("$id/$L->clang");
			return true;
		}
		return false;
	}
	/**
	 * Delete specified shipping type
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id = (int)$id;
		if ($this->delete_simple($id)) {
			unset($this->cache->$id);
			return true;
		}
		return false;
	}
}
