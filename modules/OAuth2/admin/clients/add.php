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
	cs\Page;

$L = new Prefix('oauth2_');
Page::instance()
	->title($L->addition_of_client)
	->content(
		h::{'cs-form form[action=admin/OAuth2/clients/list]'}(
			h::{'h2.cs-text-center'}(
				$L->addition_of_client
			).
			h::label($L->client_name).
			h::{'cs-input-text input[name=name]'}().
			h::label($L->client_domain).
			h::{'cs-input-text input[name=domain]'}().
			h::label($L->active).
			h::{'div radio[name=active][checked=1]'}(
				[
					'value' => [0, 1],
					'in'    => [$L->no, $L->yes]
				]
			).
			h::p(
				h::cs_button(
					h::{'button[type=submit][name=mode][value=add]'}($L->save),
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
