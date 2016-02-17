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
	cs\Config,
	cs\ExitException,
	cs\Language\Prefix,
	cs\Page,
	cs\User;

$Config = Config::instance();
$User   = User::instance();
if (!$User->user()) {
	throw new ExitException(403);
}
$L    = new Prefix('blogs_');
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
$Posts       = Posts::instance();
$Sections    = Sections::instance();
$post        = isset($_POST['id']) ? $Posts->get($_POST['id']) : [
	'date'           => TIME,
	'user'           => $User->id,
	'comments_count' => 0
];
$module      = path($L->Blogs);
$module_data = $Config->module('Blogs');
$Page->json(
	h::{'section.cs-blogs-post article'}(
		h::header(
			h::h1(xap($_POST['title'])).
			((array)$_POST['sections'] != [0] ? h::p(
				h::icon('bookmark').
				implode(
					', ',
					array_map(
						function ($section) use ($Sections, $L, $module) {
							$section = $Sections->get($section);
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
				h::icon('user').$User->username($post['user']).
				(
				$module_data->enable_comments ? h::icon('comments').$post['comments_count'] : ''
				)
			)
		)
	).
	h::br(2)
);
