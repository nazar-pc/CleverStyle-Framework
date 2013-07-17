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
$id							= (int)Config::instance()->route[1];
$data						= Blogs::instance()->get_section($id);
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->editing_of_posts_section($data['title']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Blogs/browse_sections';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->editing_of_posts_section($data['title'])
	).
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}(
			$L->parent_section,
			$L->section_title,
			h::info('section_path')
		),
		h::{'td.ui-widget-content.ui-corner-all'}(
			h::{'select[name=parent][size=5]'}(
				get_sections_select_section($id),
				[
					'selected'	=> $data['parent']
				]
			),
			h::{'input[name=title]'}([
				'value'	=> $data['title']
			]),
			h::{'input[name=path]'}([
				'value'	=> $data['path']
			])
		)
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $id
	]).
	h::{'input[type=hidden][name=mode][value=edit_section]'}()
);