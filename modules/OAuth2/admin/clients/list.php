<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\OAuth2;
use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page;

$L           = new Prefix('oauth2_');
$module_data = Config::instance()->module('OAuth2');
Page::instance()
	->title($L->list_of_client)
	->content(
		h::{'h2.cs-text-center'}(
			$L->list_of_clients
		).
		h::{'table.cs-table[list][center]'}(
			h::{'tr th'}(
				$L->client_name,
				'client_id',
				'client_secret',
				$L->action
			).
			h::{'tr| td'}(
				array_map(
					function ($client) use ($L) {
						return [
							[
								$client['name'],
								h::{'input[is=cs-input-text][full-width][readonly]'}(
									[
										'value' => $client['id']
									]
								),
								h::{'input[is=cs-input-text][full-width][readonly]'}(
									[
										'value' => $client['secret']
									]
								),
								h::{'a[is=cs-link-button][icon=pencil]'}(
									[
										'href'    => "admin/OAuth2/clients/edit/$client[id]",
										'tooltip' => $L->edit
									]
								).
								h::{'a[is=cs-link-button][icon=trash]'}(
									[
										'href'    => "admin/OAuth2/clients/delete/$client[id]",
										'tooltip' => $L->delete
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
		h::{'table.cs-table[right-left] tr| td'}(
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
				h::{'input[is=cs-input-text][compact][type=number]'}(
					[
						'name'  => 'general[expiration]',
						'value' => $module_data->expiration,
						'min'   => 1
					]
				).$L->seconds
			]
		).
		h::{'p.cs-text-left a[is=cs-link-button]'}(
			$L->add_client,
			[
				'href' => 'admin/OAuth2/clients/add'
			]
		)
	);
