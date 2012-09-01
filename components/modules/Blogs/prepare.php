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
global $Core, $Index, $Config, $L, $Page;
$Index->title_auto	= false;
$Page->css('components/modules/'.MODULE.'/includes/css/style.css');
$rc					= &$Config->__get('routing')['current'];
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
function get_posts_list ($posts, $module) {
	global $Blogs, $L, $User;
	$content	= [];
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
		$post['short_content']."\n".
		h::footer(
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
				h::a(
					h::icon('comment').$post['comments_count'],
					[
						'href'			=> $module.'/'.$post['path'].':'.$post['id'].'#comments',
						'data-title'	=> $L->comments
					]
				).
				' | '.
				h::a(
					$L->read_more,
					[
						'href'			=> $module.'/'.$post['path'].':'.$post['id']
					]
				)
			)
		);
	}
	return h::article($content);
}
function get_comments_tree ($comments, $post, $module) {
	global	$User, $L;
	$content	= '';
	foreach ($comments as $comment) {
		$content	.= h::{'article.cs-blogs-comment'}(
			h::{'img.cs-blogs-comment-avatar'}([
				'src'	=> $User->avatar ? h::url($User->avatar, true) : 'includes/img/guest.gif',
				'alt'	=> $User->get_username($comment['user']),
				'title'	=> $User->get_username($comment['user'])
			]).
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
					'datetime'		=> date('c', $comment['date']),
					//'pubdate'//TODO wait while "pubdate" it will be standartized by W3C
				]
			).
			h::{'a.cs-blogs-comment-hash'}(
				'#',
				[
					'href'	=> $module.'/'.$post['path'].':'.$post['id'].'#comment_'.$comment['id']
				]
			).
			h::{'div.cs-blogs-comment-text'}(
				$comment['text']
			).
			(
				$comment['comments_count'] ? get_comments_tree($comment['comments'], $post, $module) : ''
			),
			[
				'id'	=> '#comment_'.$comment['id']
			]
		);
	}
	return $content;
}