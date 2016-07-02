<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Config,
	cs\Language\Prefix,
	cs\Page,
	h;

class Helpers {
	/**
	 * Return HTML with posts list
	 *
	 * @param int[]  $posts
	 * @param int    $posts_count
	 * @param int    $page
	 * @param string $base_url
	 *
	 * @return string
	 */
	static function show_posts_list ($posts, $posts_count, $page, $base_url) {
		$module_data = Config::instance()->module('Blogs');
		$L           = new Prefix('blogs_');
		$Page        = Page::instance();
		$Page->content(
			h::cs_blogs_head_actions()
		);
		if (!$posts) {
			$Page->content(
				h::{'p.cs-text-center'}($L->no_posts_yet)
			);
			return;
		}
		$Page->content(
			h::{'section[is=cs-blogs-posts] script[type=application/ld+json]'}(
				json_encode(
					Posts::instance()->get_as_json_ld($posts),
					JSON_UNESCAPED_UNICODE
				)
			).
			h::{'.cs-block-margin.cs-text-center.cs-margin nav[is=cs-nav-pagination]'}(
				pages(
					$page,
					ceil($posts_count / $module_data->posts_per_page),
					function ($page) use ($base_url) {
						return $base_url.($page > 1 ? "/$page" : '');
					},
					true
				)
			)
		);
	}
}
