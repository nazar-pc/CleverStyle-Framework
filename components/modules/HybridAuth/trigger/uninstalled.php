<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs;
Trigger::instance()->register(
	'admin/System/components/modules/install/process',
	function ($data) {
		if ($data['name'] != 'HybridAuth') {
			return;
		}
		Config::instance()->module('HybridAuth')->providers	= [];
	}
);