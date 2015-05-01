<?php
/**
 * @package   HybridAuth
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\HybridAuth;
use
	cs\Config,
	cs\DB\Accessor,
	cs\Singleton;
/**
 * @method static Social_integration instance($check = false)
 */
class Social_integration {
	use
		Accessor,
		Singleton;
	protected $data_model = [
		'id'         => 'int:0',
		'provider'   => 'text',
		'identifier' => 'text',
		'profile'    => 'text'
	];
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
}
