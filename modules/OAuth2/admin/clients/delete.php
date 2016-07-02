<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
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
	->title($L->deletion_of_client($client['name']))
	->content(
		h::{'form[is=cs-form][action=admin/OAuth2/clients/list]'}(
			h::{'h2.cs-text-center'}(
				$L->sure_to_delete_client($client['name'])
			).
			h::{'input[type=hidden][name=id]'}(
				[
					'value' => $client['id']
				]
			).
			h::p(
				h::{'button[is=cs-button][type=submit][name=mode][value=delete]'}(
					$L->yes
				).
				h::{'button[is=cs-button][type=button]'}(
					$L->cancel,
					[
						'onclick' => 'history.go(-1);'
					]
				)
			)
		)
	);
