<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	h,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;
$Index          = Index::instance();
$Index->buttons = false;
$L              = new Prefix('composer_');
if (file_exists(DIR.'/storage/Composer/last_execution.log')) {
	require_once __DIR__.'/../ansispan.php';
	$Index->content(
		h::p($L->last_log).
		h::pre(
			ansispan(file_get_contents(DIR.'/storage/Composer/last_execution.log')),
			[
				'style' => 'background: #1a1a1a'
			]
		)
	);
}
Page::instance()->config(
	[
		'force' => true
	],
	'cs.composer'
);
$Index->content(
	h::{'p.cs-center button.cs-composer-admin-force-update[is=cs-button]'}($L->force_update)
);
