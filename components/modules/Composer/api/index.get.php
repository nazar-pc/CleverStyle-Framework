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
	cs\ExitException,
	cs\Page,
	cs\User;
if (!User::instance()->admin()) {
	throw new ExitException(403);
}
$log_file = STORAGE.'/Composer/last_execution.log';
if (file_exists($log_file)) {
	require_once __DIR__.'/../ansispan.php';
	Page::instance()->json(
		ansispan(file_get_contents($log_file))
	);
}
