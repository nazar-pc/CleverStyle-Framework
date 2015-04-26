<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Event,
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\Text,
	cs\User,
	cs\DB\Accessor,
	cs\Singleton,
	cs\modules\Json_ld\Json_ld;

/**
 * @method static Posts instance($check = false)
 */
class Posts {
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
	 * Get data of specified post
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
		$L        = Language::instance();
		$id       = (int)$id;
		$data     = $this->cache->get(
			"posts/$id/$L->clang",
			function () use ($id, $L) {
				$data = $this->db()->qf(
					[
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
							`id` = '%s'
						LIMIT 1",
						$id
					]
				);
				if ($data) {
					$data['title']         = $this->ml_process($data['title']);
					$data['path']          = $this->ml_process($data['path']);
					$data['content']       = $this->ml_process($data['content']);
					$data['short_content'] = truncate(explode('<!-- pagebreak -->', $data['content'])[0]);
					$data['sections']      = $this->db()->qfas(
						"SELECT `section`
						FROM `[prefix]blogs_posts_sections`
						WHERE `id` = $id"
					);
					$data['tags']          = $this->get_tag(
						$this->db()->qfas(
							[
								"SELECT DISTINCT `tag`
								FROM `[prefix]blogs_posts_tags`
								WHERE
									`id`	= $id AND
									`lang`	= '%s'",
								$L->clang
							]
						) ?: []
					);
					if (!$data['tags']) {
						$l            = $this->db()->qfs(
							"SELECT `lang`
							FROM `[prefix]blogs_posts_tags`
							WHERE `id` = $id
							LIMIT 1"
						);
						$data['tags'] = $this->db()->qfas(
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
			}
		);
		$Comments = null;
		Event::instance()->fire(
			'Comments/instance',
			[
				'Comments' => &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		$data['comments_count'] = (int)(Config::instance()->module('Blogs')->enable_comments && $Comments ? $Comments->count($data['id']) : 0);
		return $data;
	}
	/**
	 * Get data of specified post
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
	 */
	function get_as_json_ld ($id) {
		$post = $this->get($id);;
		if (!$post) {
			return false;
		}
		$base_structure = [
			'@context' =>
				[
					'content'        => 'articleBody',
					'title'          => 'headline',
					'comments_count' => 'commentCount',
					'tags'           => 'keywords',
					'datetime'       => '',
					'sections_paths' => '',
					'tags_paths'     => ''
				] + Json_ld::context_stub(isset($post[0]) ? $post[0] : $post)
		];
		if (isset($post[0])) {
			$graph = [];
			foreach ($post as $p) {
				$graph[] = $this->get_as_json_ld_single_post($p);
			}
			return
				$base_structure +
				[
					'@graph' => $graph
				];
		}
		return
			$base_structure +
			$this->get_as_json_ld_single_post($post);
	}
	protected function get_as_json_ld_single_post ($post) {
		if (preg_match_all('/<img[^>]src=["\'](.*)["\']/Uims', $post['content'], $images)) {
			$images = $images[1];
		}
		$Sections = Sections::instance();
		$sections = [];
		if ($post['sections'] != [0]) {
			$sections = array_column(
				$Sections->get($post['sections']),
				'title'
			);
		}
		$L            = Language::instance();
		$base_url     = Config::instance()->base_url();
		$module_path  = path($L->Blogs);
		$section_path = "$base_url/$module_path/".path($L->section);
		$tag_path     = "$base_url/$module_path/".path($L->tag);
		$url          = "$base_url/$module_path/$post[path]:$post[id]";
		return
			[
				'@id'            => $url,
				'@type'          => 'BlogPosting',
				'articleSection' => $sections,
				'author'         => Json_ld::Person($post['user']),
				'datePublished'  => Json_ld::Date($post['date']),
				'image'          => $images,
				'inLanguage'     => $L->clang,
				'url'            => $url,
				'datetime'       => $L->to_locale(date($L->_datetime_long, $post['date'] ?: TIME)),
				'sections_paths' => array_map(
					function ($section) use ($section_path, $Sections) {
						$section = $Sections->get($section);
						return "$section_path/$section[full_path]";
					},
					$post['sections']
				),
				'tags_paths'     => array_map(
					function ($tag) use ($tag_path) {
						return "$tag_path/$tag";
					},
					$post['tags']
				)
			] + $post;
	}
	/**
	 * Get latest posts
	 *
	 * @param int $page
	 * @param int $number
	 *
	 * @return int[]
	 */
	function get_latest_posts ($page, $number) {
		$number = (int)$number;
		$from   = ($page - 1) * $number;
		return $this->db()->qfas(
			"SELECT `id`
			FROM `[prefix]blogs_posts`
			WHERE `draft` = 0
			ORDER BY `date` DESC
			LIMIT $from, $number"
		) ?: [];
	}
	/**
	 * Get posts for section
	 *
	 * @param int $section
	 * @param int $page
	 * @param int $number
	 *
	 * @return int[]
	 */
	function get_for_section ($section, $page, $number) {
		$section = (int)$section;
		$number  = (int)$number;
		$from    = ($page - 1) * $number;
		return $this->db()->qfas(
			"SELECT `s`.`id`
			FROM `[prefix]blogs_posts_sections` AS `s`
				LEFT JOIN `[prefix]blogs_posts` AS `p`
			ON `s`.`id` = `p`.`id`
			WHERE
				`s`.`section`	= $section AND
				`p`.`draft`		= 0
			ORDER BY `p`.`date` DESC
			LIMIT $from, $number"
		) ?: [];
	}
	/**
	 * Get posts for tag
	 *
	 * @param int    $tag
	 * @param string $lang
	 * @param int    $page
	 * @param int    $number
	 *
	 * @return int[]
	 */
	function get_for_tag ($tag, $lang, $page, $number) {
		$number = (int)$number;
		$from   = ($page - 1) * $number;
		return $this->db()->qfas(
			[
				"SELECT `t`.`id`
				FROM `[prefix]blogs_posts_tags` AS `t`
					LEFT JOIN `[prefix]blogs_posts` AS `p`
				ON `t`.`id` = `p`.`id`
				WHERE
					`t`.`tag`	= '%s' AND
					`p`.`draft`	= 0 AND
					`t`.`lang`	= '%s'
				ORDER BY `p`.`date` DESC
				LIMIT $from, $number",
				$tag,
				$lang
			]
		) ?: [];
	}
	/**
	 * Get count of posts for tag
	 *
	 * @param int    $tag
	 * @param string $lang
	 * @param int    $page
	 * @param int    $number
	 *
	 * @return int
	 */
	function get_for_tag_count ($tag, $lang, $page, $number) {
		$number = (int)$number;
		$from   = ($page - 1) * $number;
		return $this->db()->qfs(
			[
				"SELECT COUNT(`t`.`id`)
				FROM `[prefix]blogs_posts_tags` AS `t`
					LEFT JOIN `[prefix]blogs_posts` AS `p`
				ON `t`.`id` = `p`.`id`
				WHERE
					`t`.`tag`	= '%s' AND
					`p`.`draft`	= 0 AND
					`t`.`lang`	= '%s'
				ORDER BY `p`.`date` DESC
				LIMIT $from, $number",
				$tag,
				$lang
			]
		) ?: 0;
	}
	/**
	 * Add new post
	 *
	 * @param string   $title
	 * @param string   $path
	 * @param string   $content
	 * @param int[]    $sections
	 * @param string[] $tags
	 * @param bool     $draft
	 *
	 * @return false|int Id of created post on success of <b>false</> on failure
	 */
	function add ($title, $path, $content, $sections, $tags, $draft) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		$sections = array_intersect(
			array_keys(Sections::instance()->get_list()),
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
			$draft ? 0 : time(),
			(int)(bool)$draft
		)
		) {
			$id = $this->db_prime()->id();
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
	 * @param int      $id
	 * @param string   $title
	 * @param string   $path
	 * @param string   $content
	 * @param int[]    $sections
	 * @param string[] $tags
	 * @param bool     $draft
	 *
	 * @return bool
	 */
	function set ($id, $title, $path, $content, $sections, $tags, $draft) {
		return $this->set_internal($id, $title, $path, $content, $sections, $tags, $draft);
	}
	/**
	 * Set data of specified post
	 *
	 * @param int      $id
	 * @param string   $title
	 * @param string   $path
	 * @param string   $content
	 * @param int[]    $sections
	 * @param string[] $tags
	 * @param bool     $draft
	 * @param bool     $add
	 *
	 * @return bool
	 */
	function set_internal ($id, $title, $path, $content, $sections, $tags, $draft, $add = false) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		$Config      = Config::instance();
		$L           = Language::instance();
		$id          = (int)$id;
		$path        = path(trim($path ?: $title));
		$title       = xap(trim($title));
		$module_data = $Config->module('Blogs');
		$content     = xap($content, true, $module_data->allow_iframes_without_content);
		$sections    = array_intersect(
			array_keys(Sections::instance()->get_list()),
			$sections
		);
		if (empty($sections) || count($sections) > $module_data->max_sections) {
			return false;
		}
		$sections = implode(
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
		$tags     = array_unique($tags);
		$tags     = implode(
			',',
			array_unique(
				array_map(
					function ($tag) use ($id, $L) {
						return "($id, $tag, '$L->clang')";
					},
					$this->process_tags($tags)
				)
			)
		);
		$data     = $this->get($id);
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
		)
		) {
			return false;
		}
		if ($add && $Config->core['multilingual']) {
			foreach ($Config->core['active_languages'] as $lang) {
				if ($lang != $L->clanguage) {
					$lang = $L->get('clang', $lang);
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
		$old_files = isset($old_files[1]) ? $old_files[1] : [];
		$new_files = isset($new_files[1]) ? $new_files[1] : [];
		if ($old_files || $new_files) {
			foreach (array_diff($old_files, $new_files) as $file) {
				Event::instance()->fire(
					'System/upload_files/del_tag',
					[
						'tag' => "Blogs/posts/$id/$L->clang",
						'url' => $file
					]
				);
			}
			unset($file);
			foreach (array_diff($new_files, $old_files) as $file) {
				Event::instance()->fire(
					'System/upload_files/add_tag',
					[
						'tag' => "Blogs/posts/$id/$L->clang",
						'url' => $file
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
				time(),
				$id
			);
		}
		$Cache = $this->cache;
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
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id = (int)$id;
		if (!$this->db_prime()->q(
			[
				"DELETE FROM `[prefix]blogs_posts`
				WHERE `id` = $id
				LIMIT 1",
				"DELETE FROM `[prefix]blogs_posts_sections`
				WHERE `id` = $id",
				"DELETE FROM `[prefix]blogs_posts_tags`
				WHERE `id` = $id"
			]
		)
		) {
			return false;
		}
		$this->ml_del('Blogs/posts/title', $id);
		$this->ml_del('Blogs/posts/path', $id);
		$this->ml_del('Blogs/posts/content', $id);
		Event::instance()->fire(
			'System/upload_files/del_tag',
			[
				'tag' => "Blogs/posts/$id%"
			]
		);
		$Comments = null;
		Event::instance()->fire(
			'Comments/instance',
			[
				'Comments' => &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		if ($Comments) {
			$Comments->del_all($id);
		}
		$Cache = $this->cache;
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
		return $this->cache->get(
			'total_count',
			function () {
				return $this->db()->qfs(
					"SELECT COUNT(`id`)
					FROM `[prefix]blogs_posts`
					WHERE `draft` = 0"
				);
			}
		);
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
	/**
	 * Get tag text
	 *
	 * @param int|int[] $id
	 *
	 * @return string|string[]
	 */
	function get_tag ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get_tag($i);
			}
			return $id;
		}
		$id = (int)$id;
		return $this->cache->get(
			"tags/$id",
			function () use ($id) {
				return $this->db()->qfs(
					[
						"SELECT `text`
						FROM `[prefix]blogs_tags`
						WHERE `id` = '%s'
						LIMIT 1",
						$id
					]
				);
			}
		);
	}
	/**
	 * Find tag by its text
	 *
	 * @param string $tag_text
	 *
	 * @return false|int
	 */
	function find_tag ($tag_text) {
		return $this->db()->qfs(
			[
				"SELECT `id`
				FROM  `[prefix]blogs_tags`
				WHERE `text` = '%s'
				LIMIT 1",
				trim(xap($tag_text))
			]
		);
	}
	/**
	 * Accepts array of string tags and returns corresponding array of id's of these tags, new tags will be added automatically
	 *
	 * @param string[] $tags
	 *
	 * @return int[]
	 */
	private function process_tags ($tags) {
		if (!$tags) {
			return [];
		}
		$tags = xap($tags);
		$cdb  = $this->db_prime();
		$cdb->insert(
			"INSERT IGNORE INTO `[prefix]blogs_tags`
				(`text`)
			VALUES
				('%s')",
			array_map(
				function ($tag) {
					return [$tag];
				},
				$tags
			),
			true
		);
		$in = [];
		foreach ($tags as $tag) {
			$in[] = $cdb->s($tag);
		}
		$in = implode(',', $in);
		return $cdb->qfas(
			"SELECT `id`
			FROM `[prefix]blogs_tags`
			WHERE `text` IN($in)"
		);
	}
}
