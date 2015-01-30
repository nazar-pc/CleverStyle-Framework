<?php
/**
 * @package        Content
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */

namespace cs\modules\Content;

use
	cs\Config,
	cs\Event,
	cs\Page;

Event::instance()->on(
	'System/Page/display',
	function () {
		$module_data = Config::instance()->module('Content');
		if ($module_data->simple_insert && $module_data->active()) {
			$Page          = Page::instance();
			$Page->Content = preg_replace_callback(
				'/{(Content|Content_title):(.+)}/Uims',
				function ($match) {
					$content = Content::instance()->get($match[2]);
					return $content[$match[1] == 'Content' ? 'content' : 'title'];
				},
				$Page->Content
			);
		}
	}
);
