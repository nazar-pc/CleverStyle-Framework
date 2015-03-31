<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
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
	h::{'cs-table[center][list][with-header]'}(
		h::{'cs-table-row cs-table-cell'}([
			$L->key,
			$L->title,
			$L->type,
			$L->action
		]).
		h::{'cs-table-row| cs-table-cell'}(array_map(
			function ($item) use ($L) {
				return [
					$item['key'],
					$item['title'],
					$item['type'],
					h::{'button.uk-button.cs-content-edit'}(
						$L->edit,
						[
							'data-key' => $item['key']
						]
					).
					h::{'button.uk-button.cs-content-delete'}(
						$L->delete,
						[
							'data-key' => $item['key']
						]
					)
				];
			},
			$all_items
		) ?: false)
	).
	h::{'p button.uk-button.cs-content-add'}($L->add).
	h::{'cs-table[right-left] cs-table-row cs-table-cell'}(
		h::info('content_simple_insert'),
		h::radio([
			'name'    => 'simple_insert',
			'value'   => [0, 1],
			'in'      => [$L->no, $L->yes],
			'checked' => $module_data->simple_insert
		])
	)
);
