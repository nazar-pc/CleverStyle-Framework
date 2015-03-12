<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route,
	cs\User;

if (!Event::instance()->fire('Blogs/new_post')) {
	return;
}

$Config						= Config::instance();
$module_data				= $Config->module('Blogs');
$L							= Language::instance();
$Page						= Page::instance();
$Route						= Route::instance();
$User						= User::instance();
$Page->title($L->new_post);
if (!$User->admin() && $module_data->new_posts_only_from_admins) {
	error_code(403);
	return;
}
if (!$User->user()) {
	if ($User->bot()) {
		error_code(403);
		return;
	} else {
		$Page->warning($L->for_registered_users_only);
		return;
	}
}
$module						= path($L->Blogs);
if (isset($_POST['title'], $_POST['sections'], $_POST['content'], $_POST['tags'], $_POST['mode'])) {
	$draft	= false;
	switch ($_POST['mode']) {
		case 'draft':
			$draft	= true;
		case 'publish':
			$save	= true;
			if (empty($_POST['title'])) {
				$Page->warning($L->post_title_empty);
				$save	= false;
			}
			if (empty($_POST['sections']) && $_POST['sections'] !== '0') {
				$Page->warning($L->no_post_sections_specified);
				$save	= false;
			}
			if (empty($_POST['content'])) {
				$Page->warning($L->post_content_empty);
				$save	= false;
			}
			if (empty($_POST['tags'])) {
				$Page->warning($L->no_post_tags_specified);
				$save	= false;
			}
			if ($save) {
				$Blogs	= Blogs::instance();
				$id		= $Blogs->add($_POST['title'], null, $_POST['content'], $_POST['sections'], _trim(explode(',', $_POST['tags'])), $draft);
				if ($id) {
					interface_off();
					_header('Location: '.$Config->base_url()."/$module/".$Blogs->get($id)['path'].":$id");
					return;
				} else {
					$Page->warning($L->post_adding_error);
				}
			}
		break;
	}
}
$Index						= Index::instance();
$Index->form				= true;
$Index->action				= "$module/new_post";
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$disabled					= [];
$max_sections				= $module_data->max_sections;
$content					= uniqid('post_content');
$Page->replace($content, isset($_POST['content']) ? $_POST['content'] : '');
$sections					= get_sections_select_post($disabled);
if (count($sections['in']) > 1) {
	$sections	= [
		$L->post_section,
		h::{'select.cs-blogs-new-post-sections[size=7][required]'}(
			$sections,
			[
				'name'		=> 'sections[]',
				'disabled'	=> $disabled,
				'selected'	=> isset($_POST['sections']) ? $_POST['sections'] : (isset($Route->route[1]) ? $Route->route[1] : []),
				$max_sections < 1 ? 'multiple' : false
			]
		).
		($max_sections > 1 ? h::br().$L->select_sections_num($max_sections) : '')
	];
} else {
	$sections	= false;
}
$Index->content(
	h::{'h2.cs-center'}(
		$L->new_post
	).
	h::{'div.cs-blogs-post-preview-content'}().
	h::{'cs-table.cs-blogs-post-form[right-left] cs-table-row| cs-table-cell'}(
		[
			$L->post_title,
			h::{'h1.cs-blogs-new-post-title[contenteditable=true][level=0]'}(
				isset($_POST['title']) ? $_POST['title'] : '<br>'
			)
		],
		$sections,
		[
			$L->post_content,
			(
				functionality('inline_editor') ? h::{'div.cs-blogs-new-post-content.INLINE_EDITOR'}(
					$content
				) : h::{'textarea.cs-blogs-new-post-content.EDITOR[name=content][required]'}(
					isset($_POST['content']) ? $_POST['content'] : ''
				)
			).
			h::br().
			$L->post_use_pagebreak
		],
		[
			$L->post_tags,
			h::{'input.cs-blogs-new-post-tags[name=tags][required]'}([
				'value'			=> isset($_POST['tags']) ? $_POST['tags'] : false,
				'placeholder'	=> 'CleverStyle, CMS, Open Source'
			])
		]
	).
	(
		!$sections ? h::{'input[type=hidden][name=sections[]][value=0]'}() : ''
	).
	h::{'button.uk-button.cs-blogs-post-preview'}(
		$L->preview
	).
	h::{'button.uk-button[type=submit][name=mode][value=publish]'}(
		$L->publish
	).
	h::{'button.uk-button[type=submit][name=mode][value=draft]'}(
		$L->to_drafts
	)
);
