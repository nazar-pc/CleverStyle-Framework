<?php
/**
 * @package        HybridAuth
 * @category       modules
 * @author         HybridAuth authors
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com> (integration with CleverStyle CMS)
 * @copyright      HybridAuth authors
 * @license        MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
use
	Hybrid_Auth,
	cs\Cache,
	cs\Config,
	cs\DB,
	cs\Session,
	cs\User;
/**
 * Returns array of user id, that are contacts of specified user
 *
 * @param int $user
 *
 * @return int[]
 */
function get_user_contacts ($user) {
	$Cache  = Cache::instance();
	$Config = Config::instance();
	$user   = (int)$user;
	if (
		!$user ||
		$user == User::GUEST_ID ||
		!$Config->module('HybridAuth')->enable_contacts_detection
	) {
		return [];
	}
	return $Cache->get(
		"HybridAuth/contacts/$user",
		function () use ($user, $Config) {
			/**
			 * @var \cs\DB\_Abstract $cdb
			 */
			$cdb = DB::instance()->{$Config->module('HybridAuth')->db('integration')};
			return $cdb->qfas(
				[
					"SELECT `i`.`id`
					FROM `[prefix]users_social_integration` AS `i`
					INNER JOIN `[prefix]users_social_integration_contacts` AS `c`
					ON
						`i`.`identifier`	= `c`.`identifier` AND
						`i`.`provider`		= `c`.`provider`
					INNER JOIN `[prefix]users` AS `u`
					ON
						`i`.`id`		= `u`.`id` AND
						`u`.`status`	= '%s'
					WHERE `c`.`id`	= '%s'
					GROUP BY `i`.`id`",
					User::STATUS_ACTIVE,
					$user
				]
			) ?: [];
		}
	);
}

/**
 * Updates user contacts for specified provider
 *
 * @param \Hybrid_User_Contact[] $contacts
 * @param string                 $provider
 */
function update_user_contacts ($contacts, $provider) {
	$Cache = Cache::instance();
	$id    = User::instance()->id;
	/**
	 * @var \cs\DB\_Abstract $cdb
	 */
	$cdb = DB::instance()->{Config::instance()->module('HybridAuth')->db('integration')}();
	$cdb->q(
		"DELETE FROM `[prefix]users_social_integration_contacts`
		WHERE
			`id`		= '%s' AND
			`provider`	= '%s'",
		$id,
		$provider
	);
	if (!empty($contacts)) {
		$insert = [];
		$params = [];
		foreach ($contacts as $contact) {
			$insert[] = "('%s', '%s', '%s')";
			$params[] = $id;
			$params[] = $provider;
			$params[] = $contact->identifier;
		}
		$insert = implode(',', $insert);
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
	unset($Cache->{"HybridAuth/contacts/$id"});
}

function add_session_after () {
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
 * Get HybridAuth instance with current configuration. Strongly recommended for usage
 *
 * @param null|string $provider
 * @param null|string $base_url
 *
 * @return Hybrid_Auth
 */
function get_hybridauth_instance ($provider = null, $base_url = null) {
	require_once __DIR__.'/../../Hybrid/Auth.php';
	$Config     = Config::instance();
	$User       = User::instance();
	$HybridAuth = new Hybrid_Auth(
		[
			'base_url'  => $base_url ?: $Config->base_url()."/HybridAuth/$provider/endpoint/".md5($provider.Session::instance()->get_id()),
			'providers' => $Config->module('HybridAuth')->providers
		]
	);
	if ($User->user() && current_module() != 'HybridAuth') {
		$HybridAuth->restoreSessionData(serialize($User->get_data('HybridAuth_session')));
	}
	return $HybridAuth;
}
