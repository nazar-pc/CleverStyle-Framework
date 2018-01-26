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
								h::{'cs-input-text[full-width] input[readonly]'}(
									[
										'value' => $client['id']
									]
								),
								h::{'cs-input-text[full-width] input[readonly]'}(
									[
										'value' => $client['secret']
									]
								),
								h::{'cs-link-button[icon=pencil-alt]'}(
									h::a(
										[
											'href' => "admin/OAuth2/clients/edit/$client[id]"
										]
									),
									[
										'tooltip' => $L->edit
									]
								).
								h::{'cs-link-button[icon=trash-alt]'}(
									h::a(
										[
											'href' => "admin/OAuth2/clients/delete/$client[id]"
										]
									),
									[
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
				h::{'cs-input-text[compact] input[type=number]'}(
					[
						'name'  => 'general[expiration]',
						'value' => $module_data->expiration,
						'min'   => 1
					]
				).$L->seconds
			]
		).
		h::{'p.cs-text-left cs-link-button a'}(
			$L->add_client,
			[
				'href' => 'admin/OAuth2/clients/add'
			]
		)
	);
