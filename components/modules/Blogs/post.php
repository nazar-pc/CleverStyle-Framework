<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page\Meta,
	cs\Page,
	cs\Route,
	cs\User;

if (!Event::instance()->fire('Blogs/post')) {
	return;
}

$Config      = Config::instance();
$module_data = $Config->module('Blogs');
$L           = Language::instance();
$Page        = Page::instance();
$User        = User::instance();
$Comments    = null;
Event::instance()->fire(
	'Comments/instance',
	[
		'Comments' => &$Comments
	]
);
/**
 * @var \cs\modules\Comments\Comments $Comments
 */
$Blogs   = Blogs::instance();
$rc      = Route::instance()->route;
$post_id = (int)mb_substr($rc[1], mb_strrpos($rc[1], ':') + 1);
if (!$post_id) {
	error_code(404);
	return;
}
$post = $Blogs->get_as_json_ld($post_id);
if (
	!$post ||
	(
		$post['draft'] && $post['user'] != $User->id
	)
) {
	error_code(404);
	return;
}
$module = path($L->Blogs);
if ($post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
	code_header(303);
	_header("Location: $post[url]");
	return;
}
$Page->title($post['title']);
$Page->Description = description($post['short_content']);
$Page->canonical_url($post['url']);
$Meta = Meta::instance();
$Meta
	->article()
	->article('published_time', date('Y-m-d', $post['date'] ?: TIME))
	->article('author', $post['author']['url'])
	->article('section', $post['articleSection'] ? $post['articleSection'][0] : false)
	->article('tag', $post['tags']);
foreach ($post['image'] as $image) {
	$Meta->image($image);
}
unset($image);
/////////////////TODO DELETE when Web Components added
$content = uniqid('post_content');
$Page->replace($content, $post['content']);
/////////////////TODO DELETE when Web Components added
Index::instance()->content(
	h::{'section.cs-blogs-post article'}(
		h::header(
			(
			$User->admin() &&
			$User->get_permission('admin/Blogs', 'index') &&
			$User->get_permission('admin/Blogs', 'edit_post') ? ' '.h::{'a.uk-button'}(
					[
						h::icon('pencil'),
						[
							'href'       => "$module/edit_post/$post[id]",
							'data-title' => $L->edit
						]
					],
					[
						h::icon('trash-o'),
						[
							'href'       => "admin/Blogs/delete_post/$post[id]",
							'data-title' => $L->delete
						]
					]
				) : (
			$User->id == $post['user'] ? ' '.h::{'a.uk-button.cs-button-compact'}(
					h::icon('pencil'),
					[
						'href'       => "$module/edit_post/$post[id]",
						'data-title' => $L->edit
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
							$section = $Blogs->get_section($section);
							return h::a(
								$section['title'],
								[
									'href' => "$module/".path($L->section)."/$section[full_path]"
								]
							);
						},
						$post['sections']
					)
				)
			) : '')
		).
		"$content\n".
		h::footer(
			h::p(
				h::icon('tags').
				implode(
					', ',
					array_map(
						function ($tag) use ($L, $module) {
							return h::{'a[level=0][rel=tag]'}(
								$tag,
								[
									'href' => "$module/".path($L->tag)."/$tag"
								]
							);
						},
						$post['tags']
					)
				)
			).
			h::hr().
			h::p(
				h::time(
					$L->to_locale(date($L->_datetime_long, $post['date'] ?: TIME)),
					[
						'datetime' => date('c', $post['date'] ?: TIME)
					]
				).
				h::a(
					h::icon('user').$User->username($post['user']),
					[
						'href' => path($L->profile).'/'.$User->get('login', $post['user']),
						'rel'  => 'author'
					]
				).
				(
				$module_data->enable_comments && $Comments ? h::icon('comments').$post['comments_count'] : ''
				)
			)
		)
	).
	(
	$module_data->enable_comments && $Comments ? $Comments->block($post['id']) : ''
	)
);
