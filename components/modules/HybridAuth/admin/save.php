<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs;
if (!isset($_POST['edit_settings'])) {
	return;
}
switch ($_POST['edit_settings']) {
	case 'save':
		Index::instance()->save(Config::instance()->module('HybridAuth')->set([
			'providers'					=> $_POST['providers'],
			'enable_contacts_detection'	=> $_POST['enable_contacts_detection']
		]));
	break;
}