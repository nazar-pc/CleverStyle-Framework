<?php
/**
 * @package		Disqus
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Supports next triggers:
 *  Comments/instance
 *  [
 *   'Comments'		=> <i>&$Comments</i>
 *  ]
 */
namespace	cs\modules\Disqus;
use			cs\Trigger;
Trigger::instance()->register(
	'Comments/instance',
	function ($data) {
		$data['Comments']	= Disqus::instance();
		return false;
	}
);
