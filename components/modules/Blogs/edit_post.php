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
global $Page, $Index, $L, $User, $Blogs, $Config;
if (
	!isset($Config->routing['current'][1]) ||
	!($post = $Blogs->get($Config->routing['current'][1]))
) {
	define('ERROR_PAGE', 404);
	return;
}
if ($post['user'] != $User->id) {
	define('ERROR_PAGE', 403);
	return;
}
$Page->title(
	$L->editing_of_post($post['title'])
);
if (!$User->is('user')) {
	$Page->warning($L->for_reistered_users_only);
	return;
}
$module						= path($L->{MODULE});
if (isset($_POST['id'], $_POST['title'], $_POST['sections'], $_POST['content'], $_POST['tags'], $_POST['mode'])) {
	if ($Blogs->get($_POST['id'])['user'] != $User->id) {
		define('ERROR_PAGE', 403);
		return;
	}
	switch ($_POST['mode']) {
		case 'save':
			$save	= true;
			if (empty($_POST['title'])) {
				$Page->warning($L->post_title_empty);
				$save	= false;
			}
			if (empty($_POST['sections'])) {
				$Page->warning($L->no_post_sections_selected);
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
				if ($Blogs->set($_POST['id'], $_POST['title'], null, $_POST['content'], $_POST['sections'], _trim(explode(',', $_POST['tags'])))) {
					interface_off();
					header('Location: '.$Config->server['base_url'].'/'.$L->{MODULE}.'/'.$Blogs->get($_POST['id'])['path'].':'.$_POST['id']);
					return;
				} else {
					$Page->warning($L->post_saving_error);
				}
			}
		break;
		case 'delete':
			if ($Blogs->del($_POST['id'])) {
				interface_off();
				header('Location: '.$Config->server['base_url'].'/'.$L->{MODULE});
				return;
			} else {
				$Page->warning($L->post_deleting_error);
			}
		break;
	}
}
$Index->form				= true;
$Index->action				= $module.'/edit_post/'.$post['id'];
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$disabled					= [];
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages'}(
		$L->editing_of_post($post['title'])
	).
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		[
			$L->post_title,
			h::input([
				'name'		=> 'title',
				'value'		=> isset($_POST['title']) ? $_POST['title'] : $post['title'],
				'required'
			])
		],
		[
			$L->post_section,
			h::select(
				get_sections_select_post($disabled),
				[
					'name'		=> 'sections[]',
					'size'		=> 7,
					'disabled'	=> $disabled,
					'selected'	=> isset($_POST['sections']) ? $_POST['sections'] : $post['sections'],
					'multiple',
					'required'
				]
			)
		],
		[
			$L->post_content,
			h::textarea(
				isset($_POST['content']) ? $_POST['content'] : $post['content'],
				[
					'name'	=> 'content',
					'class'	=> 'cs-wide-textarea EDITOR'
				]
			)
		],
		[
			$L->post_tags,
			h::input([
				'name'		=> 'tags',
				'value'		=> isset($_POST['tags']) ? $_POST['tags'] : implode(', ', $Blogs->get_tag($post['tags'])),
				'required'
			])
		]
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $post['id']
	]).
	h::{'button#cs-new-post-preview'}(//TODO make this button workable
		$L->preview
	).
	h::{'button[type=submit][name=mode][value=save]'}(
		$L->save
	).
	h::{'button[type=submit][name=mode][value=delete]'}(
		$L->delete
	)
);