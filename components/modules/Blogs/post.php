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
$rc		= &$Config->routing['current'];
$post	= (int)mb_substr($rc[1], mb_strrpos($rc[1], ':')+1);
if (!$post) {
	define('ERROR_PAGE', 404);
	return;
}
$post	= $Blogs->get($post);
if (!$post || $post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
	define('ERROR_PAGE', 404);
	return;
}
$module	= path($L->{MODULE});
$Page->title($post['title']);
$Index->content(
	h::{'section.cs-blogs-post article'}(
		h::header(
			h::h2($post['title']).
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
		$post['content'].
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
				)
			)
		)
	)
);