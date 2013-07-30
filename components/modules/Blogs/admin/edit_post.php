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
			cs\Page;
$Blogs						= Blogs::instance();
$Config						= Config::instance();
$Index						= Index::instance();
$L							= Language::instance();
$post						= $Blogs->get($Config->route[1]);
Page::instance()->title(
	$L->editing_of_post($post['title'])
);
$Index->form				= true;
$Index->action				= 'admin/Blogs/browse_posts';
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->reset_button		= true;
$disabled					= [];
$max_sections				= $Config->module('Blogs')->max_sections;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->editing_of_post($post['title'])
	).
	h::{'div.cs-blogs-post-preview-content'}().
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr| td'}(
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
			h::{'textarea.cs-blogs-new-post-content.cs-wide-textarea.EDITOR[name=content][required]'}(
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
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $post['id']
	]).
	h::{'button.cs-blogs-post-preview'}(
		$L->preview,
		[
			'data-id'	=> $post['id']
		]
	).
	h::{'button[type=submit][name=mode][value=edit_post]'}(
		$L->save
	).
	h::{'button[type=submit][name=mode][value=edit_post_draft]'}(
		$L->to_drafts
	)
);