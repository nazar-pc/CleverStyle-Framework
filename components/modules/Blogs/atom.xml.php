<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Language,
	cs\Page,
	cs\User;

$Config   = Config::instance();
$L        = Language::instance();
$Page     = Page::instance();
$User     = User::instance();
$title    = [
	get_core_ml_text('name'),
	$L->Blogs
];
$Posts    = Posts::instance();
$Sections = Sections::instance();
$Tags     = Tags::instance();
$number   = $Config->module('Blogs')->posts_per_page;
if (isset($_GET['section'])) {
	$section = $Sections->get($_GET['section']);
	if (!$section) {
		error_code(404);
		return;
	}
	$title[] = $L->section;
	$title[] = $section['title'];
	$posts   = $Posts->get_for_section($section['id'], 1, $number);
} elseif (isset($_GET['tag'])) {
	$tag = $Tags->get($_GET['tag']);
	if (!$tag) {
		error_code(404);
		return;
	}
	$title[] = $L->tag;
	$title[] = $tag['text'];
	$posts   = $Posts->get_for_tag($tag['id'], $L->clang, 1, $number);
} else {
	$posts = $Posts->get_latest_posts(1, $number);
}
$title[]  = $L->latest_posts;
$title    = implode($Config->core['title_delimiter'], $title);
$base_url = $Config->base_url();
_header('Content-Type: application/atom+xml');
interface_off();

function get_favicon_path ($theme) {
	$theme_favicon = "$theme/img/favicon";
	if (file_exists(THEMES."/$theme_favicon.png")) {
		return "$theme_favicon.png";
	} elseif (file_exists(THEMES."/$theme_favicon.ico")) {
		return "$theme_favicon.ico";
	}
	return 'favicon.ico';
}

/**
 * @var \cs\_SERVER $_SERVER
 */
$Page->content(
	"<?xml version=\"1.0\" encoding=\"utf-8\"?>\n".
	h::feed(
		h::title($title).
		h::id($Config->core_url().$_SERVER->request_uri).
		str_replace(
			'>',
			'/>',
			h::link(
				[
					'href' => $Config->core_url().$_SERVER->request_uri,
					'rel'  => 'self'
				]
			)
		).
		h::updated(date('c')).
		'<icon>'.get_favicon_path($Config->core['theme'])."</icon>\n".
		h::entry(
			array_map(
				function ($post) use ($Posts, $Sections, $User, $base_url) {
					$post = $Posts->get($post);
					return
						h::title($post['title']).
						h::id("$base_url/Blogs/:$post[id]").
						h::updated(date('c', $post['date'])).
						h::published(date('c', $post['date'])).
						str_replace(
							'>',
							'/>',
							h::link(
								[
									'href' => "$base_url/Blogs/:$post[id]"
								]
							)
						).
						h::{'author name'}($User->username($post['user'])).
						h::category(
							$post['sections'] == ['0'] ? false : array_map(
								function ($category) {
									return [
										'term'  => h::prepare_attr_value($category['title']),
										'label' => h::prepare_attr_value($category['title']),
										'level' => 0
									];
								},
								$Sections->get($post['sections'])
							)
						).
						h::summary(
							htmlentities($post['short_content']),
							[
								'type' => 'html'
							]
						).
						h::content(
							htmlentities($post['content']),
							[
								'type' => 'html'
							]
						);
				},
				$posts ?: []
			)
		),
		[
			'xmlns'    => 'http://www.w3.org/2005/Atom',
			'xml:lang' => $L->clang,
			'xml:base' => "$base_url/"
		]
	)
);
