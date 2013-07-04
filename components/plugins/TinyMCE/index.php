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
global $Page, $Config;
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
