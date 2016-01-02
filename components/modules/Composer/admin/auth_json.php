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
	cs\Config,
	cs\Index,
	cs\Language\Prefix;

$Index       = Index::instance();
$L           = new Prefix('composer_');
$module_data = Config::instance()->module('Composer');

if (isset($_POST['auth_json'])) {
	$module_data->auth_json = $_POST['auth_json'];
	$Index->save(true);
}

$Index->content(
	h::p($L->auth_json_contents).
	h::{'p textarea[is=cs-textarea][autosize][name=auth_json]'}($module_data->auth_json ?: '')
);
