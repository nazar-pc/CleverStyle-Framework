<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages\admin;
use
	h,
	cs\Language\Prefix,
	cs\Page,
	cs\modules\Static_pages\Pages,
	cs\modules\Static_pages\Categories;

class Controller {
	/**
	 * @param \cs\Request $Request
	 */
	public static function index ($Request) {
		$L     = new Prefix('static_pages_');
		$Page  = Page::instance();
		$Pages = Pages::instance();
		switch ($Request->data('mode')) {
			case 'add_page':
				if ($Pages->add(
					$Request->data['category'],
					$Request->data['title'],
					$Request->data['path'],
					$Request->data['content'],
					$Request->data['interface']
				)
				) {
					$Page->success($L->changes_saved);
				} else {
					$Page->warning($L->changes_save_error);
				}
				break;
			case 'edit_page':
				if ($Pages->set(
					$Request->data['id'],
					$Request->data['category'],
					$Request->data['title'],
					$Request->data['path'],
					$Request->data['content'],
					$Request->data['interface']
				)
				) {
					$Page->success($L->changes_saved);
				} else {
					$Page->warning($L->changes_save_error);
				}
				break;
		}
	}
	/**
	 * @return string
	 */
	public static function browse_categories () {
		return h::cs_static_pages_admin_categories_list();
	}
	/**
	 * @param \cs\Request $Request
	 *
	 * @return string
	 */
	public static function browse_pages ($Request) {
		$L        = new Prefix('static_pages_');
		$category = $Request->route_ids(0);
		Page::instance()->title(
			$category ? Categories::instance()->get($category)['title'] : $L->root_category
		);
		return h::cs_static_pages_admin_pages_list(
			[
				'category' => $category
			]
		);
	}
	/**
	 * @param \cs\Request $Request
	 */
	public static function add_page ($Request) {
		$L = new Prefix('static_pages_');
		Page::instance()->title($L->adding_of_page)
			->content(
				h::{'form[is=cs-form][action=admin/Static_pages]'}(
					h::h2(
						$L->adding_of_page
					).
					h::{'table.cs-table[center] tr'}(
						h::th(
							$L->category,
							$L->page_title,
							h::info('static_pages_page_path'),
							h::info('static_pages_page_interface')
						),
						h::td(
							h::{'select[is=cs-select][full-width][name=category][size=5]'}(
								static::get_categories_list(),
								[
									'selected' => isset($Request->route[1]) ? (int)$Request->route[1] : 0
								]
							),
							h::{'input[is=cs-input-text][full-width][name=title]'}(),
							h::{'input[is=cs-input-text][full-width][name=path]'}(),
							h::{'div radio[name=interface]'}(
								[
									'checked' => 1,
									'value'   => [0, 1],
									'in'      => [$L->off, $L->on]
								]
							)
						)
					).
					h::{'table.cs-table[center] tr'}(
						h::th($L->content),
						h::{'td cs-editor textarea[cs-textarea][autosize][name=content]'}()
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=add_page]'}(
							$L->save,
							[
								'tooltip' => $L->save_info
							]
						).
						h::{'button[is=cs-button][type=button]'}(
							$L->cancel,
							[
								'onclick' => 'history.go(-1);'
							]
						)
					)
				)
			);
	}
	/**
	 * @param \cs\Request $Request
	 */
	public static function edit_page ($Request) {
		$L        = new Prefix('static_pages_');
		$id       = (int)$Request->route[1];
		$data     = Pages::instance()->get($id);
		$textarea = h::{'textarea[is=cs-textarea][autosize][name=content]'}($data['content']);
		Page::instance()
			->title($L->editing_of_page($data['title']))
			->content(
				h::{'form[is=cs-form][action=admin/Static_pages]'}(
					h::h2(
						$L->editing_of_page($data['title'])
					).
					h::{'table.cs-table[center] tr'}(
						h::th(
							$L->category,
							$L->page_title,
							h::info('static_pages_page_path'),
							h::info('static_pages_page_interface')
						),
						h::td(
							h::{'select[is=cs-select][full-width][name=category][size=5]'}(
								static::get_categories_list(),
								[
									'selected' => $data['category']
								]
							),
							h::{'input[is=cs-input-text][full-width][name=title]'}(
								[
									'value' => $data['title']
								]
							),
							h::{'input[is=cs-input-text][full-width][name=path]'}(
								[
									'value' => $data['path']
								]
							),
							h::{'div radio[name=interface]'}(
								[
									'checked' => $data['interface'],
									'value'   => [0, 1],
									'in'      => [$L->off, $L->on]
								]
							)
						)
					).
					h::{'table.cs-table[center] tr'}(
						h::th($L->content),
						h::td(
							$data['interface'] ? h::cs_editor($textarea) : $textarea
						)
					).
					h::{'input[type=hidden][name=id]'}(
						[
							'value' => $id
						]
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=edit_page]'}(
							$L->save,
							[
								'tooltip' => $L->save_info
							]
						).
						h::{'button[is=cs-button][type=button]'}(
							$L->cancel,
							[
								'onclick' => 'history.go(-1);'
							]
						)
					)
				)
			);
	}
	/**
	 * @param int|null   $current
	 * @param array|null $structure
	 * @param int        $level
	 *
	 * @return array
	 */
	protected static function get_categories_list ($current = null, $structure = null, $level = 0) {
		$list = [
			'in'    => [],
			'value' => []
		];
		if ($structure === null) {
			$structure       = Pages::instance()->get_structure();
			$L               = new Prefix('static_pages_');
			$list['in'][]    = $L->root_category;
			$list['value'][] = 0;
		} else {
			if ($structure['id'] == $current) {
				return $list;
			}
			$list['in'][]    = str_repeat('&nbsp;', $level).$structure['title'];
			$list['value'][] = $structure['id'];
		}
		if (!empty($structure['categories'])) {
			foreach ($structure['categories'] as $category) {
				$tmp           = static::get_categories_list($current, $category, $level + 1);
				$list['in']    = array_merge($list['in'], $tmp['in']);
				$list['value'] = array_merge($list['value'], $tmp['value']);
			}
		}
		return $list;
	}
}
