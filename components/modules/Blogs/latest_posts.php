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
global $Index, $Blogs, $Page, $L, $User, $db, $Config;
$Page->title($L->latest_posts);
$module		= path($L->{MODULE});
if ($User->is('user')) {
	$Index->content(
		h::{'a.cs-button'}(
			$L->new_post,
			[
				'href'	=> $module.'/new_post'
			]
		).
		h::br()
	);
}
$page		= isset($Config->routing['current'][1]) ? (int)$Config->routing['current'][1] : 1;
$from		= --$page*50;
$cdb		= $db->{$Config->module(MODULE)->db('posts')};
$posts		= $cdb->qfa(
	"SELECT `id`
		FROM `[prefix]blogs_posts`
		ORDER BY `id` DESC
		LIMIT $from, 10",
	true
);
foreach ($posts as $post) {
	$post	= $Blogs->get($post);
	$Index->content(
		h::{'section.cs-blogs-post-latest article'}(
			h::header(
				h::{'h1 a'}(
					$post['title'],
					[
						'href'	=> $module.'/'.$post['path'].':'.$post['id']
					]
				).
				h::p(
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
				)
			).
			$post['content']."\n".
			h::footer(
				h::hr().
				h::p(
					h::time(
						$L->to_locale(date($L->_datetime_long, $post['date'])),
						[
							'datetime'	=> date('c', $post['date']),
							//'pubdate'//TODO wait while "pubdate" it will be standartized by W3C
						]
					).
					' | '.
					h::a(
						$User->get_username($post['user']),
						[
							'href'	=> 'profile/'.$User->get('login', $post['user']),
							'rel'	=> 'author',
							'title'	=> $L->author
						]
					).
					' | '.
					h::a(
						$L->read_more,
						[
							'href'	=> $module.'/'.$post['path'].':'.$post['id']
						]
					)
				)
			)
		)
	);
}