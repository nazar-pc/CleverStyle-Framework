<?php
if (!isset($_POST['edit_settings'])) {
	return;
}
global $Config, $L, $Index, $Cache;
$disk_cache		= $Config->core['disk_cache'];
$memcache_cache	= $Config->core['memcache'];
if ($_POST['edit_settings'] == 'apply' || $_POST['edit_settings'] == 'save') {
	foreach ($Config->admin_parts as $part) {
		if (isset($_POST[$part])) {
			$temp = &$Config->$part;
			foreach ($_POST[$part] as $item => $value) {
				$temp[$item] = $value;
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
			$update[] = $part;
			unset($temp);
		}
	}
	unset($part);
}
if ($_POST['edit_settings'] == 'apply' && $Cache->cache) {
	if ($Index->apply() && !$Config->core['cache_compress_js_css']) {
		flush_pcache();
	}
} elseif ($_POST['edit_settings'] == 'save' && isset($update)) {
	if ($Index->save() && !$Config->core['cache_compress_js_css']) {
		flush_pcache();
	}
} elseif ($_POST['edit_settings'] == 'cancel' && $Cache->cache) {
	$Index->cancel();
}