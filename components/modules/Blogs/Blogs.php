<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			cs\Trigger,
			cs\Cache\Prefix,
			cs\Config,
			cs\Language,
			cs\Text,
			cs\User,
			cs\DB\Accessor,
			cs\Singleton;

class Blogs {
	use Accessor,
		Singleton;

	/**
	 * @var Prefix
	 */
	protected	$cache;

	protected function construct () {
		$this->cache	= new Prefix('Blogs');
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
	 * Get data of specified post
	 *
	 * @param int			$id
	 *
	 * @return array|bool
	 */
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i	= $this->get($i);
			}
			return $id;
		}
		$L			= Language::instance();
		$id			= (int)$id;
		$data		= $this->cache->get("posts/$id/$L->clang", function () use($id, $L) {
			if ($data	= $this->db()->qf([
				"SELECT
					`id`,
					`user`,
					`date`,
					`title`,
					`path`,
					`content`,
					`draft`
				FROM `[prefix]blogs_posts`
				WHERE
					`id` = '%s' AND
					(
						`user`	= '%s' OR
						`draft`	= 0
					)
				LIMIT 1",
				$id,
				User::instance()->id
			])) {
				$data['title']			= $this->ml_process($data['title']);
				$data['path']			= $this->ml_process($data['path']);
				$data['content']		= $this->ml_process($data['content']);
				$data['short_content']	= truncate(explode('<!-- pagebreak -->', $data['content'])[0]);
				$data['sections']		= $this->db()->qfas(
					"SELECT `section`
					FROM `[prefix]blogs_posts_sections`
					WHERE `id` = $id"
				);
				$data['tags']								= $this->db()->qfas([
					"SELECT DISTINCT `tag`
					FROM `[prefix]blogs_posts_tags`
					WHERE
						`id`	= $id AND
						`lang`	= '%s'",
					$L->clang
				]);
				if (!$data['tags']) {
					$l				= $this->db()->qfs(
						"SELECT `lang`
						FROM `[prefix]blogs_posts_tags`
						WHERE `id` = $id
						LIMIT 1"
					);
					$data['tags']	= $this->db()->qfas(
						"SELECT DISTINCT `tag`
						FROM `[prefix]blogs_posts_tags`
						WHERE
							`id`	= $id AND
							`lang`	= '$l'"
					);
					unset($l);
				}
			}
			return $data;
		});
		$Comments	= null;
		Trigger::instance()->run(
			'Comments/instance',
			[
				'Comments'	=> &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		$data['comments_count']	= (int)(Config::instance()->module('Blogs')->enable_comments && $Comments ? $Comments->count($data['id']) : 0);
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
	 * @param bool		$draft
	 *
	 * @return bool|int				Id of created post on success of <b>false</> on failure
	 */
	function add ($title, $path, $content, $sections, $tags, $draft) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		$sections	= array_intersect(
			array_keys($this->get_sections_list()),
			$sections
		);
		if (empty($sections) || count($sections) > Config::instance()->module('Blogs')->max_sections) {
			return false;
		}
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]blogs_posts`
				(
					`user`,
					`date`,
					`draft`
				)
			VALUES
				(
					'%s',
					'%s',
					'%s'
				)",
			User::instance()->id,
			$draft ? 0 : TIME,
			(int)(bool)$draft
		)) {
			$id	= $this->db_prime()->id();
			if ($this->set_internal($id, $title, $path, $content, $sections, $tags, $draft, true)) {
				return $id;
			} else {
				$this->db_prime()->q(
					"DELETE FROM `[prefix]blogs_posts`
					WHERE `id` = $id
					LIMIT 1"
				);
				$this->db_prime()->q(
					"DELETE FROM `[prefix]blogs_posts_sections`
					WHERE `id` = $id"
				);
				$this->db_prime()->q(
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
	 * @param bool		$draft
	 *
	 * @return bool
	 */
	function set ($id, $title, $path, $content, $sections, $tags, $draft) {
		return $this->set_internal($id, $title, $path, $content, $sections, $tags, $draft);
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
	 * @param bool		$draft
	 * @param bool		$add
	 *
	 * @return bool
	 */
	function set_internal ($id, $title, $path, $content, $sections, $tags, $draft, $add = false) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		$Config			= Config::instance();
		$L				= Language::instance();
		$id				= (int)$id;
		$path			= path(trim($path ?: $title));
		$title			= xap(trim($title));
		$module_data	= $Config->module('Blogs');
		$content		= xap($content, true, $module_data->allow_iframes_without_content);
		$sections		= array_intersect(
			array_keys($this->get_sections_list()),
			$sections
		);
		if (empty($sections) || count($sections) > $module_data->max_sections) {
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
		$tags		= array_unique($tags);
		$tags		= implode(
			',',
			array_unique(
				array_map(
					function ($tag) use ($id, $L) {
						return "($id, $tag, '$L->clang')";
					},
					$processed = $this->process_tags($tags)
				)
			)
		);
		$data		= $this->get($id);
		if (!$this->db_prime()->q(
			[
				"DELETE FROM `[prefix]blogs_posts_sections`
				WHERE `id` = '%5\$s'",
				"INSERT INTO `[prefix]blogs_posts_sections`
					(`id`, `section`)
				VALUES
					$sections",
				"UPDATE `[prefix]blogs_posts`
				SET
					`title`		= '%s',
					`path`		= '%s',
					`content`	= '%s',
					`draft`		= '%s'
				WHERE `id` = '%s'
				LIMIT 1",
				"DELETE FROM `[prefix]blogs_posts_tags`
				WHERE
					`id`	= '%5\$s' AND
					`lang`	= '$L->clang'",
				"INSERT INTO `[prefix]blogs_posts_tags`
					(`id`, `tag`, `lang`)
				VALUES
					$tags"
			],
			$this->ml_set('Blogs/posts/title', $id, $title),
			$this->ml_set('Blogs/posts/path', $id, $path),
			$this->ml_set('Blogs/posts/content', $id, $content),
			(int)(bool)$draft,
			$id
		)) {
			return false;
		}
		if ($add && $Config->core['multilingual']) {
			foreach ($Config->core['active_languages'] as $lang) {
				if ($lang != $L->clanguage) {
					$lang	= $L->get('clang', $lang);
					$this->db_prime()->q(
						"INSERT INTO `[prefix]blogs_posts_tags`
							(`id`, `tag`, `lang`)
						SELECT `id`, `tag`, '$lang'
						FROM `[prefix]blogs_posts_tags`
						WHERE
							`id`	= $id AND
							`lang`	= '$L->clang'"
					);
				}
			}
			unset($lang);
		}
		preg_match_all('/"(http[s]?:\/\/.*)"/Uims', $data['content'], $old_files);
		preg_match_all('/"(http[s]?:\/\/.*)"/Uims', $content, $new_files);
		$old_files	= isset($old_files[1]) ? $old_files[1] : [];
		$new_files	= isset($new_files[1]) ? $new_files[1] : [];
		if ($old_files || $new_files) {
			foreach (array_diff($old_files, $new_files) as $file) {
				Trigger::instance()->run(
					'System/upload_files/del_tag',
					[
						'tag'	=> "Blogs/posts/$id/$L->clang",
						'url'	=> $file
					]
				);
			}
			unset($file);
			foreach (array_diff($new_files, $old_files) as $file) {
				Trigger::instance()->run(
					'System/upload_files/add_tag',
					[
						'tag'	=> "Blogs/posts/$id/$L->clang",
						'url'	=> $file
					]
				);
			}
			unset($file);
		}
		unset($old_files, $new_files);
		if ($data['draft'] == 1 && !$draft && $data['date'] == 0) {
			$this->db_prime()->q(
				"UPDATE `[prefix]blogs_posts`
				SET `date` = '%s'
				WHERE `id` = '%s'
				LIMIT 1",
				TIME,
				$id
			);
		}
		$Cache		= $this->cache;
		unset(
			$Cache->{"posts/$id"},
			$Cache->sections,
			$Cache->total_count
		);
		return true;
	}
	/**
	 * Delete specified post
	 *
	 * @param int	$id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id		= (int)$id;
		if (!$this->db_prime()->q([
			"DELETE FROM `[prefix]blogs_posts`
			WHERE `id` = $id
			LIMIT 1",
			"DELETE FROM `[prefix]blogs_posts_sections`
			WHERE `id` = $id",
			"DELETE FROM `[prefix]blogs_posts_tags`
			WHERE `id` = $id"
		])) {
			return false;
		}
		$this->ml_del('Blogs/posts/title', $id);
		$this->ml_del('Blogs/posts/path', $id);
		$this->ml_del('Blogs/posts/content', $id);
		Trigger::instance()->run(
			'System/upload_files/del_tag',
			[
				'tag'	=> "Blogs/posts/$id%"
			]
		);
		$Comments	= null;
		Trigger::instance()->run(
			'Comments/instance',
			[
				'Comments'	=> &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		if ($Comments) {
			$Comments->del_all($id);
		}
		$Cache	= $this->cache;
		unset(
			$Cache->{"posts/$id"},
			$Cache->sections,
			$Cache->total_count
		);
		return true;
	}
	/**
	 * Get total count of posts
	 *
	 * @return int
	 */
	function get_total_count () {
		return $this->cache->get('total_count', function () {
			return $this->db()->qfs(
				"SELECT COUNT(`id`)
				FROM `[prefix]blogs_posts`
				WHERE `draft` = 0"
			);
		});
	}
	/**
	 * Get array of sections in form [<i>id</i> => <i>title</i>]
	 *
	 * @return array|bool
	 */
	function get_sections_list () {
		$L		= Language::instance();
		return $this->cache->get("sections/list/$L->clang", function () {
			return $this->get_sections_list_internal(
				$this->get_sections_structure()
			);
		});
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
		$L		= Language::instance();
		return $this->cache->get("sections/structure/$L->clang", function () {
			return $this->get_sections_structure_internal();
		});
	}
	private function get_sections_structure_internal ($parent = 0) {
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
			$structure['title']	= Language::instance()->root_section;
			$structure['posts']	= $this->db()->qfs([
				"SELECT COUNT(`s`.`id`)
				FROM `[prefix]blogs_posts_sections` AS `s`
					LEFT JOIN `[prefix]blogs_posts` AS `p`
				ON `s`.`id` = `p`.`id`
				WHERE
					`s`.`section`	= '%s' AND
					`p`.`draft`		= 0",
				$structure['id']
			]);
		}
		$sections				= $this->db()->qfa([
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
				$structure['sections'][$this->ml_process($section['path'])]	= $this->get_sections_structure_internal($section['id']);
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
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i	= $this->get_section($i);
			}
			return $id;
		}
		$L		= Language::instance();
		$id		= (int)$id;
		return $this->cache->get("sections/$id/$L->clang", function () use ($id) {
			$data				= $this->db()->qf([
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
			]);
			$data['title']		= $this->ml_process($data['title']);
			$data['path']		= $this->ml_process($data['path']);
			$data['full_path']	= [$data['path']];
			$parent				= $data['parent'];
			while ($parent != 0) {
				$section				= $this->get_section($parent);
				$data['full_path'][]	= $section['path'];
				$parent					= $section['parent'];
			}
			$data['full_path']	= implode('/', array_reverse($data['full_path']));
			return $data;
		});
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
		$parent	= (int)$parent;
		$posts	= $this->db_prime()->qfa(
			"SELECT `id`
			FROM `[prefix]blogs_posts_sections`
			WHERE `section` = $parent"
		);
		if ($this->db_prime()->q(
			"INSERT INTO `[prefix]blogs_sections`
				(`parent`)
			VALUES
				($parent)"
		)) {
			$Cache	= $this->cache;
			$id		= $this->db_prime()->id();
			if ($posts) {
				$this->db_prime()->q(
					"UPDATE `[prefix]blogs_posts_sections`
					SET `section` = $id
					WHERE `section` = $parent"
				);
				foreach ($posts as $post) {
					unset($Cache->{"posts/$post[id]"});
				}
				unset($post);
			}
			unset($posts);
			$this->set_section($id, $parent, $title, $path);
			unset(
				$Cache->{'sections/list'},
				$Cache->{'sections/structure'}
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
		$parent	= (int)$parent;
		$path	= path($path ?: $title);
		$title	= xap(trim($title));
		$id		= (int)$id;
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
		)) {
			unset($this->cache->sections);
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
		$id					= (int)$id;
		$parent_section		= $this->db_prime()->qfs([
			"SELECT `parent`
			FROM `[prefix]blogs_sections`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		]);
		$new_posts_section	= $this->db_prime()->qfs([
			"SELECT `id`
			FROM `[prefix]blogs_sections`
			WHERE
				`parent` = '%s' AND
				`id` != '%s'
			LIMIT 1",
			$parent_section,
			$id
		]);
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
		)) {
			$this->ml_del('Blogs/sections/title', $id);
			$this->ml_del('Blogs/sections/path', $id);
			unset($this->cache->{'/'});
			return true;
		} else {
			return false;
		}
	}
	private function ml_process ($text, $auto_translation = true) {
		return Text::instance()->process($this->cdb(), $text, $auto_translation, true);
	}
	private function ml_set ($group, $label, $text) {
		return Text::instance()->set($this->cdb(), $group, $label, $text);
	}
	private function ml_del ($group, $label) {
		return Text::instance()->del($this->cdb(), $group, $label);
	}
	/**
	 * Get array of tags list in form [<i>id</i> => <i>text</i>]
	 *
	 * @TODO remove method, add find_tag() instead
	 *
	 * @return array
	 */
	function get_tags_list () {
		$L		= Language::instance();
		return $this->cache->get("tags/$L->clang", function () {
			$tags	= $this->db()->qfa(
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
			return $data;
		});
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
		$id		= array_search(
			mb_strtolower($tag),
			_mb_strtolower($this->get_tags_list())
		);
		if ($id === false) {
			if ($this->db_prime()->q(
				"INSERT INTO `[prefix]blogs_tags`
					(`text`)
				VALUES
					('%s')",
				$tag
			)) {
				$id	= $this->db_prime()->id();
				if ($clean_cache) {
					unset($this->cache->tags);
				}
				return $id;
			}
			return false;
		}
		return $id;
	}
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
			unset($this->cache->tags);
		}
		return array_values(array_unique($tags));
	}
}
