<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
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
if (!api_path()) {
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
	if (!function_exists(__NAMESPACE__.'\\get_posts_list')) {
		function get_posts_list ($posts) {
			$Comments	= null;
			Trigger::instance()->run(
				'Comments/instance',
				[
					'Comments'	=> &$Comments
				]
			);
			/**
			 * @var \cs\modules\Comments\Comments $Comments
			 */
			$Blogs		= Blogs::instance();
			$L			= Language::instance();
			$Page		= Page::instance();
			$User		= User::instance();
			$module		= path($L->Blogs);
			$content	= [];
			if (empty($posts)) {
				return '';
			}
			foreach ($posts as $post) {
				$post			= $Blogs->get($post);
				$short_content	= uniqid('post_content', true);
				$Page->replace($short_content, $post['short_content']);
				$content[]		= h::header(
					h::{'h1 a'}(
						$post['title'],
						[
							'href'	=> "$module/$post[path]:$post[id]"
						]
					).
					($post['sections'] != [0] ? h::p(
						h::icon('bookmark').
						implode(', ', array_map(
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
				"$short_content\n".
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
							h::icon('user').$User->username($post['user']),
							[
								'href'			=> path($L->profile).'/'.$User->get('login', $post['user']),
								'rel'			=> 'author'
							]
						).
						(
							Config::instance()->module('Blogs')->enable_comments && $Comments ? h::a(
								h::icon('comments').$post['comments_count'],
								[
									'href'			=> "$module/$post[path]:$post[id]#comments"
								]
							) : ''
						).
						h::a(
							h::icon('double-angle-right').$L->read_more,
							[
								'href'			=> "$module/$post[path]:$post[id]"
							]
						)
					)
				);
			}
			return h::article($content);
		}
	}
	function head_actions () {
		$User	= User::instance();
		if ($User->user()) {
			$Index					= Index::instance();
			$L						= Language::instance();
			$module					= path($L->Blogs);
			$module_data	= Config::instance()->module('Blogs');
			/**
			 * If administrator
			 */
			if ($User->admin() && $User->get_permission('admin/Blogs', 'index')) {
				$Index->content(
					h::{'a.uk-button'}(
						h::icon('gears'),
						[
							'href'			=> 'admin/Blogs',
							'data-title'	=> $L->administration
						]
					)
				);
			}
			$Index->content(
				$User->admin() || !$module_data->new_posts_only_from_admins ? h::{'a.uk-button'}(
					h::icon('pencil').$L->new_post,
					[
						'href'			=> "$module/new_post",
						'data-title'	=> $L->new_post
					]
				).
				h::{'a.uk-button'}(
					h::icon('archive').$L->drafts,
					[
						'href'			=> "$module/".path($L->drafts),
						'data-title'	=> $L->drafts
					]
				).
				h::br() : ''
			);
		}
	}
}
