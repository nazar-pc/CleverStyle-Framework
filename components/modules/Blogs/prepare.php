<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h;
if (!API) {
	global $Core, $Index, $Config, $L, $Page;
	$Index->title_auto	= false;
	if (!$Config->core['cache_compress_js_css']) {
		$Page->css('components/modules/'.MODULE.'/includes/css/general.css');
		$Page->js([
			'components/modules/'.MODULE.'/includes/js/general.js',
			'components/modules/'.MODULE.'/includes/js/functions.js'
		]);
	} elseif (!(
		file_exists(PCACHE.'/module.'.MODULE.'.js') && file_exists(PCACHE.'/module.'.MODULE.'.css')
	)) {
		rebuild_pcache();
	}
	$rc					= &$Config->route;
	if (!isset($rc[0])) {
		$rc[0]	= 'latest_posts';
	}
	switch ($rc[0]) {
		case path($L->latest_posts):
			$rc[0]	= 'latest_posts';
		break;
		case path($L->section):
			$rc[0]	= 'section';
		break;
		case path($L->tag):
			$rc[0]	= 'tag';
		break;
		case path($L->new_post):
			$rc[0]	= 'new_post';
		break;
		case path($L->drafts):
			$rc[0]	= 'drafts';
		break;
		default:
			if (mb_strpos($rc[0], ':')) {
				array_unshift($rc, 'post');
			} else {
				define('ERROR_PAGE', 404);
				return;
			}
		break;
		case 'latest_posts':
		case 'section':
		case 'tag':
		case 'new_post':
		case 'edit_post':
		case 'drafts':
	}
	$Page->title($L->{MODULE});
	include_once MFOLDER.'/class.php';
	$Core->create('cs\\modules\\Blogs\\Blogs');
	function get_sections_select_post (&$disabled, $current = null, $structure = null, $level = 0) {
		$list	= [
			'in'	=> [],
			'value'	=> []
		];
		if ($structure === null) {
			global $Blogs, $L;
			$structure			= $Blogs->get_sections_structure();
			$list['in'][]		= $L->root_section;
			$list['value'][]	= 0;
		} else {
			if ($structure['id'] == $current) {
				return $list;
			}
			$list['in'][]		= str_repeat('&nbsp;', $level).$structure['title'];
			$list['value'][]	= $structure['id'];
		}
		if (!empty($structure['sections'])) {
			$disabled[]			= $structure['id'];
			foreach ($structure['sections'] as $section) {
				$tmp			= get_sections_select_post($disabled, $current, $section, $level+1);
				$list['in']		= array_merge($list['in'], $tmp['in']);
				$list['value']	= array_merge($list['value'], $tmp['value']);
			}
		}
		return $list;
	}
	function get_posts_list ($posts) {
		global $Blogs, $L, $User, $Config;
		$module		= path($L->{MODULE});
		$content	= [];
		if (empty($posts)) {
			return '';
		}
		foreach ($posts as $post) {
			$post		= $Blogs->get($post);
			$content[]	= h::header(
				h::{'h1 a'}(
					$post['title'],
					[
						'href'	=> $module.'/'.$post['path'].':'.$post['id']
					]
				).
				($post['sections'] != [0] ? h::p(
					h::icon('suitcase').
					implode(', ', array_map(
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
			$post['short_content']."\n".
			h::footer(
				h::hr().
				h::p(
					h::time(
						$L->to_locale(date($L->_datetime_long, $post['date'])),
						[
							'datetime'		=> date('c', $post['date'])
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
						$Config->module(MODULE)->enable_comments ? h::a(
							h::icon('comment').$post['comments_count'],
							[
								'href'			=> $module.'/'.$post['path'].':'.$post['id'].'#comments'
							]
						) : ''
					).
					h::a(
						h::icon('note').$L->read_more,
						[
							'href'			=> $module.'/'.$post['path'].':'.$post['id']
						]
					)
				)
			);
		}
		return h::article($content);
	}
}
function get_comments_tree ($comments, $post) {
	global	$User, $L;
	$module		= path($L->{MODULE});
	$content	= '';
	if (is_array($comments) && !empty($comments)) {
		foreach ($comments as $comment) {
			$content	.= h::{'article.cs-blogs-comment'}(
				h::a(
					h::{'img.cs-blogs-comment-avatar'}([
						'src'	=> $User->get('avatar', $comment['user']) ? h::url($User->get('avatar', $comment['user']), true) : 'includes/img/guest.gif',
						'alt'	=> $User->get_username($comment['user']),
						'title'	=> $User->get_username($comment['user'])
					]),
					[
						'href'			=> path($L->profile).'/'.$User->get('login', $comment['user']),
						'rel'			=> 'author'
					]
				).
				h::{'a.cs-blogs-comment-author'}(
					$User->get_username($comment['user']),
					[
						'href'			=> path($L->profile).'/'.$User->get('login', $comment['user']),
						'rel'			=> 'author'
					]
				).
				h::{'time.cs-blogs-comment-date'}(
					date('dmY', TIME) == date('dmY', $comment['date']) ? date($L->_time, $comment['date']) : $L->to_locale(date($L->_datetime, $comment['date'])),
					[
						'datetime'		=> date('c', $comment['date'])
					]
				).
				h::{'a.cs-blogs-comment-link'}(
					h::icon('link'),
					[
						'href'	=> $module.'/'.$post['path'].':'.$post['id'].'#comment_'.$comment['id']
					]
				).
				(
					$comment['parent'] ? h::{'a.cs-blogs-comment-parent'}(
						h::icon('arrowreturnthick-1-n'),
						[
							'href'	=> $module.'/'.$post['path'].':'.$post['id'].'#comment_'.$comment['parent']
						]
					) : ''
				).
				(
					$User->id == $comment['user'] ||
					(
						$User->admin() &&
						$User->get_user_permission('admin/'.MODULE, 'index') &&
						$User->get_user_permission('admin/'.MODULE, 'edit_comment')
					) ? h::{'icon.cs-blogs-comment-edit.cs-pointer'}('pencil') : ''
				).
				(
					!$comment['comments'] &&
					(
						$User->id == $comment['user'] ||
						(
							$User->admin() &&
							$User->get_user_permission('admin/'.MODULE, 'index') &&
							$User->get_user_permission('admin/'.MODULE, 'delete_comment')
						)
					) ? h::{'icon.cs-blogs-comment-delete.cs-pointer'}('trash') : ''
				).
				h::{'div.cs-blogs-comment-text'}(
					$comment['text']
				).
				(
					$comment['comments'] ? get_comments_tree($comment['comments'], $post) : ''
				),
				[
					'id'	=> 'comment_'.$comment['id']
				]
			);
		}
	}
	return $content;
}