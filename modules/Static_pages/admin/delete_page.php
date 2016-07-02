<?php
/**
 * @package   Static Pages
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Static_pages;
use
	h,
	cs\Language\Prefix,
	cs\Page,
	cs\Request;

$L     = new Prefix('static_pages_');
$id    = (int)Request::instance()->route[1];
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
