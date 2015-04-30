<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Config,
	cs\Index;

if (!isset($_POST['mode'])) {
	return;
}
$Index    = Index::instance();
$Posts    = Posts::instance();
$Sections = Sections::instance();
switch ($_POST['mode']) {
	case 'add_section':
		$Index->save($Sections->add($_POST['parent'], $_POST['title'], isset($_POST['path']) ? $_POST['path'] : null));
		break;
	case 'edit_section':
		$Index->save($Sections->set($_POST['id'], $_POST['parent'], $_POST['title'], isset($_POST['path']) ? $_POST['path'] : null));
		break;
	case 'delete_section':
		$Index->save($Sections->del($_POST['id']));
		break;
	case 'delete_post':
		$Index->save($Posts->del($_POST['id']));
		break;
	case 'general':
		$Index->save(Config::instance()->module('Blogs')->set($_POST['general']));
		break;
}
