<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\modules\OAuth2;
use            h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;
$Index = Index::instance();
$L     = new Prefix('oauth2_');
Page::instance()->title($L->list_of_client);
$module_data = Config::instance()->module('OAuth2');
$Index->content(
	h::{'h2.cs-center'}(
		$L->list_of_clients
	).
	h::{'cs-table[list][center][with-header]'}(
		h::{'cs-table-row cs-table-cell'}(
			$L->client_name,
			'client_id',
			'client_secret',
			$L->action
		).
		h::{'cs-table-row| cs-table-cell'}(
			array_map(
				function ($client) use ($L) {
					return [
						[
							$client['name'],
							h::{'input{disabled]'}($client['id']),
							h::{'input{disabled]'}($client['secret']),
							h::{'a.uk-button.cs-button-compact'}(
								[
									h::icon('pencil'),
									[
										'href'       => "admin/OAuth2/clients/edit/$client[id]",
										'data-title' => $L->edit
									]
								]
							).
							h::{'a.uk-button.cs-button-compact'}(
								[
									h::icon('trash-o'),
									[
										'href'       => "admin/OAuth2/clients/delete/$client[id]",
										'data-title' => $L->delete
									]
								]
							)
						],
						[
							'class' => $client['active'] ? false : 'text-muted'
						]
					];
				},
				OAuth2::instance()->clients_list()
			) ?: false
		)
	).
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			h::info('oauth2_allow_guest_tokens'),
			h::radio(
				[
					'name'    => 'general[guest_tokens]',
					'checked' => $module_data->guest_tokens,
					'value'   => [0, 1],
					'in'      => [$L->no, $L->yes]
				]
			)
		],
		[
			h::info('oauth2_automatic_prolongation'),
			h::radio(
				[
					'name'    => 'general[automatic_prolongation]',
					'checked' => $module_data->automatic_prolongation,
					'value'   => [0, 1],
					'in'      => [$L->no, $L->yes]
				]
			)
		],
		[
			h::info('oauth2_expiration'),
			h::{'input[type=number]'}(
				[
					'name'  => 'general[expiration]',
					'value' => $module_data->expiration,
					'min'   => 1
				]
			).$L->seconds
		]
	).
	h::{'p.cs-left a.uk-button'}(
		[
			$L->add_client,
			[
				'href' => 'admin/OAuth2/clients/add'
			]
		]
	)
);
