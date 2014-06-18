<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use
	h,
	cs\Config,
	cs\Index,
	cs\Language\Prefix;

$Index               = Index::instance();
$L                   = new Prefix('content_');
$Content             = Content::instance();
$Index->apply_button = false;
$all_items           = $Content->get($Content->get_all());
$module_data         = Config::instance()->module('Content');

if (isset($_POST['simple_insert'])) {
	$module_data->simple_insert = $_POST['simple_insert'];
	$Index->save(true);
}

$Index->content(
	h::{'table.cs-center-all.cs-table'}(
		h::{'thead tr th'}(
			$L->key,
			$L->title,
			$L->type,
			$L->action
		).
		h::{'tbody tr| td'}(array_map(
			function ($item) use ($L, $Index) {
				return [
					$item['key'],
					$item['title'],
					$item['type'],
					h::{'button.cs-content-edit'}(
						$L->edit,
						[
							'data-key' => $item['key']
						]
					).
					h::{'button.cs-content-delete'}(
						$L->delete,
						[
							'data-key' => $item['key']
						]
					)
				];
			},
			$all_items
		))
	).
	h::{'p button.cs-content-add'}($L->add).
	h::{'table.cs-table-borderless.cs-left-even.cs-right-odd tr td'}(
		h::info('content_simple_insert'),
		h::{'input[type=radio]'}([
			'name'    => 'simple_insert',
			'value'   => [0, 1],
			'in'      => [$L->no, $L->yes],
			'checked' => $module_data->simple_insert
		])
	)
);
