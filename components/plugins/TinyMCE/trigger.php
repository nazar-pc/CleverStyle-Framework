<?php
global $Core;
$clean_pcache = function ($data = null) {
	$plugin = basename(__DIR__);
	if (
		(
			$data['name'] == $plugin ||
			$data === null
		) &&
		file_exists(PCACHE.'/plugin.'.$plugin.'.js')
	) {
		unlink(PCACHE.'/plugin.'.$plugin.'.js');
	}
};
$Core->register_trigger(
	'admin/System/components/plugins/disable',
	$clean_pcache
);
$Core->register_trigger(
	'admin/System/general/optimization/clean_pcache',
	$clean_pcache
);
$Core->register_trigger(
	'System/Page/rebuild_cache',
	function ($data) {
		$plugin = basename(__DIR__);
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
			$key	= gzencode(
				'var tinyMCEPreInit={base:\'/components/plugins/'.$plugin.'\',suffix:\'\'};'.
				$content.
				'tinymce.each("' . implode(',', $files) . '".split(","),function(f){tinymce.ScriptLoader.markDone(tinyMCE.baseURL+"/"+f+".js");});'.
				file_get_contents(PLUGINS.'/'.$plugin.'/jquery.tinymce.js').
				file_get_contents(PLUGINS.'/'.$plugin.'/TinyMCE.js'),
				9
			),
			LOCK_EX|FILE_BINARY
		);
		$data['key']	.= md5($key);
	}
);