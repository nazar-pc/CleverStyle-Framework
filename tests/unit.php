<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;

require_once __DIR__.'/../core/traits/Singleton/Base.php';
require_once __DIR__.'/Singleton.php';
require_once __DIR__.'/Mock_object.php';

$tmp = __DIR__.'/'.uniqid('.tmp', true);
/** @noinspection MkdirRaceConditionInspection */
@mkdir($tmp);
define('CACHE', $tmp);
/** @noinspection RealpathOnRelativePathsInspection */
define('DIR', realpath(__DIR__.'/..'));
define('ENGINES', DIR.'/core/engines');
define('MODULES', DIR.'/components/modules');
define('PLUGINS', DIR.'/components/plugins');

require_once __DIR__.'/../core/thirdparty/upf.php';
require_once __DIR__.'/../core/functions.php';

register_shutdown_function(
	function () use ($tmp) {
		exec("rm -rf ".escapeshellarg($tmp));
	}
);
