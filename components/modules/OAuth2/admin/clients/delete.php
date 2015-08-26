<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use
	h,
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\Route;
$Index						= Index::instance();
$L							= new Prefix('oauth2_');
$client						= OAuth2::instance()->get_client(Route::instance()->route[2]);
Page::instance()->title($L->deletion_of_client($client['name']));
$Index->buttons				= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2/clients/list';
$Index->content(
	h::{'h2.cs-center'}(
		$L->sure_to_delete_client($client['name'])
	).
	h::{'button[is=cs-button][type=submit]'}($L->yes).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $client['id']
	]).
	h::{'input[type=hidden][name=mode][value=delete]'}()
);
