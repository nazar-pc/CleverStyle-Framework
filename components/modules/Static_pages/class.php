<?php
/**
 * @package        Static Pages
 * @category       modules
 * @version        0.001
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
	 * Database index for texts
	 *
	 * @var int
	 */
	private	$texts;
	/**
	 * Saving indexes of used databases
	 */
	function __construct () {
		global $Config;
		$this->pages	= $Config->module(MODULE)->db('pages');
		$this->texts	= $Config->module(MODULE)->db('texts');
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
		global $db, $Cache;
		$id	= (int)$id;
		if (($data = $Cache->{'Static_pages/pages/'.$id}) === false) {
			$data	= $db->{$this->pages}->qf([
				"SELECT `category`, `title`, `path`, `content`, `interface`
				FROM `[prefix]static_pages`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]);
			if ($data) {
				$Cache->{'Static_pages/pages/'.$id}	= $data;
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
				(`category`, `title`, `path`, `content`, `interface`)
			VALUES
				('%s', '%s', '%s', '%s', '%s')",
			$category,
			$title,
			$path,
			$content,
			$interface
		)) {
			unset($Cache->{'Static_pages/structure'});
			return $db->{$this->pages}()->id();
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
		global $db, $Cache;
		$category	= (int)$category;
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$interface	= (int)$interface;
		$id			= (int)$id;
		if ($db->{$this->pages}()->q(
			"UPDATE `[prefix]static_pages`
			SET `category` = '%s', `title` = '%s', `path` = '%s', `content` = '%s', `interface` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$category,
			$title,
			$path,
			$content,
			$interface,
			$id
		)) {
			unset($Cache->{'Static_pages/structure'});
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
			(int)$id
		)) {
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
		global $Cache;
		if (($data = $Cache->{'Static_pages/structure'}) === false) {
			$data	= $this->get_structure_internal();
			if ($data) {
				$Cache->{'Static_pages/structure'}	= $data;
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
				$db->{$this->pages}->qf([
					"SELECT `title`, `path`
					FROM `[prefix]static_pages_categories`
					WHERE `id` = '%s'
					LIMIT 1",
					$parent
				])
			);
		}
		$pages						= $db->{$this->pages}->qfa([
			"SELECT `id`, `path`
			FROM `[prefix]static_pages`
			WHERE `category` = '%s'",
			$parent
		]);
		$structure['pages']			= [];
		if (!empty($pages)) {
			foreach ($pages as $page) {
				$structure['pages'][$page['path']]	= $page['id'];
			}
			unset($page);
		}
		unset($pages);
		$categories					= $db->{$this->pages}->qfa([
			"SELECT `id`, `path`
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
		$id	= (int)$id;
		return $db->{$this->pages}->qf([
			"SELECT `title`, `path`, `content`, `interface`
			FROM `[prefix]static_pages_categories`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		]);
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
				(`parent`, `title`, `path`)
			VALUES
				('%s', '%s', '%s')",
			$parent,
			$title,
			$path
		)) {
			unset($Cache->{'Static_pages/structure'});
			return $db->{$this->pages}()->id();
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
		global $db, $Cache;
		$parent	= (int)$parent;
		$path	= $this->path(str_replace('/', ' ', $path ?: $title));
		$id		= (int)$id;
		if ($db->{$this->pages}()->q(
			"UPDATE `[prefix]static_pages_categories`
			SET `parent` = '%s', `title` = '%s', `path` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$parent,
			$title,
			$path,
			$id
		)) {
			unset($Cache->{'Static_pages/structure'});
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
				"DELETE FROM `[prefix]static_pages_categories` WHERE `id` = '%s' LIMIT 1",
				"UPDATE `[prefix]static_pages_categories` SET `parent` = '0' WHERE `parent` = '%s'",
				"UPDATE `[prefix]static_pages` SET `category` = '0' WHERE `category` = '%s'"
			],
			$id
		)) {
			unset($Cache->{'Static_pages'});
			return true;
		} else {
			return false;
		}
	}
}