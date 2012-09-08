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
global $Page, $Index, $L, $User, $Config;
$Page->title($L->new_post);
if (!$User->is('user')) {
	if ($User->is('bot')) {
		define('ERROR_PAGE', 403);
		return;
	} else {
		$Page->warning($L->for_reistered_users_only);
		return;
	}
}
$module						= path($L->{MODULE});
if (isset($_POST['title'], $_POST['sections'], $_POST['content'], $_POST['tags'], $_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'publish':
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
				global $Blogs;
				$id	= $Blogs->add($_POST['title'], null, $_POST['content'], $_POST['sections'], _trim(explode(',', $_POST['tags'])));
				if ($id) {
					interface_off();
					header('Location: '.$Config->server['base_url'].'/'.$module.'/'.$Blogs->get($id)['path'].':'.$id);
					return;
				} else {
					$Page->warning($L->post_adding_error);
				}
			}
		break;
	}
}
$Index->form				= true;
$Index->action				= $module.'/new_post';
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$disabled					= [];
$max_sections				= $Config->module(MODULE)->max_sections;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages'}(
		$L->new_post
	).
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
		[
			$L->post_title,
			h::input([
				'name'		=> 'title',
				'value'		=> isset($_POST['title']) ? $_POST['title'] : false,
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
					'selected'	=> isset($_POST['sections']) ? $_POST['sections'] : (isset($Config->routing['current'][1]) ? $Config->routing['current'][1] : []),
					$max_sections < 1 ? 'multiple' : false,
					'required'
				]
			).
			($max_sections > 1 ? h::br().$L->select_sections_num($max_sections) : '')
		],
		[
			$L->post_content,
			h::textarea(
				isset($_POST['content']) ? $_POST['content'] : '',
				[
					'name'	=> 'content',
					'class'	=> 'cs-wide-textarea EDITOR'
				]
			).
			h::br().
			$L->post_use_pagebreak
		],
		[
			$L->post_tags,
			h::input([
				'name'		=> 'tags',
				'value'		=> isset($_POST['tags']) ? $_POST['tags'] : false,
				'required'
			])
		]
	).
	/*h::{'button#cs-new-post-preview'}(//TODO make this button workable
		$L->preview
	).*/
	h::{'button[type=submit][name=mode][value=publish]'}(
		$L->publish
	)
);