<?php
/**
 * @package		Plupload
 * @category	modules
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU GPL v2, see license.txt
 */
global $Core;
$Core->register_trigger(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] == 'Plupload') {
			global $Config;
			$Config->module('Plupload')->max_file_size		= '5mb';
			$Config->module('Plupload')->confirmation_time	= '900';
		}
	}
);