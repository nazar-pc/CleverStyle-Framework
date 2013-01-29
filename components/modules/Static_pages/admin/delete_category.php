<?php
/**
 * @package		Static Pages
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			h;
global $Index, $L, $Page, $Static_pages, $Config;
$id							= (int)$Config->route[1];
$title						= $Static_pages->get_category($id)['title'];
$Page->title($L->deletion_of_page_category($title));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/'.MODULE;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->sure_to_delete_page_category($title)
	).
	h::{'button[type=submit]'}($L->yes).
	h::{"input[type=hidden][name=id][value=$id]"}().
	h::{'input[type=hidden][name=mode][value=delete_category]'}()
);