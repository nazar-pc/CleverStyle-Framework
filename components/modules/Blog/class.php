<?php
/**
 * @package        Static Pages
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Blog;
class Blog {
	/**
	 * Database index for posts
	 *
	 * @var int
	 */
	private	$posts;
	/**
	 * Database index for comments
	 *
	 * @var int
	 */
	private	$comments;
	/**
	 * Saving indexes of used databases
	 */
	function __construct () {
		global $Config;
		$this->posts	= $Config->module(basename(__DIR__))->db('posts');
		$this->comments	= $Config->module(basename(__DIR__))->db('comments');
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
	 * Get data of specified post
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if (($data = $Cache->{'Blog/posts_'.$L->clang.'/'.$id}) === false) {
			$data	= $db->{$this->posts}->qf([
				"SELECT `id`, `category`, `title`, `path`, `content`, `interface`
				FROM `[prefix]blog_posts`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]);
			if ($data) {
				$data['title']		= $this->ml_process($data['title']);
				$data['path']		= $this->ml_process($data['path']);
				$data['content']	= $this->ml_process($data['content']);
				$Cache->{'Blog/posts_'.$L->clang.'/'.$id}	= $data;
			}
		}
		return $data;
	}
	/**
	 * Add new post
	 *
	 * @param int		$category
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int		$interface
	 *
	 * @return bool|int				Id of created post on success of <b>false</> on failure
	 */
	function add ($category, $title, $path, $content, $interface) {
		global $db, $Cache;
		$category	= (int)$category;
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$interface	= (int)$interface;
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blog_posts`
				(`category`, `interface`)
			VALUES
				('%s', '%s')",
			$category,
			$interface
		)) {
			$id	= $db->{$this->posts}()->id();
			$this->set($id, $category, $title, $path, $content, $interface);
			unset($Cache->Blog);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified post
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
		if ($db->{$this->posts}()->q(
			"UPDATE `[prefix]blog_posts`
			SET `category` = '%s', `title` = '%s', `path` = '%s', `content` = '%s', `interface` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$category,
			$this->ml_set('Blog/posts/title', $id, $title),
			$this->ml_set('Blog/posts/path', $id, $path),
			$this->ml_set('Blog/posts/content', $id, $content),
			$interface,
			$id
		)) {
			unset(
				//$Cache->{'Blog/structure_'.$L->clang},
				$Cache->{'Blog/posts_'.$L->clang.'/'.$id}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete specified post
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del ($id) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if ($db->{$this->posts}()->q(
			"DELETE FROM `[prefix]blog_posts`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		)) {
			$this->ml_del('Blog/posts/title', $id);
			$this->ml_del('Blog/posts/path', $id);
			$this->ml_del('Blog/posts/content', $id);
			unset(
				//$Cache->{'Blog/structure_'.$L->clang},
				$Cache->{'Blog/posts_'.$L->clang.'/'.$id}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Get array of posts structure
	 *
	 * @return array|bool
	 */
	function get_categories_structure () {
		global $Cache, $L;
		if (($data = $Cache->{'Blog/categories_structure_'.$L->clang}) === false) {
			$data	= $this->get_categories_structure_internal();
			if ($data) {
				$Cache->{'Blog/categories_structure_'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_categories_structure_internal ($parent = 0) {
		global $db;
		$structure					= ['id'	=> $parent];
		if ($parent != 0) {
			$structure	= array_merge(
				$structure,
				$this->get_category($parent)
			);
		}
		$categories					= $db->{$this->posts}->qfa([
			"SELECT `id`, `path`
			FROM `[prefix]blog_categories`
			WHERE `parent` = '%s'",
			$parent
		]);
		$structure['categories']	= [];
		foreach ($categories as $category) {
			$structure['categories'][$category['path']]	= $this->get_categories_structure_internal($category['id']);
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
		$data			= $db->{$this->posts}->qf([
			"SELECT `id`, `title`, `path`, `parent`
			FROM `[prefix]blog_categories`
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
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blog_categories`
				(`parent`)
			VALUES
				('%s')",
			$parent
		)) {
			$id	= $db->{$this->posts}()->id();
			$this->set_category($id, $parent, $title, $path);
			unset($Cache->Blog);
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
		if ($db->{$this->posts}()->q(
			"UPDATE `[prefix]blog_categories`
			SET `parent` = '%s', `title` = '%s', `path` = '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$parent,
			$this->ml_set('Blog/categories/title', $id, $title),
			$this->ml_set('Blog/categories/path', $id, $path),
			$id
		)) {
			unset($Cache->{'Blog/categories_structure_'.$L->clang});
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
		if ($db->{$this->posts}()->q(
			[
				"DELETE FROM `[prefix]blog_categories` WHERE `id` = '%s' LIMIT 1",
				"UPDATE `[prefix]blog_categories` SET `parent` = '0' WHERE `parent` = '%s'",
				"UPDATE `[prefix]blog_posts` SET `category` = '0' WHERE `category` = '%s'"
			],
			$id
		)) {
			$this->ml_del('Blog/categories/title', $id);
			$this->ml_del('Blog/categories/path', $id);
			unset($Cache->Blog);
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text) {
		global $Text;
		return $Text->process($this->posts, $text);
	}
	private function ml_set ($group, $label, $text) {
		global $Text;
		if ($text === 'index') {
			return $text;
		}
		return $Text->set($this->posts, $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		global $Text;
		return $Text->del($this->posts, $group, $label);
	}
}