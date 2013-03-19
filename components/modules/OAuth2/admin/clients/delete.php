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
global $Index, $L, $Page, $OAuth2, $Config;
$client						= $OAuth2->get_client($Config->route[2]);
$Page->title($L->deletion_of_client($client['name']));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/'.MODULE.'/clients/list';
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->sure_to_delete_client($client['name'])
	).
	h::{'button[type=submit]'}($L->yes).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $client['id']
	]).
	h::{'input[type=hidden][name=mode][value=delete]'}()
);