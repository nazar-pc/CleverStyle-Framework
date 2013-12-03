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
$section					= Blogs::instance()->get_section(Config::instance()->route[1]);
$Index						= Index::instance();
$L							= Language::instance();
Page::instance()->title($L->deletion_of_posts_section($section['title']));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/Blogs/browse_sections';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->sure_to_delete_posts_section($section['title'])
	).
	h::{'button[type=submit]'}($L->yes).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $section['id']
	]).
	h::{'input[type=hidden][name=mode][value=delete_section]'}()
);