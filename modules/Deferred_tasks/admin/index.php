<?php
/**
 * @package  Deferred tasks
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs;
use
	h,
	cs\Language\Prefix;

$Config      = Config::instance();
$L           = new Prefix('deferred_tasks_');
$Page        = Page::instance();
$module_data = $Config->module('Deferred_tasks');
if (isset($_POST['general'])) {
	$module_data->set($_POST['general']);
	$Page->success($L->changes_saved);
}

$core_url = $Config->core_url();
$Page->content(
	h::{'cs-form form'}(
		h::{'label info'}('deferred_tasks_security_key').
		h::{'cs-input-text[full-width] input[name=general[security_key]]'}(
			[
				'value' => $module_data->security_key
			]
		).
		h::{'label info'}('deferred_tasks_max_number_of_workers').
		h::{'cs-input-text[full-width] input[type=number][min=1][name=general[max_number_of_workers]]'}(
			[
				'value' => $module_data->max_number_of_workers
			]
		).
		h::br(2).
		h::label($L->insert_line_into_cron).
		h::{'cs-input-text[full-width] input[readonly]'}(
			[
				'value' => "* * * * wget -O /dev/null $core_url/Deferred_tasks/$module_data->security_key"
			]
		).
		h::label($L->or_use_online_services).
		h::{'cs-input-text[full-width] input[readonly]'}(
			[
				'value' => "$core_url/Deferred_tasks/$module_data->security_key"
			]
		).
		h::{'p cs-button'}(
			h::{'button[type=submit]'}($L->save),
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
