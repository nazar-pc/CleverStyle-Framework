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

$post = Posts::instance()->get(Request::instance()->route[1]);
$L    = new Prefix('blogs_');
$Page = Page::instance();
$Page->title($L->deletion_of_post($post['title']));
$Page->content(
	h::{'form[is=cs-form][action=admin/Blogs/browse_posts]'}(
		h::{'h2.cs-text-center'}(
			$L->sure_to_delete_post($post['title'])
		).
		h::p(
			h::{'button[is=cs-button][type=submit][name=mode][value=delete_post]'}($L->yes).
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
				'value' => $post['id']
			]
		)
	)
);
