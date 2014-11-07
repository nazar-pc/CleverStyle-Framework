<?php
/**
 * @package		CleverStyle CMS
 * @subpackage	System module
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
/**
 * Provides next triggers:<br>
 *  admin/System/general/optimization/clean_pcache
 */
namespace	cs\modules\System;
use
	cs\Cache,
	cs\Config,
	cs\Index,
	cs\Trigger;
$Index	= Index::instance();
$Config	= Config::instance();
if (isset($_POST['apply']) || isset($_POST['save'])) {
	foreach (['core', 'db', 'storage', 'components', 'replace', 'routing'] as $part) {
		if (isset($_POST[$part])) {
			$temp = &$Config->$part;
			foreach ($_POST[$part] as $item => $value) {
				switch ($item) {
					case 'name':
					case 'closed_title':
					case 'closed_text':
					case 'mail_from_name':
					case 'mail_signature':
					case 'rules':
						$value	= set_core_ml_text($item, $value);
					break;
					case 'url':
					case 'cookie_domain':
					case 'cookie_path':
					case 'ip_black_list':
					case 'ip_admin_list':
						$value	= _trim(explode("\n", $value));
						if ($value[0] == '') {
							$value	= [];
						}
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
$Cache	= Cache::instance();
if (isset($_POST['apply']) && $Cache->cache_state()) {
	if ($Index->apply() && !$Config->core['cache_compress_js_css']) {
		clean_pcache();
		Trigger::instance()->run('admin/System/general/optimization/clean_pcache');
	}
} elseif (isset($_POST['save'])) {
	$save = $Index->save();
	if ($save && !$Config->core['cache_compress_js_css']) {
		clean_pcache();
		Trigger::instance()->run('admin/System/general/optimization/clean_pcache');
	}
} elseif (isset($_POST['cancel']) && $Cache->cache_state()) {
	$Index->cancel();
}
