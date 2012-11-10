<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
global $Config, $User, $L, $Mail, $Page, $Index, $db, $Key;
$rc			= $Config->routing['current'];
/**
 * If user is registered, provider not found or this is request for final authentication and session does not corresponds - return user to the base url
 */
if (
	$User->user() ||
	!(
		isset($rc[0], $Config->module(MODULE)->providers[$rc[0]]) &&
		$Config->module(MODULE)->providers[$rc[0]]['enabled']
	) ||
	(
		isset($rc[2]) && strpos($rc[2], $User->get_session()) !== 0
	)
) {
	header('Location: '.$Config->server['base_url']);
	code_header(301);
	interface_off();
	return;
}
/**
 * Merging confirmation
 */
if (isset($rc[1]) && $rc[1] == 'merge_confirmation') {
	/**
	 * If confirmation key is valid - make merging
	 */
	if ($data = $Key->get($db_id, $rc[1])) {
		$db->$db_id()->q(
			"INSERT INTO `[prefix]users_social_integration`
				(
					`id`,
					`provider`,
					`identifier`,
					`profile`
				) VALUES (
					'%s',
					'%s',
					'%s',
					'%s'
				)",
			$data['id'],
			$data['provider'],
			$data['identifier'],
			$data['profile']
		);
		$User->del_session_data('HybridAuth');
		$existing_data	= $User->get(array_keys($data['profile_info']), $data['id']);
		foreach ($data['profile_info'] as $item => $value) {
			if (!$existing_data[$item]) {
				$User->set($item, $value, $data['id']);
			}
		}
		unset($existing_data, $item, $value);
		$User->add_session($data['id']);
		header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
		_setcookie('HybridAuth_referer', '');
		$Index->content(
			$L->hybridauth_merging_confirmed_successfully($L->{$data['provider']})
		);
	} else {
		$Index->content($L->hybridauth_merge_confirm_code_invalid);
	}
}
/**
 * If registration is not allowed - show corresponding error
 */
if (!$Config->core['allow_user_registration']) {
	$Page->title($L->registration_prohibited);
	$Page->warning($L->registration_prohibited);
	return;
}
/**
 * If referer is internal address and not current authentication module - save referer
 */
if (
	!$User->get_session_data('HybridAuth') &&
	isset($_SERVER['HTTP_REFERER']) &&
	strpos($_SERVER['HTTP_REFERER'], $Config->server['base_url'].'/'.MODULE) === false &&
	strpos($_SERVER['HTTP_REFERER'], $Config->server['base_url']) === 0
) {
	_setcookie('HybridAuth_referer', $_SERVER['HTTP_REFERER']);
}
require_once __DIR__.'/Hybrid/Auth.php';
$db_id	= $Config->module(MODULE)->db('integration');
/**
 * Authentication endpoint
 */
