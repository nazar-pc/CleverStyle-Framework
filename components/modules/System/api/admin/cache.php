<?php
/**
 * @package        CleverStyle CMS
 * @subpackage     System module
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
$Cache  = Cache::instance();
$Config = Config::instance();
$Page   = Page::instance();
$rc     = $Config->route;
if (isset($rc[2])) {
	switch ($rc[2]) {
		case 'clean_cache':
			time_limit_pause();
			if ($_POST['partial_path']) {
				$result = $Cache->del($_POST['partial_path']);
			} else {
				$result = $Cache->clean();
				clean_classes_cache();
			}
			time_limit_pause(false);
			if ($result) {
				$Cache->disable();
				$Page->content(1);
			} else {
				$Page->content(0);
			}
			break;
		case 'clean_pcache':
			if (clean_pcache()) {
				if (!isset($rc[3])) {
					time_limit_pause();
					Core::instance()->api_request('System/admin/cache/clean_pcache/api');
					time_limit_pause(false);
				}
				$Page->content(1);
			} else {
				$Page->content(0);
			}
			break;
	}
} else {
	$Page->content(0);
}
