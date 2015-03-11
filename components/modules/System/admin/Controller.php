<?php
/**
 * @package    CleverStyle CMS
 * @subpackage System module
 * @category   modules
 * @author     Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright  Copyright (c) 2015, Nazar Mokrynskyi
 * @license    MIT License, see license.txt
 */
namespace cs\modules\System\admin;
use
	cs\Cache,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Language,
	cs\Page,
	cs\Text,
	cs\modules\System\admin\Controller\components,
	cs\modules\System\admin\Controller\general,
	cs\modules\System\admin\Controller\users,
	cs\modules\System\admin\Controller\components_save,
	cs\modules\System\admin\Controller\users_save,
	cs\modules\System\admin\Controller\layout_elements,
	cs\modules\System\admin\Controller\packages_manipulation;

class Controller {
	use
		components,
		general,
		users,
		components_save,
		users_save,
		layout_elements,
		packages_manipulation;
	static function index (
		/** @noinspection PhpUnusedParameterInspection */
		$route_ids,
		$route_path
	) {
		$L           = Language::instance();
		$Page        = Page::instance();
		$save_method = "$route_path[0]_$route_path[1]_save";
		if (method_exists(__CLASS__, $save_method)) {
			self::$save_method();
		} else {
			self::save();
		}
		$Page->title($L->{$route_path[0]});
		$Page->title($L->{$route_path[1]});
	}
	static function save () {
		$Index  = Index::instance();
		$Config = Config::instance();
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
								$value = Text::instance()->set(
									Config::instance()->module('System')->db('texts'),
									'System/Config/core',
									$item,
									$value
								);
								break;
							case 'url':
							case 'cookie_domain':
							case 'cookie_path':
							case 'ip_black_list':
							case 'ip_admin_list':
								$value = _trim(explode("\n", $value));
								if ($value[0] == '') {
									$value = [];
								}
						}
						$temp[$item] = xap($value, true);
					}
					unset($item, $value);
					if ($part == 'routing' || $part == 'replace') {
						$temp['in']  = explode("\n", $temp['in']);
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
		$Cache = Cache::instance();
		if (isset($_POST['apply']) && $Cache->cache_state()) {
			/** @noinspection NotOptimalIfConditionsInspection */
			if ($Index->apply() && !$Config->core['cache_compress_js_css']) {
				clean_pcache();
				Event::instance()->fire('admin/System/general/optimization/clean_pcache');
			}
		} elseif (isset($_POST['save'])) {
			$save = $Index->save();
			if ($save && !$Config->core['cache_compress_js_css']) {
				clean_pcache();
				Event::instance()->fire('admin/System/general/optimization/clean_pcache');
			}
		} /** @noinspection NotOptimalIfConditionsInspection */ elseif (isset($_POST['cancel']) && $Cache->cache_state()) {
			$Index->cancel();
		}
	}
}
