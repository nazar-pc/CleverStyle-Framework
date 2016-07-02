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
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\Language,
	cs\User,
	cs\CRUD_helpers,
	cs\Singleton,
	cs\modules\Json_ld\Json_ld;

/**
 * @method static $this instance($check = false)
 */
class Posts {
	use
		CRUD_helpers,
		Singleton;
	protected $data_model                  = [
		'id'       => 'int:0',
		'user'     => 'int:0',
		'date'     => 'int:0',
		'title'    => 'ml:text',
		'path'     => 'ml:text',
		'content'  => 'ml:html',
		'draft'    => 'int:0..1',
		'sections' => [
			'data_model' => [
				'id'      => 'int:0',
				'section' => 'int:0'
			]
		],
		'tags'     => [
			'data_model'     => [
				'id'  => 'int:0',
				'tag' => 'int:0'
			],
			'language_field' => 'lang'
		]
	];
	protected $table                       = '[prefix]blogs_posts';
	protected $data_model_ml_group         = 'Blogs/posts';
	protected $data_model_files_tag_prefix = 'Blogs/posts';
	/**
	 * @var Cache\Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = Cache::prefix('Blogs');
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
		$L    = Language::instance();
		$id   = (int)$id;
		$data = $this->cache->get(
			"posts/$id/$L->clang",
			function () use ($id) {
				$data = $this->read($id);
				if ($data) {
					$data['short_content'] = truncate(explode('<!-- pagebreak -->', $data['content'])[0]);
					$data['tags']          = $this->read_tags_processing($data['tags']);
				}
				return $data;
			}
		);
		return $data;
	}
	/**
	 * @param int $page
	 * @param int $count
	 *
	 * @return int[]
	 */
	function get_all ($page, $count) {
		return $this->search([], $page, $count, 'id');
	}
	/**
	 * Transform tags ids back into array of strings
	 *
	 * @param int[] $tags
	 *
	 * @return string[]
	 */
	protected function read_tags_processing ($tags) {
		return array_column(Tags::instance()->get($tags) ?: [], 'text');
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
		return $this->post_to_jsonld($post);
	}
	/**
	 * @param array|array[] $post
	 *
	 * @return array
	 */
	function post_to_jsonld ($post) {
		$base_structure = [
			'@context' =>
				[
					'content'        => 'articleBody',
					'title'          => 'headline',
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
						[$this, 'post_to_jsonld_single_post'],
						$post
					)
				];
		}
		return
			$base_structure +
			$this->post_to_jsonld_single_post($post);
	}
	protected function post_to_jsonld_single_post ($post) {
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
		$L            = Language::prefix('blogs_');
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
	 * @param int $count
	 *
	 * @return int[]
	 */
	function get_latest_posts ($page, $count) {
		$search_parameters = [
			'draft' => 0
		];
		return $this->search($search_parameters, $page, $count, 'date', false) ?: [];
	}
	/**
	 * Get posts for section
	 *
	 * @param int $section
	 * @param int $page
	 * @param int $count
	 *
	 * @return int[]
	 */
	function get_for_section ($section, $page, $count) {
		$search_parameters = [
			'draft'    => 0,
			'sections' => [
				'section' => $section
			]
		];
		return $this->search($search_parameters, $page, $count, 'date', false) ?: [];
	}
	/**
	 * Get number of posts for section
	 *
	 * @param int $section
	 *
	 * @return int
	 */
	function get_for_section_count ($section) {
		$search_parameters = [
			'draft'       => 0,
			'sections'    => [
				'section' => $section
			],
			'total_count' => true
		];
		return $this->search($search_parameters);
	}
	/**
	 * Get posts for tag
	 *
	 * @param int    $tag
	 * @param string $lang
	 * @param int    $page
	 * @param int    $count
	 *
	 * @return int[]
	 */
	function get_for_tag ($tag, $lang, $page, $count) {
		$search_parameters = [
			'draft' => 0,
			'tags'  => [
				'tag'  => $tag,
				'lang' => $lang
			]
		];
		return $this->search($search_parameters, $page, $count, 'date', false) ?: [];
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
		$search_parameters = [
			'draft'       => 0,
			'tags'        => [
				'tag'  => $tag,
				'lang' => $lang
			],
			'total_count' => true
		];
		return $this->search($search_parameters);
	}
	/**
	 * Get drafts
	 *
	 * @param int $user
	 * @param int $page
	 * @param int $count
	 *
	 * @return int[]
	 */
	function get_drafts ($user, $page, $count) {
		$search_parameters = [
			'user'  => $user,
			'draft' => 1
		];
		return $this->search($search_parameters, $page, $count, 'date', false) ?: [];
	}
	/**
	 * Get number of drafts
	 *
	 * @param int $user
	 *
	 * @return int
	 */
	function get_drafts_count ($user) {
		$search_parameters = [
			'user'        => $user,
			'draft'       => 1,
			'total_count' => true
		];
		return $this->search($search_parameters);
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
			User::instance()->id,
			$draft ? 0 : time(),
			$title,
			path($path ?: $title),
			$content,
			$draft,
			$sections,
			$this->prepare_tags($tags)
		);
		if ($id) {
			$this->cache_cleanups($id);
		}
		return $id;
	}
	/**
	 * Transform array of string tags into array of their ids
	 *
	 * @param string[] $tags
	 *
	 * @return int[]
	 */
	protected function prepare_tags ($tags) {
		return Tags::instance()->add($tags) ?: [];
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
			array_merge([0], array_column(Sections::instance()->get_all(), 'id')),
			$sections
		);
		$sections = array_values($sections);
		return $sections && count($sections) <= Config::instance()->module('Blogs')->max_sections;
	}
	/**
	 * @param int $id
	 */
	protected function cache_cleanups ($id) {
		$Cache = $this->cache;
		unset(
			$Cache->{"posts/$id"},
			$Cache->sections,
			$Cache->total_count
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
			$id,
			$old_data['user'],
			$old_data['draft'] == 1 && $old_data['date'] == 0 && !$draft ? time() : $old_data['date'],
			$title,
			path($path ?: $title),
			$content,
			$draft,
			$sections,
			$this->prepare_tags($tags)
		);
		$this->cache_cleanups($id);
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
			Event::instance()->fire(
				'Comments/deleted',
				[
					'module' => 'Blogs',
					'item'   => $id
				]
			);
			$this->cache_cleanups($id);
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
				$search_parameters = [
					'draft'       => 0,
					'total_count' => true
				];
				return $this->search($search_parameters);
			}
		);
	}
}
