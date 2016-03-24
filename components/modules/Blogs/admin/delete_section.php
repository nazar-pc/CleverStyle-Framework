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
	h,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$section = Sections::instance()->get(Request::instance()->route[1]);
$L       = new Prefix('blogs_');
$Page    = Page::instance();
$Page->title($L->deletion_of_posts_section($section['title']));
$Page->content(
	h::{'form[is=cs-form][action=admin/Blogs/browse_sections]'}(
		h::{'h2.cs-text-center'}(
			$L->sure_to_delete_posts_section($section['title'])
		).
		h::p(
			h::{'button[is=cs-button][type=submit][name=mode][value=delete_section]'}($L->yes).
			h::{'button[is=cs-button]'}(
				$L->cancel,
				[
					'type'    => 'button',
					'onclick' => 'history.go(-1);'
				]
			)
		).
		h::{'input[type=hidden][name=id]'}(
			[
				'value' => $section['id']
			]
		)
	)
);
