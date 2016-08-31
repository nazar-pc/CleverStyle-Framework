<?php
/**
 * @package   CleverStyle Framework
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

$Config = Config::instance();
$cdb    = DB::instance()->db_prime(
	$Config->module('System')->db('texts')
);
$cdb->q(
	"UPDATE `[prefix]texts`
	SET `label` = 'site_name'
	WHERE
		`label` = 'name' AND
		`group`	= 'System/Config/core'"
);
$Config->core['site_name'] = $Config->core['name'];
$Config->save();
