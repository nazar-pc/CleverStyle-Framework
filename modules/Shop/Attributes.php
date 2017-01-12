<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache,
	cs\Cache\Prefix as Cache_prefix,
	cs\Config,
	cs\Language,
	cs\Language\Prefix as Language_prefix,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Attributes {
	use
		Singleton,
		Common_actions;

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
		'title_internal' => 'ml:text',
		'value'          => 'ml:json'
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
	 * @return array|false
	 */
	public function get ($id) {
		return $this->get_common($id);
	}
	/**
	 * Get array of all attributes
	 *
	 * @return int[] Array of attributes ids
	 */
	public function get_all () {
		return $this->get_all_common();
	}
	/**
	 * Get array with attribute types ids as keys and string translations as values
	 *
	 * @return array
	 */
	public function get_type_to_name_array () {
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
	 * Add new attribute
	 *
	 * @param int          $type
	 * @param string       $title
	 * @param string       $title_internal
	 * @param array|string $value
	 *
	 * @return false|int Id of created attribute on success of <b>false</> on failure
	 */
	public function add ($type, $title, $title_internal, $value) {
		return $this->add_common(func_get_args());
	}
	/**
	 * Set data of specified attribute
	 *
	 * @param int          $id
	 * @param int          $type
	 * @param string       $title
	 * @param string       $title_internal
	 * @param array|string $value
	 *
	 * @return bool
	 */
	public function set ($id, $type, $title, $title_internal, $value) {
		return $this->set_common(func_get_args());
	}
	/**
	 * Delete specified attribute
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		$return = $this->del_common($id);
		if ($return) {
			Cache::instance()->del('Shop/categories');
		}
		return $return;
	}
}
