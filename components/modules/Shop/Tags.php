<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\modules\Tags\Tags as Tags_trait,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Tags {
	use
		Tags_trait,
		Singleton;
	/**
	 * @var Prefix
	 */
	protected $cache;
	/**
	 * @var string
	 */
	protected $table = '[prefix]shop_tags';

	protected function construct () {
		$this->cache = new Prefix('Shop/tags');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Shop')->db('shop');
	}
}
