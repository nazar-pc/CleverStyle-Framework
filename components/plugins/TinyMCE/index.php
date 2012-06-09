<?php
global $Page, $Config;
if (!$Page->interface) {
	return;
}
$plugin = _basename(_dirname(__FILE__));
if ($Config->core['cache_compress_js_css']) {
	if (_file_exists(PCACHE.DS.'plugin.'.$plugin.'.js')) {
		return;
	}
	$languages = _mb_substr(get_list(PLUGINS.DS.$plugin.DS.'langs', false, 'f'), 0, -3);
	$files[] = "tiny_mce";
	$content = file_get_contents(PLUGINS.DS.$plugin.DS.'tiny_mce.js');
	foreach ($languages as $language) {
		$files[] = 'langs/'.$language;
		$content .= file_get_contents(PLUGINS.DS.$plugin.DS.'langs'.DS.$language.'.js');
	}
	$plugins = get_list(PLUGINS.DS.$plugin.DS.'plugins', false, 'd');
	foreach ($plugins as $plugin_tiny) {
		$files[] = 'plugins/'.$plugin_tiny.'/editor_plugin';
		$content .= file_get_contents(PLUGINS.DS.$plugin.DS.'plugins'.DS.$plugin_tiny.DS.'editor_plugin.js');
		foreach ($languages as $language) {
			if (file_exists($file = PLUGINS.DS.$plugin.DS.'plugins'.DS.$plugin_tiny.DS.'langs'.DS.$language.'.js')) {
				$files[] = 'plugins/'.$plugin_tiny.'/langs/'.$language;
				$content .= file_get_contents($file);
			}
		}
	}
	$themes = get_list(PLUGINS.DS.$plugin.DS.'themes', false, 'd');
	foreach ($themes as $theme) {
		$files[] = 'themes/'.$theme.'/editor_template';
		$content .= file_get_contents(PLUGINS.DS.$plugin.DS.'themes'.DS.$theme.DS.'editor_template.js');
		foreach ($languages as $language) {
			if (file_exists($file = PLUGINS.DS.$plugin.DS.'themes'.DS.$theme.DS.'langs'.DS.$language.'.js')) {
				$files[] = 'themes/'.$theme.'/langs/'.$language;
				$content .= file_get_contents($file);
			}
		}
	}
	_file_put_contents(
		PCACHE.DS.'plugin.'.$plugin.'.js',
		gzencode(
			'var tinyMCEPreInit={base:\'/components/plugins/'.$plugin.'\',suffix:\'\'};'.
			$content.
			'tinymce.each("' . implode(',', $files) . '".split(","),function(f){tinymce.ScriptLoader.markDone(tinyMCE.baseURL+"/"+f+".js");});'.
			file_get_contents(PLUGINS.DS.$plugin.DS.'jquery.tinymce.js').
			file_get_contents(PLUGINS.DS.$plugin.DS.'TinyMCE.js'),
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