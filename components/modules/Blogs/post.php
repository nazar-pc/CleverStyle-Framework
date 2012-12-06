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
$post				= $Blogs->get($post, true);
if (!$post) {
	define('ERROR_PAGE', 404);
	return;
}
$module				= path($L->{MODULE});
if ($post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
	code_header(303);
	header('Location: '.$Config->server['base_url'].'/'.$module.'/'.$post['path'].':'.$post['id']);
	return;
}
$Page->title($post['title']);
$tags				= $Blogs->get_tag($post['tags']);
$Page->Keywords		= keywords(
	$post['title'].' '.implode(' ', $tags)).'. '.$Page->Keywords;
$Page->Description	= description($post['short_content']);
$Page->link([
	'href'	=> $Config->server['base_url'].'/'.$module.'/'.$post['path'].':'.$post['id'],
	'rel'	=> 'canonical'
]);
$Index->content(
	h::{'section.cs-blogs-post article'}(
		h::header(
			h::h1(
				$post['title'].
				(
					$post['draft'] ? h::sup($L->draft) : ''
				).
				(
					$User->admin() &&
					$User->get_user_permission('admin/'.MODULE, 'index') &&
					$User->get_user_permission('admin/'.MODULE, 'edit_post') ? ' '.h::{'a.cs-button-compact'}(
						[
							h::icon('wrench'),
							[
								'href'			=> $module.'/edit_post/'.$post['id'],
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
				h::icon('suitcase').
				implode(
					', ',
					array_map(
						function ($section) use ($Blogs, $L, $module) {
							$section	= $Blogs->get_section($section);
							return h::a(
								$section['title'],
								[
									'href'	=> $module.'/'.path($L->section).'/'.$section['full_path']
								]
							);
						},
						$post['sections']
					)
				)
			) : '')
		).
		$post['content']."\n".
		h::footer(
			h::p(
				h::icon(
					'tag'
				).
				implode(
					', ',
					array_map(
						function ($tag) use ($L, $module) {
							return h::a(
								$tag,
								[
									'href'	=> $module.'/'.path($L->tag).'/'.$tag,
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
					$L->to_locale(date($L->_datetime_long, $post['date'])),
					[
						'datetime'		=> date('c', $post['date']),
						//'pubdate'//TODO wait while "pubdate" it will be standardized by W3C
					]
				).
				h::a(
					h::icon('person').$User->get_username($post['user']),
					[
						'href'			=> path($L->profile).'/'.$User->get('login', $post['user']),
						'rel'			=> 'author'
					]
				).
				(
					$Config->module(MODULE)->enable_comments ? h::icon('comment').$post['comments_count'] : ''
				)
			)
		)
	).
	(
		$Config->module(MODULE)->enable_comments ? h::{'section#comments.cs-blogs-comments'}(
			$L->comments.':'.
			(
				!$post['comments_count'] ? h::{'article.cs-blogs-no-comments'}(
					$L->no_comments_yet
				) : get_comments_tree($post['comments'], $post)
			)
		).
		h::p($L->add_comment.':').
		(
			$User->user() ? h::{'section.cs-blogs-comment-write'}(
				h::{'textarea.cs-blogs-comment-write-text.cs-wide-textarea.SEDITOR'}(
					'',
					[
						'data-post'		=> $post['id'],
						'data-parent'	=> 0,
						'data-id'		=> 0
					]
				).
				h::br().
				h::{'button.cs-blogs-comment-write-send'}(
					$L->send_comment
				).
				h::{'button.cs-blogs-comment-write-edit'}(
					$L->save,
					[
						'style'	=>	'display: none'
					]
				).
				h::{'button.cs-blogs-comment-write-cancel'}(
					$L->cancel,
					[
						'style'	=>	'display: none'
					]
				)
			) : h::p($L->register_for_comments_sending)
		) : ''
	)
);