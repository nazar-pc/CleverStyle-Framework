<?php
/**
 * @package		Comments
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Supports next triggers:
 *  Comments/instance
 *  [
 *   'Comments'		=> <i>&$Comments</i>
 *  ]
 */
namespace	cs\modules\Comments;
use			cs\Trigger;
Trigger::instance()->register(
	'Comments/instance',
	function ($data) {
		$data['Comments']	= Comments::instance();
		return false;
	}
);