<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			cs\DB\Accessor,
			cs\Singleton,
			cs\Cache\Prefix,
			cs\Config,
			cs\Language,
			cs\Text;

/**
 * @method static Static_pages instance($check = false)
 */
class Static_pages {
	use	Accessor,
		Singleton;

	/**
	 * @var Prefix
	 */
	protected	$cache;

	protected function construct () {
		$this->cache	= new Prefix('Static_pages');
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
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		$L		= Language::instance();
		$id		= (int)$id;
		return $this->cache->get("pages/$id/$L->clang", function () use ($id) {
			if ($data = $this->db()->qf([
				"SELECT
					`id`,
					`category`,
					`title`,
					`path`,
					`content`,
					`interface`
				FROM `[prefix]static_pages`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			])) {
				$data['title']		= $this->ml_process($data['title']);
				$data['path']		= $this->ml_process($data['path']);
				$data['content']	= $this->ml_process($data['content']);
			}
			return $data;
		});
	}
	/**
	 * Add new page
	 *
	 * @param int		$category
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int		$interface
	 *
	 * @return bool|int				Id of created page on success of <b>false</> on failure
	 */
	function add ($category, $title, $path, $content, $interface) {
		$category	= (int)$category;
		$interface	= (int)$interface;
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]static_pages`
				(`category`, `interface`)
			VALUES
				('%s', '%s')",
			$category,
			$interface
		)) {
			$id	= $this->db_prime()->id();
			$this->set($id, $category, $title, $path, $content, $interface);
			unset($this->cache->{'/'});
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified page
	 *
	 * @param int		$id
	 * @param int		$category
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int		$interface
	 *
	 * @return bool
	 */
	function set ($id, $category, $title, $path, $content, $interface) {
		$category	= (int)$category;
		$title		= trim($title);
		$path		= path($path ?: $title);
		$interface	= (int)$interface;
		$id			= (int)$id;
		if ($this->db_prime()->q(
			"UPDATE `[prefix]static_pages`
			SET
				`category`	= '%s',
				`title`		= '%s',
				`path`		= '%s',
				`content`	= '%s',
				`interface`	= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$category,
			$this->ml_set('Static_pages/pages/title', $id, $title),
			$this->ml_set('Static_pages/pages/path', $id, $path),
			$this->ml_set('Static_pages/pages/content', $id, $content),
			$interface,
			$id
		)) {
			$Cache	= $this->cache;
			unset(
				$Cache->structure,
				$Cache->{"pages/$id"}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete specified page
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id		= (int)$id;
		if ($this->db_prime()->q(
			"DELETE FROM `[prefix]static_pages`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		)) {
			$this->ml_del('Static_pages/pages/title', $id);
			$this->ml_del('Static_pages/pages/path', $id);
			$this->ml_del('Static_pages/pages/content', $id);
			$Cache	= $this->cache;
			unset(
				$Cache->structure,
				$Cache->{"pages/$id"}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Get array of pages structure
	 *
	 * @return array|bool
	 */
	function get_structure () {
		$L		= Language::instance();
		return $this->cache->get("structure/$L->clang", function () {
			return $this->get_structure_internal();
		});
	}
	private function get_structure_internal ($parent = 0) {
		$structure					= ['id'	=> $parent];
		if ($parent != 0) {
			$structure	= array_merge(
				$structure,
				$this->get_category($parent)
			);
		}
		$pages						= $this->db()->qfas([
			"SELECT `id`
			FROM `[prefix]static_pages`
			WHERE `category` = '%s'",
			$parent
		]);
		$structure['pages']			= [];
		if (!empty($pages)) {
			foreach ($pages as $id) {
				$structure['pages'][$this->get($id)['path']]	= $id;
			}
			unset($id);
		}
		unset($pages);
		$categories					= $this->db()->qfa([
			"SELECT
				`id`,
				`path`
			FROM `[prefix]static_pages_categories`
			WHERE `parent` = '%s'",
			$parent
		]);
		$structure['categories']	= [];
		foreach ($categories as $category) {
			$structure['categories'][$category['path']]	= $this->get_structure_internal($category['id']);
		}
		return $structure;
	}
	/**
	 * Get data of specified category
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get_category ($id) {
		$id				= (int)$id;
		$data			= $this->db()->qf([
			"SELECT
				`id`,
				`title`,
				`path`,
				`parent`
			FROM `[prefix]static_pages_categories`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		]);
		$data['title']	= $this->ml_process($data['title']);
		$data['path']	= $this->ml_process($data['path']);
		return $data;
	}
	/**
	 * Add new category
	 *
	 * @param int		$parent
	 * @param string	$title
	 * @param string	$path
	 *
	 * @return bool|int			Id of created category on success of <b>false</> on failure
	 */
	function add_category ($parent, $title, $path) {
		$parent	= (int)$parent;
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]static_pages_categories`
				(`parent`)
			VALUES
				('%s')",
			$parent
		)) {
			$id	= $this->db_prime()->id();
			$this->set_category($id, $parent, $title, $path);
			unset($this->cache->structure);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified category
	 *
	 * @param int		$id
	 * @param int		$parent
	 * @param string	$title
	 * @param string	$path
	 *
	 * @return bool
	 */
	function set_category ($id, $parent, $title, $path) {
		$parent	= (int)$parent;
		$title	= trim($title);
		$path	= path($path ?: $title);
		$id		= (int)$id;
		if ($this->db_prime()->q(
			"UPDATE `[prefix]static_pages_categories`
			SET
				`parent`	= '%s',
				`title`		= '%s',
				`path`		= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$parent,
			$this->ml_set('Static_pages/categories/title', $id, $title),
			$this->ml_set('Static_pages/categories/path', $id, $path),
			$id
		)) {
			unset($this->cache->structure);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete specified category
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del_category ($id) {
		$id	= (int)$id;
		if ($this->db_prime()->q(
			[
				"UPDATE `[prefix]static_pages_categories`
				SET `parent` = '0'
				WHERE `parent` = '%s'",
				"UPDATE `[prefix]static_pages`
				SET `category` = '0'
				WHERE `category` = '%s'",
				"DELETE FROM `[prefix]static_pages_categories`
				WHERE `id` = '%s'
				LIMIT 1"
			],
			$id
		)) {
			$this->ml_del('Static_pages/categories/title', $id);
			$this->ml_del('Static_pages/categories/path', $id);
			unset($this->cache->{'/'});
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text) {
		return Text::instance()->process($this->cdb(), $text);
	}
	private function ml_set ($group, $label, $text) {
		if ($text === 'index') {
			return $text;
		}
		return Text::instance()->set($this->cdb(), $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		return Text::instance()->del($this->cdb(), $group, $label);
	}
}
