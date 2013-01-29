<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\Blogs;
use			h;
global $Index, $L, $Page, $Config;
$Page->title($L->addition_of_posts_section);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/'.MODULE.'/browse_sections';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->addition_of_posts_section
	).
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}(
			$L->parent_section,
			$L->section_title,
			h::info('section_path')
		),
		h::{'td.ui-widget-content.ui-corner-all'}(
			h::{'select[name=parent][size=5]'}(
				get_sections_select_section(),
				[
					'selected'	=> isset($Config->route[1]) ? (int)$Config->route[1] : 0
				]
			),
			h::{'input[name=title]'}(),
			h::{'input[name=path]'}()
		)
	).
	h::{'input[type=hidden][name=mode][value=add_section]'}()
);