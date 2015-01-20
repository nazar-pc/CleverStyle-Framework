<?php
/**
 * @package        Blogs
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Language,
	cs\Page,
	cs\User;
$Config = Config::instance();
$User   = User::instance();
if (!$User->user()) {
	error_code(403);
	return;
}
$L    = Language::instance();
$Page = Page::instance();
if (empty($_POST['title'])) {
	$Page->warning($L->post_title_empty);
	$Page->json($Page->Top);
	return;
}
if (empty($_POST['sections']) && $_POST['sections'] !== '0') {
	$Page->warning($L->no_post_sections_specified);
	$Page->json($Page->Top);
	return;
}
if (empty($_POST['content'])) {
	$Page->warning($L->post_content_empty);
	$Page->json($Page->Top);
	return;
}
if (empty($_POST['tags'])) {
	$Page->warning($L->no_post_tags_specified);
	$Page->json($Page->Top);
	return;
}
$Blogs       = Blogs::instance();
$post        = isset($_POST['id']) ? $Blogs->get($_POST['id']) : [
	'date'           => TIME,
	'user'           => $User->id,
	'comments_count' => 0
];
$module      = path($L->Blogs);
$module_data = $Config->module('Blogs');
$Page->json(
	h::{'section.cs-blogs-post[level=0] article[level=0]'}(
		h::header(
			h::h1(xap($_POST['title'])).
			((array)$_POST['sections'] != [0] ? h::p(
				h::icon('bookmark').
				implode(
					', ',
					array_map(
						function ($section) use ($Blogs, $L, $module) {
							$section = $Blogs->get_section($section);
							return h::a(
								$section['title'],
								[
									'href' => "$module/".path($L->section)."/$section[full_path]"
								]
							);
						},
						(array)$_POST['sections']
					)
				)
			) : '')
		).
		xap($_POST['content'], true, $module_data->allow_iframes_without_content)."\n".
		h::footer(
			h::p(
				h::icon('tags').
				implode(
					', ',
					array_map(
						function ($tag) use ($L, $module) {
							$tag = xap($tag);
							return h::a(
								$tag,
								[
									'href' => "$module/".path($L->tag)."/$tag",
									'rel'  => 'tag'
								]
							);
						},
						_trim(explode(',', $_POST['tags']))
					)
				)
			).
			h::hr().
			h::p(
				h::time(
					$L->to_locale(date($L->_datetime_long, $post['date'])),
					[
						'datetime' => date('c', $post['date'])
					]
				).
				h::a(
					h::icon('user').$User->username($post['user']),
					[
						'href' => path($L->profile).'/'.$User->get('login', $post['user']),
						'rel'  => 'author'
					]
				).
				(
				$module_data->enable_comments ? h::icon('comments').$post['comments_count'] : ''
				)
			)
		)
	).
	h::br(2)
);
