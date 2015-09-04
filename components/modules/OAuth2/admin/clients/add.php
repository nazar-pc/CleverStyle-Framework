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
	cs\Index,
	cs\Language\Prefix,
	cs\Page;
$Index = Index::instance();
$L     = new Prefix('oauth2_');
Page::instance()->title($L->addition_of_client);
$Index->cancel_button_back = true;
$Index->action             = 'admin/OAuth2/clients/list';
$Index->content(
	h::{'h2.cs-text-center'}(
		$L->addition_of_client
	).
	h::label($L->client_name).
	h::{'input[is=cs-input-text][name=name]'}().
	h::label($L->client_domain).
	h::{'input[is=cs-input-text][name=domain]'}().
	h::label($L->active).
	h::{'div radio[name=active][checked=1]'}(
		[
			'value' => [0, 1],
			'in'    => [$L->no, $L->yes]
		]
	).
	h::{'input[type=hidden][name=mode][value=add]'}()
);
