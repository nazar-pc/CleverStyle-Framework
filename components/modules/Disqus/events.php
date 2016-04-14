<?php
/**
 * @package   Comments
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Disqus;
use
	cs\Config,
	cs\Event;

Event::instance()->on(
	'Comments/instance',
	function ($data) {
		if (!Config::instance()->module('Disqus')->enabled()) {
			return true;
		}
		$data['Comments'] = Disqus::instance();
		return false;
	}
);
