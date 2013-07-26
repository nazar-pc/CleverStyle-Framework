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
use			h,
			Hybrid_Auth,
			cs\Cache,
			cs\Config,
			cs\DB,
			cs\Language,
			cs\Page,
			cs\Trigger,
			cs\User;
Trigger::instance()->register(
	'System/Page/external_login_list',
	function ($data) {
		$Config			= Config::instance();
		$Page			= Page::instance();
		$User			= User::instance();
		if (!(
			$Config->core['allow_user_registration'] &&
			$Page->interface &&
			$User->guest() &&
			!$User->bot()
		)) {
			return;
		}
		$providers		= $Config->module('HybridAuth')->providers;
		foreach ($providers as $provider => $pdata) {
			if (!$pdata['enabled']) {
				unset($providers[$provider]);
			}
		}
		unset($provider, $pdata);
		if (!count($providers)) {
			return;
		}
		$L				= Language::instance();
		$data['list']	= h::{'ul.cs-hybrid-auth-providers-list li'}(
			[
				$L->or_login_with,
				[
					'class'	=> 'ui-widget-header'
				]
			],
			array_map(
				function ($provider) use ($L) {
					return [
						h::div().
						$L->$provider,
						[
							'data-provider'	=> $provider,
							'class'			=> 'ui-widget-content cs-hybrid-auth-'.$provider
						]
					];
				},
				array_keys($providers)
			)
		);
	}
);
Trigger::instance()->register(
	'System/User/registration/confirmation/after',
	function () {
		if ($referer = _getcookie('HybridAuth_referer')) {
			header('Refresh: 5; url='.$referer);
			_setcookie('HybridAuth_referer', '');
		}
	}
);
Trigger::instance()->register(
	'System/User/del_user/after',
	function ($data) {
		/**
		 *	@var \cs\DB\_Abstract $cdb
		 */
		$cdb			= DB::instance()->{Config::instance()->module('HybridAuth')->db('integration')}();
		$cdb->q(
			[
				"DELETE FROM `[prefix]users_social_integration`
				WHERE `id` = '%s'",
				"DELETE FROM `[prefix]users_social_integration_contacts`
				WHERE `id` = '%s'"
			],
			$data['id']
		);
	}
);
Trigger::instance()->register(
	'System/User/get_contacts',
	function ($data) {
		$data['contacts']	= array_unique(array_merge(
			$data['contacts'],
			get_user_contacts($data['id'])
		));
	}
);
/**
 * Returns array of user id, that are contacts of specified user
 *
 * @param int		$user
 *
 * @return int[]
 */
function get_user_contacts ($user) {
	$Cache	= Cache::instance();
	$Config	= Config::instance();
	$user	= (int)$user;
	if (
		!$user ||
		$user == 1 ||
		!$Config->module('HybridAuth')->enable_contacts_detection
	) {
		return [];
	}
	if (!($data = $Cache->{'HybridAuth/contacts/'.$user})) {
		/**
		 *	@var \cs\DB\_Abstract $cdb
		 */
		$cdb									= DB::instance()->{$Config->module('HybridAuth')->db('integration')};
		$data									= $cdb->qfas([
			"SELECT `i`.`id`
			FROM `[prefix]users_social_integration` AS `i`
			INNER JOIN `[prefix]users_social_integration_contacts` AS `c`
			ON
				`i`.`identifier`	= `c`.`identifier` AND
				`i`.`provider`		= `c`.`provider`
			INNER JOIN `[prefix]users` AS `u`
			ON
				`i`.`id`		= `u`.`id` AND
				`u`.`status`	= '1'
			WHERE `c`.`id`	= '%s'
			GROUP BY `i`.`id`",
			$user
		]) ?: [];
		$Cache->{'HybridAuth/contacts/'.$user}	= $data;
	}
	return $data;
}
/**
 * Updates user contacts for specified provider
 *
 * @param \Hybrid_User_Contact[]	$contacts
 * @param string					$provider
 */
function update_user_contacts ($contacts, $provider) {
	$Cache	= Cache::instance();
	$id		= User::instance()->id;
	/**
	 *	@var \cs\DB\_Abstract $cdb
	 */
	$cdb	= DB::instance()->{Config::instance()->module('HybridAuth')->db('integration')}();
	$cdb->q(
		"DELETE FROM `[prefix]users_social_integration_contacts`
		WHERE
			`id`		= '%s' AND
			`provider`	= '%s'",
		$id,
		$provider
	);
	if (!empty($contacts)) {
		$insert	= [];
		$params	= [];
		foreach ($contacts as $contact) {
			$insert[]	= "('%s', '%s', '%s')";
			$params[]	= $id;
			$params[]	= $provider;
			$params[]	= $contact->identifier;
		}
		$insert	= implode(',', $insert);
		$cdb->q(
			"INSERT INTO `[prefix]users_social_integration_contacts`
			(
				`id`,
				`provider`,
				`identifier`
			) VALUES $insert",
			$params
		);
	}
	unset($Cache->{'HybridAuth/contacts/'.$id});
}
function add_session_after () {
	$User	= User::instance();
	$User->set_data(
		'HybridAuth_session',
		array_merge(
			$User->get_data('HybridAuth_session') ?: [],
			unserialize(get_hybridauth_instance()->getSessionData())
		)
	);
}
/**
 * Get HybridAuth instance with current configuration. Strongly recommended for usage
 *
 * @param null|string	$provider
 * @param null|string	$base_url
 *
 * @return Hybrid_Auth
 */
function get_hybridauth_instance ($provider = null, $base_url = null) {
	require_once __DIR__.'/Hybrid/Auth.php';
	$Config			= Config::instance();
	$User			= User::instance();
	$HybridAuth		= new Hybrid_Auth([
		'base_url'	=> $base_url ?: $Config->base_url().'/HybridAuth/'.$provider.'/endpoint/'.$User->get_session(),
		'providers'	=> $Config->module('HybridAuth')->providers
	]);
	if ($User->user() && MODULE != 'HybridAuth') {
		$HybridAuth->restoreSessionData(serialize($User->get_data('HybridAuth_session')));
	}
	return $HybridAuth;
}