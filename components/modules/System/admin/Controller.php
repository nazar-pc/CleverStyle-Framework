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
	cs\modules\System\admin\Controller\components_manipulation,
	h;

class Controller {
	use
		components,
		general,
		users,
		components_save,
		users_save,
		components_manipulation;
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
	static protected function core_input ($item, $type = 'text', $info_item = null, $disabled = false, $min = false, $max = false, $post_text = '') {
		$Config = Config::instance();
		$L      = Language::instance();
		if ($type != 'radio') {
			switch ($item) {
				default:
					$value = $Config->core[$item];
					break;
				case 'name':
				case 'closed_title':
				case 'mail_from_name':
					$value = get_core_ml_text($item);
			}
			return [
				$info_item !== false ? h::info($info_item ?: $item) : $L->$item,
				h::input(
					[
						'name'  => "core[$item]",
						'value' => $value,
						'min'   => $min,
						'max'   => $max,
						'type'  => $type,
						($disabled ? 'disabled' : '')
					]
				).
				$post_text
			];
		} else {
			return [
				$info_item !== false ? h::info($info_item ?: $item) : $L->$item,
				h::radio(
					[
						'name'    => "core[$item]",
						'checked' => $Config->core[$item],
						'value'   => [0, 1],
						'in'      => [$L->off, $L->on]
					]
				)
			];
		}
	}
	static protected function core_textarea ($item, $editor = null, $info_item = null) {
		switch ($item) {
			default:
				$content = Config::instance()->core[$item];
				break;
			case 'closed_text':
			case 'mail_signature':
			case 'rules':
				$content = get_core_ml_text($item);
		}
		return [
			h::info($info_item ?: $item),
			h::textarea(
				$content,
				[
					'name'  => "core[$item]",
					'class' => $editor ? " $editor" : ''
				]
			)
		];
	}
	static protected function core_select ($items_array, $item, $id = null, $info_item = null, $multiple = false, $size = 5) {
		return [
			h::info($info_item ?: $item),
			h::select(
				$items_array,
				[
					'name'     => "core[$item]".($multiple ? '[]' : ''),
					'selected' => Config::instance()->core[$item],
					'size'     => $size,
					'id'       => $id ?: false,
					$multiple ? 'multiple' : false
				]
			)
		];
	}
}
