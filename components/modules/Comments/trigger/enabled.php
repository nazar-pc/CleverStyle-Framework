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
use			cs\Config,
			cs\Page,
			cs\Trigger;
Trigger::instance()->register(
	'admin/System/components/modules/disable',
	function ($data) {
		if ($data['name'] == 'Comments') {
			clean_pcache();
		}
	}
);
Trigger::instance()->register(
	'admin/System/general/optimization/clean_pcache',
	function () {
		clean_pcache();
	}
);
Trigger::instance()->register(
	'System/Page/rebuild_cache',
	function ($data) {
		if (file_exists(PCACHE.'/module.Comments.js') && file_exists(PCACHE.'/module.Comments.css')) {
			return;
		}
		rebuild_pcache($data);
	}
);
function clean_pcache () {
	if (file_exists(PCACHE.'/module.Comments.js')) {
		unlink(PCACHE.'/module.Comments.js');
	}
	if (file_exists(PCACHE.'/module.Comments.css')) {
		unlink(PCACHE.'/module.Comments.css');
	}
}
function rebuild_pcache (&$data = null) {
	$key	= [];
	file_put_contents(
		PCACHE.'/module.Comments.js',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/Comments/includes/js/general.js'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	file_put_contents(
		PCACHE.'/module.Comments.css',
		$key[]	= gzencode(
			file_get_contents(MODULES.'/Comments/includes/css/general.css'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5(implode('', $key));
	}
}
Trigger::instance()->register(
	'System/Page/pre_display',
	function () {
		if (!Config::instance()->core['cache_compress_js_css']) {
			Page::instance()->css(
				'components/modules/Comments/includes/css/general.css'
			)->js(
				'components/modules/Comments/includes/js/general.js'
			);
		} elseif (!(
			file_exists(PCACHE.'/module.Comments.js') && file_exists(PCACHE.'/module.Comments.css')
		)) {
			rebuild_pcache();
		}
	}
);
Trigger::instance()->register(
	'Comments/instance',
	function ($data) {
		$data['Comments']	= Comments::instance();
	}
);