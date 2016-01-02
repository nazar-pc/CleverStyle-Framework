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
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Text,
	cs\User,
	cs\DB\Accessor,
	cs\Singleton;

/**
 * @method static Sections instance($check = false)
 */
class Sections {
	use
		Accessor,
		Singleton;

	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Blogs');
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
	 * Get array of sections in form [<i>id</i> => <i>title</i>]
	 *
	 * @return array|false
	 */
	function get_list () {
		$L = Language::instance();
		return $this->cache->get(
			"sections/list/$L->clang",
			function () {
				return $this->get_list_internal(
					$this->get_structure()
				);
			}
		);
	}
	private function get_list_internal ($structure) {
		if (!empty($structure['sections'])) {
			$list = [];
			foreach ($structure['sections'] as $section) {
				$list += $this->get_list_internal($section);
			}
			return $list;
		} else {
			return [$structure['id'] => $structure['title']];
		}
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
			$structure['title'] = Language::instance()->root_section;
			$structure['posts'] = $this->db()->qfs(
				[
					"SELECT COUNT(`s`.`id`)
					FROM `[prefix]blogs_posts_sections` AS `s`
						LEFT JOIN `[prefix]blogs_posts` AS `p`
					ON `s`.`id` = `p`.`id`
					WHERE
						`s`.`section`	= '%s' AND
						`p`.`draft`		= 0",
					$structure['id']
				]
			);
		}
		$sections              = $this->db()->qfa(
			[
				"SELECT
					`id`,
					`path`
				FROM `[prefix]blogs_sections`
				WHERE `parent` = '%s'",
				$parent
			]
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
				$data = $this->db()->qf(
					[
						"SELECT
							`id`,
							`title`,
							`path`,
							`parent`,
							(
								SELECT COUNT(`s`.`id`)
								FROM `[prefix]blogs_posts_sections` AS `s`
									LEFT JOIN `[prefix]blogs_posts` AS `p`
								ON `s`.`id` = `p`.`id`
								WHERE
									`s`.`section`	= '%1\$s' AND
									`p`.`draft`		= 0
							) AS `posts`
						FROM `[prefix]blogs_sections`
						WHERE `id` = '%1\$s'
						LIMIT 1",
						$id
					]
				);
				if ($data) {
					$data['title']     = $this->ml_process($data['title']);
					$data['path']      = $this->ml_process($data['path']);
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
		$parent = (int)$parent;
		$posts  = $this->db_prime()->qfa(
			"SELECT `id`
			FROM `[prefix]blogs_posts_sections`
			WHERE `section` = $parent"
		) ?: [];
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]blogs_sections`
				(`parent`)
			VALUES
				($parent)"
		)
		) {
			$Cache = $this->cache;
			$id    = $this->db_prime()->id();
			$this->db_prime()->q(
				"UPDATE `[prefix]blogs_posts_sections`
					SET `section` = $id
					WHERE `section` = $parent"
			);
			foreach ($posts as $post) {
				unset($Cache->{"posts/$post[id]"});
			}
			unset($posts, $post);
			$this->set($id, $parent, $title, $path);
			unset(
				$Cache->{'sections/list'},
				$Cache->{'sections/structure'}
			);
			return $id;
		}
		return false;
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
		$parent = (int)$parent;
		$path   = path($path ?: $title);
		$title  = xap(trim($title));
		$id     = (int)$id;
		if ($this->db_prime()->q(
			"UPDATE `[prefix]blogs_sections`
			SET
				`parent`	= '%s',
				`title`		= '%s',
				`path`		= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$parent,
			$this->ml_set('Blogs/sections/title', $id, $title),
			$this->ml_set('Blogs/sections/path', $id, $path),
			$id
		)
		) {
			unset($this->cache->sections);
			return true;
		}
		return false;
	}
	/**
	 * Delete specified section
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id                = (int)$id;
		$parent_section    = $this->db_prime()->qfs(
			[
				"SELECT `parent`
				FROM `[prefix]blogs_sections`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]
		);
		$new_posts_section = $this->db_prime()->qfs(
			[
				"SELECT `id`
				FROM `[prefix]blogs_sections`
				WHERE
					`parent` = '%s' AND
					`id` != '%s'
				LIMIT 1",
				$parent_section,
				$id
			]
		);
		if ($this->db_prime()->q(
			[
				"UPDATE `[prefix]blogs_sections`
				SET `parent` = '%2\$s'
				WHERE `parent` = '%1\$s'",
				"UPDATE IGNORE `[prefix]blogs_posts_sections`
				SET `section` = '%3\$s'
				WHERE `section` = '%1\$s'",
				"DELETE FROM `[prefix]blogs_posts_sections`
				WHERE `section` = '%1\$s'",
				"DELETE FROM `[prefix]blogs_sections`
				WHERE `id` = '%1\$s'
				LIMIT 1"
			],
			$id,
			$parent_section,
			$new_posts_section ?: $parent_section
		)
		) {
			$this->ml_del('Blogs/sections/title', $id);
			$this->ml_del('Blogs/sections/path', $id);
			unset($this->cache->{'/'});
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text) {
		return Text::instance()->process($this->cdb(), $text, true);
	}
	private function ml_set ($group, $label, $text) {
		return Text::instance()->set($this->cdb(), $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		return Text::instance()->del($this->cdb(), $group, $label);
	}
}
