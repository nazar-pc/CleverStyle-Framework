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
if (
	isset($_POST['edit_settings'], $_POST['max_file_size']) &&
	$_POST['edit_settings'] == 'save'
) {
	$module_data					= Config::instance()->module('Plupload');
	$module_data->max_file_size		= xap($_POST['max_file_size']);
	$module_data->confirmation_time	= (int)$_POST['confirmation_time'];
	Index::instance()->save(true);
}
Page::instance()->main_sub_menu	= h::{'li.uk-active a'}(
	'Plupload',
	[
		'href'	=> 'admin/Plupload'
	]
);