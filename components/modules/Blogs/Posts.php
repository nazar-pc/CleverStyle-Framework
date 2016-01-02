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
	cs\Event,
	cs\Cache\Prefix,
	cs\Config,
	cs\Language,
	cs\User,
	cs\CRUD,
	cs\Singleton,
	cs\plugins\Json_ld\Json_ld;

/**
 * @method static Posts instance($check = false)
 */
class Posts {
	use
		CRUD,
		Singleton;
	protected $data_model                  = [
		'id'      => 'int:0',
		'user'    => 'int:0',
		'date'    => 'int:0',
		'title'   => 'ml:text',
		'path'    => 'ml:text',
		'content' => 'ml:html',
		'draft'   => 'int:0..1'
	];
	protected $table                       = '[prefix]blogs_posts';
	protected $data_model_ml_group         = 'Blogs/posts';
	protected $data_model_files_tag_prefix = 'Blogs/posts';
	protected $table_sections              = '[prefix]blogs_posts_sections';
	protected $table_tags                  = '[prefix]blogs_posts_tags';
	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Blogs');
		if (Config::instance()->module('Blogs')->allow_iframes_without_content) {
			$this->data_model['content'] = 'ml:html_iframe';
		}
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
				$data = $this->read($id);
				if ($data) {
					$data['short_content'] = truncate(explode('<!-- pagebreak -->', $data['content'])[0]);
					$data['sections']      = $this->db()->qfas(
						"SELECT `section`
						FROM `$this->table_sections`
						WHERE `id` = $id"
					);
					$data['tags']          = $this->db()->qfas(
						"SELECT DISTINCT `tag`
						FROM `$this->table_tags`
						WHERE
							`id`	= $id AND
							`lang`	= '$L->clang'"
					);
					if (!$data['tags']) {
						$l            = $this->db()->qfs(
							"SELECT `lang`
							FROM `$this->table_tags`
							WHERE `id` = $id
							LIMIT 1"
						);
						$data['tags'] = $this->db()->qfas(
							"SELECT DISTINCT `tag`
							FROM `$this->table_tags`
							WHERE
								`id`	= $id AND
								`lang`	= '$l'"
						);
					}
					$data['tags'] = array_column(
						Tags::instance()->get($data['tags']),
						'text'
					);
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
		$data['comments_count'] =
			Config::instance()->module('Blogs')->enable_comments && $Comments
				? $Comments->count($data['id'])
				: 0;
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
		$post = $this->get($id);
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
					'datetime'       => null,
					'sections_paths' => null,
					'tags_paths'     => null
				] + Json_ld::context_stub(isset($post[0]) ? $post[0] : $post)
		];
		if (isset($post[0])) {
			return
				$base_structure +
				[
					'@graph' => array_map(
						[$this, 'get_as_json_ld_single_post'],
						$post
					)
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
			FROM `$this->table`
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
			FROM `$this->table_sections` AS `s`
				LEFT JOIN `$this->table` AS `p`
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
				FROM `$this->table_tags` AS `t`
					LEFT JOIN `$this->table` AS `p`
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
	 * Get number of posts for tag
	 *
	 * @param int    $tag
	 * @param string $lang
	 *
	 * @return int
	 */
	function get_for_tag_count ($tag, $lang) {
		return $this->db()->qfs(
			[
				"SELECT COUNT(`t`.`id`)
				FROM `$this->table_tags` AS `t`
					LEFT JOIN `$this->table` AS `p`
				ON `t`.`id` = `p`.`id`
				WHERE
					`t`.`tag`	= '%s' AND
					`p`.`draft`	= 0 AND
					`t`.`lang`	= '%s'",
				$tag,
				$lang
			]
		) ?: 0;
	}
	/**
	 * Get drafts
	 *
	 * @param int $user
	 * @param int $page
	 * @param int $number
	 *
	 * @return int[]
	 */
	function get_drafts ($user, $page, $number) {
		$number = (int)$number;
		$from   = ($page - 1) * $number;
		return $this->db()->qfas(
			[
				"SELECT `id`
				FROM `$this->table`
				WHERE
					`draft` = 1 AND
					`user`	= '%s'
				ORDER BY `date` DESC
				LIMIT $from, $number",
				$user
			]
		) ?: [];
	}
	/**
	 * Get number of drafts
	 *
	 * @param int $user
	 *
	 * @return int
	 */
	function get_drafts_count ($user) {
		return $this->db()->qfs(
			[
				"SELECT COUNT(`id`)
				FROM `$this->table`
				WHERE
					`draft` = 1 AND
					`user`	= '%s'",
				$user
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
		if (!$this->check_arguments($content, $sections, $tags)) {
			return false;
		}
		$id = $this->create(
			[
				User::instance()->id,
				$draft ? 0 : time(),
				$title,
				path($path ?: $title),
				$content,
				(int)(bool)$draft
			]
		);
		if ($id) {
			$this->final_updates_and_cache_cleanups($id, $sections, $tags);
		}
		return $id;
	}
	/**
	 * @param string   $content
	 * @param int[]    $sections
	 * @param string[] $tags
	 *
	 * @return bool
	 */
	protected function check_arguments ($content, &$sections, $tags) {
		if (empty($tags) || empty($content)) {
			return false;
		}
		$sections = array_intersect(
			array_keys(Sections::instance()->get_list()),
			$sections
		);
		return
			$sections && count($sections) <= Config::instance()->module('Blogs')->max_sections;
	}
	/**
	 * @param int      $id
	 * @param int[]    $sections
	 * @param string[] $tags
	 */
	protected function final_updates_and_cache_cleanups ($id, $sections, $tags) {
		$this->update_sections($id, $sections);
		$this->update_tags($id, $tags);
		$Cache = $this->cache;
		unset(
			$Cache->{"posts/$id"},
			$Cache->sections,
			$Cache->total_count
		);
	}
	/**
	 * Remove existing sections and set as specified
	 *
	 * @param int   $id
	 * @param int[] $sections Empty array to just remove all existing sections
	 */
	protected function update_sections ($id, $sections = []) {
		$this->db_prime()->q(
			"DELETE FROM `$this->table_sections`
			WHERE `id` = %d",
			$id
		);
		if (!$sections) {
			return;
		}
		$id = (int)$id;
		$this->db_prime()->insert(
			"INSERT INTO `$this->table_sections`
				(
					`id`,
					`section`
				) VALUES (
					$id,
					%d
				)",
			array_unique($sections),
			true
		);
	}
	/**
	 * Remove existing tags and set as specified
	 *
	 * @param int      $id
	 * @param string[] $tags Empty array to just remove all existing tags
	 */
	protected function update_tags ($id, $tags = []) {
		if (!$tags) {
			$this->db_prime()->q(
				"DELETE FROM `$this->table_tags`
				WHERE
					`id` = %d",
				$id
			);
			return;
		}
		$L = Language::instance();
		$this->db_prime()->q(
			"DELETE FROM `$this->table_tags`
			WHERE
				`id`	= %d AND
				`lang`	= '%s'",
			$id,
			$L->clang
		);
		$id = (int)$id;
		$this->db_prime()->insert(
			"INSERT INTO `$this->table_tags`
				(
					`id`,
					`tag`,
					`lang`
				) VALUES (
					$id,
					%d,
					'$L->clang'
				)",
			Tags::instance()->add($tags),
			true
		);
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
		if (!$this->check_arguments($content, $sections, $tags)) {
			return false;
		}
		$old_data = $this->get($id);
		$result   = $this->update(
			[
				$id,
				$old_data['user'],
				$old_data['draft'] == 1 && $old_data['date'] == 0 && !$draft ? time() : $old_data['date'],
				$title,
				path($path ?: $title),
				$content,
				(int)(bool)$draft
			]
		);
		if ($result) {
			$this->final_updates_and_cache_cleanups($id, $sections, $tags);
		}
		return $result;
	}
	/**
	 * Delete specified post
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	function del ($id) {
		$id     = (int)$id;
		$result = $this->delete($id);
		if ($result) {
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
			$this->final_updates_and_cache_cleanups($id, [], []);
		}
		return $result;
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
					FROM `$this->table`
					WHERE `draft` = 0"
				);
			}
		);
	}
}
