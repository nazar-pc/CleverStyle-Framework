<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\OAuth2;
use			h,
			cs\Config,
			cs\Index,
			cs\Language,
			cs\Page;
$Index						= Index::instance();
$L							= Language::instance();
$client						= OAuth2::instance()->get_client(Config::instance()->route[2]);
Page::instance()->title($L->editing_of_client($client['name']));
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2/clients/list';
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->editing_of_client($client['name'])
	).
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr'}(
		h::th($L->client_name).
		h::{'td input[name=name]'}([
			'value'	=> $client['name']
		]),
		h::th('client_secret').
		h::{'td input[name=secret]'}([
			'value'	=> $client['secret']
		]),
		h::th($L->client_domain).
		h::{'td input[name=domain]'}([
			'value'	=> $client['domain']
		]),
		h::th($L->active).
		h::{'td input[type=radio][name=active]'}([
			'checked'	=> $client['active'],
			'value'		=> [0, 1],
			'in'		=> [$L->no, $L->yes]
		])
	).
	h::{'input[type=hidden][name=id]'}([
		'value'	=> $client['id']
	]).
	h::{'input[type=hidden][name=mode][value=edit]'}()
);