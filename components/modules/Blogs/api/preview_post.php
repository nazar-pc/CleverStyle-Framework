<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			\h;
global $Config, $User, $Page, $Blogs, $L;
include_once MFOLDER.'/../prepare.php';
/**
 * If AJAX request from local referer, user is not guest
 */
if (!$Config->server['referer']['local'] || !$Config->server['ajax'] || !$User->user()) {
	sleep(1);
	return;
}
if (empty($_POST['title'])) {
	$Page->warning($L->post_title_empty);
	$Page->content($Page->Top);
	return;
}
if (empty($_POST['sections'])) {
	$Page->warning($L->no_post_sections_specified);
	$Page->content($Page->Top);
	return;
}
if (empty($_POST['content'])) {
	$Page->warning($L->post_content_empty);
	$Page->content($Page->Top);
	return;
}
if (empty($_POST['tags'])) {
	$Page->warning($L->no_post_tags_specified);
	$Page->content($Page->Top);
	return;
}
if (isset($_POST['id'])) {
	$post	= $Blogs->get($_POST['id']);
} else {
	$post	= [
		'date'				=> TIME,
		'user'				=> $User->id,
		'comments_count'	=> 0
	];
}
$module	= path($L->{'MODULE'});
$Page->content(
	h::{'section.cs-blogs-post article'}(
		h::header(
			h::h1(xap($_POST['title'])).
			((array)$_POST['sections'] != [0] ? h::p(
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
					(array)$_POST['sections']
				)
				)
			) : '')
		).
		$_POST['content']."\n".
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
						_trim(explode(',', $_POST['tags']))
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
	h::br(2)
);