<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs\modules\Blogs;
class Blogs {
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
	 * Get data of specified post
	 *
	 * @param int			$id
	 * @param bool			$comments	Get comments structure, or not. Comments count will be returned anyway
	 *
	 * @return array|bool
	 */
	function get ($id, $comments = false) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if (($data = $Cache->{'Blogs/posts/'.$id.'/'.$L->clang}) === false) {
			$data	= $db->{$this->posts}->qf([
				"SELECT
					`id`,
					`user`,
					`date`,
					`title`,
					`path`,
					`content`
				FROM `[prefix]blogs_posts`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]);
			if ($data) {
				$data['title']								= $this->ml_process($data['title']);
				$data['path']								= $this->ml_process($data['path']);
				$data['content']							= $this->ml_process($data['content']);
				$data['short_content']						= truncate(explode('<!-- pagebreak -->', $data['content'])[0]);
				$data['sections']							= $db->{$this->posts}->qfas(
					"SELECT `section`
					FROM `[prefix]blogs_posts_sections`
					WHERE `id` = $id"
				);
				$data['tags']								= $db->{$this->posts}->qfas(
					"SELECT `tag`
					FROM `[prefix]blogs_posts_tags`
					WHERE `id` = $id"
				);
				$data['comments_count']						= (int)$db->{$this->comments}->qfs([
					"SELECT COUNT(`id`)
					FROM `[prefix]blogs_comments`
					WHERE
						`post`	= $id AND
						`lang`	= '%s'",
					$L->clang
				]);
				$Cache->{'Blogs/posts/'.$id.'/'.$L->clang}	= $data;
			}
		}
		if ($comments) {
			$data['comments']	= $this->get_comments($id);
		}
		return $data;
	}
	/**
	 * Add new post
	 *
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int[]		$sections
	 * @param string[]	$tags
	 *
	 * @return bool|int				Id of created post on success of <b>false</> on failure
	 */
	function add ($title, $path, $content, $sections, $tags) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		global $db, $User, $Config;
		$path		= path(str_replace(['/', '\\'], '_', $path ?: $title));
		$sections	= array_intersect(
			array_keys($this->get_sections_list()),
			$sections
		);
		if (empty($sections) || count($sections) > $Config->module(MODULE)->max_sections) {
			return false;
		}
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blogs_posts`
				(`user`, `date`)
			VALUES
				('%s', '%s')",
			$User->id,
			TIME
		)) {
			$id	= $db->{$this->posts}()->id();
			if ($this->set($id, $title, $path, $content, $sections, $tags)) {
				return $id;
			} else {
				$db->{$this->posts}()->q(
					"DELETE FROM `[prefix]blogs_posts`
					WHERE `id` = $id
					LIMIT 1"
				);
				$db->{$this->posts}()->q(
					"DELETE FROM `[prefix]blogs_posts_sections`
					WHERE `id` = $id"
				);
				$db->{$this->posts}()->q(
					"DELETE FROM `[prefix]blogs_posts_tags`
					WHERE `id` = $id"
				);
			}
		}
		return false;
	}
	/**
	 * Set data of specified post
	 *
	 * @param int		$id
	 * @param string	$title
	 * @param string	$path
	 * @param string	$content
	 * @param int[]		$sections
	 * @param string[]	$tags
	 *
	 * @return bool
	 */
	function set ($id, $title, $path, $content, $sections, $tags) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		global $db, $Cache, $Config;
		$id			= (int)$id;
		$title		= trim(xap($title));
		$path		= path(str_replace(['/', '\\'], '_', $path ?: $title));
		$content	= xap($content, true);
		$sections	= array_intersect(
			array_keys($this->get_sections_list()),
			$sections
		);
		if (empty($sections) || count($sections) > $Config->module(MODULE)->max_sections) {
			return false;
		}
		$sections	= implode(
			',',
			array_unique(
				array_map(
					function ($section) use ($id) {
						return "($id, $section)";
					},
					$sections
				)
			)
		);
		$tags		= implode(
			',',
			array_unique(
				array_map(
					function ($tag) use ($id) {
						return "($id, $tag)";
					},
					$this->process_tags($tags)
				)
			)
		);
		if ($db->{$this->posts}()->q(
			[
				"DELETE FROM `[prefix]blogs_posts_sections`
				WHERE `id` = '%4\$s'",
				"INSERT INTO `[prefix]blogs_posts_sections`
					(`id`, `section`)
				VALUES
					$sections",
				"UPDATE `[prefix]blogs_posts`
				SET
					`title` = '%s',
					`path` = '%s',
					`content` = '%s'
				WHERE `id` = '%s'
				LIMIT 1",
				"DELETE FROM `[prefix]blogs_posts_tags`
				WHERE `id` = '%4\$s'",
				"INSERT INTO `[prefix]blogs_posts_tags`
					(`id`, `tag`)
				VALUES
					$tags"
			],
			$this->ml_set('Blogs/posts/title', $id, $title),
			$this->ml_set('Blogs/posts/path', $id, $path),
			$this->ml_set('Blogs/posts/content', $id, $content),
			$id
		)) {
			unset(
				$Cache->{'Blogs/posts/'.$id},
				$Cache->{'Blogs/sections'}
			);
			return true;
		} else {
			$this->ml_del('Blogs/posts/title', $id);
			$this->ml_del('Blogs/posts/path', $id);
			$this->ml_del('Blogs/posts/content', $id);
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
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->posts}()->q([
			"DELETE FROM `[prefix]blogs_posts`
			WHERE `id` = $id
			LIMIT 1",
			"DELETE FROM `[prefix]blogs_posts_sections`
			WHERE `id` = $id",
			"DELETE FROM `[prefix]blogs_posts_tags`
			WHERE `id` = $id",
			"DELETE FROM `[prefix]blogs_comments`
			WHERE `post` = $id"
		])) {
			$this->ml_del('Blogs/posts/title', $id);
			$this->ml_del('Blogs/posts/path', $id);
			$this->ml_del('Blogs/posts/content', $id);
			unset(
				$Cache->{'Blogs/posts/'.$id},
				$Cache->{'Blogs/sections'}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Get total count of posts
	 *
	 * @return int
	 */
	function get_total_count () {
		global $Cache, $db;
		if (($data = $Cache->{'Blogs/total_count'}) === false) {
			$Cache->{'Blogs/total_count'}	= $data	= $db->{$this->posts}->qfs(
				"SELECT COUNT(`id`)
				FROM `[prefix]blogs_posts`"
			);
		}
		return $data;
	}
	/**
	 * Get array of sections in form [<i>id</i> => <i>title</i>]
	 *
	 * @return array|bool
	 */
	function get_sections_list () {
		global $Cache, $L;
		if (($data = $Cache->{'Blogs/sections/list/'.$L->clang}) === false) {
			$data	= $this->get_sections_list_internal(
				$this->get_sections_structure()
			);
			if ($data) {
				$Cache->{'Blogs/sections/list/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_sections_list_internal ($structure) {
		if (!empty($structure['sections'])) {
			$list	= [];
			foreach ($structure['sections'] as $section) {
				$list += $this->get_sections_list_internal($section);
			}
			return $list;
		} else {
			return [$structure['id'] => $structure['title']];
		}
	}
	/**
	 * Get array of sections structure
	 *
	 * @return array|bool
	 */
	function get_sections_structure () {
		global $Cache, $L;
		if (($data = $Cache->{'Blogs/sections/structure/'.$L->clang}) === false) {
			$data	= $this->get_sections_structure_internal();
			if ($data) {
				$Cache->{'Blogs/sections/structure/'.$L->clang}	= $data;
			}
		}
		return $data;
	}
	private function get_sections_structure_internal ($parent = 0) {
		global $db;
		$structure				= [
			'id'	=> $parent,
			'posts'	=> 0
		];
		if ($parent != 0) {
			$structure			= array_merge(
				$structure,
				$this->get_section($parent)
			);
		} else {
			global $L;
			$structure['title']	= $L->root_section;
			$structure['posts']	= $db->{$this->posts}->qfs([
				"SELECT COUNT(`id`)
				FROM `[prefix]blogs_posts_sections`
				WHERE `section` = '%s'",
				$structure['id']
			]);
		}
		$sections				= $db->{$this->posts}->qfa([
			"SELECT
				`id`,
				`path`
			FROM `[prefix]blogs_sections`
			WHERE `parent` = '%s'",
			$parent
		]);
		$structure['sections']	= [];
		if (!empty($sections)) {
			foreach ($sections as $section) {
				$structure['sections'][$section['path']]	= $this->get_sections_structure_internal($section['id']);
			}
		}
		return $structure;
	}
	/**
	 * Get data of specified section
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get_section ($id) {
		global $db, $Cache, $L;
		$id	= (int)$id;
		if (($data = $Cache->{'Blogs/sections/'.$id.'/'.$L->clang}) === false) {
			$data											= $db->{$this->posts}->qf([
				"SELECT
					`id`,
					`title`,
					`path`,
					`parent`,
					(
						SELECT COUNT(`id`)
						FROM `[prefix]blogs_posts_sections`
						WHERE `section` = '%1\$s'
					) AS `posts`
				FROM `[prefix]blogs_sections`
				WHERE `id` = '%1\$s'
				LIMIT 1",
				$id
			]);
			$data['title']									= $this->ml_process($data['title']);
			$data['path']									= $this->ml_process($data['path']);
			$data['full_path']								= [$data['path']];
			$parent											= $data['parent'];
			while ($parent != 0) {
				$section				= $this->get_section($parent);
				$data['full_path'][]	= $section['path'];
				$parent					= $section['parent'];
			}
			$data['full_path']								= implode('/', array_reverse($data['full_path']));
			$Cache->{'Blogs/sections/'.$id.'/'.$L->clang}	= $data;
		}
		return $data;
	}
	/**
	 * Add new section
	 *
	 * @param int		$parent
	 * @param string	$title
	 * @param string	$path
	 *
	 * @return bool|int			Id of created section on success of <b>false</> on failure
	 */
	function add_section ($parent, $title, $path) {
		global $db, $Cache;
		$parent	= (int)$parent;
		$path	= path(str_replace(['/', '\\'], '_', $path ?: $title));
		$posts	= $db->{$this->posts}()->qfa(
			"SELECT `id`
			FROM `[prefix]blogs_posts_sections`
			WHERE `section` = $parent"
		);
		if ($db->{$this->posts}()->q(
			"INSERT INTO `[prefix]blogs_sections`
				(`parent`)
			VALUES
				($parent)"
		)) {
			$id	= $db->{$this->posts}()->id();
			if ($posts) {
				$db->{$this->posts}()->q(
					"UPDATE `[prefix]blogs_posts_sections`
					SET `section` = $id
					WHERE `section` = $parent"
				);
				foreach ($posts as $post) {
					unset($Cache->{'Blogs/posts/'.$post['id']});
				}
				unset($post);
			}
			unset($posts);
			$this->set_section($id, $parent, $title, $path);
			unset(
				$Cache->{'Blogs/sections/list'},
				$Cache->{'Blogs/sections/structure'}
			);
			return $id;
		} else {
			return false;
		}
	}
	/**
	 * Set data of specified section
	 *
	 * @param int		$id
	 * @param int		$parent
	 * @param string	$title
	 * @param string	$path
	 *
	 * @return bool
	 */
	function set_section ($id, $parent, $title, $path) {
		global $db, $Cache;
		$parent	= (int)$parent;
		$title	= trim($title);
		$path	= path(str_replace(['/', '\\'], '_', $path ?: $title));
		$id		= (int)$id;
		if ($db->{$this->posts}()->q(
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
		)) {
			unset(
				$Cache->{'Blogs/sections'}
			);
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Delete specified section
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del_section ($id) {
		global $db, $Cache;
		$id						= (int)$id;
		$parent_section		= $db->{$this->posts}()->qfs([
			"SELECT `parent`
			FROM `[prefix]blogs_sections`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		]);
		$new_section_for_posts	= $db->{$this->posts}()->qfs([
			"SELECT `id`
			FROM `[prefix]blogs_sections`
			WHERE
				`parent` = '%s' AND
				`id` != '%s'
			LIMIT 1",
			$parent_section,
			$id
		]);
		if ($db->{$this->posts}()->q(
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
			$new_section_for_posts ?: $parent_section
		)) {
			$this->ml_del('Blogs/sections/title', $id);
			$this->ml_del('Blogs/sections/path', $id);
			unset($Cache->Blogs);
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text, $auto_translation = true) {
		global $Text;
		return $Text->process($this->posts, $text, $auto_translation);
	}
	private function ml_set ($group, $label, $text) {
		global $Text;
		return $Text->set($this->posts, $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		global $Text;
		return $Text->del($this->posts, $group, $label);
	}
	/**
	 * Get array of tags list in form [<i>id</i> => <i>text</i>]
	 *
	 * @return array
	 */
	function get_tags_list () {
		global $db, $Cache, $L;
		if (($data = $Cache->{'Blogs/tags/'.$L->clang}) === false) {
			$tags	= $db->{$this->posts}->qfa(
				"SELECT
					`id`,
					`text`
				FROM `[prefix]blogs_tags`"
			);
			$data	= [];
			if (is_array($tags) && !empty($tags)) {
				foreach ($tags as $tag) {
					$data[$tag['id']]	= $this->ml_process($tag['text']);
				}
				unset($tag);
			}
			unset($tags);
			$Cache->{'Blogs/tags/'.$L->clang}	= $data;
		}
		return $data;
	}
	/**
	 * Get tag text
	 *
	 * @param int|int[]			$id
	 *
	 * @return string|string[]
	 */
	function get_tag ($id) {
		$tags	= $this->get_tags_list();
		if (is_array($id)) {
			return array_map(
				function ($id) use ($tags) {
					return $tags[$id];
				},
				$id
			);
		}
		return $tags[$id];
	}
	/**
	 * Add tag, in most cases this function is not needed for usage, use ::process_tags() instead
	 *
	 * @param string		$tag
	 * @param bool			$clean_cache
	 *
	 * @return bool|int
	 */
	private function add_tag ($tag, $clean_cache = true) {
		$tag	= trim(xap($tag));
		if (($id = array_search($tag, $this->get_tags_list())) === false) {
			global $db, $Cache;
			if ($db->{$this->posts}()->q(
				"INSERT INTO `[prefix]blogs_tags`
					(`text`)
				VALUES
					('')"
			)) {
				$id	= $db->{$this->posts}()->id();
				if ($db->{$this->posts}()->q(
					"UPDATE `[prefix]blogs_tags`
					SET `text` = '%s'
					WHERE `id` = $id
					LIMIT 1",
					$this->ml_set('Blogs/tags', $id, $tag)
				)) {
					if ($clean_cache) {
						unset($Cache->{'Blogs/tags'});
					}
					return $id;
				} else {
					$db->{$this->posts}()->q(
						"DELETE FROM `[prefix]blogs_tags`
						WHERE `id` = $id
						LIMIT 1"
					);
				}
			}
			return false;
		}
		return $id;
	}
	/* *
	 * Delete tag with specified id
	 *
	 * @param int	$id
	 *
	 * @return bool
	 * /
	private function del_tag ($id) {
		global $db, $Cache;
		$id	= (int)$id;
		if ($db->{$this->posts}()->q(
			[
				"DELETE FROM `[prefix]blogs_posts_tags`
				WHERE `tag` = '%s'",
				"DELETE FROM `[prefix]blogs_tags`
				WHERE `id` = '%s'"
			],
			$id
		)) {
			$this->ml_del('Blogs/tags', $id);
			unset($Cache->{'Blogs/tags'});
			return true;
		}
		return false;
	}*/
	/**
	 * Accepts array of string tags and returns corresponding array of id's of these tags, new tags will be added automatically
	 *
	 * @param string[]	$tags
	 *
	 * @return int[]
	 */
	private function process_tags ($tags) {
		$tags_list	= $this->get_tags_list();
		$exists		= array_keys($tags_list, $tags);
		$tags		= array_fill_keys($tags, null);
		foreach ($exists as $tag) {
			$tags[$tags_list[$tag]]	= $tag;
		}
		unset($exists);
		$added		= false;
		foreach ($tags as $tag => &$id) {
			if ($id === null) {
				if (trim($tag)) {
					$id		= $this->add_tag(trim($tag), false);
					$added	= true;
				} else {
					unset($tags[$tag]);
				}
			}
		}
		if ($added) {
			global $Cache;
			unset($Cache->{'Blogs/tags'});
		}
		return array_values(array_unique($tags));
	}
	/**
	 * Get comments of specified post
	 *
	 * @param int			$id		Post id
	 * @param int			$parent
	 *
	 * @return bool|array
	 */
	protected function get_comments ($id, $parent = 0) {
		global $db, $Cache, $L;
		if ($parent != 0 || ($comments = $Cache->{'Blogs/posts/'.$id.'/comments/'.$L->clang}) === false) {
			$id											= (int)$id;
			$parent										= (int)$parent;
			$comments									= $db->{$this->comments}->qfa([
				"SELECT
					`id`,
					`parent`,
					`user`,
					`date`,
					`text`,
					`lang`
				FROM `[prefix]blogs_comments`
				WHERE
					`parent`	= $parent AND
					`post`		= $id AND
					`lang`		= '%s'",
				$L->clang
			]);
			if ($comments) {
				foreach ($comments as &$comment) {
					$comment['comments']	= $this->get_comments($id, $comment['id']);
				}
				unset($comment);
			}
			if ($parent == 0) {
				$Cache->{'Blogs/posts/'.$id.'/comments/'.$L->clang}	= $comments;
			}
		}
		return $comments;
	}
	/**
	 * Get comment data
	 *
	 * @param int			$id Comment id
	 *
	 * @return array|bool		Array of comment data on success or <b>false</b> on failure
	 */
	function get_comment ($id) {
		global $db;
		$id	= (int)$id;
		return $db->{$this->comments}->qf(
			"SELECT
				`id`,
				`parent`,
				`post`,
				`user`,
				`date`,
				`text`,
				`lang`
			FROM `[prefix]blogs_comments`
			WHERE `id` = $id
			LIMIT 1"
		);
	}
	/**
	 * Add new comment
	 *
	 * @param int			$post	Post id
	 * @param string		$text	Comment text
	 * @param int			$parent	Parent comment id
	 *
	 * @return array|bool			Array of comment data on success or <b>false</b> on failure
	 */
	function add_comment ($post, $text, $parent = 0) {
		global $db, $Cache, $L, $User;
		$text	= xap($text, true);
		if (!$text) {
			return false;
		}
		$post	= (int)$post;
		$parent	= (int)$parent;
		if (
			$parent != 0 &&
			$db->{$this->comments}()->qfs(
				"SELECT `post`
				FROM `[prefix]blogs_comments`
				WHERE `id` = $parent
				LIMIT 1"
			) != $post
		) {
			return false;
		}
		if ($db->{$this->comments}()->q(
			"INSERT INTO `[prefix]blogs_comments`
				(`parent`, `post`, `user`, `date`, `text`, `lang`)
			VALUES
				('%s', '%s', '%s', '%s', '%s', '%s')",
			$parent,
			$post,
			$User->id,
			TIME,
			$text,
			$L->clang
		)) {
			unset($Cache->{'Blogs/posts/'.$post.'/comments'});
			return [
				'id'		=> $db->{$this->comments}()->id(),
				'parent'	=> $parent,
				'post'		=> $post,
				'user'		=> $User->id,
				'date'		=> TIME,
				'text'		=> $text,
				'lang'		=> $L->clang
			];
		}
		return false;
	}
	/**
	 * Set comment text
	 *
	 * @param int			$id		Comment id
	 * @param string		$text	New comment text
	 *
	 * @return array|bool			Array of comment data on success or <b>false</b> on failure
	 */
	function set_comment ($id, $text) {
		global $db, $Cache;
		$text	= xap($text, true);
		if (!$text) {
			return false;
		}
		$id				= (int)$id;
		$comment		= $db->{$this->comments}()->qf(
			"SELECT
				`id`,
				`parent`,
				`post`,
				`user`,
				`date`,
				`lang`
			FROM `[prefix]blogs_comments`
			WHERE `id` = $id
			LIMIT 1"
		);
		if (!$comment) {
			return false;
		}
		if ($db->{$this->comments}()->q(
			"UPDATE `[prefix]blogs_comments`
			SET `text` = '%s'
			WHERE `id` = $id
			LIMIT 1",
			$text
		)) {
			unset($Cache->{'Blogs/posts/'.$comment['post'].'/comments'});
			$comment['text']	= $text;
			return $comment;
		}
		return false;
	}
	/**
	 * Delete comment
	 *
	 * @param int	$id	Comment id
	 *
	 * @return bool
	 */
	function del_comment ($id) {
		global $db, $Cache;
		$id				= (int)$id;
		$comment		= $db->{$this->comments}()->qf(
			"SELECT `p`.`post`, COUNT(`c`.`id`) AS `count`
			FROM `[prefix]blogs_comments` AS `p` LEFT OUTER JOIN `[prefix]blogs_comments` AS `c`
			ON `p`.`id` = `c`.`parent`
			WHERE `p`.`id` = $id
			LIMIT 1"
		);
		if (!$comment || $comment['count']) {
			return false;
		}
		if ($db->{$this->comments}()->q(
			"DELETE FROM `[prefix]blogs_comments`
			WHERE `id` = $id
			LIMIT 1"
		)) {
			unset($Cache->{'Blogs/posts/'.$comment['post']});
			return true;
		}
		return false;
	}
}
/**
 * For IDE
 */
if (false) {
	global $Blogs;
	$Blogs = new Blogs;
}