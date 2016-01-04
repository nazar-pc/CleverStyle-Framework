<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Page\Meta,
	cs\Page,
	cs\Route,
	cs\User;

if (!Event::instance()->fire('Blogs/post')) {
	return;
}

$Config   = Config::instance();
$Page     = Page::instance();
$User     = User::instance();
$Comments = null;
Event::instance()->fire(
	'Comments/instance',
	[
		'Comments' => &$Comments
	]
);
/**
 * @var \cs\modules\Comments\Comments $Comments
 */
$Posts   = Posts::instance();
$rc      = Route::instance()->route;
$post_id = (int)mb_substr($rc[1], mb_strrpos($rc[1], ':') + 1);
if (!$post_id) {
	throw new ExitException(404);
}
$post = $Posts->get_as_json_ld($post_id);
if (
	!$post ||
	(
		$post['draft'] && $post['user'] != $User->id
	)
) {
	throw new ExitException(404);
}
if ($post['path'] != mb_substr($rc[1], 0, mb_strrpos($rc[1], ':'))) {
	status_code(303);
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
	->article('section', $post['articleSection'] ? $post['articleSection'][0] : false)
	->article('tag', $post['tags']);
array_map([$Meta, 'image'], $post['image']);
$comments_enabled = $Config->module('Blogs')->enable_comments && $Comments;
$is_admin         =
	$User->admin() &&
	$User->get_permission('admin/Blogs', 'index') &&
	$User->get_permission('admin/Blogs', 'edit_post');
$Page->content(
	h::{'article[is=cs-blogs-post]'}(
		h::{'script[type=application/ld+json]'}(
			json_encode($post, JSON_UNESCAPED_UNICODE)
		),
		[
			'comments_enabled' => $comments_enabled,
			'can_edit'         => $is_admin || $User->id == $post['user'],
			'can_delete'       => $is_admin
		]
	).
	($comments_enabled ? $Comments->block($post['id']) : '')
);
