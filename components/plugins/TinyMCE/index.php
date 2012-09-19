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
$plugin = basename(__DIR__);
if (!$Config->core['cache_compress_js_css']) {
	$Page->js([
		'components/plugins/'.$plugin.'/jquery.tinymce.js',
		'components/plugins/'.$plugin.'/tiny_mce.js',
		'components/plugins/'.$plugin.'/TinyMCE.js'
	]);
} elseif (!file_exists(PCACHE.'/plugin.'.$plugin.'.js')) {
	rebuild_pcache();
}
