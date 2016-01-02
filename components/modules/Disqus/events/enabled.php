<?php
/**
 * @package		Disqus
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013-2016, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Supports next events:
 *  Comments/instance
 *  [
 *   'Comments'		=> <i>&$Comments</i>
 *  ]
 */
namespace	cs\modules\Disqus;
use
	cs\Event;
Event::instance()->on(
	'Comments/instance',
	function ($data) {
		$data['Comments']	= Disqus::instance();
		return false;
	}
);
