<?php
/**
 * @package   Content
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Content;

use
	h,
	cs\Config,
	cs\Language\Prefix,
	cs\Page;

$L           = new Prefix('content_');
$Page        = Page::instance();
$Content     = Content::instance();
$all_items   = $Content->get($Content->get_all());
$module_data = Config::instance()->module('Content');

if (isset($_POST['simple_insert'])) {
	$module_data->simple_insert = $_POST['simple_insert'];
	$Page->success($L->changes_saved);
}
$Page->content(
	h::{'cs-form form'}(
		h::{'table.cs-table[center][list]'}(
			h::{'tr th'}(
				[
					$L->key,
					$L->title,
					$L->type,
					$L->action
				]
			).
			h::{'tr| td'}(
				array_map(
					function ($item) use ($L) {
						return [
							$item['key'],
							$item['title'],
							$item['type'],
							h::{'cs-button button.cs-content-edit'}(
								$L->edit,
								[
									'data-key' => $item['key']
								]
							).
							h::{'cs-button button.cs-content-delete'}(
								$L->delete,
								[
									'data-key' => $item['key']
								]
							)
						];
					},
					$all_items
				) ?: false
			)
		).
		h::{'p cs-button button.cs-content-add'}($L->add).
		h::{'label info'}('content_simple_insert').
		h::{'div radio'}(
			[
				'name'    => 'simple_insert',
				'value'   => [0, 1],
				'in'      => [$L->no, $L->yes],
				'checked' => $module_data->simple_insert
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
