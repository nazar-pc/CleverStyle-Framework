<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace	cs\modules\HybridAuth;
use
	Exception,
	h,
	Hybrid_Endpoint,
	cs\Config,
	cs\Event,
	cs\Index,
	cs\Key,
	cs\Language,
	cs\Mail,
	cs\Page,
	cs\Route,
	cs\Session,
	cs\User;
/**
 * Provides next events:
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
class Controller {
	static function index () {
		$Config		= Config::instance();
		$Index		= Index::instance();
		$Session	= Session::instance();
		$User		= User::instance();
		$rc			= Route::instance()->route;
		/**
		 * If user is registered, provider not found or this is request for final authentication and session does not corresponds - return user to the base url
		 */
		if (
			(
				$User->user() &&
				(
					!isset($rc[0]) ||
					$rc[0] != 'merge_confirmation'
				)
			) ||
			!(
				isset($rc[0]) &&
				(
					(
						isset($Config->module('HybridAuth')->providers[$rc[0]]) &&
						$Config->module('HybridAuth')->providers[$rc[0]]['enabled']
					) ||
					(
						$rc[0] == 'merge_confirmation' &&
						isset($rc[1])
					)
				)
			) ||
			(
				isset($rc[2]) && strpos($rc[2], md5($rc[0].$Session->get_id())) !== 0
			)
		) {
			_header('Location: '.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
			code_header(301);
			interface_off();
			return;
		}
		/**
		 * Merging confirmation
		 */
		$db_id		= $Config->module('HybridAuth')->db('integration');
		$Social_integration = Social_integration::instance();
		$Key		= Key::instance();
		$L			= Language::instance();
		if (isset($rc[1]) && $rc[0] == 'merge_confirmation') {
			/**
			 * If confirmation key is valid - make merging
			 */
			if ($data = $Key->get($db_id, $rc[1], true)) {
				$Social_integration->add(
					$data['id'],
					$data['provider'],
					$data['identifier'],
					$data['profile']
				);
				$Session->del_data('HybridAuth');
				$HybridAuth		= get_hybridauth_instance($data['provider']);
				$adapter		= $HybridAuth->getAdapter($data['provider']);
				$User->set_data(
					'HybridAuth_session',
					array_merge(
						$User->get_data('HybridAuth_session') ?: [],
						unserialize($HybridAuth->getSessionData())
					)
				);
				if ($User->get('status', $data['id']) == User::STATUS_NOT_ACTIVATED) {
					$User->set('status', User::STATUS_ACTIVE, $data['id']);
				}
				Event::instance()->fire(
					'HybridAuth/add_session/before',
					[
						'adapter'	=> $adapter,
						'provider'	=> $data['provider']
					]
				);
				$Session->add($data['id']);
				add_session_after();
				Event::instance()->fire(
					'HybridAuth/add_session/after',
					[
						'adapter'	=> $adapter,
						'provider'	=> $data['provider']
					]
				);
				unset($HybridAuth, $adapter);
				static::update_data(
					$data['contacts'],
					$data['provider'],
					$data['profile_info']
				);
				_header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
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
		$Page		= Page::instance();
		if (!$Config->core['allow_user_registration']) {
			$Page->title($L->registration_prohibited);
			$Page->warning($L->registration_prohibited);
			return;
		}
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		/**
		 * If referer is internal address and not current authentication module - save referer
		 */
		if (
			!$Session->get_data('HybridAuth') &&
			strpos($_SERVER->referer, $Config->base_url().'/HybridAuth') === false &&
			strpos($_SERVER->referer, $Config->base_url()) === 0
		) {
			_setcookie('HybridAuth_referer', $_SERVER->referer);
		}
		require_once __DIR__.'/Hybrid/Auth.php';
		$Mail		= Mail::instance();
		/**
		 * Authentication endpoint
		 */
		if (isset($rc[1]) && $rc[1] == 'endpoint') {
			require_once __DIR__.'/Hybrid/Endpoint.php';
			Hybrid_Endpoint::process($_REQUEST);
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
					'avatar'	=> $profile->photoURL
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
						$id	= $Social_integration->find_integration(
							$rc[0],
							$profile->identifier
						)
					) && $User->get('status', $id) == User::STATUS_ACTIVE
				) {
					static::add_session_and_update_data(
						$id,
						$adapter,
						$rc[0],
						$profile_info
					);
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
					} catch (\ExitException $e) {
						throw $e;
					} catch (Exception $e) {
						unset($e);
					}
				}
				/**
				 * If integrated service returns email
				 */
				if ($email) {
					$adapter = $HybridAuth->getAdapter($rc[0]);
					/**
					 * Search for user with such email
					 */
					$user = $User->get_id(hash('sha224', $email));
					/**
					 * If email is already registered - make merge of accounts and login
					 */
					if ($user) {
						$Social_integration->add(
							$user,
							$rc[0],
							$profile->identifier,
							$profile->profileURL
						);
						if ($User->get('status', $user) == User::STATUS_NOT_ACTIVATED) {
							$User->set('status', User::STATUS_ACTIVE, $user);
						}
						static::add_session_and_update_data(
							$user,
							$adapter,
							$rc[0],
							$profile_info
						);
						return;
					}
					/**
					 * If user doesn't exists - try to register user
					 */
					if (!Event::instance()->fire(
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
					$result = $User->registration($email, false, false);
					if (!$result || $result == 'error') {
						$Page->title($L->reg_server_error);
						$Page->warning($L->reg_server_error);
						_header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
						_setcookie('HybridAuth_referer', '');
						return;
					}
					$Social_integration->add(
						$result['id'],
						$rc[0],
						$profile->identifier,
						$profile->profileURL
					);
					/**
					 * Registration is successful, confirmation is not needed
					 */
					static::finish_registration_send_email(
						$result['id'],
						$result['password'],
						$adapter,
						$rc[0],
						$profile_info
					);
				/**
				 * If integrated service does not returns email - ask user for email
				 */
				} else {
					$Session->set_data(
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
					$Index->custom_buttons	= h::{'button.uk-button[type=submit]'}($L->submit);
					$Index->content(
						h::{'p.cs-center'}(
							$L->please_type_your_email.':'.
							h::{'input[name=email]'}(
								isset($_POST['email']) ? $_POST['email'] : ''
							)
						)
					);
				}
			} catch (\ExitException $e) {
				throw $e;
			} catch (Exception $e) {
				trigger_error($e->getMessage());
				_header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
				_setcookie('HybridAuth_referer', '');
			}
		/**
		 * If user specified email
		 */
		} elseif ($HybridAuth_data = $Session->get_data('HybridAuth')) {
			/**
			 * Try to register user
			 */
			if (!Event::instance()->fire(
				'HybridAuth/registration/before',
				[
					'provider'		=> $rc[0],
					'email'			=> strtolower($_POST['email']),
					'identifier'	=> $HybridAuth_data['identifier'],
					'profile'		=> $HybridAuth_data['profile']
				]
			)) {
				return;
			}
			$result = $User->registration($_POST['email']);
			if ($result === false) {
				$Page->title($L->please_type_correct_email);
				$Page->warning($L->please_type_correct_email);
				goto email_form;
			} elseif ($result == 'error') {
				$Page->title($L->reg_server_error);
				$Page->warning($L->reg_server_error);
				_header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
				_setcookie('HybridAuth_referer', '');
				return;
			/**
			 * If email is already registered
			 */
			} elseif ($result == 'exists') {
				/**
				 * Send merging confirmation email
				 */
				$id							= $User->get_id(hash('sha224', strtolower($_POST['email'])));
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
					$Index->content(
						h::p(
							$L->hybridauth_merge_confirmation($L->{$rc[0]})
						)
					);
				} else {
					$User->registration_cancel();
					$Page->title($L->sending_reg_mail_error_title);
					$Page->warning($L->sending_reg_mail_error);
					_header('Refresh: 5; url='.$HybridAuth_data['referer']);
				}
				return;
			/**
			 * Registration is successful and confirmation is not required
			 */
			} elseif ($result['reg_key'] === true) {
				$Session->del_data('HybridAuth');
				$Social_integration->add(
					$result['id'],
					$rc[0],
					$HybridAuth_data['identifier'],
					$HybridAuth_data['profile']
				);
				$profile_info = $HybridAuth_data['profile_info'];
				try {
					$HybridAuth = get_hybridauth_instance($rc[0]);
					$adapter    = $HybridAuth->getAdapter($rc[0]);
					static::finish_registration_send_email(
						$result['id'],
						$result['password'],
						$adapter,
						$rc[0],
						$profile_info
					);
				} catch (\ExitException $e) {
					throw $e;
				} catch (Exception $e) {
					trigger_error($e->getMessage());
					_header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
					_setcookie('HybridAuth_referer', '');
				}
			} else {
				$profile_info	= $HybridAuth_data['profile_info'];
				$body			= $L->reg_need_confirmation_mail_body(
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
					static::update_data(
						$HybridAuth_data['contacts'],
						$rc[0],
						$profile_info
					);
					_setcookie('HybridAuth_referer', '');
					$Index->content($L->reg_confirmation);
				} else {
					$User->registration_cancel();
					$Page->title($L->sending_reg_mail_error_title);
					$Page->warning($L->sending_reg_mail_error);
					_header('Refresh: 5; url='.(_getcookie('HybridAuth_referer') ?: $Config->base_url()));
					_setcookie('HybridAuth_referer', '');
				}
			}
			$Social_integration->add(
				$result['id'],
				$rc[0],
				$HybridAuth_data['identifier'],
				$HybridAuth_data['profile']
			);
		}
	}
	/**
	 * @param int                      $user_id
	 * @param \Hybrid_Provider_Adapter $adapter
	 * @param string                   $provider
	 * @param array                    $profile_info
	 *
	 */
	protected static function add_session_and_update_data ($user_id, $adapter, $provider, $profile_info) {
		Event::instance()->fire(
			'HybridAuth/add_session/before',
			[
				'adapter'  => $adapter,
				'provider' => $provider
			]
		);
		Session::instance()->add($user_id);
		add_session_after();
		Event::instance()->fire(
			'HybridAuth/add_session/after',
			[
				'adapter'  => $adapter,
				'provider' => $provider
			]
		);
		$Config   = Config::instance();
		$contacts = [];
		if ($Config->module('HybridAuth')->enable_contacts_detection) {
			try {
				$contacts = $adapter->getUserContacts();
			} catch (Exception $e) {
				unset($e);
			}
		}
		static::update_data(
			$contacts,
			$provider,
			$profile_info
		);
		$redirect_to = _getcookie('HybridAuth_referer') ?: $Config->base_url();
		_header("Location: $redirect_to");
		_setcookie('HybridAuth_referer', '');
		code_header(301);
	}
	/**
	 * @param array  $contacts
	 * @param string $provider
	 * @param array  $profile_info
	 */
	protected static function update_data ($contacts, $provider, $profile_info) {
		$User    = User::instance();
		$user_id = $User->id;
		if ($user_id != User::GUEST_ID) {
			$existing_data = $User->get(array_keys($profile_info), $user_id);
			foreach ($profile_info as $item => $value) {
				if (!$existing_data[$item] || $existing_data[$item] != $value) {
					$User->set($item, $value, $user_id);
				}
			}
			update_user_contacts($contacts, $provider);
		}
	}
	/**
	 * @param int                      $user_id
	 * @param string                   $password
	 * @param \Hybrid_Provider_Adapter $adapter
	 * @param string                   $provider
	 * @param array                    $profile_info
	 */
	protected static function finish_registration_send_email ($user_id, $password, $adapter, $provider, $profile_info) {
		$L         = Language::instance();
		$User      = User::instance();
		$user_data = $User->$user_id;
		$base_url  = Config::instance()->base_url();
		$body      = $L->reg_success_mail_body(
			isset($profile_info['username']) ? $profile_info['username'] : $user_data->username(),
			get_core_ml_text('name'),
			"$base_url/profile/settings",
			$user_data->login,
			$password
		);
		/**
		 * Send notification email
		 */
		if (Mail::instance()->send_to(
			$user_data->email,
			$L->reg_success_mail(get_core_ml_text('name')),
			$body
		)
		) {
			static::add_session_and_update_data(
				$user_id,
				$adapter,
				$provider,
				$profile_info
			);
		} else {
			$User->registration_cancel();
			Page::instance()
				->title($L->sending_reg_mail_error_title)
				->warning($L->sending_reg_mail_error);
			$redirect_to = _getcookie('HybridAuth_referer') ?: $base_url;
			_header("Refresh: 5; url=$redirect_to");
			_setcookie('HybridAuth_referer', '');
		}
	}
}
