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
$Page->title($L->list_of_client);
$Index->apply_button	= false;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->list_of_clients
	).
	h::{'table.cs-fullwidth-table.cs-center-all'}(
		h::{'th.ui-widget-header.ui-corner-all'}([
												 $L->client_name,
			'client_id',
			'client_secret',
			$L->action
		]).
		h::tr(array_map(
			function ($client) use ($L) {
				return h::{'td.ui-widget-content.ui-corner-all'}(
					[
						$client['name'],
						h::{'input{disabled]'}($client['id']),
						h::{'input{disabled]'}($client['secret']),
						h::{'a.cs-button-compact'}([
							h::icon('wrench'),
							[
								'href'			=> 'admin/OAuth2/clients/edit/'.$client['id'],
								'data-title'	=> $L->edit
							]
						]).
						h::{'a.cs-button-compact'}([
							h::icon('trash'),
							[
								'href'			=> 'admin/OAuth2/clients/delete/'.$client['id'],
								'data-title'	=> $L->delete
							]
						])
					],
					[
						'class'	=> $client['active'] ? false : 'ui-state-disabled'
					]
				);
			},
			$OAuth2->clients_list()
		))
	).
	h::{'p.cs-left a.cs-button'}([
		$L->add_client,
		[
			'href'	=> 'admin/OAuth2/clients/add'
		]
	]).
	h::{'table.cs-fullwidth-table.cs-left-even.cs-right-odd tr td'}(
		h::info('allow_guest_tokens'),
		h::{'input[type=radio]'}([
			'name'		=> 'guest_tokens',
			'checked'	=> $Config->module('OAuth2')->guest_tokens,
			'value'		=> [0, 1],
			'in'		=> [$L->off, $L->on]
		])
	).
	h::{'input[type=hidden][name=mode][value=general]'}()
);