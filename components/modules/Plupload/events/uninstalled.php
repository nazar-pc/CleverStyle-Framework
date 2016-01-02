<?php
/**
 * @package   Plupload
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license   GNU GPL v2, see license.txt
 */
namespace cs;
Event::instance()->on(
	'admin/System/components/modules/install/after',
	function ($data) {
		if ($data['name'] == 'Plupload') {
			$Config                                        = Config::instance();
			$Config->module('Plupload')->max_file_size     = '5mb';
			$Config->module('Plupload')->confirmation_time = '900';
		}
	}
);
