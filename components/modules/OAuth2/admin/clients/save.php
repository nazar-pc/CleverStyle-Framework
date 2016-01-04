<?php
/**
 * @package   OAuth2
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\OAuth2;
use
	cs\Config,
	cs\Language,
	cs\Page;

$L      = Language::instance();
$Page   = Page::instance();
$OAuth2 = OAuth2::instance();
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'add':
			if ($OAuth2->add_client($_POST['name'], $_POST['domain'], $_POST['active'])) {
				$Page->success($L->changes_saved);
			} else {
				$Page->warning($L->changes_save_error);
			}
			break;
		case 'edit':
			if ($OAuth2->set_client($_POST['id'], $_POST['secret'], $_POST['name'], $_POST['domain'], $_POST['active'])) {
				$Page->success($L->changes_saved);
			} else {
				$Page->warning($L->changes_save_error);
			}
			break;
		case 'delete':
			if ($OAuth2->del_client($_POST['id'])) {
				$Page->success($L->changes_saved);
			} else {
				$Page->warning($L->changes_save_error);
			}
	}
}
if (isset($_POST['general'])) {
	if (Config::instance()->module('OAuth2')->set($_POST['general'])) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}
