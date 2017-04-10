<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
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
 * @method static $this instance($check = false)
 */
class Menu {
	use
		Singleton;
	const INIT_STATE_METHOD = 'init';
	/**
	 * @var array
	 */
	public $section_items;
	/**
	 * @var array
	 */
	public $items;

	protected function init () {
		$this->section_items = [];
		$this->items         = [];
	}
	/**
	 * Get menu in HTML format
	 *
	 * @return string
	 */
	public function get_menu () {
		Event::instance()->fire('admin/System/Menu');
		$current_module = Request::instance()->current_module;
		if (isset($this->section_items[$current_module])) {
			$content = $this->render_sections($current_module);
		} else {
			$content = $this->render_items($current_module);
		}
		return h::cs_group($content ?: false);
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
		foreach ($this->section_items[$module] as $item) {
			$dropdown = $this->render_items($module, $item[1]['href']);
			if ($dropdown) {
				$dropdown = h::{'cs-dropdown cs-group[vertical]'}($dropdown);
			}
			// Render as button without `href` attribute
			unset($item[1]['href']);
			$content .=
				h::{'cs-button[icon-after=caret-down]'}(
					h::button(
						$item[0],
						array_filter(
							$item[1],
							function ($item) {
								return $item == 'type';
							},
							ARRAY_FILTER_USE_KEY
						)
					),
					array_filter(
						$item[1],
						function ($item) {
							return $item != 'type';
						},
						ARRAY_FILTER_USE_KEY
					)
				).
				$dropdown;
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
		$content = '';
		foreach ($this->items[$module] as $item) {
			/**
			 * Nested items for parent
			 */
			if ($base_href && strpos($item[1]['href'], $base_href) !== 0) {
				continue;
			}
			$content .= h::cs_link_button(
				h::a(
					$item[0],
					array_filter(
						$item[1],
						function ($item) {
							return in_array($item, ['href', 'target']);
						},
						ARRAY_FILTER_USE_KEY
					)
				),
				array_filter(
					$item[1],
					function ($item) {
						return !in_array($item, ['href', 'target']);
					},
					ARRAY_FILTER_USE_KEY
				)
			);
		}
		return $content;
	}
	/**
	 * Add second-level item into menu
	 *
	 * All third-level items which start with the same `$href` will be inside this second-level menu item
	 *
	 * @param string $module
	 * @param string $title
	 * @param array  $attributes `href` and `target` if present will go to `a` element, all other attributes will go to `cs-link-button` element
	 */
	public function add_section_item ($module, $title, $attributes = []) {
		$this->section_items[$module][] = [
			$title,
			$attributes
		];
	}
	/**
	 * Add third-level item into menu (second-level when there is corresponding section items)
	 *
	 * @param string $module
	 * @param string $title
	 * @param array  $attributes
	 */
	public function add_item ($module, $title, $attributes = []) {
		$this->items[$module][] = [
			$title,
			$attributes
		];
	}
}
