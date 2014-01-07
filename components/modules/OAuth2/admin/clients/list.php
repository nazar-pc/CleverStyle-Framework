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
			cs\Language\Prefix,
			cs\Page;
$Index					= Index::instance();
$L						= new Prefix('oauth2_');
Page::instance()->title($L->list_of_client);
$Index->apply_button	= false;
$module_data			= Config::instance()->module('OAuth2');
$Index->content(
	h::{'p.lead.cs-center'}(
		$L->list_of_clients
	).
	h::{'table.cs-table.cs-center-all'}(
		h::{'thead tr th'}([
			$L->client_name,
			'client_id',
			'client_secret',
			$L->action
		]).
		h::{'tbody tr'}(array_map(
			function ($client) use ($L) {
				return h::td(
					[
						$client['name'],
						h::{'input{disabled]'}($client['id']),
						h::{'input{disabled]'}($client['secret']),
						h::{'a.cs-button-compact'}([
							h::icon('pencil'),
							[
								'href'			=> "admin/OAuth2/clients/edit/$client[id]",
								'data-title'	=> $L->edit
							]
						]).
						h::{'a.cs-button-compact'}([
							h::icon('trash-o'),
							[
								'href'			=> "admin/OAuth2/clients/delete/$client[id]",
								'data-title'	=> $L->delete
							]
						])
					],
					[
						'class'	=> $client['active'] ? false : 'text-muted'
					]
				);
			},
			OAuth2::instance()->clients_list()
		))
	).
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd.uk-margin-top tr| td'}(
		[
			h::info('oauth2_allow_guest_tokens'),
			h::{'input[type=radio]'}([
				'name'		=> 'general[guest_tokens]',
				'checked'	=> $module_data->guest_tokens,
				'value'		=> [0, 1],
				'in'		=> [$L->no, $L->yes]
			])
		],
		[
			h::info('oauth2_automatic_prolongation'),
			h::{'input[type=radio]'}([
				'name'		=> 'general[automatic_prolongation]',
				'checked'	=> $module_data->automatic_prolongation,
				'value'		=> [0, 1],
				'in'		=> [$L->no, $L->yes]
			])
		],
		[
			h::info('oauth2_expiration'),
			h::{'input[type=number]'}([
				'name'		=> 'general[expiration]',
				'value'		=> $module_data->expiration,
				'min'		=> 1
			]).$L->seconds
		]
	).
	h::{'p.cs-left a.cs-button'}([
		$L->add_client,
		[
			'href'	=> 'admin/OAuth2/clients/add'
		]
	])
);
