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
			cs\User;
$Blogs						= Blogs::instance();
$Config						= Config::instance();
$L							= Language::instance();
$Page						= Page::instance();
$User						= User::instance();
if (
	!isset($Config->route[1]) ||
	!($post = $Blogs->get($Config->route[1]))
) {
	define('ERROR_CODE', 404);
	return;
}
if (
	$post['user'] != $User->id &&
	!(
		$User->admin() &&
		$User->get_user_permission('admin/Blogs', 'index') &&
		$User->get_user_permission('admin/Blogs', 'edit_post')
	)
) {
	define('ERROR_CODE', 403);
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
					header('Location: '.$Config->base_url()."/$module/$post[path]:$post[id]");
					return;
				} else {
					$Page->warning($L->post_saving_error);
				}
			}
		break;
		case 'delete':
			if ($Blogs->del($post['id'])) {
				interface_off();
				header('Location: '.$Config->base_url()."/$module");
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
$max_sections				= $Config->module('Blogs')->max_sections;
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->editing_of_post($post['title'])
	).
	h::{'div.cs-blogs-post-preview-content'}().
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}(
		[
			$L->post_title,
			h::{'input.cs-blogs-new-post-title[name=title][required]'}([
				'value'		=> isset($_POST['title']) ? $_POST['title'] : $post['title']
			])
		],
		[
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
		],
		[
			$L->post_content,
			h::{'textarea.cs-blogs-new-post-content.EDITOR[name=content][required]'}(
				isset($_POST['content']) ? $_POST['content'] : $post['content']
			).
			h::br().
			$L->post_use_pagebreak
		],
		[
			$L->post_tags,
			h::{'input.cs-blogs-new-post-tags[name=tags][required]'}([
				'value'		=> isset($_POST['tags']) ? $_POST['tags'] : implode(', ', $Blogs->get_tag($post['tags']))
			])
		]
	).
	h::{'button.cs-blogs-post-preview'}(
		$L->preview,
		[
			'data-id'	=> $post['id']
		]
	).
	h::{'button[type=submit][name=mode][value=save]'}(
		$L->publish
	).
	h::{'button[type=submit][name=mode][value=draft]'}(
		$L->to_drafts
	).
	h::{'button[type=submit][name=mode][value=delete]'}(
		$L->delete
	)
);