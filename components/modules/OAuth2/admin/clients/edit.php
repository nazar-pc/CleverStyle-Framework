<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\modules\OAuth2;
use
	h,
	cs\Index,
	cs\Language\Prefix,
	cs\Page,
	cs\Route;
$Index  = Index::instance();
$L      = new Prefix('oauth2_');
$client = OAuth2::instance()->get_client(Route::instance()->route[2]);
Page::instance()->title($L->editing_of_client($client['name']));
$Index->cancel_button_back = true;
$Index->action             = 'admin/OAuth2/clients/list';
$Index->content(
	h::{'h2.cs-text-center'}(
		$L->editing_of_client($client['name'])
	).
	h::{'table.cs-table[right-left] tr| td'}(
		[
			$L->client_name,
			h::{'input[name=name]'}(
				[
					'value' => $client['name']
				]
			)
		],
		[
			'client_secret',
			h::{'input[name=secret]'}(
				[
					'value' => $client['secret']
				]
			)
		],
		[
			$L->client_domain,
			h::{'input[name=domain]'}(
				[
					'value' => $client['domain']
				]
			)
		],
		[
			$L->active,
			h::{'radio[name=active]'}(
				[
					'checked' => $client['active'],
					'value'   => [0, 1],
					'in'      => [$L->no, $L->yes]
				]
			)
		]
	).
	h::{'input[type=hidden][name=id]'}(
		[
			'value' => $client['id']
		]
	).
	h::{'input[type=hidden][name=mode][value=edit]'}()
);
