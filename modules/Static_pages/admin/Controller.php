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
		$L          = new Prefix('static_pages_');
		$Page       = Page::instance();
		$Pages      = Pages::instance();
		$Categories = Categories::instance();
		switch ($Request->data('mode')) {
			case 'add_category':
				if ($Categories->add($Request->data['parent'], $Request->data['title'], $Request->data['path'])) {
					$Page->success($L->changes_saved);
				} else {
					$Page->warning($L->changes_save_error);
				}
				break;
			case 'edit_category':
				if ($Categories->set($Request->data['id'], $Request->data['parent'], $Request->data['title'], $Request->data['path'])) {
					$Page->success($L->changes_saved);
				} else {
					$Page->warning($L->changes_save_error);
				}
				break;
			case 'delete_category':
				if ($Categories->del($Request->data['id'])) {
					$Page->success($L->changes_saved);
				} else {
					$Page->warning($L->changes_save_error);
				}
				break;
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
			case 'delete_page':
				if ($Pages->del($Request->data['id'])) {
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
		$L = new Prefix('static_pages_');
		return
			h::{'table.cs-table[list]'}(
				h::{'tr th'}(
					[
						$L->pages_category,
						[
							'style' => 'width: 80%'
						]
					],
					$L->action
				).
				h::{'tr| td'}(
					static::get_categories_rows()
				)
			).
			h::{'p.cs-text-left'}($L->index_page_path).
			h::{'p.cs-text-left a[is=cs-link-button]'}(
				[
					$L->add_category,
					[
						'href' => 'admin/Static_pages/add_category'
					]
				],
				[
					$L->add_page,
					[
						'href' => 'admin/Static_pages/add_page'
					]
				]
			);
	}
	/**
	 * @param \cs\Request $Request
	 */
	public static function add_category ($Request) {
		$L = new Prefix('static_pages_');
		Page::instance()
			->title($L->addition_of_page_category)
			->content(
				h::{'form[is=cs-form][action=admin/Static_pages]'}(
					h::h2($L->addition_of_page_category).
					h::label($L->parent_category).
					h::{'select[is=cs-select][name=parent][size=5]'}(
						static::get_categories_list(),
						[
							'selected' => isset($Request->route[1]) ? (int)$Request->route[1] : 0
						]
					).
					h::label($L->category_title).
					h::{'input[is=cs-input-text][name=title]'}().
					h::{'label info'}('static_pages_category_path').
					h::{'input[is=cs-input-text][name=path]'}().
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=add_category]'}(
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
	public static function edit_category ($Request) {
		$L    = new Prefix('static_pages_');
		$id   = (int)$Request->route[1];
		$data = Categories::instance()->get($id);
		Page::instance()
			->title($L->editing_of_page_category($data['title']))
			->content(
				h::{'form[is=cs-form][action=admin/Static_pages]'}(
					h::h2($L->editing_of_page_category($data['title'])).
					h::label($L->parent_category).
					h::{'select[is=cs-select][name=parent][size=5]'}(
						static::get_categories_list($id),
						[
							'selected' => $data['parent']
						]
					).
					h::label($L->category_title).
					h::{'input[is=cs-input-text][name=title]'}(
						[
							'value' => $data['title']
						]
					).
					h::{'label info'}('static_pages_category_path').
					h::{'input[is=cs-input-text][name=path]'}(
						[
							'value' => $data['path']
						]
					).
					h::{'input[type=hidden][name=id]'}(
						[
							'value' => $id
						]
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=edit_category]'}(
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
	public static function delete_category ($Request) {
		$L     = new Prefix('static_pages_');
		$id    = (int)$Request->route[1];
		$title = Categories::instance()->get($id)['title'];
		Page::instance()
			->title($L->deletion_of_page_category($title))
			->content(
				h::{'form[is=cs-form][action=admin/Static_pages]'}(
					h::{'h2.cs-text-center'}(
						$L->sure_to_delete_page_category($title)
					).
					h::{'input[type=hidden][name=id]'}(
						[
							'value' => $id
						]
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=delete_category]'}(
							$L->yes
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
	 *
	 * @return string
	 */
	public static function browse_pages ($Request) {
		$L  = new Prefix('static_pages_');
		$rc = $Request->route;
		return
			h::{'table.cs-table[list]'}(
				h::{'tr th'}(
					[
						$L->page_title,
						[
							'style' => 'width: 80%'
						]
					],
					$L->action
				).
				h::{'tr| td'}(
					static::get_pages_rows($Request)
				)
			).
			h::{'p.cs-text-left a[is=cs-link-button]'}(
				$L->add_page,
				[
					'href' => 'admin/Static_pages/add_page/'.array_slice($rc, -1)[0]
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
	 * @param \cs\Request $Request
	 */
	public static function delete_page ($Request) {
		$L     = new Prefix('static_pages_');
		$id    = (int)$Request->route[1];
		$title = Pages::instance()->get($id)['title'];
		Page::instance()
			->title($L->deletion_of_page($title))
			->content(
				h::{'form[is=cs-form][action=admin/Static_pages]'}(
					h::{'h2.cs-text-center'}(
						$L->sure_to_delete_page($title)
					).
					h::{'input[type=hidden][name=id]'}(
						[
							'value' => $id
						]
					).
					h::p(
						h::{'button[is=cs-button][type=submit][name=mode][value=delete_page]'}(
							$L->yes
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
	 * @param array|null $structure
	 * @param int        $level
	 * @param array      $parent_categories
	 *
	 * @return array
	 */
	protected static function get_categories_rows ($structure = null, $level = 0, $parent_categories = []) {
		$L    = new Prefix('static_pages_');
		$root = false;
		if ($structure === null) {
			$structure          = Pages::instance()->get_structure();
			$structure['title'] = $L->root_category;
			$root               = true;
		}
		$parent_categories[] = $structure['id'];
		$content             = [
			[
				[
					h::a(
						$structure['title'].
						h::{'b.cs-static-pages-count'}(
							count($structure['pages']),
							[
								'tooltip' => $L->pages_in_category
							]
						),
						[
							'href' => 'admin/Static_pages/browse_pages/'.implode('/', $parent_categories)
						]
					),
					[
						'class' => "cs-static-pages-padding-left-$level"
					]
				],
				h::{'a[is=cs-link-button][icon=plus]'}(
					[
						'href'    => "admin/Static_pages/add_category/$structure[id]",
						'tooltip' => $L->add_subcategory
					]
				).
				h::{'a[is=cs-link-button][icon=file-text]'}(
					[
						'href'    => "admin/Static_pages/add_page/$structure[id]",
						'tooltip' => $L->add_page
					]
				).
				(!$root ?
					h::{'a[is=cs-link-button][icon=pencil]'}(
						[
							'href'    => "admin/Static_pages/edit_category/$structure[id]",
							'tooltip' => $L->edit
						]
					).
					h::{'a[is=cs-link-button][icon=trash]'}(
						[
							'href'    => "admin/Static_pages/delete_category/$structure[id]",
							'tooltip' => $L->delete
						]
					)
					: false
				)
			]
		];
		if (!empty($structure['categories'])) {
			foreach ($structure['categories'] as $category) {
				$content = array_merge($content, static::get_categories_rows($category, $level + 1, $parent_categories));
			}
		}
		return $content;
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
	/**
	 * @param \cs\Request $Request
	 *
	 * @return array
	 */
	protected static function get_pages_rows ($Request) {
		$L          = new Prefix('static_pages_');
		$Pages      = Pages::instance();
		$Categories = Categories::instance();
		$categories = array_slice($Request->route, 2);
		$structure  = $Pages->get_structure();
		$path       = [];
		if (!empty($categories)) {
			foreach ($categories as $category) {
				$category = $Categories->get($category)['path'];
				if (isset($structure['categories'][$category])) {
					$structure = $structure['categories'][$category];
					$path[]    = $structure['path'];
				}
			}
		}
		Page::instance()->title($structure['id'] == 0 ? $L->root_category : $structure['title']);
		$path    = !empty($path) ? implode('/', $path).'/' : '';
		$content = [];
		if (!empty($structure['pages'])) {
			foreach ($structure['pages'] as &$page) {
				$page      = $Pages->get($page);
				$content[] = [
					[
						h::a(
							$page['title'],
							[
								'href' => $path.$page['path']
							]
						),
						[
							'class' => 'cs-static-pages-padding-left-0'
						]
					],
					h::{'a[is=cs-link-button][icon=file-text]'}(
						[
							'href'    => "admin/Static_pages/edit_page/$page[id]",
							'tooltip' => $L->edit
						]
					).
					h::{'a[is=cs-link-button][icon=trash]'}(
						[
							'href'    => "admin/Static_pages/delete_page/$page[id]",
							'tooltip' => $L->delete
						]
					)
				];
			}
			unset($page);
		}
		return $content;
	}
}
