<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
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
 *   'provider'   => provider   //Provider name
 *   'email'      => email      //Email
 *   'identifier' => identifier //Identifier given by provider
 *   'profile'    => profile    //Profile url
 *  ]
 *
 *  HybridAuth/add_session/before
 *  [
 *   'adapter'  => $Adapter //instance of Hybrid_Provider_Adapter
 *   'provider' => provider //Provider name
 *  ]
 *
 *  HybridAuth/add_session/after
 *  [
 *   'adapter'  => $Adapter //instance of Hybrid_Provider_Adapter
 *   'provider' => provider //Provider name
 *  ]
 */
class Controller {
	static function index () {
		$route = Route::instance()->route;
		/**
		 * This should be present in any case, if not - exit from here
		 */
		if (!isset($route[0])) {
			self::redirect();
			return;
		}
		$Config             = Config::instance();
		$Index              = Index::instance();
		$Session            = Session::instance();
		$User               = User::instance();
		$Social_integration = Social_integration::instance();
		$L                  = Language::instance();
		/**
		 * Confirmation of accounts merging
		 */
		if ($route[0] == 'merge_confirmation') {
			self::merge_confirmation($route, $Social_integration, $Session, $User, $Index, $L);
			return;
		}
		$provider = $route[0];
		/**
		 * Authenticated users are not allowed to sign in, also provider should exist and be enabled
		 */
		if (
			$User->user() ||
			!@$Config->module('HybridAuth')->providers[$provider]['enabled']
		) {
			self::redirect();
			return;
		}
		/**
		 * If registration is not allowed - show corresponding error
		 * @TODO allow sign in, but not registration, should be moved in different place
		 */
		$Page = Page::instance();
		if (!$Config->core['allow_user_registration']) {
			$Page->title($L->registration_prohibited);
			$Page->warning($L->registration_prohibited);
			return;
		}
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		/**
		 * If referer is internal website address, but not HybridAuth module - save referer to cookie
		 */
		if (
			!$Session->get_data('HybridAuth') &&
			strpos($_SERVER->referer, $Config->base_url()) === 0 &&
			strpos($_SERVER->referer, $Config->base_url().'/HybridAuth') === false
		) {
			_setcookie('HybridAuth_referer', $_SERVER->referer);
		}
		require_once __DIR__.'/Hybrid/Auth.php';
		require_once __DIR__.'/Hybrid/Endpoint.php';
		/**
		 * Handle authentication endpoint
		 */
		if (isset($route[1]) && $route[1] == 'endpoint') {
			/**
			 * `$rc[2]` should be present and contain special hash for security reasons (this is called as callback from provider)
			 */
			if (
				!isset($route[2]) ||
				strpos($route[2], md5($provider.$Session->get_id())) !== 0
			) {
				self::redirect();
				return;
			}
			Hybrid_Endpoint::process($_REQUEST);
			return;
		}
		/**
		 * If user did not specified email
		 */
		if (!isset($_POST['email'])) {
			self::email_not_specified($provider, $Social_integration, $Session, $User, $Index, $L, $Config, $Page);
			return;
		}
		/**
		 * If user specified email
		 */
		self::email_was_specified($provider, $Social_integration, $Session, $User, $Index, $L, $Config, $Page);
	}
	/**
	 * Redirect to referer or home page
	 *
	 * @throws \ExitException
	 *
	 * @param bool $with_delay If `true` - redirect will be made in 5 seconds after page load
	 */
	protected static function redirect ($with_delay = false) {
		$redirect_to = _getcookie('HybridAuth_referer') ?: Config::instance()->base_url();
		$action      = $with_delay ? 'Refresh: 5; url=' : 'Location: ';
		_header($action.$redirect_to);
		_setcookie('HybridAuth_referer', '');
		if (!$with_delay) {
			code_header(301);
			interface_off();
			throw new \ExitException;
		}
	}
	/**
	 * @param string[]           $route
	 * @param Social_integration $Social_integration
	 * @param Session            $Session
	 * @param User               $User
	 * @param Index              $Index
	 * @param Language           $L
	 */
	protected static function merge_confirmation ($route, $Social_integration, $Session, $User, $Index, $L) {
		if (!isset($route[1])) {
			self::redirect();
		}
		/**
		 * Check confirmation code
		 */
		$data = self::get_data_by_confirmation_code($route[1]);
		if (!$data) {
			$Index->content($L->hybridauth_merge_confirm_code_invalid);
			return;
		}
		/**
		 * If confirmation key is valid - make merging
		 */
		$Social_integration->add(
			$data['id'],
			$data['provider'],
			$data['identifier'],
			$data['profile']
		);
		$Session->del_data('HybridAuth');
		$HybridAuth = get_hybridauth_instance($data['provider']);
		$adapter    = $HybridAuth->getAdapter($data['provider']);
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
				'adapter'  => $adapter,
				'provider' => $data['provider']
			]
		);
		$Session->add($data['id']);
		add_session_after();
		Event::instance()->fire(
			'HybridAuth/add_session/after',
			[
				'adapter'  => $adapter,
				'provider' => $data['provider']
			]
		);
		unset($HybridAuth, $adapter);
		self::update_data(
			$data['contacts'],
			$data['provider'],
			$data['profile_info']
		);
		self::redirect(true);
		$Index->content(
			$L->hybridauth_merging_confirmed_successfully($L->{$data['provider']})
		);
	}
	/**
	 * @param string $code
	 *
	 * @return false|array
	 */
	protected static function get_data_by_confirmation_code ($code) {
		return Key::instance()->get(
			Config::instance()->module('HybridAuth')->db('integration'),
			$code,
			true
		);
	}
	/**
	 * @throws \ExitException
	 *
	 * @param string             $provider
	 * @param Social_integration $Social_integration
	 * @param Session            $Session
	 * @param User               $User
	 * @param Index              $Index
	 * @param Language           $L
	 * @param Config             $Config
	 * @param Page               $Page
	 *
	 * @return bool
	 */
	protected static function email_not_specified ($provider, $Social_integration, $Session, $User, $Index, $L, $Config, $Page) {
		try {
			$HybridAuth = get_hybridauth_instance($provider);
			$adapter    = $HybridAuth->authenticate($provider);
			/**
			 * @var \Hybrid_User_Profile $profile
			 */
			$profile      = $adapter->getUserProfile();
			$profile_info = [
				'username' => $profile->displayName,
				'avatar'   => $profile->photoURL
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
				$id = $Social_integration->find_integration(
					$provider,
					$profile->identifier
				)
				) && $User->get('status', $id) == User::STATUS_ACTIVE
			) {
				static::add_session_and_update_data(
					$id,
					$adapter,
					$provider,
					$profile_info
				);
				return false;
			}
			$email = $profile->emailVerified ?: $profile->email;
			/**
			 * @var \Hybrid_User_Contact[] $contacts
			 */
			$contacts = [];
			if ($Config->module('HybridAuth')->enable_contacts_detection) {
				try {
					$contacts = $adapter->getUserContacts();
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
				$adapter = $HybridAuth->getAdapter($provider);
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
						$provider,
						$profile->identifier,
						$profile->profileURL
					);
					if ($User->get('status', $user) == User::STATUS_NOT_ACTIVATED) {
						$User->set('status', User::STATUS_ACTIVE, $user);
					}
					static::add_session_and_update_data(
						$user,
						$adapter,
						$provider,
						$profile_info
					);
					return false;
				}
				/**
				 * If user doesn't exists - try to register user
				 */
				if (!Event::instance()->fire(
					'HybridAuth/registration/before',
					[
						'provider'   => $provider,
						'email'      => $email,
						'identifier' => $profile->identifier,
						'profile'    => $profile->profileURL
					]
				)
				) {
					return false;
				}
				$result = $User->registration($email, false, false);
				if (!$result || $result == 'error') {
					$Page->title($L->reg_server_error);
					$Page->warning($L->reg_server_error);
					self::redirect(true);
					return false;
				}
				$Social_integration->add(
					$result['id'],
					$provider,
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
					$provider,
					$profile_info
				);
				/**
				 * If integrated service does not returns email - ask user for email
				 */
			} else {
				$Session->set_data(
					'HybridAuth',
					[
						'profile_info' => $profile_info,
						'contacts'     => $contacts,
						'provider'     => $provider,
						'identifier'   => $profile->identifier,
						'profile'      => $profile->profileURL
					]
				);
				self::email_form($Index, $L);
			}
		} catch (\ExitException $e) {
			throw $e;
		} catch (Exception $e) {
			trigger_error($e->getMessage());
			self::redirect(true);
		}
		return true;
	}
	/**
	 * @param Index    $Index
	 * @param Language $L
	 */
	protected function email_form ($Index, $L) {
		$Index->form           = true;
		$Index->buttons        = false;
		$Index->custom_buttons = h::{'button.uk-button[type=submit]'}($L->submit);
		$Index->content(
			h::{'p.cs-center'}(
				$L->please_type_your_email.':'.
				h::{'input[name=email]'}(
					isset($_POST['email']) ? $_POST['email'] : ''
				)
			)
		);
	}
	/**
	 * @throws \ExitException
	 *
	 * @param string             $provider
	 * @param Social_integration $Social_integration
	 * @param Session            $Session
	 * @param User               $User
	 * @param Index              $Index
	 * @param Language           $L
	 * @param Config             $Config
	 * @param Page               $Page
	 * @param array              $HybridAuth_data
	 *
	 * @return bool
	 */
	protected static function email_was_specified ($provider, $Social_integration, $Session, $User, $Index, $L, $Config, $Page) {
		$HybridAuth_data = $Session->get_data('HybridAuth');
		if (!$HybridAuth_data) {
			self::redirect();
		}
		$Mail = Mail::instance();
		/**
		 * Try to register user
		 */
		if (!Event::instance()->fire(
			'HybridAuth/registration/before',
			[
				'provider'   => $provider,
				'email'      => strtolower($_POST['email']),
				'identifier' => $HybridAuth_data['identifier'],
				'profile'    => $HybridAuth_data['profile']
			]
		)
		) {
			return false;
		}
		$core_url = $Config->core_url();
		$result   = $User->registration($_POST['email']);
		if ($result === false) {
			$Page->title($L->please_type_correct_email);
			$Page->warning($L->please_type_correct_email);
			self::email_form($Index, $L);
			return false;
		} elseif ($result == 'error') {
			$Page->title($L->reg_server_error);
			$Page->warning($L->reg_server_error);
			self::redirect(true);
			return false;
			/**
			 * If email is already registered
			 */
		} elseif ($result == 'exists') {
			/**
			 * Send merging confirmation email
			 */
			$id                    = $User->get_id(hash('sha224', strtolower($_POST['email'])));
			$HybridAuth_data['id'] = $id;
			_setcookie('HybridAuth_referer', '');
			$confirmation_code = self::set_data_generate_confirmation_code($HybridAuth_data);
			$body              = $L->hybridauth_merge_confirmation_mail_body(
				$User->username($id) ?: strstr($_POST['email'], '@', true),
				get_core_ml_text('name'),
				$L->$provider,
				"$core_url/HybridAuth/merge_confirmation/$confirmation_code",
				$L->time($Config->core['registration_confirmation_time'], 'd')
			);
			if ($Mail->send_to(
				$_POST['email'],
				$L->hybridauth_merge_confirmation_mail_title(get_core_ml_text('name')),
				$body
			)
			) {
				_setcookie('HybridAuth_referer', '');
				$Index->content(
					h::p(
						$L->hybridauth_merge_confirmation($L->$provider)
					)
				);
			} else {
				$User->registration_cancel();
				$Page->title($L->sending_reg_mail_error_title);
				$Page->warning($L->sending_reg_mail_error);
				self::redirect(true);
			}
			return false;
			/**
			 * Registration is successful and confirmation is not required
			 */
		} elseif ($result['reg_key'] === true) {
			$Session->del_data('HybridAuth');
			$Social_integration->add(
				$result['id'],
				$provider,
				$HybridAuth_data['identifier'],
				$HybridAuth_data['profile']
			);
			$profile_info = $HybridAuth_data['profile_info'];
			try {
				$HybridAuth = get_hybridauth_instance($provider);
				$adapter    = $HybridAuth->getAdapter($provider);
				self::finish_registration_send_email(
					$result['id'],
					$result['password'],
					$adapter,
					$provider,
					$profile_info
				);
			} catch (\ExitException $e) {
				throw $e;
			} catch (Exception $e) {
				trigger_error($e->getMessage());
				self::redirect(true);
			}
		} else {
			$profile_info = $HybridAuth_data['profile_info'];
			$body         = $L->reg_need_confirmation_mail_body(
				isset($profile_info['username']) ? $profile_info['username'] : strstr($result['email'], '@', true),
				get_core_ml_text('name'),
				"$core_url/HybridAuth/merge_confirmation/$result[reg_key]",
				$L->time($Config->core['registration_confirmation_time'], 'd')
			);
			if ($Mail->send_to(
				$_POST['email'],
				$L->reg_need_confirmation_mail(get_core_ml_text('name')),
				$body
			)
			) {
				self::update_data(
					$HybridAuth_data['contacts'],
					$provider,
					$profile_info
				);
				_setcookie('HybridAuth_referer', '');
				$Index->content($L->reg_confirmation);
			} else {
				$User->registration_cancel();
				$Page->title($L->sending_reg_mail_error_title);
				$Page->warning($L->sending_reg_mail_error);
				self::redirect(true);
			}
		}
		$Social_integration->add(
			$result['id'],
			$provider,
			$HybridAuth_data['identifier'],
			$HybridAuth_data['profile']
		);
		return true;
	}
	/**
	 * @throws \ExitException
	 *
	 * @param array $data
	 *
	 * @return false|string
	 */
	protected static function set_data_generate_confirmation_code ($data) {
		$code = Key::instance()->add(
			Config::instance()->module('HybridAuth')->db('integration'),
			false,
			$data,
			TIME + Config::instance()->core['registration_confirmation_time'] * 86400
		);
		if (!$code) {
			error_code(500);
			Page::instance()->error();
			throw new \ExitException;
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
		self::update_data(
			$contacts,
			$provider,
			$profile_info
		);
		self::redirect();
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
			self::add_session_and_update_data(
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
			self::redirect(true);
		}
	}
}
