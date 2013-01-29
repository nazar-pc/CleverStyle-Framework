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
global $Index, $L, $Page, $Blogs, $Config;
$post						= $Blogs->get($Config->route[1]);
$Page->title($L->deletion_of_post($post['title']));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/'.MODULE.'/browse_posts';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->sure_to_delete_post($post['title'])
	).
	h::{'button[type=submit]'}($L->yes).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $post['id']
	]).
	h::{'input[type=hidden][name=mode][value=delete_post]'}()
);