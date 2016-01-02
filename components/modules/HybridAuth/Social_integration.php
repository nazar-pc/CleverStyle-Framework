<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
use
	cs\Cache,
	cs\Config,
	cs\User,
	cs\DB\Accessor,
	cs\Singleton;
/**
 * @method static Social_integration instance($check = false)
 */
class Social_integration {
	use
		Accessor,
		Singleton;
	protected function cdb () {
		return Config::instance()->module('HybridAuth')->db('integration');
	}
	/**
	 * Add social integration
	 *
	 * @param int    $user_id
	 * @param string $provider
	 * @param string $identifier
	 * @param string $profile
	 *
	 * @return false|int|string
	 */
	function add ($user_id, $provider, $identifier, $profile) {
		return $this->db_prime()->q(
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
				)
			ON DUPLICATE KEY UPDATE
				`id`		= VALUES(`id`),
				`profile`	= VALUES(`profile`)",
			$user_id,
			$provider,
			$identifier,
			$profile
		);
	}
	/**
	 * Find user id by provider name and identifier
	 *
	 * @param string $provider
	 * @param string $identifier
	 *
	 * @return false|int User id or `false` if not found
	 */
	function find_integration ($provider, $identifier) {
		return $this->db()->qfs(
			[
				"SELECT `id`
				FROM `[prefix]users_social_integration`
				WHERE
					`provider`		= '%s' AND
					`identifier`	= '%s'
				LIMIT 1",
				$provider,
				$identifier
			]
		);
	}
	/**
	 * Returns array of user id, that are contacts of specified user
	 *
	 * @param int $user_id
	 *
	 * @return int[]
	 */
	function get_contacts ($user_id) {
		$user_id = (int)$user_id;
		if (
			!$user_id ||
			$user_id == User::GUEST_ID ||
			!Config::instance()->module('HybridAuth')->enable_contacts_detection
		) {
			return [];
		}
		return Cache::instance()->get(
			"HybridAuth/contacts/$user_id",
			function () use ($user_id) {
				return $this->db()->qfas(
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
						$user_id
					]
				) ?: [];
			}
		);
	}
	/**
	 * Set user contacts for specified provider
	 *
	 * @param int                    $user_id
	 * @param \Hybrid_User_Contact[] $contacts
	 * @param string                 $provider
	 */
	function set_contacts ($user_id, $contacts, $provider) {
		$this->db_prime()->q(
			"DELETE FROM `[prefix]users_social_integration_contacts`
			WHERE
				`id`		= '%s' AND
				`provider`	= '%s'",
			$user_id,
			$provider
		);
		if ($contacts) {
			$identifiers = [];
			foreach ($contacts as $contact) {
				$identifiers[] = $contact->identifier;
			}
			$this->db_prime()->insert(
				"INSERT INTO `[prefix]users_social_integration_contacts`
				(
					`id`,
					`provider`,
					`identifier`
				) VALUES (
					'$user_id',
					'$provider',
					'%s'
				)",
				$identifiers
			);
		}
		Cache::instance()->del("HybridAuth/contacts/$user_id");
	}
}
