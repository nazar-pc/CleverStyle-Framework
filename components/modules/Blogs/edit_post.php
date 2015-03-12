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

if (!Event::instance()->fire('Blogs/edit_post')) {
	return;
}

$Blogs						= Blogs::instance();
$Config						= Config::instance();
$module_data				= $Config->module('Blogs');
$L							= Language::instance();
$Page						= Page::instance();
$Route						= Route::instance();
$User						= User::instance();
if ($module_data->new_posts_only_from_admins && !$User->admin()) {
	error_code(403);
	return;
}
if (
	!isset($Route->route[1]) ||
	!($post = $Blogs->get($Route->route[1]))
) {
	error_code(404);
	return;
}
if (
	$post['user'] != $User->id &&
	!(
		$User->admin() &&
		$User->get_permission('admin/Blogs', 'index') &&
		$User->get_permission('admin/Blogs', 'edit_post')
	)
) {
	error_code(403);
	return;
}
$Page->title(
	$L->editing_of_post($post['title'])
);
$module						= path($L->Blogs);
if (isset($_POST['title'], $_POST['sections'], $_POST['content'], $_POST['tags'], $_POST['mode'])) {
	$draft	= false;
	switch ($_POST['mode']) {
		case 'draft':
			$draft	= true;
		case 'save':
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
				if ($Blogs->set($post['id'], $_POST['title'], null, $_POST['content'], $_POST['sections'], _trim(explode(',', $_POST['tags'])), $draft)) {
					interface_off();
					_header('Location: '.$Config->base_url()."/$module/$post[path]:$post[id]");
					return;
				} else {
					$Page->warning($L->post_saving_error);
				}
			}
		break;
		case 'delete':
			if ($Blogs->del($post['id'])) {
				interface_off();
				_header('Location: '.$Config->base_url()."/$module");
				return;
			} else {
				$Page->warning($L->post_deleting_error);
			}
		break;
	}
}
$Index						= Index::instance();
$Index->form				= true;
$Index->action				= "$module/edit_post/$post[id]";
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$disabled					= [];
$max_sections				= $module_data->max_sections;
$content					= uniqid('post_content');
$Page->replace($content, isset($_POST['content']) ? $_POST['content'] : $post['content']);
$sections					= get_sections_select_post($disabled);
if (count($sections['in']) > 1) {
	$sections	= [
		$L->post_section,
		h::{'select.cs-blogs-new-post-sections[size=7][required]'}(
			get_sections_select_post($disabled),
			[
				'name'		=> 'sections[]',
				'disabled'	=> $disabled,
				'selected'	=> isset($_POST['sections']) ? $_POST['sections'] : $post['sections'],
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
		$L->editing_of_post($post['title'])
	).
	h::{'div.cs-blogs-post-preview-content'}().
	h::{'cs-table.cs-blogs-post-form[right-left] cs-table-row| cs-table-cell'}(
		[
			$L->post_title,
			h::{'h1.cs-blogs-new-post-title[contenteditable=true]'}(
				isset($_POST['title']) ? $_POST['title'] : $post['title']
			)
		],
		$sections,
		[
			$L->post_content,
			(
				functionality('inline_editor') ? h::{'div.cs-blogs-new-post-content.INLINE_EDITOR'}(
					$content
				) : h::{'textarea.cs-blogs-new-post-content.EDITOR[name=content][required]'}(
					isset($_POST['content']) ? $_POST['content'] : $post['content']
				)
			).
			h::br().
			$L->post_use_pagebreak
		],
		[
			$L->post_tags,
			h::{'input.cs-blogs-new-post-tags[name=tags][required]'}([
				'value'			=> htmlspecialchars_decode(
					isset($_POST['tags']) ? $_POST['tags'] : implode(', ', $Blogs->get_tag($post['tags'])),
					ENT_QUOTES | ENT_HTML5 | ENT_DISALLOWED | ENT_SUBSTITUTE
				),
				'placeholder'	=> 'CleverStyle, CMS, Open Source'
			])
		]
	).
	(
		!$sections ? h::{'input[type=hidden][name=sections[]][value=0]'}() : ''
	).
	h::{'button.uk-button.cs-blogs-post-preview'}(
		$L->preview,
		[
			'data-id'	=> $post['id']
		]
	).
	h::{'button.uk-button[type=submit][name=mode][value=save]'}(
		$L->publish
	).
	h::{'button.uk-button[type=submit][name=mode][value=draft]'}(
		$L->to_drafts
	).
	h::{'button.uk-button[type=submit][name=mode][value=delete]'}(
		$L->delete
	)
);
