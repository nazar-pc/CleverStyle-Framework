<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Config	= Config::instance();
$L		= Language::instance();
Index::instance()->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}([
		[
			h::info('key_expire'),
			h::{'input[type=number]'}([
				'name'			=> 'core[key_expire]',
				'value'			=> $Config->core['key_expire'],
				'min'			=> 1
			]).
			$L->seconds
		],
		[
			h::info('ip_black_list'),
			h::textarea(
				$Config->core['ip_black_list'],
				[
					'name' => 'core[ip_black_list]'
				]
			)
		],
		[
			h::info('ip_admin_list_only'),
			h::radio([
				'name'			=> 'core[ip_admin_list_only]',
				'checked'		=> $Config->core['ip_admin_list_only'],
				'value'			=> [0, 1],
				'in'			=> [$L->off, $L->on]
			])
		],
		[
			h::info('ip_admin_list'),
			h::textarea(
				$Config->core['ip_admin_list'],
				[
					'name' => 'core[ip_admin_list]'
				]
			).
			h::br().
			$L->current_ip.': '.h::b(User::instance()->ip)
		]
	])
);
