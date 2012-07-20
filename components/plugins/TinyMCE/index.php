<?php
global $Page, $Config;
if (!$Page->interface) {
	return;
}
$plugin = basename(dirname(__FILE__));
if ($Config->core['cache_compress_js_css']) {
	if (file_exists(PCACHE.'/plugin.'.$plugin.'.js')) {
		return;
	}
	$languages = _mb_substr(get_files_list(PLUGINS.'/'.$plugin.'/langs', false, 'f'), 0, -3);
	$files[] = "tiny_mce";
	$content = file_get_contents(PLUGINS.'/'.$plugin.'/tiny_mce.js');
	foreach ($languages as $language) {
		$files[] = 'langs/'.$language;
		$content .= file_get_contents(PLUGINS.'/'.$plugin.'/langs/'.$language.'.js');
	}
	$plugins = get_files_list(PLUGINS.'/'.$plugin.'/plugins', false, 'd');
	foreach ($plugins as $plugin_tiny) {
		$files[] = 'plugins/'.$plugin_tiny.'/editor_plugin';
		$content .= file_get_contents(PLUGINS.'/'.$plugin.'/plugins/'.$plugin_tiny.'/editor_plugin.js');
		foreach ($languages as $language) {
			if (file_exists($file = PLUGINS.'/'.$plugin.'/plugins/'.$plugin_tiny.'/langs/'.$language.'.js')) {
				$files[] = 'plugins/'.$plugin_tiny.'/langs/'.$language;
				$content .= file_get_contents($file);
			}
		}
	}
	$themes = get_files_list(PLUGINS.'/'.$plugin.'/themes', false, 'd');
	foreach ($themes as $theme) {
		$files[] = 'themes/'.$theme.'/editor_template';
		$content .= file_get_contents(PLUGINS.'/'.$plugin.'/themes/'.$theme.'/editor_template.js');
		foreach ($languages as $language) {
			if (file_exists($file = PLUGINS.'/'.$plugin.'/themes/'.$theme.'/langs/'.$language.'.js')) {
				$files[] = 'themes/'.$theme.'/langs/'.$language;
				$content .= file_get_contents($file);
			}
		}
	}
	file_put_contents(
		PCACHE.'/plugin.'.$plugin.'.js',
		gzencode(
			'var tinyMCEPreInit={base:\'/components/plugins/'.$plugin.'\',suffix:\'\'};'.
			$content.
			'tinymce.each("' . implode(',', $files) . '".split(","),function(f){tinymce.ScriptLoader.markDone(tinyMCE.baseURL+"/"+f+".js");});'.
			file_get_contents(PLUGINS.'/'.$plugin.'/jquery.tinymce.js').
			file_get_contents(PLUGINS.'/'.$plugin.'/TinyMCE.js'),
			9
		),
		LOCK_EX|FILE_BINARY
	);
} else {
	$Page->js([
		'components/plugins/'.$plugin.'/jquery.tinymce.js',
		'components/plugins/'.$plugin.'/tiny_mce.js',
		'components/plugins/'.$plugin.'/TinyMCE.js'
	]);
}