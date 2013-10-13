<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page,
			cs\Trigger,
			cs\User;
$Config				= Config::instance();
$L					= Language::instance();
$Page				= Page::instance();
$User				= User::instance();
$Comments			= null;
Trigger::instance()->run(
	'Comments/instance',
	[
		'Comments'	=> &$Comments
	]
);
/**
 * @var \cs\modules\Comments\Comments $Comments
 */
$Blogs				= Blogs::instance();
$rc					= $Config->route;
$post				= (int)mb_substr($rc[1], mb_strrpos($rc[1], ':')+1);
if (!$post) {
	error_code(404);
	return;
}
$post				= $Blogs->get($post, true);
if (!$post) {
	error_code(404);
	return;
}
$module				= path($L->Blogs);
if ($post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
	code_header(303);
	header("Location: {$Config->base_url()}/$module/$post[path]:$post[id]");
	return;
}
$Page->title($post['title']);
$tags				= $Blogs->get_tag($post['tags']);
$Page->Keywords		= keywords($post['title'].' '.implode(' ', $tags));
$Page->Description	= description($post['short_content']);
$Page->canonical_url(
	"{$Config->base_url()}/$module/$post[path]:$post[id]"
)->og(
	'type',
	'article'
)->og(
	'published_time',
	date('Y-m-d', $post['date'] ?: TIME),
	'article:'
)->og(
	'author',
	$Config->base_url().'/'.path($L->profile).'/'.$User->get('login', $post['user']),
	'article:'
)->og(
	'section',
	$post['sections'] == [0] ? false : $Blogs->get_section($post['sections'][0])['title'],
	'article:'
)->og(
	'tag',
	$tags,
	'article:'
);
Index::instance()->content(
	h::{'section.cs-blogs-post article'}(
		h::header(
			(
				$User->admin() &&
				$User->get_permission('admin/Blogs', 'index') &&
				$User->get_permission('admin/Blogs', 'edit_post') ? ' '.h::{'a.cs-button'}(
					[
						h::icon('edit'),
						[
							'href'			=> "$module/edit_post/$post[id]",
							'data-title'	=> $L->edit
						]
					],
					[
						h::icon('trash'),
						[
							'href'			=> "admin/Blogs/delete_post/$post[id]",
							'data-title'	=> $L->delete
						]
					]
				) : (
					$User->id == $post['user'] ? ' '.h::{'a.cs-button-compact'}(
						h::icon('edit'),
						[
							'href'			=> "$module/edit_post/$post[id]",
							'data-title'	=> $L->edit
						]
					) : ''
				)
			).
			h::h1(
				$post['title'].
				(
					$post['draft'] ? h::sup($L->draft) : ''
				)
			).
			($post['sections'] != [0] ? h::p(
				h::icon('bookmark').
				implode(
					', ',
					array_map(
						function ($section) use ($Blogs, $L, $module) {
							$section	= $Blogs->get_section($section);
							return h::a(
								$section['title'],
								[
									'href'	=> "$module/".path($L->section)."/$section[full_path]"
								]
							);
						},
						$post['sections']
					)
				)
			) : '')
		).
		"$post[content]\n".
		h::footer(
			h::p(
				h::icon('tags').
				implode(
					', ',
					array_map(
						function ($tag) use ($L, $module) {
							return h::a(
								$tag,
								[
									'href'	=> "$module/".path($L->tag)."/$tag",
									'rel'	=> 'tag'
								]
							);
						},
						$tags
					)
				)
			).
			h::hr().
			h::p(
				h::time(
					$L->to_locale(date($L->_datetime_long, $post['date'] ?: TIME)),
					[
						'datetime'		=> date('c', $post['date'] ?: TIME)
					]
				).
				h::a(
					h::icon('user').$User->username($post['user']),
					[
						'href'			=> path($L->profile).'/'.$User->get('login', $post['user']),
						'rel'			=> 'author'
					]
				).
				(
					$Config->module('Blogs')->enable_comments && $Comments ? h::icon('comments').$post['comments_count'] : ''
				)
			)
		)
	).
	(
		$Config->module('Blogs')->enable_comments && $Comments ? $Comments->block($post['id']) : ''
	)
);