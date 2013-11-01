<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
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
	h::{'p.lead.cs-center'}(
		$L->addition_of_client
	).
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr'}(
		h::th($L->client_name).
		h::{'td input[name=name]'}(),
		h::th($L->client_domain).
		h::{'td input[name=domain]'}(),
		h::th($L->active).
		h::{'td input[type=radio][name=active][checked=1]'}([
			'value'		=> [0, 1],
			'in'		=> [$L->no, $L->yes]
		])
	).
	h::{'input[type=hidden][name=mode][value=add]'}()
);