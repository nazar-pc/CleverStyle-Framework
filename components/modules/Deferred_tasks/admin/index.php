<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h,
			cs\Language\Prefix;
$Config					= Config::instance();
$module_data			= $Config->module('Deferred_tasks');
$Index					= Index::instance();
$Index->apply_button	= false;
$L						= new Prefix('deferred_tasks_');
$Index->content(
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr| td'}(
		[
			h::info('deferred_tasks_security_key'),
			h::{'input[name=general[security_key]]'}([
				'value'		=> $module_data->security_key
			])
		],
		[
			h::info('deferred_tasks_number_of_workers'),
			h::{'input[type=number][min=1][name=general[number_of_workers]]'}([
				'value'		=> $module_data->number_of_workers
			])
		]
	).
	h::p(
		$L->insert_line_into_cron,
		h::{'textarea[disabled]'}("* * * * wget -O /dev/null {$Config->base_url()}/Deferred_tasks/$module_data->security_key"),
		$L->or_use_online_services.
		h::{'textarea[disabled]'}($Config->base_url()."/Deferred_tasks/$module_data->security_key")
	)
);