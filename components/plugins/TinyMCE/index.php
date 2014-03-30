<?php
/**
 * @package		TinyMCE
 * @category	plugins
 * @author		Moxiecode Systems AB
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	Moxiecode Systems AB
 * @license		GNU Lesser General Public License 2.1, see license.txt
 */
namespace	cs\plugins\TinyMCE;
use			cs\Config,
			cs\Page;
$Page	= Page::instance();
if (!$Page->interface) {
	return;
}
if (!Config::instance()->core['cache_compress_js_css']) {
	$Page->js([
		'components/plugins/TinyMCE/includes/js/tinymce.min.js',
		'components/plugins/TinyMCE/includes/js/z.integration.js'
	]);
}
