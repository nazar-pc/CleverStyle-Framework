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
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Page\Meta,
	cs\Page,
	cs\User,
	h;

class Controller {
	/**
	 * @param \cs\Request $Request
	 */
	static function latest_posts ($Request) {
		if (!Event::instance()->fire('Blogs/latest_posts')) {
			return;
		}
		$Config = Config::instance();
		$L      = new Prefix('blogs_');
		$Meta   = Meta::instance();
		$Page   = Page::instance();
		$Posts  = Posts::instance();
		/**
		 * Page title
		 */
		$Page->title($L->latest_posts);
		/**
		 * Now add link to Atom feed for latest posts
		 */
		$Page->atom('Blogs/atom.xml', $L->latest_posts);
		/**
		 * Set page of blog type (Open Graph protocol)
		 */
		$Meta->blog();
		/**
		 * Determine current page
		 */
		$page = static::get_page_and_set_title($Request, $Page, $L);
		/**
		 * Get posts for current page in JSON-LD structure format
		 */
		$posts_per_page = $Config->module('Blogs')->posts_per_page;
		$posts          = $Posts->get_latest_posts($page, $posts_per_page);
		/**
		 * Base url (without page number)
		 */
		$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->latest_posts);
		/**
		 * Render posts page
		 */
		Helpers::show_posts_list(
			$posts,
			$Posts->get_total_count(),
			$page,
			$base_url
		);
	}
	/**
	 * @param \cs\Request $Request
	 * @param Page        $Page
	 * @param Prefix      $L
	 *
	 * @return int
	 */
	protected static function get_page_and_set_title ($Request, $Page, $L) {
		$page = max(
			isset($Request->route_ids[0]) ? array_slice($Request->route_ids, -1)[0] : 1,
			1
		);
		/**
		 * If this is not first page - show that in page title
		 */
		if ($page > 1) {
			$Page->title($L->page_number($page));
		}
		return $page;
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function section ($Request) {
		if (!Event::instance()->fire('Blogs/section')) {
			return;
		}
		$Config   = Config::instance();
		$L        = new Prefix('blogs_');
		$Meta     = Meta::instance();
		$Page     = Page::instance();
		$Posts    = Posts::instance();
		$Sections = Sections::instance();
		/**
		 * At first - determine part of url and get sections list based on that path
		 */
		$sections = $Sections->get_by_path(
			array_slice($Request->route_path, 1)
		);
		if (!$sections) {
			throw new ExitException(400);
		}
		$sections = $Sections->get($sections);
		/**
		 * Now lets set page title using sections names from page path
		 * We will not remove `$section` variable after, since it will be direct parent of each shown post
		 */
		foreach ($sections as $section) {
			$Page->title($section['title']);
		}
		/**
		 * Now add link to Atom feed for posts from current section only
		 */
		/** @noinspection PhpUndefinedVariableInspection */
		$Page->atom(
			"Blogs/atom.xml/?section=$section[id]",
			implode($Config->core['title_delimiter'], [$L->latest_posts, $L->section, $section['title']])
		);
		/**
		 * Set page of blog type (Open Graph protocol)
		 */
		$Meta->blog();
		/**
		 * Determine current page
		 */
		$page = static::get_page_and_set_title($Request, $Page, $L);
		/**
		 * Get posts for current page in JSON-LD structure format
		 */
		$posts_per_page = $Config->module('Blogs')->posts_per_page;
		$posts          = $Posts->get_for_section($section['id'], $page, $posts_per_page);
		/**
		 * Base url (without page number)
		 */
		$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->section)."/$section[full_path]";
		/**
		 * Render posts page
		 */
		Helpers::show_posts_list(
			$posts,
			$section['posts'],
			$page,
			$base_url
		);
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	static function post ($Request, $Response) {
		if (!Event::instance()->fire('Blogs/post')) {
			return;
		}

		$Config   = Config::instance();
		$Page     = Page::instance();
		$User     = User::instance();
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
		$Posts   = Posts::instance();
		$rc      = $Request->route;
		$post_id = (int)mb_substr($rc[1], mb_strrpos($rc[1], ':') + 1);
		if (!$post_id) {
			throw new ExitException(404);
		}
		$post = $Posts->get_as_json_ld($post_id);
		if (
			!$post ||
			(
				$post['draft'] && $post['user'] != $User->id
			)
		) {
			throw new ExitException(404);
		}
		if ($post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
			$Response->redirect($post['url'], 303);
			return;
		}
		$Page->title($post['title']);
		$Page->Description = description($post['short_content']);
		$Page->canonical_url($post['url']);
		$Meta = Meta::instance();
		$Meta
			->article()
			->article('published_time', date('Y-m-d', $post['date'] ?: TIME))
			->article('section', $post['articleSection'] ? $post['articleSection'][0] : false)
			->article('tag', $post['tags']);
		array_map([$Meta, 'image'], $post['image']);
		$comments_enabled = $Config->module('Blogs')->enable_comments && $Comments;
		$is_admin         =
			$User->admin() &&
			$User->get_permission('admin/Blogs', 'index') &&
			$User->get_permission('admin/Blogs', 'edit_post');
		$Page->content(
			h::{'article[is=cs-blogs-post]'}(
				h::{'script[type=application/ld+json]'}(
					json_encode($post, JSON_UNESCAPED_UNICODE)
				),
				[
					'comments_enabled' => $comments_enabled,
					'can_edit'         => $is_admin || $User->id == $post['user'],
					'can_delete'       => $is_admin
				]
			).
			($comments_enabled ? $Comments->block($post['id']) : '')
		);
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @throws ExitException
	 */
	static function tag ($Request) {
		if (!Event::instance()->fire('Blogs/tag')) {
			return;
		}
		$Config = Config::instance();
		$L      = new Prefix('blogs_');
		$Meta   = Meta::instance();
		$Page   = Page::instance();
		$Posts  = Posts::instance();
		$Tags   = Tags::instance();
		/**
		 * If no tag specified
		 */
		if (!isset($Request->route[1])) {
			throw new ExitException(404);
		}
		/**
		 * Find tag
		 */
		$tag = $Tags->get_by_text($Request->route[1]);
		if (!$tag) {
			throw new ExitException(404);
		}
		$tag = $Tags->get($tag);
		/**
		 * Add tag to page title
		 */
		$Page->title($tag['text']);
		/**
		 * Now add link to Atom feed for posts with current tag only
		 */
		$Page->atom(
			"Blogs/atom.xml/?tag=$tag[id]",
			implode($Config->core['title_delimiter'], [$L->latest_posts, $L->tag, $tag['text']])
		);
		/**
		 * Set page of blog type (Open Graph protocol)
		 */
		$Meta->blog();
		/**
		 * Determine current page
		 */
		$page = max($Request->route(2) ?: 1, 1);
		/**
		 * If this is not first page - show that in page title
		 */
		if ($page > 1) {
			$Page->title($L->blogs_nav_page($page));
		}
		/**
		 * Get posts for current page in JSON-LD structure format
		 */
		$posts_per_page = $Config->module('Blogs')->posts_per_page;
		$posts          = $Posts->get_for_tag($tag['id'], $L->clang, $page, $posts_per_page);
		$posts_count    = $Posts->get_for_tag_count($tag['id'], $L->clang);
		/**
		 * Base url (without page number)
		 */
		$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->tag).'/'.$Request->route[1];
		/**
		 * Render posts page
		 */
		Helpers::show_posts_list(
			$posts,
			$posts_count,
			$page,
			$base_url
		);
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	static function new_post ($Request, $Response) {
		if (!Event::instance()->fire('Blogs/new_post')) {
			return;
		}

		$Config      = Config::instance();
		$module_data = $Config->module('Blogs');
		$L           = new Prefix('blogs_');
		$Page        = Page::instance();
		$User        = User::instance();
		$Page->title($L->new_post);
		if (!$User->admin() && $module_data->new_posts_only_from_admins) {
			throw new ExitException(403);
		}
		if (!$User->user()) {
			$Page->warning($L->for_registered_users_only);
			return;
		}
		$module = path($L->Blogs);
		$data   = static::check_request_data($Request, $Page, $L);
		if ($data) {
			$Posts = Posts::instance();
			$id    = $Posts->add($data['title'], null, $data['content'], $data['sections'], _trim(explode(',', $data['tags'])), $data['mode'] == 'draft');
			if ($id) {
				$Response->redirect($Config->base_url()."/$module/".$Posts->get($id)['path'].":$id");
				return;
			} else {
				$Page->warning($L->post_adding_error);
			}
		}
		$disabled     = [];
		$max_sections = $module_data->max_sections;
		$content      = uniqid('post_content', true);
		$Page->replace($content, $Request->data('content') ?: '');
		$sections = get_sections_select_post($disabled);
		if (count($sections['in']) > 1) {
			$sections = [
				$L->post_section,
				h::{'select.cs-blogs-new-post-sections[is=cs-select][size=7][required]'}(
					$sections,
					[
						'name'     => 'sections[]',
						'disabled' => $disabled,
						'selected' => $Request->data('sections') ?: (isset($Request->route[1]) ? $Request->route[1] : []),
						$max_sections < 1 ? 'multiple' : false
					]
				).
				($max_sections > 1 ? h::br().$L->select_sections_num($max_sections) : '')
			];
		} else {
			$sections = false;
		}
		$Page->content(
			h::form(
				h::{'h2.cs-text-center'}(
					$L->new_post
				).
				h::{'div.cs-blogs-post-preview-content'}().
				h::{'table.cs-table.cs-blogs-post-form[right-left] tr| td'}(
					[
						$L->post_title,
						h::{'h1.cs-blogs-new-post-title[contenteditable=true]'}(
							$Request->data('title') ?: '<br>'
						)
					],
					$sections,
					[
						$L->post_content,
						(functionality('inline_editor')
							? h::{'cs-editor-inline div.cs-blogs-new-post-content'}($content)
							: h::{'cs-editor textarea.cs-blogs-new-post-content[is=cs-textarea][autosize][name=content][required]'}(
								$Request->data('content') ?: ''
							)
						).
						h::br().
						$L->post_use_pagebreak
					],
					[
						$L->post_tags,
						h::{'input.cs-blogs-new-post-tags[is=cs-input-text][name=tags][required]'}(
							[
								'value'       => $Request->data('tags') ?: false,
								'placeholder' => 'CleverStyle, CMS, Open Source'
							]
						)
					]
				).
				(
				!$sections ? h::{'input[type=hidden][name=sections[]][value=0]'}() : ''
				).
				h::{'button.cs-blogs-post-preview[is=cs-button]'}(
					$L->preview
				).
				h::{'button[is=cs-button][type=submit][name=mode][value=publish]'}(
					$L->publish
				).
				h::{'button[is=cs-button][type=submit][name=mode][value=draft]'}(
					$L->to_drafts
				).
				h::{'button[is=cs-button]'}(
					$L->cancel,
					[
						'type'    => 'button',
						'onclick' => 'history.go(-1);'
					]
				)
			)
		);
	}
	/**
	 * @param \cs\Request $Request
	 * @param Page        $Page
	 * @param Prefix      $L
	 *
	 * @return array|false
	 */
	protected static function check_request_data ($Request, $Page, $L) {
		$data = $Request->data('title', 'sections', 'content', 'tags', 'mode');
		if ($data && in_array($data['mode'], ['draft', 'publish'])) {
			if (empty($data['title'])) {
				$Page->warning($L->post_title_empty);
				return false;
			}
			if (empty($data['sections']) && $data['sections'] !== '0') {
				$Page->warning($L->no_post_sections_specified);
				return false;
			}
			if (empty($data['content'])) {
				$Page->warning($L->post_content_empty);
				return false;
			}
			if (empty($data['tags'])) {
				$Page->warning($L->no_post_tags_specified);
				return false;
			}
		}
		return $data;
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	static function edit_post ($Request, $Response) {
		if (!Event::instance()->fire('Blogs/edit_post')) {
			return;
		}

		$Posts       = Posts::instance();
		$Config      = Config::instance();
		$module_data = $Config->module('Blogs');
		$L           = new Prefix('blogs_');
		$Page        = Page::instance();
		$User        = User::instance();
		if ($module_data->new_posts_only_from_admins && !$User->admin()) {
			throw new ExitException(403);
		}
		if (
			!isset($Request->route[1]) ||
			!($post = $Posts->get($Request->route[1]))
		) {
			throw new ExitException(404);
		}
		if (
			$post['user'] != $User->id &&
			!(
				$User->admin() &&
				$User->get_permission('admin/Blogs', 'index') &&
				$User->get_permission('admin/Blogs', 'edit_post')
			)
		) {
			throw new ExitException(403);
		}
		$Page->title(
			$L->editing_of_post($post['title'])
		);
		$module = path($L->Blogs);
		$data   = static::check_request_data($Request, $Page, $L);
		if ($data) {
			$result = $Posts->set(
				$post['id'],
				$data['title'],
				null,
				$data['content'],
				$data['sections'],
				_trim(explode(',', $data['tags'])),
				$data['mode'] == 'draft'
			);
			if ($result) {
				$Response->redirect($Config->base_url()."/$module/$post[path]:$post[id]");
				return;
			} else {
				$Page->warning($L->post_saving_error);
			}
		} elseif ($Request->data('mode') == 'delete') {
			if ($Posts->del($post['id'])) {
				$Response->redirect($Config->base_url()."/$module");
				return;
			} else {
				$Page->warning($L->post_deleting_error);
			}
		}
		$disabled     = [];
		$max_sections = $module_data->max_sections;
		$content      = uniqid('post_content', true);
		$Page->replace($content, $Request->data('content') ?: $post['content']);
		$sections = get_sections_select_post($disabled);
		if (count($sections['in']) > 1) {
			$sections = [
				$L->post_section,
				h::{'select.cs-blogs-new-post-sections[is=cs-select][size=7][required]'}(
					get_sections_select_post($disabled),
					[
						'name'     => 'sections[]',
						'disabled' => $disabled,
						'selected' => $Request->data('sections') ?: $post['sections'],
						$max_sections < 1 ? 'multiple' : false
					]
				).
				($max_sections > 1 ? h::br().$L->select_sections_num($max_sections) : '')
			];
		} else {
			$sections = false;
		}
		$Page->content(
			h::form(
				h::{'h2.cs-text-center'}(
					$L->editing_of_post($post['title'])
				).
				h::{'div.cs-blogs-post-preview-content'}().
				h::{'table.cs-table.cs-blogs-post-form[right-left] tr| td'}(
					[
						$L->post_title,
						h::{'h1.cs-blogs-new-post-title[contenteditable=true]'}(
							$Request->data('title') ?: $post['title']
						)
					],
					$sections,
					[
						$L->post_content,
						(
						functionality('inline_editor') ? h::{'cs-editor-inline div.cs-blogs-new-post-content'}(
							$content
						) : h::{'cs-editor textarea.cs-blogs-new-post-content[is=cs-textarea][autosize][name=content][required]'}(
							$Request->data('content') ?: $post['content']
						)
						).
						h::br().
						$L->post_use_pagebreak
					],
					[
						$L->post_tags,
						h::{'input.cs-blogs-new-post-tags[is=cs-input-text][name=tags][required]'}(
							[
								'value'       => $Request->data('tags') ?: implode(', ', $post['tags']),
								'placeholder' => 'CleverStyle, CMS, Open Source'
							]
						)
					]
				).
				(!$sections ? h::{'input[type=hidden][name=sections[]][value=0]'}() : '').
				h::{'button.cs-blogs-post-preview[is=cs-button]'}(
					$L->preview,
					[
						'data-id' => $post['id']
					]
				).
				h::{'button[is=cs-button][type=submit][name=mode][value=save]'}(
					$L->publish
				).
				h::{'button[is=cs-button][type=submit][name=mode][value=draft]'}(
					$L->to_drafts
				).
				h::{'button[is=cs-button][type=submit][name=mode][value=delete]'}(
					$L->delete
				).
				h::{'button[is=cs-button]'}(
					$L->cancel,
					[
						'type'    => 'button',
						'onclick' => 'history.go(-1);'
					]
				)
			)
		);
	}
	/**
	 * @param \cs\Request $Request
	 */
	static function drafts ($Request) {
		if (!Event::instance()->fire('Blogs/drafts')) {
			return;
		}
		$Config = Config::instance();
		$L      = new Prefix('blogs_');
		$Page   = Page::instance();
		$Posts  = Posts::instance();
		$User   = User::instance();
		$Page->title($L->drafts);
		/**
		 * Determine current page
		 */
		$page = static::get_page_and_set_title($Request, $Page, $L);
		/**
		 * Get posts for current page in JSON-LD structure format
		 */
		$posts_per_page = $Config->module('Blogs')->posts_per_page;
		$posts          = $Posts->get_drafts($User->id, $page, $posts_per_page);
		$posts_count    = $Posts->get_drafts_count($User->id);
		/**
		 * Base url (without page number)
		 */
		$base_url = $Config->base_url().'/'.path($L->Blogs).'/'.path($L->drafts);
		/**
		 * Render posts page
		 */
		Helpers::show_posts_list(
			$posts,
			$posts_count,
			$page,
			$base_url
		);
	}
	/**
	 * @param \cs\Request  $Request
	 * @param \cs\Response $Response
	 *
	 * @throws ExitException
	 */
	static function atom_xml ($Request, $Response) {
		$Config   = Config::instance();
		$L        = new Prefix('blogs_');
		$Page     = Page::instance();
		$User     = User::instance();
		$title    = [
			get_core_ml_text('name'),
			$L->Blogs
		];
		$Posts    = Posts::instance();
		$Sections = Sections::instance();
		$Tags     = Tags::instance();
		$number   = $Config->module('Blogs')->posts_per_page;
		$section  = $Request->query('section');
		$tag      = $Request->query('tag');
		if ($section) {
			$section = $Sections->get($section);
			if (!$section) {
				throw new ExitException(404);
			}
			$title[] = $L->section;
			$title[] = $section['title'];
			$posts   = $Posts->get_for_section($section['id'], 1, $number);
		} elseif ($tag) {
			$tag = $Tags->get($tag);
			if (!$tag) {
				throw new ExitException(404);
			}
			$title[] = $L->tag;
			$title[] = $tag['text'];
			$posts   = $Posts->get_for_tag($tag['id'], $L->clang, 1, $number);
		} else {
			$posts = $Posts->get_latest_posts(1, $number);
		}
		$title[]  = $L->latest_posts;
		$title    = implode($Config->core['title_delimiter'], $title);
		$base_url = $Config->base_url();
		$Response->header('content-type', 'application/atom+xml');
		$Page->interface = false;

		$url = $Config->core_url().$Request->uri;
		$Page->content(
			"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n".
			h::feed(
				h::title($title).
				h::id($url).
				str_replace(
					'>',
					'/>',
					h::link(
						[
							'href' => $url,
							'rel'  => 'self'
						]
					)
				).
				h::updated(date('c')).
				'<icon>'.static::get_favicon_path($Config->core['theme'])."</icon>\n".
				h::entry(
					array_map(
						function ($post) use ($Posts, $Sections, $User, $base_url) {
							$post = $Posts->get($post);
							return
								h::title($post['title']).
								h::id("$base_url/Blogs/:$post[id]").
								h::updated(date('c', $post['date'])).
								h::published(date('c', $post['date'])).
								str_replace(
									'>',
									'/>',
									h::link(
										[
											'href' => "$base_url/Blogs/:$post[id]"
										]
									)
								).
								h::{'author name'}($User->username($post['user'])).
								h::category(
									$post['sections'] == ['0'] ? false : array_map(
										function ($category) {
											return [
												'term'  => $category['title'],
												'label' => $category['title']
											];
										},
										$Sections->get($post['sections'])
									)
								).
								h::summary(
									htmlentities($post['short_content']),
									[
										'type' => 'html'
									]
								).
								h::content(
									htmlentities($post['content']),
									[
										'type' => 'html'
									]
								);
						},
						$posts ?: []
					)
				),
				[
					'xmlns'    => 'http://www.w3.org/2005/Atom',
					'xml:lang' => $L->clang,
					'xml:base' => "$base_url/"
				]
			)
		);
	}
	/**
	 * @param string $theme
	 *
	 * @return string
	 */
	protected static function get_favicon_path ($theme) {
		$theme_favicon = "$theme/img/favicon";
		if (file_exists(THEMES."/$theme_favicon.png")) {
			return "$theme_favicon.png";
		} elseif (file_exists(THEMES."/$theme_favicon.ico")) {
			return "$theme_favicon.ico";
		}
		return 'favicon.ico';
	}
}
