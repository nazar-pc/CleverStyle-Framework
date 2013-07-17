<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
namespace	cs;
use			h;
$Config	= Config::instance();
if (
	isset($_POST['edit_settings'], $_POST['max_file_size']) &&
	$_POST['edit_settings'] == 'save'
) {
	$Config->module('Plupload')->max_file_size		= xap($_POST['max_file_size']);
	$Config->module('Plupload')->confirmation_time	= (int)$_POST['confirmation_time'];
	Index::instance()->save(true);
}
Page::instance()->menumore		= h::a(
	[
		'Plupload',
		[
			'href'	=> 'admin/Plupload',
			'class'	=> 'active'
		]
	]
);