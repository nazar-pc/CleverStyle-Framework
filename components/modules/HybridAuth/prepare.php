<?php
/**
 * @package		HybridAuth
 * @category	modules
 * @author		HybridAuth authors
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright	HybridAuth authors
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\HybridAuth;
use			Exception,
			h,
			Hybrid_Endpoint;
/**
 * Provides next triggers:
 *  HybridAuth/registration/before
 *  [
 *   'provider'		=> provider		//Provider name
 *   'email'		=> email		//Email
 *   'identifier'	=> identifier	//Identifier given by provider
 *   'profile'		=> profile		//Profile url
 *  ]
 *
 *  HybridAuth/add_session/before
 *  [
 *   'adapter'	=> $Adapter		//instance of Hybrid_Provider_Adapter
 *   'provider'	=> provider		//Provider name
 *  ]
 *
 *  HybridAuth/add_session/after
 *  [
 *   'adapter'	=> $Adapter		//instance of Hybrid_Provider_Adapter
 *   'provider'	=> provider		//Provider name
 *  ]
 */
global $Config, $User, $L, $Mail, $Page, $Index, $db, $Key, $Core;
$rc			= $Config->route;
/**
 * If user is registered, provider not found or this is request for final authentication and session does not corresponds - return user to the base url
 */
if (
	$User->user() ||
	!(
		isset($rc[0]) &&
		(
			(
				$Config->module('HybridAuth')->providers[$rc[0]] &&
				$Config->module('HybridAuth')->providers[$rc[0]]['enabled']
			) ||
			(
				$rc[0] == 'merge_confirmation' &&
				isset($rc[1])
			)
		)
	) ||
	(
		isset($rc[2]) && strpos($rc[2], $User->get_session()) !== 0
	)
) {
	header('Location: '.$Config->base_url());
	code_header(301);
	interface_off();
	return;
}
/**
 * Merging confirmation
 */
