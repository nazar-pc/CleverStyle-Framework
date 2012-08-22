<?php
/**
 * @package        Static Pages
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
class Static_pages {
	/**
	 * Database index for pages
	 *
	 * @var int
	 */
	private	$pages;
	/**
	 * Saving indexes of used databases
	 */
	function __construct () {
		global $Config;
		$this->pages	= $Config->module(basename(__DIR__))->db('pages');
	}
	/**
	 * Prepare string to use as path
	 *
	 * @param string	$text
	 *
	 * @return string
	 */
	private function path ($text) {
		return strtr(
			$text,
			[
				' '		=> '_',
				'/'		=> '_',
				'\\'	=> '_'
			]
		);
	}
	/**
	 * Get data of specified page
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if (($data = $Cache->{'Static_pages/pages/'.$id.'/'.$L->clang}) === false) {
			$data	= $db->{$this->pages}->qf([
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
			]);
			if ($data) {
				$data['title']		= $this->ml_process($data['title']);
				$data['path']		= $this->ml_process($data['path']);
				$data['content']	= $this->ml_process($data['content']);
				$Cache->{'Static_pages/pages/'.$id.'/'.$L->clang}	= $data;
			}
		}
		return $data;
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
		global $db, $Cache;
		$category	= (int)$category;
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$interface	= (int)$interface;
		if ($db->{$this->pages}()->q(
			"INSERT INTO `[prefix]static_pages`
				(`category`, `interface`)
			VALUES
				('%s', '%s')",
			$category,
			$interface
		)) {
			$id	= $db->{$this->pages}()->id();
			$this->set($id, $category, $title, $path, $content, $interface);
			unset($Cache->Static_pages);
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
		global $db, $Cache, $L;
		$category	= (int)$category;
		$title		= trim($title);
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$interface	= (int)$interface;
		$id			= (int)$id;
		if ($db->{$this->pages}()->q(
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
			unset(
				$Cache->{'Static_pages/structure/'.$L->clang},
				$Cache->{'Static_pages/pages/'.$id.'/'.$L->clang}
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
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->pages}()->q(
			"DELETE FROM `[prefix]static_pages`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		)) {
			$this->ml_del('Static_pages/pages/title', $id);
			$this->ml_del('Static_pages/pages/path', $id);
			$this->ml_del('Static_pages/pages/content', $id);
			unset(
				$Cache->{'Static_pages/structure'},
				$Cache->{'Static_pages/pages/'.$id}
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
		global $Cache, $L;
		if (($data = $Cache->{'Static_pages/structure/'.$L->clang}) === false) {
			$data	= $this->get_structure_internal();
			if ($data) {
				$Cache->{'Static_pages/structure/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_structure_internal ($parent = 0) {
		global $db;
		$structure					= ['id'	=> $parent];
		if ($parent != 0) {
			$structure	= array_merge(
				$structure,
				$this->get_category($parent)
			);
		}
		$pages						= $db->{$this->pages}->qfa(
			[
				"SELECT `id`
				FROM `[prefix]static_pages`
				WHERE `category` = '%s'",
				$parent
			],
			true
		);
		$structure['pages']			= [];
		if (!empty($pages)) {
			foreach ($pages as $id) {
				$structure['pages'][$this->get($id)['path']]	= $id;
			}
			unset($id);
		}
		unset($pages);
		$categories					= $db->{$this->pages}->qfa([
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
		global $db;
		$id				= (int)$id;
		$data			= $db->{$this->pages}->qf([
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
		global $db, $Cache;
		$parent	= (int)$parent;
		$path	= $this->path(str_replace('/', ' ', $path ?: $title));
		if ($db->{$this->pages}()->q(
			"INSERT INTO `[prefix]static_pages_categories`
				(`parent`)
			VALUES
				('%s')",
			$parent
		)) {
			$id	= $db->{$this->pages}()->id();
			$this->set_category($id, $parent, $title, $path);
			unset($Cache->{'Static_pages/structure'});
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
		global $db, $Cache, $L;
		$parent	= (int)$parent;
		$title	= trim($title);
		$path	= $this->path(str_replace('/', ' ', $path ?: $title));
		$id		= (int)$id;
		if ($db->{$this->pages}()->q(
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
			unset($Cache->{'Static_pages/structure/'.$L->clang});
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
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->pages}()->q(
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
			unset($Cache->Static_pages);
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text) {
		global $Text;
		return $Text->process($this->pages, $text);
	}
	private function ml_set ($group, $label, $text) {
		global $Text;
		if ($text === 'index') {
			return $text;
		}
		return $Text->set($this->pages, $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		global $Text;
		return $Text->del($this->pages, $group, $label);
	}
}