<?php
/**
 * @package    CleverStyle Framework
 * @subpackage Test
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2016, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;

/**
 * Hack: HHVM doesn't have ENT_DISALLOWED constant unfortunately, remove when https://github.com/facebook/hhvm/issues/4938 resolved
 */
defined('ENT_DISALLOWED') || define('ENT_DISALLOWED', 128);

if (!defined('CORE')) {
	define('CORE', realpath(__DIR__.'/../core'));
}
require_once __DIR__.'/code_coverage.php';

require_once __DIR__.'/../core/traits/Singleton/Base.php';
require_once __DIR__.'/Singleton.php';
require_once __DIR__.'/Mock_object.php';
require_once __DIR__.'/functions.php';
require_once __DIR__.'/../core/thirdparty/upf.php';
require_once __DIR__.'/../core/thirdparty/cli/cli.php';
require_once __DIR__.'/../core/functions.php';

if (!defined('DEBUG')) {
	define('DEBUG', false);
}
