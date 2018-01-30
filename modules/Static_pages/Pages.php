<?php
/**
 * @package  Static Pages
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Static_pages;
use
	cs\CRUD_helpers,
	cs\Singleton,
	cs\Cache\Prefix,
	cs\Config,
	cs\Language;

/**
 * @method static $this instance($check = false)
 */
class Pages {
	use
		CRUD_helpers,
		Singleton;
	protected $data_model                  = [
		'id'        => 'int',
		'category'  => 'int',
		'title'     => 'ml:text',
		'path'      => 'ml:text',
		'content'   => 'ml:',
		'interface' => 'int:0..1'
	];
	protected $table                       = '[prefix]static_pages';
	protected $data_model_ml_group         = 'Static_pages/pages';
	protected $data_model_files_tag_prefix = 'Static_pages/pages';
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
	 * Get data of specified page
	 *
	 * @param int|int[] $id
	 *
	 * @return array|array|false
	 */
	public function get ($id) {
		if (is_array($id)) {
			return array_map([$this, 'get'], $id);
		}
		$L  = Language::instance();
		$id = (int)$id;
		return $this->cache->get(
			"pages/$id/$L->clang",
			function () use ($id) {
				$data = $this->read($id);
				if ($data) {
					if ($data['category']) {
						$category          = Categories::instance()->get($data['category']);
						$data['full_path'] = "$category[full_path]/$data[path]";
					} else {
						$data['full_path'] = $data['path'];
					}
				}
				return $data;
			}
		);
	}
	/**
	 * Get pages for category
	 *
	 * @param int $category
	 *
	 * @return int[]
	 */
	public function get_for_category ($category) {
		$search_parameters = [
			'category' => $category
		];
		return $this->search($search_parameters, 1, PHP_INT_MAX, 'title', true) ?: [];
	}
	/**
	 * Get number of pages for category
	 *
	 * @param int $category
	 *
	 * @return int
	 */
	public function get_for_category_count ($category) {
		$search_parameters = [
			'category'    => $category,
			'total_count' => true
		];
		return $this->search($search_parameters);
	}
	/**
	 * Get array of pages structure
	 *
	 * @return array|false
	 */
	public function get_map () {
		$L = Language::instance();
		return $this->cache->get(
			"map/$L->clang",
			function () {
				$pages = $this->get($this->search([], 1, PHP_INT_MAX) ?: []);
				return array_column($pages, 'id', 'full_path');
			}
		);
	}
	/**
	 * Add new page
	 *
	 * @param int    $category
	 * @param string $title
	 * @param string $path
	 * @param string $content
	 * @param int    $interface
	 *
	 * @return false|int Id of created page on success of <b>false</> on failure
	 */
	public function add ($category, $title, $path, $content, $interface) {
		$id = $this->create($category, $title, path($path ?: $title), $content, $interface);
		if ($id) {
			$this->cache->del('/');
		}
		return $id;
	}
	/**
	 * Set data of specified page
	 *
	 * @param int    $id
	 * @param int    $category
	 * @param string $title
	 * @param string $path
	 * @param string $content
	 * @param int    $interface
	 *
	 * @return bool
	 */
	public function set ($id, $category, $title, $path, $content, $interface) {
		$result = $this->update($id, $category, $title, path($path ?: $title), $content, $interface);
		if ($result) {
			$this->cache->del('/');
		}
		return $result;
	}
	/**
	 * Delete specified page
	 *
	 * @param int $id
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
