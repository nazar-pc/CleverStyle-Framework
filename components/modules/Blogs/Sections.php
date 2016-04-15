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
	cs\Cache\Prefix as Cache_prefix,
	cs\Config,
	cs\Language,
	cs\Language\Prefix as Language_prefix,
	cs\Text,
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
	 * @var Cache_prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Cache_prefix('Blogs');
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
	 * Get array of sections structure
	 *
	 * @return array|false
	 */
	function get_structure () {
		$L = Language::instance();
		return $this->cache->get(
			"sections/structure/$L->clang",
			function () {
				return $this->get_structure_internal();
			}
		);
	}
	private function get_structure_internal ($parent = 0) {
		$structure = [
			'id'    => $parent,
			'posts' => 0
		];
		if ($parent != 0) {
			$structure = array_merge(
				$structure,
				$this->get($parent)
			);
		} else {
			$L                  = new Language_prefix('blogs_');
			$structure['title'] = $L->root_section;
			$structure['posts'] = Posts::instance()->get_for_section_count($structure['id']);
		}
		$sections              = $this->db()->qfa(
			"SELECT
				`id`,
				`path`
			FROM `[prefix]blogs_sections`
			WHERE `parent` = '%s'",
			$parent
		) ?: [];
		$structure['sections'] = [];
		foreach ($sections as $section) {
			$structure['sections'][$this->ml_process($section['path'])] = $this->get_structure_internal($section['id']);
		}
		return $structure;
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
					$data['posts']     = Posts::instance()->get_for_section_count($id);
					$data['full_path'] = [$data['path']];
					$parent            = $data['parent'];
					while ($parent != 0) {
						$section             = $this->get($parent);
						$data['full_path'][] = $section['path'];
						$parent              = $section['parent'];
					}
					$data['full_path'] = implode('/', array_reverse($data['full_path']));
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
				return $this->get(
					$this->search([], 1, PHP_INT_MAX, 'id', true) ?: []
				);
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
		if (!is_array($path)) {
			$path = explode('/', $path);
		}
		$structure = $this->get_structure();
		$ids       = [];
		foreach ($path as $p) {
			if (!isset($structure['sections'][$p])) {
				break;
			}
			array_shift($path);
			$structure = $structure['sections'][$p];
			$ids[]     = $structure['id'];
		}
		return $ids ?: false;
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
		$id = $this->create($parent, $title, $path);
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
		$result = $this->update($id, $parent, $title, $path);
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
	private function ml_process ($text) {
		return Text::instance()->process($this->cdb(), $text, true);
	}
}
