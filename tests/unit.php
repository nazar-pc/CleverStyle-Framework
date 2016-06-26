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
require_once __DIR__.'/functions.php';
require_once __DIR__.'/../core/thirdparty/upf.php';
require_once __DIR__.'/../core/functions.php';

if (!defined('DEBUG')) {
	define('DEBUG', false);
}

require_once __DIR__.'/code_coverage.php';
