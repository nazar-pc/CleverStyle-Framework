<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs;

$Config                 = Config::instance();
$core                   = &$Config->core;
$core['db_mirror_mode'] = $core['maindb_for_write'];
unset(
	$core['maindb_for_write']
);
$Config->save();
