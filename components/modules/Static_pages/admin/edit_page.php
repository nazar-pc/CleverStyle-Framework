<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Index						= Index::instance();
$L							= Language::instance();
$id							= (int)Config::instance()->route[1];
$data						= Static_pages::instance()->get($id);
Page::instance()->title($L->editing_of_page($data['title']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->editing_of_page($data['title'])
	).
	h::{'table.cs-fullwidth-table.cs-center-all tr'}(
		h::{'th.ui-widget-header.ui-corner-all'}(
			$L->category,
			$L->page_title,
			h::info('page_path'),
			h::info('page_interface')
		),
		h::{'td.ui-widget-content.ui-corner-all'}(
			h::{'select[name=category][size=5]'}(
				get_categories_list(),
				[
					'selected'	=> $data['category']
				]
			),
			h::{'input[name=title]'}([
				'value'	=> $data['title']
			]),
			h::{'input[name=path]'}([
				'value'	=> $data['path']
			]),
			h::{'div input[type=radio][name=interface]'}([
				'checked'	=> $data['interface'],
				'value'		=> [0, 1],
				'in'		=> [$L->off, $L->on]
			])
		),
		h::{'th.ui-widget-header.ui-corner-all[colspan=4]'}(
			$L->content
		),
		h::{'td.ui-widget-content.ui-corner-all[colspan=4] textarea.cs-wide-textarea[name=content]'}(
			$data['content'],
			[
				'class'	=> $data['interface'] ? 'EDITOR' : ''
			]
		)
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $id
	]).
	h::{'input[type=hidden][name=mode][value=edit_page]'}()
);