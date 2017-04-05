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
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$L      = new Prefix('oauth2_');
$client = OAuth2::instance()->get_client(Request::instance()->route[2]);
Page::instance()
	->title($L->editing_of_client($client['name']))
	->content(
		h::{'cs-form form[action=admin/OAuth2/clients/list]'}(
			h::{'h2.cs-text-center'}(
				$L->editing_of_client($client['name'])
			).
			h::label($L->client_name).
			h::{'cs-input-text input[name=name]'}(
				[
					'value' => $client['name']
				]
			).
			h::label('client_secret').
			h::{'cs-input-text input[name=secret]'}(
				[
					'value' => $client['secret']
				]
			).
			h::label($L->client_domain).
			h::{'cs-input-text input[name=domain]'}(
				[
					'value' => $client['domain']
				]
			).
			h::label($L->active).
			h::{'div radio[name=active]'}(
				[
					'checked' => $client['active'],
					'value'   => [0, 1],
					'in'      => [$L->no, $L->yes]
				]
			).
			h::{'input[type=hidden][name=id]'}(
				[
					'value' => $client['id']
				]
			).
			h::p(
				h::cs_button(
					h::{'button[type=submit][name=mode][value=edit]'}($L->save),
					[
						'tooltip' => $L->save_info
					]
				).
				h::{'cs-button button[type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
