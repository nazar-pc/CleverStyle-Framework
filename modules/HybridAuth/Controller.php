<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
use
	Exception,
	h,
	Hybrid_Endpoint,
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\Key,
	cs\Language\Prefix,
	cs\Mail,
	cs\Page,
	cs\Request,
	cs\Response,
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
	/**
	 * @param Request  $Request
	 * @param Response $Response
	 *
	 * @throws ExitException
	 */
	public static function index ($Request, $Response) {
		$route = $Request->route;
		/**
		 * This should be present in any case, if not - exit from here
		 */
		if (!isset($route[0])) {
			self::redirect();
			return;
		}
		$Config             = Config::instance();
		$Page               = Page::instance();
		$User               = User::instance();
		$Social_integration = Social_integration::instance();
		$L                  = new Prefix('hybridauth_');
		/**
		 * Confirmation of accounts merging
		 */
		if ($route[0] == 'merge_confirmation') {
			self::merge_confirmation($route, $Page, $L);
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
		$referer = $Request->header('referer');
		/**
		 * If referer is internal website address, but not HybridAuth module - save referer to cookie
		 */
		if (
			strpos($referer, $Config->base_url()) === 0 &&
			strpos($referer, $Config->base_url().'/HybridAuth') === false
		) {
			$Response->cookie('HybridAuth_referer', $referer, 0, true);
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
				strpos($route[2], md5($provider.Session::instance()->get_id())) !== 0
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
			self::email_not_specified($Config, $provider, $Social_integration, $User, $Page, $L);
			return;
		}
		/**
		 * If user specified email
		 */
		self::email_was_specified($provider, $Social_integration, $User, $Page, $L, $Config, $Response);
	}
	/**
	 * Redirect to referer or home page
	 *
	 * @param bool $with_delay If `true` - redirect will be made in 5 seconds after page load
	 *
	 * @throws ExitException
	 */
	protected static function redirect ($with_delay = false) {
		$Response    = Response::instance();
		$redirect_to = Request::instance()->cookie('HybridAuth_referer') ?: Config::instance()->base_url();
		$Response->cookie('HybridAuth_referer', '');
		if ($with_delay) {
			$Response->header('refresh', "5; url=$redirect_to");
		} else {
			$Response->redirect($redirect_to, 301);
			Page::instance()->interface = false;
			throw new ExitException;
		}
	}
	/**
	 * @param string[] $route
	 * @param Page     $Page
	 * @param Prefix   $L
	 *
	 * @throws ExitException
	 */
	protected static function merge_confirmation ($route, $Page, $L) {
		if (!isset($route[1])) {
			self::redirect();
		}
		/**
		 * Check confirmation code
		 */
		$data = self::get_data_by_confirmation_code($route[1]);
		if (!$data) {
			$Page->warning($L->merge_confirm_code_invalid);
			return;
		}
		/**
		 * If confirmation key is valid  - merge social profile with main account
		 */
		self::add_integration_create_session(
			$data['id'],
			$data['provider'],
			$data['identifier'],
			$data['profile']
		);
		self::save_hybridauth_session();
		$Page->success(
			$L->merging_confirmed_successfully($L->{$data['provider']})
		);
	}
	/**
	 * @param int    $id
	 * @param string $provider
	 * @param string $identifier
	 * @param string $profile
	 */
	protected static function add_integration_create_session ($id, $provider, $identifier, $profile) {
		Social_integration::instance()->add($id, $provider, $identifier, $profile);
		$User = User::instance();
		/**
		 * If user was not activated before - activate him
		 */
		if ($User->get('status', $id) == User::STATUS_NOT_ACTIVATED) {
			$User->set('status', User::STATUS_ACTIVE, $id);
		}
		self::add_session_and_update_data($id, $provider, true);
	}
	/**
	 * Save HybridAuth session in user's data in order to restore it next time when calling `get_hybridauth_instance()`
	 */
	protected static function save_hybridauth_session () {
		$User = User::instance();
		$User->set_data(
			'HybridAuth_session',
			array_merge(
				$User->get_data('HybridAuth_session') ?: [],
				unserialize(get_hybridauth_instance()->getSessionData())
			)
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
	 * @param Config             $Config
	 * @param string             $provider
	 * @param Social_integration $Social_integration
	 * @param User               $User
	 * @param Page               $Page
	 * @param Prefix             $L
	 *
	 * @throws ExitException
	 */
	protected static function email_not_specified ($Config, $provider, $Social_integration, $User, $Page, $L) {
		$profile = self::authenticate_hybridauth($provider);
		/**
		 * Check whether this account was already registered in system. If registered - make login
		 */
		$user = $Social_integration->find_integration($provider, $profile->identifier);
		if (
			$user &&
			$User->get('status', $user) == User::STATUS_ACTIVE
		) {
			self::add_session_and_update_data($user, $provider);
			return;
		}
		if (!Config::instance()->core['allow_user_registration']) {
			Page::instance()
				->title($L->registration_prohibited)
				->warning($L->registration_prohibited);
			return;
		}
		$email = strtolower($profile->emailVerified ?: $profile->email);
		/**
		 * If integrated service does not returns email - ask user for email
		 */
		if (!$email) {
			self::email_form($Page, $L);
			return;
		}
		/**
		 * Search for user with such email
		 */
		$user = $User->get_id(hash('sha224', $email));
		/**
		 * If email is already registered - merge social profile with main account
		 */
		if ($user) {
			self::add_integration_create_session($user, $provider, $profile->identifier, $profile->profileURL);
			return;
		}
		/**
		 * If user doesn't exists - try to register user
		 */
		$result = self::try_to_register($provider, $email, false);
		if (!$result) {
			return;
		}
		$Social_integration->add($result['id'], $provider, $profile->identifier, $profile->profileURL);
		/**
		 * Registration is successful, confirmation is not needed
		 */
		self::finish_registration_send_email($Config, $result['id'], $result['password'], $provider);
	}
	/**
	 * Returns profile
	 *
	 * @param string $provider
	 *
	 * @return \Hybrid_User_Profile
	 *
	 * @throws ExitException
	 */
	protected static function authenticate_hybridauth ($provider) {
		try {
			return get_hybridauth_instance($provider)::authenticate($provider)->getUserProfile();
		} catch (ExitException $e) {
			throw $e;
		} catch (Exception $e) {
			trigger_error($e->getMessage());
			self::redirect(true);
			throw new ExitException;
		}
	}
	/**
	 * @throws ExitException
	 *
	 * @param string $provider
	 * @param string $email
	 * @param bool   $email_from_user
	 *
	 * @return array|false|string
	 */
	protected static function try_to_register ($provider, $email, $email_from_user) {
		$profile = self::authenticate_hybridauth($provider);
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
		$L      = new Prefix('hybridauth_');
		$Page   = Page::instance();
		$User   = User::instance();
		$result = $email_from_user ? $User->registration($email) : $User->registration($email, false, false);
		if (!$result && $email_from_user) {
			$Page
				->title($L->please_type_correct_email)
				->warning($L->please_type_correct_email);
			self::email_form($Page, $L);
			return false;
		}
		if (!$result || $result == 'error') {
			$Page
				->title($L->registration_server_error)
				->warning($L->registration_server_error);
			self::redirect(true);
			return false;
		}
		return $result;
	}
	/**
	 * @param Page   $Page
	 * @param Prefix $L
	 */
	protected static function email_form ($Page, $L) {
		$Page->content(
			h::{'form[is=cs-form]'}(
				h::{'p.cs-text-center'}(
					$L->please_type_your_email.':'.
					h::{'input[name=email]'}(
						isset($_POST['email']) ? $_POST['email'] : ''
					)
				).
				h::{'button[is=cs-button][type=submit]'}(
					$L->submit
				)
			)
		);
	}
	/**
	 * @param string             $provider
	 * @param Social_integration $Social_integration
	 * @param User               $User
	 * @param Page               $Page
	 * @param Prefix             $L
	 * @param Config             $Config
	 * @param Response           $Response
	 *
	 * @throws ExitException
	 */
	protected static function email_was_specified ($provider, $Social_integration, $User, $Page, $L, $Config, $Response) {
		$profile = self::authenticate_hybridauth($provider);
		/**
		 * Try to register user
		 */
		$result = self::try_to_register($provider, $_POST['email'], true);
		if (!$result) {
			return;
		}
		$core_url = $Config->core_url();
		/**
		 * If email is already registered
		 */
		if ($result == 'exists') {
			/**
			 * Send merging confirmation email
			 */
			$id                    = $User->get_id(hash('sha224', strtolower($_POST['email'])));
			$HybridAuth_data['id'] = $id;
			$confirmation_code     = self::set_data_generate_confirmation_code($HybridAuth_data);
			$title                 = $L->merge_confirmation_mail_title($Config->core['site_name']);
			$body                  = $L->merge_confirmation_mail_body(
				$User->username($id) ?: strstr($_POST['email'], '@', true),
				$Config->core['site_name'],
				$L->$provider,
				"$core_url/HybridAuth/merge_confirmation/$confirmation_code",
				$L->time($Config->core['registration_confirmation_time'], 'd')
			);
			if (self::send_registration_mail($_POST['email'], $title, $body)) {
				$Response->cookie('HybridAuth_referer', '');
				$Page->content(
					h::p(
						$L->merge_confirmation($L->$provider)
					)
				);
			}
			return;
		}
		/**
		 * Registration is successful and confirmation is not required
		 */
		if ($result['reg_key'] === true) {
			$Social_integration->add($result['id'], $provider, $profile->identifier, $profile->profileURL);
			self::finish_registration_send_email($Config, $result['id'], $result['password'], $provider);
			return;
		}
		/**
		 * Registration is successful, but confirmation is needed
		 */
		$title = $L->registration_need_confirmation_mail($Config->core['site_name']);
		$body  = $L->registration_need_confirmation_mail_body(
			self::get_adapter($provider)->getUserProfile()->displayName ?: strstr($result['email'], '@', true),
			$Config->core['site_name'],
			"$core_url/profile/registration_confirmation/$result[reg_key]",
			$L->time($Config->core['registration_confirmation_time'], 'd')
		);
		if (self::send_registration_mail($_POST['email'], $title, $body)) {
			self::update_data($provider);
			$Response->cookie('HybridAuth_referer', '');
			$Page->content($L->registration_confirmation);
		}
	}
	/**
	 * @param string $email
	 * @param string $title
	 * @param string $body
	 *
	 * @return bool
	 *
	 * @throws ExitException
	 */
	protected static function send_registration_mail ($email, $title, $body) {
		$result = Mail::instance()->send_to($email, $title, $body);
		/**
		 * If mail sending failed - cancel registration, show error message and redirect to referrer page
		 */
		if (!$result) {
			User::instance()->registration_cancel();
			$L = new Prefix('hybridauth_');
			Page::instance()
				->title($L->registration_mail_sending_error_title)
				->warning($L->registration_mail_sending_error);
			self::redirect(true);
		}
		return $result;
	}
	/**
	 * @param array $data
	 *
	 * @return false|string
	 *
	 * @throws ExitException
	 */
	protected static function set_data_generate_confirmation_code ($data) {
		$code = Key::instance()->add(
			Config::instance()->module('HybridAuth')->db('integration'),
			false,
			$data,
			time() + Config::instance()->core['registration_confirmation_time'] * 86400
		);
		if (!$code) {
			throw new ExitException(500);
		}
		return $code;
	}
	/**
	 * @param int    $user_id
	 * @param string $provider
	 * @param bool   $redirect_with_delay
	 *
	 * @throws ExitException
	 */
	protected static function add_session_and_update_data ($user_id, $provider, $redirect_with_delay = false) {
		$adapter = self::get_adapter($provider);
		Event::instance()->fire(
			'HybridAuth/add_session/before',
			[
				'adapter'  => $adapter,
				'provider' => $provider
			]
		);
		Session::instance()->add($user_id);
		self::save_hybridauth_session();
		Event::instance()->fire(
			'HybridAuth/add_session/after',
			[
				'adapter'  => $adapter,
				'provider' => $provider
			]
		);
		self::update_data($provider);
		self::redirect($redirect_with_delay);
	}
	/**
	 * @param string $provider
	 */
	protected static function update_data ($provider) {
		$User    = User::instance();
		$user_id = $User->id;
		if ($user_id != User::GUEST_ID) {
			$adapter       = self::get_adapter($provider);
			$profile       = $adapter->getUserProfile();
			$profile_info  = [
				'username' => $profile->displayName,
				'avatar'   => $profile->photoURL
			];
			$profile_info  = array_filter($profile_info);
			$existing_data = $User->get(array_keys($profile_info), $user_id);
			foreach ($profile_info as $item => $value) {
				if (!$existing_data[$item] || $existing_data[$item] != $value) {
					$User->set($item, $value, $user_id);
				}
			}
		}
	}
	/**
	 * @param Config $Config
	 * @param int    $user_id
	 * @param string $password
	 * @param string $provider
	 */
	protected static function finish_registration_send_email ($Config, $user_id, $password, $provider) {
		$L         = new Prefix('hybridauth_');
		$User      = User::instance();
		$user_data = $User->$user_id;
		$base_url  = Config::instance()->base_url();
		$title     = $L->registration_success_mail($Config->core['site_name']);
		$body      = $L->registration_success_mail_body(
			self::get_adapter($provider)->getUserProfile()->displayName ?: $user_data->username(),
			$Config->core['site_name'],
			"$base_url/profile/settings",
			$user_data->login,
			$password
		);
		/**
		 * Send notification email
		 */
		if (self::send_registration_mail($user_data->email, $title, $body)) {
			self::add_session_and_update_data($user_id, $provider);
		}
	}
	/**
	 * @param string $provider
	 *
	 * @return \Hybrid_Provider_Adapter
	 *
	 * @throws ExitException
	 */
	protected static function get_adapter ($provider) {
		try {
			return get_hybridauth_instance($provider)::getAdapter($provider);
		} catch (ExitException $e) {
			throw $e;
		} catch (Exception $e) {
			trigger_error($e->getMessage());
			throw new ExitException(500);
		}
	}
}
