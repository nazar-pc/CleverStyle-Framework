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
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Route;
$Blogs						= Blogs::instance();
$Config						= Config::instance();
$Index						= Index::instance();
$L							= Language::instance();
$Page						= Page::instance();
$post						= $Blogs->get(Route::instance()->route[1]);
$Page->title(
	$L->editing_of_post($post['title'])
);
$Index->form				= true;
$Index->action				= 'admin/Blogs/browse_posts';
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$disabled					= [];
$max_sections				= $Config->module('Blogs')->max_sections;
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
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $post['id']
	]).
	(
		!$sections ? h::{'input[type=hidden][name=sections[]][value=0]'}() : ''
	).
	h::{'button.uk-button.cs-blogs-post-preview'}(
		$L->preview,
		[
			'data-id'	=> $post['id']
		]
	).
	h::{'button.uk-button[type=submit][name=mode][value=edit_post]'}(
		$L->save
	).
	h::{'button.uk-button[type=submit][name=mode][value=edit_post_draft]'}(
		$L->to_drafts
	)
);
