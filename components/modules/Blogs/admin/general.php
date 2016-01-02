<?php
/**
 * @package   Blogs
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Blogs;
use
	h,
	cs\Config,
	cs\Language,
	cs\Page;

$module_data = Config::instance()->module('Blogs');
$L           = Language::instance();
$Page        = Page::instance();
$Page->title($L->general);
$Page->content(
	h::{'form[is=cs-form]'}(
		h::{'label info'}('posts_per_page').
		h::{'input[is=cs-input-text][type=number][min=1]'}(
			[
				'name'  => 'general[posts_per_page]',
				'value' => $module_data->posts_per_page
			]
		).
		h::{'label info'}('maximum_number_of_sections_for_post').
		h::{'input[is=cs-input-text][type=number][min=1]'}(
			[
				'name'  => 'general[max_sections]',
				'value' => $module_data->max_sections
			]
		).
		h::{'label info'}('enable_comments').
		h::{'nav[is=cs-nav-button-group] radio'}(
			[
				'name'    => 'general[enable_comments]',
				'value'   => [0, 1],
				'in'      => [$L->no, $L->yes],
				'checked' => $module_data->enable_comments
			]
		).
		h::{'label info'}('new_posts_only_from_admins').
		h::{'nav[is=cs-nav-button-group] radio'}(
			[
				'name'    => 'general[new_posts_only_from_admins]',
				'value'   => [0, 1],
				'in'      => [$L->no, $L->yes],
				'checked' => $module_data->new_posts_only_from_admins
			]
		).
		h::{'label info'}('allow_iframes_without_content').
		h::{'nav[is=cs-nav-button-group] radio'}(
			[
				'name'    => 'general[allow_iframes_without_content]',
				'value'   => [0, 1],
				'in'      => [$L->no, $L->yes],
				'checked' => $module_data->allow_iframes_without_content
			]
		).
		h::{'p button[is=cs-button][type=submit][name=mode][value=general]'}(
			$L->save
		)
	)
);
