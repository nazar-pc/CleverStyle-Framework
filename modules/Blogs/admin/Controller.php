<?php
/**
 * @package  Blogs
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\Blogs\admin;
use
	h,
	cs\Language,
	cs\Page;

class Controller {
	public static function general () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->general)
			->content(
				h::cs_blogs_admin_general()
			);
	}
	public static function browse_sections () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->browse_sections)
			->content(
				h::cs_blogs_admin_sections_list()
			);
	}
	public static function browse_posts () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->browse_posts)
			->content(
				h::cs_blogs_admin_posts_list()
			);
	}
}
