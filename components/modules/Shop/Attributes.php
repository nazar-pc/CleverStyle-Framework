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
	cs\Cache,
	cs\Cache\Prefix as Cache_prefix,
	cs\Config,
	cs\Language,
	cs\Language\Prefix as Language_prefix,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Attributes instance($check = false)
 */
class Attributes {
	use
		CRUD,
		Singleton;

	const TYPE_INT_SET     = 1;
	const TYPE_FLOAT_SET   = 2;
	const TYPE_INT_RANGE   = 3;
	const TYPE_FLOAT_RANGE = 4;
	const TYPE_STRING      = 5;
	const TYPE_STRING_SET  = 6;
	const TYPE_TEXT        = 7;
	const TYPE_SWITCH      = 8;
	const TYPE_COLOR_SET   = 9;

	protected $data_model          = [
		'id'             => 'int',
		'type'           => 'int',
		'title'          => 'ml:text',
		'internal_title' => 'ml:text',
		'value'          => 'ml:'
	];
	protected $data_model_ml_group = 'Shop/attributes';
	protected $table               = '[prefix]shop_attributes';
	/**
	 * @var Cache_prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Cache_prefix('Shop/attributes');
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
	 * Get attribute
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
	 * Get array with attribute types ids as keys and string translations as values
	 *
	 * @return array
	 */
	function get_type_to_name_array () {
		$L = new Language_prefix('shop_');
		return [
			static::TYPE_INT_SET     => $L->type_int_set,
			static::TYPE_FLOAT_SET   => $L->type_float_set,
			static::TYPE_INT_RANGE   => $L->type_int_range,
			static::TYPE_FLOAT_RANGE => $L->type_float_range,
			static::TYPE_STRING      => $L->type_string,
			static::TYPE_STRING_SET  => $L->type_string_set,
			static::TYPE_TEXT        => $L->type_text,
			static::TYPE_SWITCH      => $L->type_switch,
			static::TYPE_COLOR_SET   => $L->type_color_set
		];
	}
	/**
	 * Get array of all attributes
	 *
	 * @return int[] Array of categories ids
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
	 * Add new attribute
	 *
	 * @param int    $type
	 * @param string $title
	 * @param string $internal_title
	 * @param int    $value
	 *
	 * @return bool|int Id of created attribute on success of <b>false</> on failure
	 */
	function add ($type, $title, $internal_title, $value) {
		$id = $this->create_simple([
			$type,
			'',
			'',
			''
		]);
		if (!$id) {
			return false;
		}
		unset($this->cache->all);
		$this->set($id, $type, $title, $internal_title, $value);
		return $id;
	}
	/**
	 * Set data of specified attribute
	 *
	 * @param int    $id
	 * @param int    $type
	 * @param string $title
	 * @param string $internal_title
	 * @param int    $value Attribute that will be considered as title
	 *
	 * @return bool
	 */
	function set ($id, $type, $title, $internal_title, $value) {
		$result = $this->update_simple([
			$id,
			$type,
			trim($title),
			trim($internal_title),
			trim($value)
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
	 * Delete specified attribute
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id = (int)$id;
		if (!$this->delete_simple($id)) {
			return false;
		}
		unset(
			$this->cache->$id,
			$this->cache->all,
			Cache::instance()->{'Shop/categories'}
		);
		return true;
	}
}
