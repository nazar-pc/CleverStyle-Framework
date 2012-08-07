<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2012, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Static_pages;
use			\h;
global $Core, $Index, $Config, $Static_pages;
include_once MFOLDER.'/class.php';
$Core->create('cs\\modules\\Static_pages\\Static_pages');
$Index->title_auto	= false;
$data				= $Static_pages->get(HOME ? $Static_pages->get_structure()['pages']['index'] : $Config->routing['current'][0]);
global $Page;
if ($data['interface']) {
	if (!HOME) {
		if (!empty($Index->title)) {
			foreach ($Index->title as $title) {
				$Page->title($title);
			}
			unset($title);
		}
		$Page->title($data['title']);
	}
	$Page->content($data['content']);
} else {
	interface_off();
	$Page->Content	= $data['content'];
}