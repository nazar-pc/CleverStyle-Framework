<?php
/**
 * @package   Composer
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Composer;
use
	h,
	cs\Language\Prefix,
	cs\Page;

$Page = Page::instance();
$L    = new Prefix('composer_');
if (file_exists(DIR.'/storage/Composer/last_execution.log')) {
	require_once __DIR__.'/../ansispan.php';
	$Page->content(
		h::p($L->last_log).
		h::pre(
			ansispan(file_get_contents(DIR.'/storage/Composer/last_execution.log')),
			[
				'style' => 'background: #1a1a1a'
			]
		)
	);
}
$Page->content(
	h::{'p.cs-text-center button.cs-composer-admin-force-update[is=cs-button]'}($L->force_update)
);
