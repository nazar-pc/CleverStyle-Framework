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
global $Page, $Index, $L, $Blogs, $Config;
$post = $Blogs->get($Config->routing['current'][1]);
$Page->title(
	$L->editing_of_post($post['title'])
);
$Index->form				= true;
$Index->action				= 'admin/'.MODULE.'/browse_posts';
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->reset_button		= true;
$disabled					= [];
$max_sections				= $Config->module(MODULE)->max_sections;
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
					$max_sections < 1 ? 'multiple' : false,
					'required'
				]
			).
			($max_sections > 1 ? h::br().$L->select_sections_num($max_sections) : '')
		],
		[
			$L->post_content,
			h::textarea(
				isset($_POST['content']) ? $_POST['content'] : $post['content'],
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
				'value'		=> isset($_POST['tags']) ? $_POST['tags'] : implode(', ', $Blogs->get_tag($post['tags'])),
				'required'
			])
		]
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $post['id']
	]).
	/*h::{'button#cs-new-post-preview'}(//TODO make this button workable
		$L->preview
	).*/
	h::{'button[type=submit][name=mode][value=edit_post]'}(
		$L->save
	)
);