<?php
/**
 * @package		Blogs
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012 by Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Blogs;
use			\h;
global $Core, $Index, $Config, $L;
$Index->title_auto	= false;
$rc					= &$Config->routing['current'];
if (!isset($rc[0])) {
	$rc[0]	= 'latest_posts';
}
switch ($rc[0]) {
	case path($L->latest_posts):
		$rc[0]	= 'latest_posts';
	break;
	case path($L->section):
		$rc[0]	= 'section';
	break;
	case path($L->tag):
		$rc[0]	= 'tag';
	break;
	case path($L->new_post):
		$rc[0]	= 'new_post';
	break;
	default:
		if (mb_strpos($rc[0], ':')) {
			array_unshift($rc, 'post');
		} else {
			define('ERROR_PAGE', 404);
			return;
		}
	break;
	case 'latest_posts':
	case 'section':
	case 'tag':
	case 'new_post':
}
include_once MFOLDER.'/class.php';
$Core->create('cs\\modules\\Blogs\\Blogs');