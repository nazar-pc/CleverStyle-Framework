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
if (isset($_POST['force_update'])) {
	$result = Composer::instance()->force_update();
	$Page   = Page::instance();
	switch ($result['code']) {
		case 0:
			$Page->success($L->updated_successfully);
			break;
		case 1:
			$Page->warning($L->update_failed);
			break;
		case 2:
			$Page->warning($L->dependencies_conflict);
			break;
	}
}
if (file_exists(DIR.'/storage/Composer/last_execution.log')) {
	require_once __DIR__.'/../ansispan.php';
	$Index->content(
		h::p($L->last_log.':').
		h::pre(
			ansispan(file_get_contents(DIR.'/storage/Composer/last_execution.log')),
			[
				'style' => 'background:#1a1a1a'
			]
		)
	);
}
$Index->content(
	h::{'p.cs-center button.uk-button[type=submit][name=force_update]'}($L->force_update)
);
