<?php
/**
 * @package   Feedback
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;
use
	h;

$Config = Config::instance();
$L      = Language::instance();
$Page   = Page::instance();
$User   = User::instance();
$Page->content(
	h::{'form[is=cs-form]'}(
		h::{'section.cs-feedback-form article'}(
			h::{'header h2.cs-text-center'}($L->Feedback).
			h::{'table.cs-table[center] tr| td'}(
				[
					h::{'cs-input-text input[name=name][required]'}(
						[
							'placeholder' => $L->feedback_name,
							'value'       => $User->user() ? $User->username() : (isset($_POST['name']) ? $_POST['name'] : '')
						]
					),
					h::{'cs-input-text input[type=email][name=email][required]'}(
						[
							'placeholder' => $L->feedback_email,
							'value'       => $User->user() ? $User->email : (isset($_POST['email']) ? $_POST['email'] : '')
						]
					),
					h::{'textarea[is=cs-textarea][autosize][name=text][required]'}(
						[
							'placeholder' => $L->feedback_text,
							'value'       => isset($_POST['text']) ? $_POST['text'] : ''
						]
					),
					h::{'cs-button button[type=submit]'}($L->feedback_send)
				]
			)
		)
	)
);
if (isset($_POST['name'], $_POST['email'], $_POST['text'])) {
	if (!$_POST['name'] || !$_POST['email'] || !$_POST['text']) {
		$Page->warning($L->feedback_fill_all_fields);
		return;
	}
	$result = Mail::instance()->send_to(
		$Config->core['admin_email'],
		$L->feedback_email_from(xap($_POST['name']), $Config->core['site_name']),
		xap($_POST['text']),
		null,
		null,
		$_POST['email']
	);
	if ($result) {
		$Page->success($L->feedback_sent_successfully);
	} else {
		$Page->warning($L->feedback_sending_error);
	}
}
