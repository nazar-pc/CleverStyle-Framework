<?php
/**
 * @package   Plupload
 * @category  modules
 * @author    Moxiecode Systems AB
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright Moxiecode Systems AB
 * @license   GNU GPL v2, see license.txt
 */
namespace cs;
use            h;
include __DIR__.'/save.php';
$Config                       = Config::instance();
$L                            = Language::instance();
$Index                        = Index::instance();
$Index->form_attributes['is'] = 'cs-form';
$Index->content(
	h::label("$L->upload_size_limit (b, kb, mb, gb)").
	h::{'input[is=cs-input-text][compact][name=max_file_size]'}(
		[
			'value' => $Config->module('Plupload')->max_file_size
		]
	).
	h::{'label info'}('plupload_confirmation_time').
	h::{'input[is=cs-input-text][compact][name=confirmation_time]'}(
		[
			'value' => $Config->module('Plupload')->confirmation_time
		]
	).
	$L->seconds.
	h::br()
);
