<?php
/**
 * @package		Feedback
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs;
use			h;
$Index			= Index::instance();
$Index->form	= true;
$Index->buttons	= false;
$Config			= Config::instance();
$L				= Language::instance();
$Page			= Page::instance();
$User			= User::instance();
$Page->css('components/modules/Feedback/includes/css/general.css');
$Index->content(
	h::{'section.cs-feedback-form article'}(
		h::{'header h2.cs-center'}($L->Feedback).
		h::{'table.cs-table-borderless.cs-center tr| td'}([
			h::{'input[name=name][required]'}([
				'placeholder'	=> $L->feedback_name,
				'value'			=> $User->user() ? $User->username() : (isset($_POST['name']) ? $_POST['name'] : '')
			]),
			h::{'input[type=email][name=email][required]'}([
				'placeholder'	=> $L->feedback_email,
				'value'			=> $User->user() ? $User->email : (isset($_POST['email']) ? $_POST['email'] : '')
			]),
			h::{'textarea[name=text][required]'}([
				'placeholder'	=> $L->feedback_text,
				'value'			=> isset($_POST['text']) ? $_POST['text'] : ''
			]),
			h::{'button[type=submit]'}($L->feedback_send)
		])
	)
);
if (isset($_POST['name'], $_POST['email'], $_POST['text'])) {
	if (!$_POST['name'] || !$_POST['email'] || !$_POST['text']) {
		$Page->warning($L->feedback_fill_all_fields);
		return;
	}
	if (Mail::instance()->send_to(
		$Config->core['admin_email'],
		$L->feedback_email_from(xap($_POST['name']), $Config->core['name']),
		xap($_POST['text']),
		null,
		null,
		$_POST['email']
	)) {
		$Page->success($L->feedback_sent_successfully);
	} else {
		$Page->warning($L->feedback_sending_error);
	}
}