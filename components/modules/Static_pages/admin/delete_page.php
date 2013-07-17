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
$title						= Static_pages::instance()->get($id)['title'];
Page::instance()->title($L->deletion_of_page($title));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->sure_to_delete_page($title)
	).
	h::{'button[type=submit]'}($L->yes).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $id
	]).
	h::{'input[type=hidden][name=mode][value=delete_page]'}()
);