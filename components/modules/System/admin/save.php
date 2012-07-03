<?php
/**
 * Provides next triggers:<br>
 *  admin/System/general/optimization/clean_pcache
 */
if (!isset($_POST['edit_settings'])) {
	return;
}
global $Config, $L, $Index, $Cache, $Core;
$debug = $Config->core['debug'];
if ($_POST['edit_settings'] == 'apply' || $_POST['edit_settings'] == 'save') {
	foreach ($Config->admin_parts as $part) {
		if (isset($_POST[$part])) {
			$temp = &$Config->$part;
			foreach ($_POST[$part] as $item => $value) {
				switch ($item) {
					case 'mirrors_url':
					case 'mirrors_cookie_domain':
					case 'mirrors_cookie_path':
					case 'ip_black_list':
					case 'ip_admin_list':
						$value = explode("\n", $value);
					break;
				}
				$temp[$item] = xap($value, true);
			}
			unset($item, $value);
			if ($part == 'routing' || $part == 'replace') {
				$temp['in'] = explode("\n", $temp['in']);
				$temp['out'] = explode("\n", $temp['out']);
				foreach ($temp['in'] as $i => $value) {
					if (empty($value)) {
						unset($temp['in'][$i], $temp['out'][$i]);
					}
				}
				unset($i, $value);
			}
			unset($temp);
		}
	}
	unset($part);
}
if ($_POST['edit_settings'] == 'apply' && $Cache->cache_state()) {
	if ($Index->apply() && !$Config->core['cache_compress_js_css']) {
		clean_pcache();
		$Core->run_trigger('admin/System/general/optimization/clean_pcache');
	}
} elseif ($_POST['edit_settings'] == 'save') {
	$save = $Index->save();
	if ($save && !$Config->core['cache_compress_js_css']) {
		clean_pcache();
		$Core->run_trigger('admin/System/general/optimization/clean_pcache');
	}
	if ($save && ($Config->core['debug'] != $debug) && $Config->core['debug']) {
		$Cache->clean();
	}
} elseif ($_POST['edit_settings'] == 'cancel' && $Cache->cache_state()) {
	$Index->cancel();
}