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
	cs\Config,
	cs\Index,
	cs\Language\Prefix,
	cs\Page;

$Index       = Index::instance();
$L           = new Prefix('composer_');
$module_data = Config::instance()->module('Composer');

if (isset($_POST['auth_json'])) {
	$module_data->auth_json = $_POST['auth_json'];
	$Index->save(true);
}

$Index->content(
	h::p($L->auth_json_contents).
	h::{'p textarea[name=auth_json]'}($module_data->auth_json ?: '')
);
