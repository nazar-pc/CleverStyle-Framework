<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	cs\CRUD,
	cs\Singleton,
	cs\Cache\Prefix,
	cs\Config,
	cs\Language;

/**
 * @method static Pages instance($check = false)
 */
class Pages {
	use
		CRUD,
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
	 * @param int $id
	 *
	 * @return array|false
	 */
	function get ($id) {
		$L  = Language::instance();
		$id = (int)$id;
		return $this->cache->get(
			"pages/$id/$L->clang",
			function () use ($id) {
				return $this->read($id);
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
	function add ($category, $title, $path, $content, $interface) {
		$id = $this->create(
			[
				$category,
				$title,
				path($path ?: $title),
				$content,
				$interface
			]
		);
		if ($id) {
			unset($this->cache->{'/'});
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
	function set ($id, $category, $title, $path, $content, $interface) {
		$result = $this->update(
			[
				$id,
				$category,
				$title,
				path($path ?: $title),
				$content,
				$interface
			]
		);
		if ($result) {
			$Cache = $this->cache;
			unset(
				$Cache->structure,
				$Cache->{"pages/$id"}
			);
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
	function del ($id) {
		$result = $this->delete($id);
		if ($result) {
			$Cache = $this->cache;
			unset(
				$Cache->structure,
				$Cache->{"pages/$id"}
			);
		}
		return $result;
	}
	/**
	 * Get array of pages structure
	 *
	 * @return array|false
	 */
	function get_structure () {
		$L = Language::instance();
		return $this->cache->get(
			"structure/$L->clang",
			function () {
				return $this->get_structure_internal();
			}
		);
	}
	private function get_structure_internal ($parent = 0) {
		$Categories = Categories::instance();
		$structure  = ['id' => $parent];
		if ($parent != 0) {
			$structure = array_merge(
				$structure,
				$Categories->get($parent)
			);
		}
		$pages              = $this->db()->qfas(
			[
				"SELECT `id`
				FROM `[prefix]static_pages`
				WHERE `category` = '%s'",
				$parent
			]
		);
		$structure['pages'] = [];
		if (!empty($pages)) {
			foreach ($pages as $id) {
				$structure['pages'][$this->get($id)['path']] = $id;
			}
		}
		unset($pages);
		$categories              = $this->db()->qfa(
			[
				"SELECT
					`id`,
					`path`
				FROM `[prefix]static_pages_categories`
				WHERE `parent` = '%s'",
				$parent
			]
		);
		$structure['categories'] = [];
		foreach ($categories as $category) {
			$structure['categories'][$category['path']] = $this->get_structure_internal($category['id']);
		}
		return $structure;
	}
}
