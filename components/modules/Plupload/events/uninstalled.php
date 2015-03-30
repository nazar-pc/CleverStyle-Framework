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
Event::instance()->on(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] == 'Plupload') {
			$Config											= Config::instance();
			$Config->module('Plupload')->max_file_size		= '5mb';
			$Config->module('Plupload')->confirmation_time	= '900';
		}
	}
);
