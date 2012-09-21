<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
global $Config, $User, $L, $Mail, $Page, $Index, $db;
$rc			= $Config->routing['current'];
if (
	$User->user() ||
	!(
		isset($rc[0], $Config->module(MODULE)->providers[$rc[0]]) &&
		$Config->module(MODULE)->providers[$rc[0]]['enabled']
	) ||
	(
		isset($rc[1]) && strpos($rc[1], $User->get_session()) !== 0
	)
) {
	header('Location: '.$Config->server['base_url']);
	code_header(301);
	interface_off();
	return;
}
if (!$Config->core['allow_user_registration']) {
	$Page->title($L->registration_prohibited);
	$Page->warning($L->registration_prohibited);
	return;
}
if (!isset($rc[1]) && $_SERVER['HTTP_REFERER']) {
	if (
		strpos($_SERVER['HTTP_REFERER'], $Config->server['base_url'].'/'.MODULE) === false &&
		strpos($_SERVER['HTTP_REFERER'], $Config->server['base_url']) === 0
	) {
		_setcookie('HybridAuth_referer', $_SERVER['HTTP_REFERER'], TIME + 30);
	}
}
require_once __DIR__.'/Hybrid/Auth.php';
if (!$User->get_session_data('HybridAuth')) {
	try {
		$HybridAuth		= new Hybrid_Auth([
			'base_url'	=> $Config->server['base_url'].'/'.MODULE.'/'.$rc[0].'/'.$User->get_session(),
			'providers'	=> [
				$rc[0]	=> $Config->module(MODULE)->providers[$rc[0]]
			]
		]);
		$adapter		= $HybridAuth->authenticate($rc[0]);
		$profile		= $adapter->getUserProfile();
		$profile_info	= [
			'username'	=> $profile->displayName,
			'about'		=> $profile->description,
			'avatar'	=> $profile->photoURL,
			'website'	=> $profile->webSiteURL,
			'gender'	=> $profile->gender == 'male' ? 0 : ($profile->gender == 'female' ? 1 : -1),
			'birthday'	=> $profile->birthMonth ? strtotime($profile->birthMonth.'/'.$profile->birthDay.'/'.$profile->birthYear) : 0
		];
		if ($id	= $db->{$Config->module(MODULE)->db('integration')}->qf(
			[
				"SELECT `id`
				FROM `[prefix]users_social_integration`
				WHERE
					`provider`		= '%s' AND
					`identifier`	= '%s'
				LIMIT 1",
				$rc[0],
				$profile->identifier
			],
			true
		)) {
			$User->add_session($id);
			header('Location: '._getcookie('HybridAuth_referer'));
			_setcookie('HybridAuth_referer', '');
			code_header(301);
		}
		if (!$profile_info['username']) {
			unset($profile_info['username']);
		}
		$email			= $profile->emailVerified ?: $profile->email;
		if ($email) {
			if ($result		= $User->registration($email, false)) {
				if ($result == 'error') {
					$Page->content($L->reg_server_error);
					return;
				} elseif ($result == 'exists') {
					$Page->content($L->reg_error_exists);
					return;
				}
				success_registration:
				$body	= $L->reg_success_mail_body(
					isset($profile_info['username']) ? $profile_info['username'] : strstr($result['email'], '@', true),
					get_core_ml_text('name'),
					$Config->core['base_url'].'/profile/'.$User->get('login', $result['id']),
					$User->get('login', $result['id']),
					$result['password']
				);
				if ($Mail->send_to(
					$result['email'],
					$L->reg_success_mail(get_core_ml_text('name')),
					$body
				)) {
					$User->set($profile_info, null, $result['id']);
					header('Location: '.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
					code_header(301);
				} else {
					$User->registration_cancel();
					$Page->title($L->sending_reg_mail_error_title);
					$Page->warning($L->sending_reg_mail_error);
				}
			} else {
				$Page->title($L->reg_server_error);
				$Page->warning($L->reg_server_error);
			}
		} else {
			$User->set_session_data(
				'HybridAuth',
				[
					'profile_info'	=> $profile_info,
					'provider'		=> $rc[0],
					'identifier'	=> $profile->identifier
				]
			);
			email_form:
			$Index->form			= true;
			$Index->buttons			= false;
			$Index->post_buttons	= h::{'button[type=submit]'}($L->submit);
			$Index->{'p.cs-center'}(
				$L->please_type_your_email.':'.
				h::{'input[name=email]'}(
					isset($_POST['email']) ? $_POST['email'] : ''
				)
			);
		}
	} catch (Exception $e) {
		$Index->content(//TODO normal errors processing
			h::p(
				'Error: please try again!',
				'Original error message: '.$e->getMessage()
			)
		);
	}
} else {
	if ($result		= $User->registration($_POST['email'])) {
		if ($result === false) {
			$Page->title($L->please_type_correct_email);
			$Page->warning($L->please_type_correct_email);
			sleep(1);
			goto email_form;
		} elseif ($result == 'error') {
			$Page->title($L->reg_server_error);
			$Page->warning($L->reg_server_error);
			return;
		} elseif ($result == 'exists') {
			$Page->title($L->reg_error_exists);
			$Page->warning($L->reg_error_exists);
			return;
		} elseif ($result['reg_key'] === true) {
			$User->del_session_data('HybridAuth');
			goto success_registration;
		}
		$body	= $L->reg_need_confirmation_mail_body(
			isset($profile_info['username']) ? $profile_info['username'] : strstr($result['email'], '@', true),
			get_core_ml_text('name'),
			$Config->core['base_url'].'/profile/registration_confirmation/'.$result['reg_key'],
			$L->time($Config->core['registration_confirmation_time'], 'd')
		);
		if ($Mail->send_to(
			$_POST['email'],
			$L->reg_need_confirmation_mail(get_core_ml_text('name')),
			$body
		)) {
			_setcookie('HybridAuth_referer', '');
			$Index->content($L->reg_confirmation);
		} else {
			$User->registration_cancel();
			$Page->title($L->sending_reg_mail_error_title);
			$Page->warning($L->sending_reg_mail_error);
		}
	} else {
		$Index->title($L->reg_server_error);
		$Index->content($L->reg_server_error);
	}
}