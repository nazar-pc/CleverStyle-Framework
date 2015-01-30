<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Supports next events:
 *  Comments/instance
 *  [
 *   'Comments'		=> <i>&$Comments</i>
 *  ]
 */
namespace	cs\modules\Comments;
use
	cs\Event;
Event::instance()->on(
	'Comments/instance',
	function ($data) {
		$data['Comments']	= Comments::instance();
		return false;
	}
);
