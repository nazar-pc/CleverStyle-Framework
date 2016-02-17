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
use
	cs\Language\Prefix,
	h;

$L    = new Prefix('plupload_');
$Page = Page::instance();
if (isset($_POST['max_file_size'], $_POST['confirmation_time'])) {
	$module_data = Config::instance()->module('Plupload');
	if ($module_data->set(
		[
			'max_file_size'     => xap($_POST['max_file_size']),
			'confirmation_time' => (int)$_POST['confirmation_time']
		]
	)
	) {
		$Page->success($L->changes_saved);
	} else {
		$Page->warning($L->changes_save_error);
	}
}

$Config = Config::instance();
$Page->content(
	h::{'form[is=cs-form]'}(
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
		h::{'p button[is=cs-button][type=submit]'}(
			$L->save,
			[
				'tooltip' => $L->save_info
			]
		)
	)
);
