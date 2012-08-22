<?php
/**
 * @package        Blog
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
		if (($data = $Cache->{'Blog/posts/'.$id.'/'.$L->clang}) === false) {
			$data	= $db->{$this->posts}->qf([
				"SELECT `id`, `user`, `title`, `path`, `content`
				FROM `[prefix]blog_posts`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]);
			if ($data) {
				$data['title']		= $this->ml_process($data['title']);
				$data['path']		= $this->ml_process($data['path']);
				$data['content']	= $this->ml_process($data['content']);
				$data['categories']	= $db->{$this->posts}->qfa(
					"SELECT `category` FROM `[prefix]blog_posts_categories` WHERE `id` = $id",
					true
				);
				$data['tags']		= $db->{$this->posts}->qfa(
					"SELECT `tag` FROM `[prefix]blog_posts_tags` WHERE `id` = $id",
					true
				);
				$Cache->{'Blog/posts/'.$id.'/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	/**
	 * Add new post
	 *
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int[]		$categories
	 * @param string[]	$tags
	 *
	 * @return bool|int				Id of created post on success of <b>false</> on failure
	 */
	function add ($title, $path, $content, $categories, $tags) {
		global $db, $User;
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$categories	= array_intersect(
			array_keys($this->get_categories_list()),
			$categories
		);
		if (empty($categories)) {
			return false;
		}
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blog_posts`
				(`user`, `date`)
			VALUES
				('%s', '%s')",
			$User->id,
			TIME
		)) {
			$id	= $db->{$this->posts}()->id();
			$this->set($id, $title, $path, $content, $categories, $tags);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified post
	 *
	 * @param int		$id
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int[]		$categories
	 * @param string[]	$tags
	 *
	 * @return bool
	 */
	function set ($id, $title, $path, $content, $categories, $tags) {
		global $db, $Cache, $L;
		$id			= (int)$id;
		$title		= trim(xap($title));
		$path		= $this->path(str_replace('/', ' ', $path ?: $title));
		$content	= xap($content, true);
		$categories	= array_intersect(
			array_keys($this->get_categories_list()),
			$categories
		);
		if (empty($categories)) {
			return false;
		}
		$categories	= implode(
			',',
			array_map(
				function ($category) use ($id) {
					return "($id, $category)";
				},
				$categories
			)
		);
		$tags		= implode(
			',',
			array_map(
				function ($tag) use ($id) {
					return "($id, $tag)";
				},
				$this->process_tags($tags)
			)
		);
		if ($db->{$this->posts}()->q(
			[
				"INSERT INTO `[prefix]blog_posts_categories`
					(`id`, `category`)
				VALUES
					$categories",
				"UPDATE `[prefix]blog_posts`
				SET
					`title` = '%s',
					`path` = '%s',
					`content` = '%s'
				WHERE `id` = '%s'
				LIMIT 1",
				"INSERT INTO `[prefix]blog_posts_tags`
					(`id`, `tag`)
				VALUES
					$tags"
			],
			$this->ml_set('Blog/posts/title', $id, $title),
			$this->ml_set('Blog/posts/path', $id, $path),
			$this->ml_set('Blog/posts/content', $id, $content),
			$id
		)) {
			unset($Cache->{'Blog/posts/'.$id.'/'.$L->clang});
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
			unset($Cache->{'Blog/posts/'.$id.'/'.$L->clang});
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Get array of categories in form [<i>id</i> => <i>title</i>]
	 *
	 * @return array|bool
	 */
	function get_categories_list () {
		global $Cache, $L;
		if (($data = $Cache->{'Blog/categories_list/'.$L->clang}) === false) {
			$data	= $this->get_categories_list_internal(
				$this->get_categories_structure()
			);
			if ($data) {
				$Cache->{'Blog/categories_list/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_categories_list_internal ($structure) {
		if (!empty($structure['categories'])) {
			$list	= [];
			foreach ($structure['categories'] as $category) {
				$list = array_merge(
					$list,
					$this->get_categories_list_internal($category)
				);
			}
			return $list;
		} else {
			return [$structure['id'] => $structure['title']];
		}
	}
	/**
	 * Get array of categories structure
	 *
	 * @return array|bool
	 */
	function get_categories_structure () {
		global $Cache, $L;
		if (($data = $Cache->{'Blog/categories_structure/'.$L->clang}) === false) {
			$data	= $this->get_categories_structure_internal();
			if ($data) {
				$Cache->{'Blog/categories_structure/'.$L->clang}	= $data;
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
			"SELECT
				`id`,
				`path`
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
			"SELECT
				`id`,
				`title`,
				`path`,
				`parent`
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
			unset(
				$Cache->{'Blog/categories_list'},
				$Cache->{'Blog/categories_structure'}
			);
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
			SET
				`parent`	= '%s',
				`title`		= '%s',
				`path`		= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$parent,
			$this->ml_set('Blog/categories/title', $id, $title),
			$this->ml_set('Blog/categories/path', $id, $path),
			$id
		)) {
			unset(
				$Cache->{'Blog/categories_list/'.$L->clang},
				$Cache->{'Blog/categories_structure/'.$L->clang}
			);
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
		$id						= (int)$id;
		$parent_category		= $db->{$this->posts}()->qf(
			[
				"SELECT `parent`
				FROM `[prefix]blog_categories`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			],
			true
		);
		$new_category_for_posts	= $db->{$this->posts}()->qf(
			[
				"SELECT `id`
				FROM `[prefix]blog_categories`
				WHERE
					`parent` = '%s' AND
					`id` != '%s'
				LIMIT 1",
				$parent_category,
				$id
			],
			true
		);
		if ($db->{$this->posts}()->q(
			[
				"UPDATE `[prefix]blog_categories`
				SET `parent` = '%2\$s'
				WHERE `parent` = '%1\$s'",
				"UPDATE IGNORE `[prefix]blog_posts_categories`
				SET `category` = '%3\$s'
				WHERE `category` = '%1\$s'",
				"DELETE FROM `[prefix]blog_posts_categories`
				WHERE `category` = '%1\$s'",
				"DELETE FROM `[prefix]blog_categories`
				WHERE `id` = '%1\$s'
				LIMIT 1"
			],
			$id,
			$parent_category,
			$new_category_for_posts ?: $parent_category
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
		return $Text->set($this->posts, $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		global $Text;
		return $Text->del($this->posts, $group, $label);
	}
	function get_tags_list () {
		global $db, $Cache, $L;
		if (($data = $Cache->{'Blog/tags/'.$L->clang}) === false) {
			$tags	= $db->{$this->posts}->qfa(
				"SELECT
					`id`,
					`text`
				FROM `[prefix]blog_tags`"
			);
			$data	= [];
			if (is_array($tags) && !empty($tags)) {
				foreach ($tags as $tag) {
					$data[$tag['id']]	= $this->ml_process($tag['text']);
				}
				unset($tag);
			}
			unset($tags);
			$Cache->{'Blog/tags/'.$L->clang}	= $data;
		}
		return $data;
	}
	function add_tag ($tag) {
		$tag	= trim(xap($tag));
		if (($id = array_search($tag, $this->get_tags_list())) === false) {
			global $db, $Cache;
			if ($db->{$this->posts}()->q(
				"INSERT INTO `[prefix]blog_tags`
					(`value`)
				VALUES
					('')"
			)) {
				$id	= $db->{$this->posts}()->id();
				$db->{$this->posts}()->q(
					"UPDATE `[prefix]blog_tags`
					SET `value` = '%s'
					WHERE `id` = $id
					LIMIT 1",
					$this->ml_set('Blog/tags', $id, $tag)
				);
				return $id;
			}
			unset($Cache->{'Blog/tags'});
			return false;
		}
		return $id;
	}
	function del_tag ($id) {
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->posts}()->q(
			[
				"DELETE FROM `[prefix]blog_posts_tags`
				WHERE `tag` = '%s'",
				"DELETE FROM `[prefix]blog_tags`
				WHERE `id` = '%s'"
			],
			$id
		)) {
			$this->ml_del('Blog/tags', $id);
			unset($Cache->{'Blog/tags'});
		}
	}
	private function process_tags ($tags) {
		$tags_list	= $this->get_tags_list();
		$exists		= array_keys($tags_list, $tags);
		$tags		= array_fill_keys($tags, null);
		foreach ($exists as $tag) {
			$tags[$tags_list[$tag]]	= $tag;
		}
		unset($exists);
		foreach ($tags as $tag => &$id) {
			if ($id === null) {
				$id	= $this->add_tag($tag);
			}
		}
		return array_values(array_unique($tags));
	}
}