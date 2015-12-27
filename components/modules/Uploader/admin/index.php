<?php
/**
 * @package   Uploader
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use            h;
include __DIR__.'/save.php';
$Config                       = Config::instance();
$L                            = Language::instance();
$Index                        = Index::instance();
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::label("$L->upload_limit (b, kb, mb, gb)").
	h::{'input[is=cs-input-text][compact][name=max_file_size]'}(
		[
			'value' => $Config->module('Uploader')->max_file_size
		]
	).
	h::{'label info'}('uploader_confirmation_time').
	h::{'input[is=cs-input-text][compact][name=confirmation_time]'}(
		[
			'value' => $Config->module('Uploader')->confirmation_time
		]
	).
	$L->seconds.
	h::br()
);
