<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
if (!isset($_POST['edit_settings'])) {
	return;
}
global $Index, $Blogs;
switch ($_POST['edit_settings']) {
	case 'save':
		global $Config;
		$Index->save($Config->module(MODULE)->set([
			'providers'					=> $_POST['providers'],
			'enable_contacts_detection'	=> $_POST['enable_contacts_detection']
		]));
	break;
}