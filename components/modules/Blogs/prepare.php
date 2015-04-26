<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	cs\Language;
if (!api_path() && !function_exists(__NAMESPACE__.'\\get_sections_select_post')) {
	function get_sections_select_post (&$disabled, $current = null, $structure = null, $level = 0) {
		$list = [
			'in'    => [],
			'value' => []
		];
		if ($structure === null) {
			$structure       = Sections::instance()->get_structure();
			$list['in'][]    = Language::instance()->root_section;
			$list['value'][] = 0;
		} else {
			if ($structure['id'] == $current) {
				return $list;
			}
			$list['in'][]    = str_repeat('&nbsp;', $level).$structure['title'];
			$list['value'][] = $structure['id'];
		}
		if (!empty($structure['sections'])) {
			$disabled[] = $structure['id'];
			foreach ($structure['sections'] as $section) {
				$tmp           = get_sections_select_post($disabled, $current, $section, $level + 1);
				$list['in']    = array_merge($list['in'], $tmp['in']);
				$list['value'] = array_merge($list['value'], $tmp['value']);
			}
		}
		return $list;
	}
}
