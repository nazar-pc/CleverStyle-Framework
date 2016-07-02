<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	cs\CRUD,
	cs\Singleton,
	cs\Cache\Prefix,
	cs\Config;

/**
 * @method static $this instance($check = false)
 */
class Categories {
	use
		CRUD,
		Singleton;
	protected $data_model          = [
		'id'     => 'int',
		'title'  => 'ml:text',
		'path'   => 'ml:text',
		'parent' => 'int'
	];
	protected $table               = '[prefix]static_pages_categories';
	protected $data_model_ml_group = 'Static_pages/categories';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Static_pages');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Static_pages')->db('pages');
	}
	/**
	 * Get data of specified category
	 *
	 * @param int|int[] $id
	 *
	 * @return array|array[]|false
	 */
	function get ($id) {
		return $this->read($id);
	}
	/**
	 * Add new category
	 *
	 * @param int    $parent
	 * @param string $title
	 * @param string $path
	 *
	 * @return false|int Id of created category on success of `false` on failure
	 */
	function add ($parent, $title, $path) {
		$id = $this->create($parent, $title, path($path ?: $title));
		if ($id) {
			unset($this->cache->structure);
		}
		return $id;
	}
	/**
	 * Set data of specified category
	 *
	 * @param int    $id
	 * @param int    $parent
	 * @param string $title
	 * @param string $path
	 *
	 * @return bool
	 */
	function set ($id, $parent, $title, $path) {
		$result = $this->update($id, $parent, $title, path($path ?: $title));
		if ($result) {
			unset($this->cache->structure);
		}
		return $result;
	}
	/**
	 * Delete specified category
	 *
	 * @param int|int[] $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$result = $this->delete($id);
		if ($result) {
			unset($this->cache->{'/'});
		}
		return $result;
	}
}
