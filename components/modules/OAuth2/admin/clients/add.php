<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */

namespace	cs\modules\OAuth2;
use			h,
			cs\Index,
			cs\Language\Prefix,
			cs\Page;
$Index						= Index::instance();
$L							= new Prefix('oauth2_');
Page::instance()->title($L->addition_of_client);
$Index->apply_button		= false;
$Index->cancel_button_back	= true;
$Index->action				= 'admin/OAuth2/clients/list';
$Index->content(
	h::{'h2d.cs-center'}(
		$L->addition_of_client
	).
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			$L->client_name,
			h::{'input[name=name]'}()
		],
		[
			$L->client_domain,
			h::{'input[name=domain]'}()
		],
		[
			$L->active,
			h::{'cs-table-cell radio[name=active][checked=1]'}([
				'value'		=> [0, 1],
				'in'		=> [$L->no, $L->yes]
			])
		]
	).
	h::{'input[type=hidden][name=mode][value=add]'}()
);
