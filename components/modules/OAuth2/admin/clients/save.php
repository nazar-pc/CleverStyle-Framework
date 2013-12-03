<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) Nazar Mokrynskyi, 2011—2013
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use			cs\Config,
			cs\Index;
$OAuth2	= OAuth2::instance();
$Index	= Index::instance();
if (isset($_POST['mode'])) {
	switch ($_POST['mode']) {
		case 'add':
			$Index->save((bool)$OAuth2->oauth2_add_client($_POST['name'], $_POST['domain'], $_POST['active']));
		break;
		case 'edit':
			$Index->save($OAuth2->set_client($_POST['id'], $_POST['secret'], $_POST['name'], $_POST['domain'], $_POST['active']));
		break;
		case 'delete':
			$Index->save($OAuth2->del_client($_POST['id']));
	}
}
if (isset($_POST['general'])) {
	$Index->save(
		Config::instance()->module('OAuth2')->set($_POST['general'])
	);
}