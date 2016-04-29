<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs\admin;
use
	h,
	cs\Language,
	cs\Page;

class Controller {
	static function general () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->general)
			->content(
				h::cs_blogs_admin_general()
			);
	}
	static function browse_sections () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->browse_sections)
			->content(
				h::cs_blogs_admin_sections_list()
			);
	}
	static function browse_posts () {
		$L = Language::prefix('blogs_');
		Page::instance()
			->title($L->browse_posts)
			->content(
				h::cs_blogs_admin_posts_list()
			);
	}
}