if (isset($rc[1]) && $rc[1] == 'endpoint') {
	require_once __DIR__.'/Hybrid/Endpoint.php';
	Hybrid_Endpoint::process();
/**
 * If user specified specify email
 */
} elseif (!isset($_POST['email'])) {
	try {
		$HybridAuth		= new Hybrid_Auth([
			'base_url'	=> $Config->server['base_url'].'/'.MODULE.'/'.$rc[0].'/endpoint/'.$User->get_session(),
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
		/**
		 * Check whether this account was already registered in system. If registered - make login
		 */
		if ($id	= $db->$db_id->qfs([
			"SELECT `id`
			FROM `[prefix]users_social_integration`
			WHERE
				`provider`		= '%s' AND
				`identifier`	= '%s'
			LIMIT 1",
			$rc[0],
			$profile->identifier
		])) {
			$User->add_session($id);
			header('Location: '._getcookie('HybridAuth_referer'));
			_setcookie('HybridAuth_referer', '');
			code_header(301);
			return;
		}
		if (!$profile_info['username']) {
			unset($profile_info['username']);
		}
		$email			= $profile->emailVerified ?: $profile->email;
		$User->set_session_data(
			'HybridAuth',
			[
				'profile_info'	=> $profile_info,
				'provider'		=> $rc[0],
				'identifier'	=> $profile->identifier,
				'profile'		=> $profile->profileURL
			]
		);
		/**
		 * If integrated service returns email
		 */
		if ($email) {
			/**
			 * Try to register user
			 */
			if ($result		= $User->registration($email, false, false)) {
				if ($result == 'error') {
					$Index->content($L->reg_server_error);
					return;
				/**
				 * If email is already registered - make merge of accounts and login
				 */
				} elseif ($result == 'exists') {
					$db->$db_id()->q(
						"INSERT INTO `[prefix]users_social_integration`
							(
								`id`,
								`provider`,
								`identifier`,
								`profile`
							) VALUES (
								'%s',
								'%s',
								'%s',
								'%s'
							)",
						$User->get_id(hash('sha224', $email)),
						$rc[0],
						$profile->identifier,
						$profile->profileURL
					);
					$User->add_session($result['id']);
					header('Location: '.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
					code_header(301);
					return;
				}
				/**
				 * Registration is successful, confirmation is not needed
				 */
				success_registration:
				$body	= $L->reg_success_mail_body(
					isset($profile_info['username']) ? $profile_info['username'] : strstr($result['email'], '@', true),
					get_core_ml_text('name'),
					$Config->server['base_url'].'/profile/'.$User->get('login', $result['id']),
					$User->get('login', $result['id']),
					$result['password']
				);
				/**
				 * Send notification email
				 */
				if ($Mail->send_to(
					$email,
					$L->reg_success_mail(get_core_ml_text('name')),
					$body
				)) {
					$User->set($profile_info, null, $result['id']);
					$User->add_session($result['id']);
					header('Location: '.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
					_setcookie('HybridAuth_referer', '');
					code_header(301);
				} else {
					$User->registration_cancel();
					$Page->title($L->sending_reg_mail_error_title);
					$Page->warning($L->sending_reg_mail_error);
					header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
					_setcookie('HybridAuth_referer', '');
				}
			} else {
				$Page->title($L->reg_server_error);
				$Page->warning($L->reg_server_error);
				header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
				_setcookie('HybridAuth_referer', '');
			}
		/**
		 * If integrated service doesn't returns email - ask user for email
		 */
		} else {
			email_form:
			$Index->form			= true;
			$Index->buttons			= false;
			$Index->post_buttons	= h::{'button[type=submit]'}($L->submit);
			$Index->content(
				h::{'p.cs-center'}(
					$L->please_type_your_email.':'.
					h::{'input[name=email]'}(
						isset($_POST['email']) ? $_POST['email'] : ''
					)
				)
			);
		}
	} catch (Exception $e) {
		trigger_error($e->getMessage());
		header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
		_setcookie('HybridAuth_referer', '');
	}
/**
 * If user specified email
 */
} elseif ($HybridAuth_data = $User->get_session_data('HybridAuth')) {
	/**
	 * Try to register user
	 */
	if ($result		= $User->registration($_POST['email'])) {
		if ($result === false) {
			$Page->title($L->please_type_correct_email);
			$Page->warning($L->please_type_correct_email);
			sleep(1);
			goto email_form;
		} elseif ($result == 'error') {
			$Page->title($L->reg_server_error);
			$Page->warning($L->reg_server_error);
			header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
			_setcookie('HybridAuth_referer', '');
			return;
		/**
		 * If email is already registered
		 */
		} elseif ($result == 'exists') {
			/**
			 * If registration confirmation is not required - make merge of accounts and login
			 */
			if (!$Config->core['require_registration_confirmation']) {
				$db->$db_id()->q(
					"INSERT INTO `[prefix]users_social_integration`
						(
							`id`,
							`provider`,
							`identifier`,
							`profile`
						) VALUES (
							'%s',
							'%s',
							'%s',
							'%s'
						)",
					$User->get_id(hash('sha224', $_POST['email'])),
					$rc[0],
					$HybridAuth_data['identifier'],
					$HybridAuth_data['profile']
				);
				$User->del_session_data('HybridAuth');
				$profile_info				= $HybridAuth_data['profile_info'];
				$email						= $_POST['email'];
				goto success_registration;
			/**
			 * If registration confirmation is required - send merging confirmation mail
			 */
			} else {
				$id							= $User->get_id(hash('sha224', $_POST['email']));
				$HybridAuth_data['id']		= $id;
				$HybridAuth_data['referer']	= _getcookie('HybridAuth_referer') ?: $Config->server['base_url'];
				_setcookie('HybridAuth_referer', '');
				$confirm_key				= $Key->add(
					$db_id,
					false,
					$HybridAuth_data
				);
				$body						= $L->hybridauth_merge_confirmation_mail_body(
					$User->get_username($id) ?: strstr($_POST['email'], '@', true),
					get_core_ml_text('name'),
					$L->{$rc[0]},
					$Config->core['url'].'/'.MODULE.'/merge_confirmation/'.$confirm_key,
					$L->time($Config->core['registration_confirmation_time'], 'd')
				);
				if ($Mail->send_to(
					$_POST['email'],
					$L->hybridauth_merge_confirmation_mail_title(get_core_ml_text('name')),
					$body
				)) {
					_setcookie('HybridAuth_referer', '');
					$Index->content($L->hybridauth_merge_confirmation($L->{$rc[0]}));
				} else {
					$User->registration_cancel();
					$Page->title($L->sending_reg_mail_error_title);
					$Page->warning($L->sending_reg_mail_error);
					header('Refresh: 5; url='.$HybridAuth_data['referer']);
				}
				return;
			}
		/**
		 * Registration is successful and confirmation is not required
		 */
		} elseif ($result['reg_key'] === true) {
			$User->del_session_data('HybridAuth');
			$profile_info				= $HybridAuth_data['profile_info'];
			$email						= $_POST['email'];
			goto success_registration;
		}
		$body	= $L->reg_need_confirmation_mail_body(
			isset($profile_info['username']) ? $profile_info['username'] : strstr($result['email'], '@', true),
			get_core_ml_text('name'),
			$Config->core['url'].'/profile/registration_confirmation/'.$result['reg_key'],
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
			header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
			_setcookie('HybridAuth_referer', '');
		}
	} else {
		$Page->title($L->reg_server_error);
		$Index->content($L->reg_server_error);
		header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->server['base_url']));
		_setcookie('HybridAuth_referer', '');
	}
}