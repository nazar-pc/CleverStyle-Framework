<?php
/**
 * @package        CleverStyle CMS
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs;
use
	h;
/**
 * Menu class is used in administration for generating second and third level of menu
 *
 * Provides next events:<br>
 *  admin/System/Menu
 *
 * @method static Menu instance($check = false)
 */
class Menu {
	use
		Singleton;
	public $section_items = [];
	public $items         = [];
	/**
	 * Get menu in HTML format
	 *
	 * @return string
	 */
	function get_menu () {
		Event::instance()->fire('admin/System/Menu');
		$current_module = current_module();
		if (isset($this->section_items[$current_module])) {
			$content = $this->render_sections($current_module);
		} else {
			$content = $this->render_items($current_module);
		}
		return h::{'ul.uk-subnav.uk-subnav-pill'}($content ?: false);
	}
	/**
	 * Render sections (automatically includes nested items)
	 *
	 * @param string $module
	 *
	 * @return string
	 */
	protected function render_sections ($module) {
		$content = '';
		foreach ($this->section_items[$module] as $href => $item) {
			$inner = $this->render_items($module, $href);
			if ($inner) {
				$inner = h::{'div.uk-dropdown.uk-dropdown-small ul.uk-nav.uk-nav-dropdown'}($inner);
			}
			$content .= $this->render_single_item_common('li[data-uk-dropdown=]', $item[0], $href, $item[1], $inner);
		}
		return $content;
	}
	/**
	 * Render items
	 *
	 * @param string $module
	 * @param string $base_href If passed - only nested elements for this base href will be rendered
	 *
	 * @return string
	 */
	protected function render_items ($module, $base_href = '') {
		if (!isset($this->items[$module])) {
			return '';
		}
		$items = $this->items[$module];
		$content = '';
		$element = 'li';
		/**
		 * If there are no nested elements
		 */
		if (!$base_href) {
			$element .= '[data-uk-dropdown=]';
		}
		foreach ($items as $href => $item) {
			/**
			 * Nested items for parent
			 */
			if ($base_href && strpos($href, $base_href) !== 0) {
				continue;
			}
			$content .= $this->render_single_item_common($element, $item[0], $href, $item[1]);
		}
		return $content;
	}
	/**
	 * Generic method for rendering elements both for sections and nested items (basically, `li` with `a` inside)
	 *
	 * @param string $element
	 * @param string $title
	 * @param string $href
	 * @param array  $arguments
	 * @param string $content
	 *
	 * @return mixed
	 */
	protected function render_single_item_common ($element, $title, $href, $arguments, $content = '') {
		if ($content) {
			$title .= ' '.h::icon('caret-down');
		}
		return h::$element(
			h::a(
				$title,
				[
					'href' => $href
				]
			).
			$content,
			$arguments
		);
	}
	/**
	 * Add second-level item into menu
	 *
	 * All third-level items which start with the same `$href` will be inside this second-level menu item
	 *
	 * @param string      $module
	 * @param string      $title
	 * @param bool|string $href
	 * @param array       $attributes
	 */
	function add_section_item ($module, $title, $href = false, $attributes = []) {
		$this->section_items[$module][$href] = [
			$title,
			$attributes
		];
	}
	/**
	 * Add third-level item into menu (second-level when there is corresponding section items)
	 *
	 * @param string      $module
	 * @param string      $title
	 * @param bool|string $href
	 * @param array       $attributes
	 */
	function add_item ($module, $title, $href = false, $attributes = []) {
		$this->items[$module][$href] = [
			$title,
			$attributes
		];
	}
}
