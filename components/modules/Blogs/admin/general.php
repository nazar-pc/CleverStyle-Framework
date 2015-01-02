<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$module_data			= Config::instance()->module('Blogs');
$Index					= Index::instance();
$Index->apply_button	= false;
$L						= Language::instance();
Page::instance()->title($L->general);
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			h::info('posts_per_page'),
			h::{'input[type=number][min=1]'}([
				'name'		=> 'general[posts_per_page]',
				'value'		=> $module_data->posts_per_page
			])
		],
		[
			h::info('maximum_number_of_sections_for_post'),
			h::{'input[type=number][min=1]'}([
				'name'		=> 'general[max_sections]',
				'value'		=> $module_data->max_sections
			])
		],
		[
			h::info('enable_comments'),
			h::radio([
				'name'		=> 'general[enable_comments]',
				'value'		=> [0, 1],
				'in'		=> [$L->no, $L->yes],
				'checked'	=> $module_data->enable_comments
			])
		],
		[
			h::info('new_posts_only_from_admins'),
			h::radio([
				'name'		=> 'general[new_posts_only_from_admins]',
				'value'		=> [0, 1],
				'in'		=> [$L->no, $L->yes],
				'checked'	=> $module_data->new_posts_only_from_admins
			])
		],
		[
			h::info('allow_iframes_without_content'),
			h::radio([
				'name'		=> 'general[allow_iframes_without_content]',
				'value'		=> [0, 1],
				'in'		=> [$L->no, $L->yes],
				'checked'	=> $module_data->allow_iframes_without_content
			])
		]
	).
	h::{'input[type=hidden][name=mode][value=general]'}()
);
