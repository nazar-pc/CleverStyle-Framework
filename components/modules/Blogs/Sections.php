<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Cache,
	cs\Config,
	cs\Language,
	cs\User,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Sections {
	use
		CRUD_helpers,
		Singleton;

	protected $data_model          = [
		'id'     => 'int:0',
		'parent' => 'int:0',
		'title'  => 'ml:text',
		'path'   => 'ml:text'
	];
	protected $table               = '[prefix]blogs_sections';
	protected $data_model_ml_group = 'Blogs/sections';
	/**
	 * @var Cache\Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = Cache::prefix('Blogs');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Blogs')->db('posts');
	}
	/**
	 * Get data of specified section
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
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
		return $this->cache->get(
			"sections/$id/$L->clang",
			function () use ($id) {
				$data = $this->read($id);
				if ($data) {
					$data['posts']      = Posts::instance()->get_for_section_count($id);
					$data['full_title'] = [$data['title']];
					$data['full_path']  = [$data['path']];
					$parent             = $data['parent'];
					while ($parent > 0) {
						$section              = $this->get($parent);
						$data['full_title'][] = $section['path'];
						$data['full_path'][]  = $section['path'];
						$parent               = $section['parent'];
					}
					$data['full_title'] = implode(' :: ', array_reverse($data['full_title']));
					$data['full_path']  = implode('/', array_reverse($data['full_path']));
				}
				return $data;
			}
		);
	}
	/**
	 * @return array[]
	 */
	function get_all () {
		$L = Language::instance();
		return $this->cache->get(
			"sections/all/$L->clang",
			function () {
				$result = $this->get(
					$this->search([], 1, PHP_INT_MAX, 'id', true) ?: []
				);
				usort(
					$result,
					function ($a, $b) {
						return strcmp($a['full_title'], $b['full_title']);
					}
				);
				return $result;
			}
		) ?: [];
	}
	/**
	 * Get sections ids for each section in full path
	 *
	 * @param string|string[] $path
	 *
	 * @return false|int[]
	 */
	function get_by_path ($path) {
		$full_path = implode('/', (array)$path);
		$sections  = $this->get_all();
		$found     = false;
		foreach ($sections as $section) {
			if ($section['full_path'] == $full_path) {
				$found = true;
			}
		}
		if (!$found) {
			return false;
		}
		/** @noinspection PhpUndefinedVariableInspection */
		$ids = [$section['id']];
		while ($section['parent']) {
			$section = $this->get($section['parent']);
			$ids[]   = $section['id'];
		}
		return array_reverse($ids);
	}
	/**
	 * Add new section
	 *
	 * @param int    $parent
	 * @param string $title
	 * @param string $path
	 *
	 * @return false|int Id of created section on success of <b>false</> on failure
	 */
	function add ($parent, $title, $path) {
		$id = $this->create($parent, $title, path($path ?: $title));
		if ($id) {
			$this->db_prime()->q(
				"UPDATE `[prefix]blogs_posts_sections`
				SET `section` = $id
				WHERE `section` = '%d'",
				$parent
			);
			unset(
				$this->cache->posts,
				$this->cache->sections
			);
		}
		return $id;
	}
	/**
	 * Set data of specified section
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
			unset($this->cache->sections);
		}
		return $result;
	}
	/**
	 * Delete specified section
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id      = (int)$id;
		$section = $this->read($id);
		if (!$section || !$this->delete($id)) {
			return false;
		}
		$new_posts_section = $this->db_prime()->qfs(
			"SELECT `id`
			FROM `$this->table`
			WHERE `parent` = '%s'
			LIMIT 1",
			$section['parent']
		) ?: $section['parent'];
		$update            = $this->db_prime()->q(
			[
				"UPDATE `[prefix]blogs_sections`
				SET `parent` = '%2\$d'
				WHERE `parent` = '%1\$d'",
				"UPDATE IGNORE `[prefix]blogs_posts_sections`
				SET `section` = '%3\$d'
				WHERE `section` = '%1\$d'"
			],
			$id,
			$section['parent'],
			$new_posts_section
		);
		if ($update) {
			$this->cache->del('/');
		}
		return $update;
	}
}