$db_id	= $Config->module('HybridAuth')->db('integration');
if (isset($rc[1]) && $rc[0] == 'merge_confirmation') {
	/**
	 * If confirmation key is valid - make merging
	 */
	if ($data = $Key->get($db_id, $rc[1], true)) {
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
		$HybridAuth		= get_hybridauth_instance($data['provider']);
		$adapter		= $HybridAuth->getAdapter($data['provider']);
		$User->set_data(
			'HybridAuth_session',
			array_merge(
				$User->get_data('HybridAuth_session') ?: [],
				unserialize($HybridAuth->getSessionData())
			)
		);
		if ($User->get('status', $data['id']) == '-1') {
			$User->set('status', 1, $data['id']);
		}
		$Core->run_trigger(
			'HybridAuth/add_session/before',
			[
				'adapter'	=> $adapter,
				'provider'	=> $data['provider']
			]
		);
		$User->add_session($data['id']);
		add_session_after();
		$Core->run_trigger(
			'HybridAuth/add_session/after',
			[
				'adapter'	=> $adapter,
				'provider'	=> $data['provider']
			]
		);
		unset($HybridAuth, $adapter);
		if ($User->id != 1) {
			$existing_data	= $User->get(array_keys($data['profile_info']), $data['id']);
			foreach ($data['profile_info'] as $item => $value) {
				if (!$existing_data[$item] || $existing_data[$item] != $value) {
					$User->set($item, $value, $data['id']);
				}
			}
			unset($existing_data, $item, $value);
			update_user_contacts($data['contacts'], $data['provider']);
		}
		header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
		_setcookie('HybridAuth_referer', '');
		$Index->content(
			$L->hybridauth_merging_confirmed_successfully($L->{$data['provider']})
		);
		unset($data);
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
	strpos($_SERVER['HTTP_REFERER'], $Config->base_url().'/'.'HybridAuth') === false &&
	strpos($_SERVER['HTTP_REFERER'], $Config->base_url()) === 0
) {
	_setcookie('HybridAuth_referer', $_SERVER['HTTP_REFERER']);
}
require_once __DIR__.'/Hybrid/Auth.php';
/**
 * Authentication endpoint
 */
if (isset($rc[1]) && $rc[1] == 'endpoint') {
	require_once __DIR__.'/Hybrid/Endpoint.php';
	Hybrid_Endpoint::process();
/**
 * If user did not specified email
 */
} elseif (!isset($_POST['email'])) {
	try {
		$HybridAuth		= get_hybridauth_instance($rc[0]);
		$adapter		= $HybridAuth->authenticate($rc[0]);
		/**
		 * @var \Hybrid_User_Profile $profile
		 */
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
		 * Remove empty fields
		 */
		foreach ($profile_info as $item => $value) {
			if (!$value) {
				unset($profile_info[$item]);
			}
		}
		unset($item, $value);
		/**
		 * Check whether this account was already registered in system. If registered - make login
		 */
		if (
			(
				$id	= $db->$db_id->qfs([
					"SELECT `id`
					FROM `[prefix]users_social_integration`
					WHERE
						`provider`		= '%s' AND
						`identifier`	= '%s'
					LIMIT 1",
					$rc[0],
					$profile->identifier
				])
			) && $User->get('status', $id) == '1'
		) {
			$Core->run_trigger(
				'HybridAuth/add_session/before',
				[
					'adapter'	=> $adapter,
					'provider'	=> $rc[0]
				]
			);
			$User->add_session($id);
			add_session_after();
			$Core->run_trigger(
				'HybridAuth/add_session/after',
				[
					'adapter'	=> $adapter,
					'provider'	=> $rc[0]
				]
			);
			if ($User->id != 1 && $Config->module('HybridAuth')->enable_contacts_detection) {
				$existing_data	= $User->get(array_keys($profile_info), $id);
				foreach ($profile_info as $item => $value) {
					if (!$existing_data[$item] || $existing_data[$item] != $value) {
						$User->set($item, $value, $id);
					}
				}
				unset($existing_data, $item, $value);
				try {
					update_user_contacts($adapter->getUserContacts(), $rc[0]);
				} catch (Exception $e) {
					unset($e);
				}
			}
			header('Location: '._getcookie('HybridAuth_referer'));
			_setcookie('HybridAuth_referer', '');
			code_header(301);
			return;
		}
		$email			= $profile->emailVerified ?: $profile->email;
		/**
		 * @var \Hybrid_User_Contact[] $contacts
		 */
		$contacts		= [];
		if ($Config->module('HybridAuth')->enable_contacts_detection) {
			try {
				$contacts	= $adapter->getUserContacts();
			} catch (Exception $e) {
				unset($e);
			}
		}
		/**
		 * If integrated service returns email
		 */
		if ($email) {
			/**
			 * Try to register user
			 */
			$adapter		= $HybridAuth->getAdapter($rc[0]);
			if (!$Core->run_trigger(
				'HybridAuth/registration/before',
				[
					'provider'		=> $rc[0],
					'email'			=> $email,
					'identifier'	=> $profile->identifier,
					'profile'		=> $profile->profileURL
				]
			)) {
				return;
			}
			if ($result		= $User->registration($email, false, false)) {
				if ($result == 'error') {
					$Index->content($L->reg_server_error);
					return;
				/**
				 * If email is already registered - make merge of accounts and login
				 */
				} elseif ($result == 'exists') {
					$user	= $User->get_id(hash('sha224', $email));
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
						$user,
						$rc[0],
						$profile->identifier,
						$profile->profileURL
					);
					if ($User->get('status', $user) == '-1') {
						$User->set('status', 1, $user);
					}
					unset($user);
					$Core->run_trigger(
						'HybridAuth/add_session/before',
						[
							'adapter'	=> $adapter,
							'provider'	=> $rc[0]
						]
					);
					$User->add_session($result['id']);
					add_session_after();
					$Core->run_trigger(
						'HybridAuth/add_session/after',
						[
							'adapter'	=> $adapter,
							'provider'	=> $rc[0]
						]
					);
					if ($User->id != 1) {
						$existing_data	= $User->get(array_keys($profile_info), $id);
						foreach ($profile_info as $item => $value) {
							if (!$existing_data[$item] || $existing_data[$item] != $value) {
								$User->set($item, $value, $id);
							}
						}
						unset($existing_data, $item, $value);
						update_user_contacts($contacts, $rc[0]);
					}
					header('Location: '.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
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
					$Config->base_url().'/profile/'.$User->get('login', $result['id']),
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
					$Core->run_trigger(
						'HybridAuth/add_session/before',
						[
							'adapter'	=> $adapter,
							'provider'	=> $rc[0]
						]
					);
					$User->add_session($result['id']);
					add_session_after();
					$Core->run_trigger(
						'HybridAuth/add_session/after',
						[
							'adapter'	=> $adapter,
							'provider'	=> $rc[0]
						]
					);
					if ($User->id != 1) {
						$existing_data	= $User->get(array_keys($profile_info), $id);
						foreach ($profile_info as $item => $value) {
							if (!$existing_data[$item] || $existing_data[$item] != $value) {
								$User->set($item, $value, $id);
							}
						}
						unset($existing_data, $item, $value);
						update_user_contacts($contacts, $rc[0]);
					}
					header('Location: '.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
					_setcookie('HybridAuth_referer', '');
					code_header(301);
				} else {
					$User->registration_cancel();
					$Page->title($L->sending_reg_mail_error_title);
					$Page->warning($L->sending_reg_mail_error);
					header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
					_setcookie('HybridAuth_referer', '');
				}
			} else {
				$Page->title($L->reg_server_error);
				$Page->warning($L->reg_server_error);
				header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
				_setcookie('HybridAuth_referer', '');
			}
			unset($adapter);
		/**
		 * If integrated service does not returns email - ask user for email
		 */
		} else {
			$User->set_session_data(
				'HybridAuth',
				[
					'profile_info'	=> $profile_info,
					'contacts'		=> $contacts,
					'provider'		=> $rc[0],
					'identifier'	=> $profile->identifier,
					'profile'		=> $profile->profileURL
				]
			);
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
		header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
		_setcookie('HybridAuth_referer', '');
	}
/**
 * If user specified email
 */
} elseif ($HybridAuth_data = $User->get_session_data('HybridAuth')) {
	/**
	 * Try to register user
	 */
	if (!$Core->run_trigger(
		'HybridAuth/registration/before',
		[
			'provider'		=> $rc[0],
			'email'			=> $_POST['email'],
			'identifier'	=> $HybridAuth_data['identifier'],
			'profile'		=> $HybridAuth_data['profile']
		]
	)) {
		return;
	}
	if ($result		= $User->registration($_POST['email'])) {
		if ($result === false) {
			$Page->title($L->please_type_correct_email);
			$Page->warning($L->please_type_correct_email);
			sleep(1);
			goto email_form;
		} elseif ($result == 'error') {
			$Page->title($L->reg_server_error);
			$Page->warning($L->reg_server_error);
			header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
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
				$user	= $User->get_id(hash('sha224', $_POST['email']));
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
					$user,
					$rc[0],
					$HybridAuth_data['identifier'],
					$HybridAuth_data['profile']
				);
				if ($User->get('status', $user) == '-1') {
					$User->set('status', 1, $user);
				}
				unset($user);
				$User->del_session_data('HybridAuth');
				$profile_info				= $HybridAuth_data['profile_info'];
				$contacts					= $HybridAuth_data['contacts'];
				$email						= $_POST['email'];
				goto success_registration;
			/**
			 * If registration confirmation is required - send merging confirmation mail
			 */
			} else {
				$id							= $User->get_id(hash('sha224', $_POST['email']));
				$HybridAuth_data['id']		= $id;
				$HybridAuth_data['referer']	= _getcookie('HybridAuth_referer') ?: $Config->base_url();
				_setcookie('HybridAuth_referer', '');
				$confirm_key				= $Key->add(
					$db_id,
					false,
					$HybridAuth_data,
					TIME + $Config->core['registration_confirmation_time'] * 86400
				);
				$body						= $L->hybridauth_merge_confirmation_mail_body(
					$User->username($id) ?: strstr($_POST['email'], '@', true),
					get_core_ml_text('name'),
					$L->{$rc[0]},
					$Config->core_url().'/HybridAuth/merge_confirmation/'.$confirm_key,
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
			$contacts					= $HybridAuth_data['contacts'];
			$email						= $_POST['email'];
			goto success_registration;
		}
		$body	= $L->reg_need_confirmation_mail_body(
			isset($profile_info['username']) ? $profile_info['username'] : strstr($result['email'], '@', true),
			get_core_ml_text('name'),
			$Config->core_url().'/profile/registration_confirmation/'.$result['reg_key'],
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
			header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
			_setcookie('HybridAuth_referer', '');
		}
	} else {
		$Page->title($L->reg_server_error);
		$Index->content($L->reg_server_error);
		header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
		_setcookie('HybridAuth_referer', '');
	}
}