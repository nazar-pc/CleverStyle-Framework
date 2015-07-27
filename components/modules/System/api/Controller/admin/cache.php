<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\api\Controller\admin;
use
	cs\Cache as System_cache,
	cs\Page,
	cs\Route;
trait cache {
	static function admin_cache_delete () {
		$Cache = System_cache::instance();
		$Page  = Page::instance();
		$rc    = Route::instance()->route;
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
						$Page->content(1);
					} else {
						$Page->content(0);
					}
					break;
			}
		} else {
			$Page->content(0);
		}
	}
}
