<?php
/**
 * @package   Deferred tasks
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h,
	cs\Language\Prefix;
include __DIR__.'/save.php';
$Config      = Config::instance();
$module_data = $Config->module('Deferred_tasks');
$Index       = Index::instance();
$L           = new Prefix('deferred_tasks_');
$base_url    = $Config->base_url();
$Index->content(
	h::{'cs-table[right-left] cs-table-row| cs-table-cell'}(
		[
			h::info('deferred_tasks_security_key'),
			h::{'input[name=general[security_key]]'}(
				[
					'value' => $module_data->security_key
				]
			)
		],
		[
			h::info('deferred_tasks_max_number_of_workers'),
			h::{'input[type=number][min=1][name=general[max_number_of_workers]]'}(
				[
					'value' => $module_data->max_number_of_workers
				]
			)
		]
	).
	h::p(
		$L->insert_line_into_cron,
		h::{'input[readonly]'}(
			[
				'value' => "* * * * wget -O /dev/null $base_url/Deferred_tasks/$module_data->security_key"
			]
		),
		$L->or_use_online_services,
		h::{'input[readonly]'}(
			[
				'value' => "$base_url/Deferred_tasks/$module_data->security_key"
			]
		)
	)
);
