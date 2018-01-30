<?php
/**
 * @package  HybridAuth
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
 */
namespace cs\modules\HybridAuth;
use
	cs\Config,
	cs\DB\Accessor,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
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
	 * @return bool
	 */
	public function add ($user_id, $provider, $identifier, $profile) {
		return (bool)$this->db_prime()->q(
			"REPLACE INTO `[prefix]users_social_integration`
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
	public function find_integration ($provider, $identifier) {
		return $this->db()->qfs(
			"SELECT `id`
			FROM `[prefix]users_social_integration`
			WHERE
				`provider`		= '%s' AND
				`identifier`	= '%s'
			LIMIT 1",
			$provider,
			$identifier
		);
	}
}
