<?php
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
}