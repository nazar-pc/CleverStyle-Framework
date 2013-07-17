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
if (!API) {
	$Config				= Config::instance();
	$Page				= Page::instance();
	$L					= Language::instance();
	Index::instance()->title_auto	= false;
	if (!$Config->core['cache_compress_js_css']) {
		$Page->css('components/modules/Blogs/includes/css/general.css');
		$Page->js([
			'components/modules/Blogs/includes/js/general.js'
		]);
	} elseif (!(
		file_exists(PCACHE.'/module.Blogs.js') && file_exists(PCACHE.'/module.Blogs.css')
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
				define('ERROR_CODE', 404);
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
	$Page->title($L->Blogs);
	function get_sections_select_post (&$disabled, $current = null, $structure = null, $level = 0) {
		$list	= [
			'in'	=> [],
			'value'	=> []
		];
		if ($structure === null) {
			$structure			= Blogs::instance()->get_sections_structure();
			$list['in'][]		= Language::instance()->root_section;
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
		$Comments	= null;
		Trigger::instance()->run(
			'Comments/instance',
			[
			'data'	=> &$Comments
			]
		);
		/**
		 * @var \cs\modules\Comments\Comments $Comments
		 */
		$Blogs		= Blogs::instance();
		$L			= Language::instance();
		$User		= User::instance();
		$module		= path($L->Blogs);
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
						$L->to_locale(date($L->_datetime_long, $post['date'] ?: TIME)),
						[
							'datetime'		=> date('c', $post['date'] ?: TIME)
						]
					).
					h::a(
						h::icon('person').$User->username($post['user']),
						[
							'href'			=> path($L->profile).'/'.$User->get('login', $post['user']),
							'rel'			=> 'author'
						]
					).
					(
						Config::instance()->module('Blogs')->enable_comments && $Comments ? h::a(
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