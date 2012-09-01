<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			\h;
global $Index, $Config, $Blogs, $Page, $L, $User;
$rc					= $Config->routing['current'];
$post				= (int)mb_substr($rc[1], mb_strrpos($rc[1], ':')+1);
if (!$post) {
	define('ERROR_PAGE', 404);
	return;
}
$post				= $Blogs->get($post);
if (!$post || $post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
	define('ERROR_PAGE', 404);
	return;
}
$module				= path($L->{MODULE});
$Page->title($post['title']);
$Page->Keywords		= keywords($post['title']).'. '.$Page->Keywords;
$Page->Description	= description($post['short_content']);
$Index->content(
	h::{'section.cs-blogs-post article'}(
		h::header(
			h::h1(
				$post['title'].
				(
					$User->is('admin') &&
					$User->get_user_permission('admin/'.MODULE, 'index') &&
					$User->get_user_permission('admin/'.MODULE, 'edit_post') ? ' '.h::{'a.cs-button-compact'}(
						[
							h::icon('wrench'),
							[
								'href'			=> 'admin/'.MODULE.'/edit_post/'.$post['id'],
								'data-title'	=> $L->edit
							]
						],
						[
							h::icon('trash'),
							[
								'href'			=> 'admin/'.MODULE.'/delete_post/'.$post['id'],
								'data-title'	=> $L->delete
							]
						]
					) : (
						$User->id == $post['user'] ? ' '.h::{'a.cs-button-compact'}(
							h::icon('wrench'),
							[
								'href'			=> $module.'/edit_post/'.$post['id'],
								'data-title'	=> $L->edit
							]
						) : ''
					)
				)
			).
			($post['sections'] != [0] ? h::p(
				$L->sections.':'.
				h::a(
					array_map(
						function ($section) use ($Blogs, $L, $module) {
							$section	= $Blogs->get_section($section);
							return [
								$section['title'],
								[
									'href'	=> $module.'/'.path($L->section).'/'.$section['full_path']
								]
							];
						},
						$post['sections']
					)
				)
			) : '')
		).
		$post['content']."\n".
		h::footer(
			h::p(
				$L->tags.':'.
				h::a(
					array_map(
						function ($tag) use ($L, $module) {
							return [
								$tag,
								[
									'href'	=> $module.'/'.path($L->tag).'/'.$tag,
									'rel'	=> 'tag'
								]
							];
						},
						$Blogs->get_tag($post['tags'])
					)
				)
			).
			h::hr().
			h::p(
				h::time(
					$L->to_locale(date($L->_datetime_long, $post['date'])),
					[
						'datetime'		=> date('c', $post['date']),
						//'pubdate'//TODO wait while "pubdate" it will be standartized by W3C
					]
				).
				' | '.
				h::a(
					$User->get_username($post['user']),
					[
						'href'			=> path($L->profile).'/'.$User->get('login', $post['user']),
						'rel'			=> 'author',
						'data-title'	=> $L->author
					]
				).
				' | '.
				h::icon('comment').$post['comments_count']
			)
		)
	).
	h::{'section#comments.cs-blogs-comments'}(
		$L->comments.':'.
		H::br(2).
		(
			!$post['comments_count'] ? h::article(
				$L->no_comments_yet
			) : get_comments_tree($post['comments'], $post, $module)
		)
	)
);