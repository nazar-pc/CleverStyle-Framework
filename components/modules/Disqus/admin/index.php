<?php
/**
 * @package   Disqus
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

$module_data = Config::instance()->module('Disqus');
if (isset($_POST['shortname'])) {
	$module_data->shortname = $_POST['shortname'];
	Index::instance()->save(true);
}

$L = Language::instance();
Page::instance()->content(
	h::{'form[is=cs-form]'}(
		h::label('Shortname').
		h::{'input[is=cs-input-text][name=shortname]'}(
			[
				'value' => $module_data->shortname ?: ''
			]
		).
		h::{'p button[is=cs-button][type=submit]'}(
			$L->save,
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
