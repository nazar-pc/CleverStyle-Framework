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
	cs\CRUD_helpers,
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Categories {
	use
		CRUD_helpers,
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
	public function get ($id) {
		if (is_array($id)) {
			return array_map([$this, 'get'], $id);
		}
		$L  = Language::instance();
		$id = (int)$id;
		return $this->cache->get(
			"categories/$id/$L->clang",
			function () use ($id) {
				$data = $this->read($id);
				if ($data) {
					$data['pages']      = Pages::instance()->get_for_category_count($id);
					$data['full_title'] = [$data['title']];
					$data['full_path']  = [$data['path']];
					$parent             = $data['parent'];
					while ($parent > 0) {
						$category             = $this->get($parent);
						$data['full_title'][] = $category['title'];
						$data['full_path'][]  = $category['path'];
						$parent               = $category['parent'];
					}
					$data['full_title'] = implode(' :: ', array_reverse($data['full_title']));
					$data['full_path']  = implode('/', array_reverse($data['full_path']));
				}
				return $data;
			}
		);
	}
	/**
	 * Get data all categories
	 *
	 * @return array[]
	 */
	public function get_all () {
		$L = Language::instance();
		return $this->cache->get(
			"categories/all/$L->clang",
			function () use ($L) {
				$result = $this->get(
					$this->search([], 1, PHP_INT_MAX, 'id', true) ?: []
				);
				if ($result) {
					usort(
						$result,
						function ($a, $b) {
							return strcmp($a['full_title'], $b['full_title']);
						}
					);
					array_unshift(
						$result,
						[
							'id'         => 0,
							'title'      => $L->static_pages_root_category,
							'full_title' => $L->static_pages_root_category,
							'path'       => '',
							'full_path'  => '',
							'pages'      => Pages::instance()->get_for_category_count(0)
						]
					);
				}
				return $result;
			}
		) ?: [];
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
	public function add ($parent, $title, $path) {
		$id = $this->create($title, path($path ?: $title), $parent);
		if ($id) {
			unset($this->cache->structure, $this->cache->categories);
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
	public function set ($id, $parent, $title, $path) {
		$result = $this->update($id, $title, path($path ?: $title), $parent);
		if ($result) {
			unset($this->cache->structure, $this->cache->categories);
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
	public function del ($id) {
		$result = $this->delete($id);
		if ($result) {
			$this->cache->del('/');
		}
		return $result;
	}
}
