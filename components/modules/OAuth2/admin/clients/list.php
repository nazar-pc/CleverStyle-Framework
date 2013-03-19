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
global $Index, $L, $Page, $OAuth2;
$Page->title($L->list_of_client);
$Index->buttons		= false;
$Index->content(
	h::{'p.ui-priority-primary.cs-state-messages.cs-center'}(
		$L->list_of_clients
	).
	h::{'table.cs-fullwidth-table.cs-center-all'}(
		h::{'th.ui-widget-header.ui-corner-all'}([
												 $L->name,
			'client_id',
			'client_secret',
			$L->actions
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
								'href'			=> 'admin/'.MODULE.'/clients/edit/'.$client['id'],
								'data-title'	=> $L->edit
							]
						]).
						h::{'a.cs-button-compact'}([
							h::icon('trash'),
							[
								'href'			=> 'admin/'.MODULE.'/clients/delete/'.$client['id'],
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
			'href'	=> 'admin/'.MODULE.'/clients/add'
		]
	])
);