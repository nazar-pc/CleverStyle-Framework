<?php
/**
 * @package		TinyMCE
 * @category	plugins
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU Lesser General Public License 2.1, see license.txt
 */
namespace cs\plugins\TinyMCE;
global $Core, $Page, $Config;
if (!$Page->interface) {
	return;
}
if (!$Config->core['cache_compress_js_css']) {
	$Page->js([
		'components/plugins/TinyMCE/tinymce.min.js',
		'components/plugins/TinyMCE/TinyMCE.js'
	]);
} elseif (!file_exists(PCACHE.'/plugin.TinyMCE.js')) {
	rebuild_pcache();
}

$Core->register_trigger(
	'admin/System/components/plugins/disable',
	function ($data) {
		if ($data['name'] == 'TinyMCE') {
			clean_pcache();
		}
	}
);
$Core->register_trigger(
	'admin/System/general/optimization/clean_pcache',
	function () {
		clean_pcache();
	}
);
$Core->register_trigger(
	'System/Page/rebuild_cache',
	function ($data) {
		rebuild_pcache($data);
	}
);
function clean_pcache () {
	if (file_exists(PCACHE.'/plugin.TinyMCE.js')) {
		unlink(PCACHE.'/plugin.TinyMCE.js');
	}
}
function rebuild_pcache (&$data = null) {
	global $Config;
	if (
		!$Config->core['cache_compress_js_css'] ||
		(
			$data !== null && !in_array('TinyMCE', $Config->components['plugins'])
		) ||
		file_exists(PCACHE.'/plugin.TinyMCE.js')
	) {
		return;
	}
	$files		= [];
	$content	= '';
	array_map(
		function ($language) use (&$files, &$content) {
			$files[]	= "langs/$language";
			$content	.= file_get_contents(PLUGINS."/TinyMCE/langs/$language.js");
		},
		_mb_substr(get_files_list(PLUGINS.'/TinyMCE/langs', false, 'f'), 0, -3)
	);
	file_put_contents(
		PCACHE.'/plugin.TinyMCE.js',
		$key	= gzencode(
			file_get_contents(PLUGINS.'/TinyMCE/tinymce.min.js').
			$content.
			'tinymce.each("' . implode(',', $files) . '".split(","),function(f){tinymce.ScriptLoader.markDone(tinyMCE.baseURL+"/"+f+".js");});'.
			file_get_contents(PLUGINS.'/TinyMCE/TinyMCE.js'),
			9
		),
		LOCK_EX | FILE_BINARY
	);
	if ($data !== null) {
		$data['key']	.= md5($key);
	}
}