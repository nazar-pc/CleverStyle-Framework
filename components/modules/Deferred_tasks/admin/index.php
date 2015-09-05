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
$Config                       = Config::instance();
$module_data                  = $Config->module('Deferred_tasks');
$Index                        = Index::instance();
$L                            = new Prefix('deferred_tasks_');
$base_url                     = $Config->base_url();
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::{'label info'}('deferred_tasks_security_key').
	h::{'input[is=cs-input-text][full-width][name=general[security_key]]'}(
		[
			'value' => $module_data->security_key
		]
	).
	h::{'label info'}('deferred_tasks_max_number_of_workers').
	h::{'input[is=cs-input-text][full-width][type=number][min=1][name=general[max_number_of_workers]]'}(
		[
			'value' => $module_data->max_number_of_workers
		]
	).
	h::br(2).
	h::label($L->insert_line_into_cron).
	h::{'input[is=cs-input-text][full-width][readonly]'}(
		[
			'value' => "* * * * wget -O /dev/null $base_url/Deferred_tasks/$module_data->security_key"
		]
	).
	h::label($L->or_use_online_services).
	h::{'input[is=cs-input-text][full-width][readonly]'}(
		[
			'value' => "$base_url/Deferred_tasks/$module_data->security_key"
		]
	).
	h::br()
);
