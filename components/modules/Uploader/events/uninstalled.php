<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
Event::instance()->on(
	'admin/System/components/modules/install/after',
	function ($data) {
		if ($data['name'] == 'Uploader') {
			$Config                                        = Config::instance();
			$Config->module('Uploader')->max_file_size     = '5mb';
			$Config->module('Uploader')->confirmation_time = '900';
		}
	}
);
