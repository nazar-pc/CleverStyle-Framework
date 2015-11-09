<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2012-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace	cs;
if (isset($_POST['save'])) {
	Index::instance()->save(Config::instance()->module('HybridAuth')->set([
		'providers'					=> $_POST['providers'],
		'enable_contacts_detection'	=> $_POST['enable_contacts_detection']
	]));
}
