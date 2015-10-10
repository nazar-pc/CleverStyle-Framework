<?php
/**
 * @package        OAuth2
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2011-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\OAuth2;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\Session,
	cs\User,
	cs\DB\Accessor,
	cs\Singleton;

//TODO: user CRUD trait
/**
 * @method static OAuth2 instance($check = false)
 */
class OAuth2 {
	use
		Accessor,
		Singleton;
	/**
	 * @var bool
	 */
	protected $guest_tokens;
	/**
	 * @var bool
	 */
	protected $automatic_prolongation;
	/**
	 * @var int
	 */
	protected $expiration;
	/**
	 * @var Prefix
	 */
	protected $cache;
	function construct () {
		$this->cache                  = new Prefix('OAuth2');
		$module_data                  = Config::instance()->module('OAuth2');
		$this->guest_tokens           = $module_data->guest_tokens;
		$this->automatic_prolongation = $module_data->automatic_prolongation;
		$this->expiration             = $module_data->expiration;
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('OAuth2')->db('oauth2');
	}
	/**
	 * Add new client
	 *
	 * @param string $name
	 * @param string $domain
	 * @param int    $active
	 *
	 * @return false|string            <i>false</i> on failure, id of created client otherwise
	 */
	function add_client ($name, $domain, $active) {
		if (
			!$domain ||
			strpos($domain, '/') !== false
		) {
			return false;
		}
		/**
		 * Generate hash in cycle, to obtain unique value
		 */
		/** @noinspection LoopWhichDoesNotLoopInspection */
		while (true) {
			$id = md5(random_bytes(1000));
			if ($this->db_prime()->qf(
				"SELECT `id`
				FROM `[prefix]oauth2_clients`
				WHERE `id` = '$id'
				LIMIT 1"
			)
			) {
				continue;
			}
			$this->db_prime()->q(
				"INSERT INTO `[prefix]oauth2_clients`
					(
						`id`,
						`secret`,
						`name`,
						`domain`,
						`active`
					) VALUES (
						'%s',
						'%s',
						'%s',
						'%s',
						'%s'
					)",
				$id,
				md5(random_bytes(1000)),
				xap($name),
				xap($domain),
				(int)(bool)$active
			);
			unset($this->cache->$id);
			return $id;
		}
	}
	/**
	 * Get client data
	 *
	 * @param string $id
	 *
	 * @return array|false
	 */
	function get_client ($id) {
		if (!is_md5($id)) {
			return false;
		}
		return $this->cache->get(
			$id,
			function () use ($id) {
				return $this->db()->qf(
					[
						"SELECT *
						FROM `[prefix]oauth2_clients`
						WHERE `id`	= '%s'
						LIMIT 1",
						$id
					]
				);
			}
		);
	}
	/**
	 * Set client data
	 *
	 * @param string $id
	 * @param string $secret
	 * @param string $name
	 * @param string $domain
	 * @param int    $active
	 *
	 * @return bool
	 */
	function set_client ($id, $secret, $name, $domain, $active) {
		if (
			!is_md5($id) ||
			!is_md5($secret) ||
			!$domain ||
			strpos($domain, '/') !== false
		) {
			return false;
		}
		$result = $this->db_prime()->q(
			"UPDATE `[prefix]oauth2_clients`
			SET
				`secret`		= '%s',
				`name`			= '%s',
				`domain`		= '%s',
				`active`		= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			$secret,
			xap($name),
			xap($domain),
			(int)(bool)$active,
			$id
		);
		unset($this->cache->$id);
		return !!$result;
	}
	/**
	 * Delete client
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	function del_client ($id) {
		$result = $this->db_prime()->q(
			[
				"DELETE FROM `[prefix]oauth2_clients`
				WHERE `id` = '%s'
				LIMIT 1",
				"DELETE FROM `[prefix]oauth2_clients_grant_access`
				WHERE `id`	= '%s'",
				"DELETE FROM `[prefix]oauth2_clients_sessions`
				WHERE `id`	= '%s'"
			],
			$id
		);
		unset($this->cache->{'/'});
		return !!$result;
	}
	/**
	 * Get clients list in form of associative array
	 *
	 * @return array|false
	 */
	function clients_list () {
		return $this->db()->qfa(
			"SELECT *
			FROM `[prefix]oauth2_clients`"
		);
	}
	/**
	 * Grant access for specified client
	 *
	 * @param string $client
	 *
	 * @return bool
	 */
	function add_access ($client) {
		$User = User::instance();
		if (!$User->user() || !$this->get_client($client)) {
			return false;
		}
		$result = $this->db_prime()->q(
			"INSERT IGNORE INTO `[prefix]oauth2_clients_grant_access`
				(
					`id`,
					`user`
				) VALUES (
					'%s',
					'%s'
				)",
			$client,
			$User->id
		);
		unset($this->cache->{"grant_access/$User->id"});
		return $result;
	}
	/**
	 * Check granted access for specified client
	 *
	 * @param string   $client
	 * @param bool|int $user If not specified - current user assumed
	 *
	 * @return bool
	 */
	function get_access ($client, $user = false) {
		$user = (int)$user ?: User::instance()->id;
		if ($user == User::GUEST_ID) {
			return $this->guest_tokens;
		}
		$clients = $this->cache->get(
			"grant_access/$user",
			function () use ($user) {
				return $this->db()->qfas(
					[
						"SELECT `id`
						FROM `[prefix]oauth2_clients_grant_access`
						WHERE `user`	= '%s'",
						$user
					]
				);
			}
		);
		return $clients ? in_array($client, $clients) : false;
	}
	/**
	 * Deny access for specified client/
	 *
	 * @param string   $client If '' - access for all clients will be denied
	 * @param bool|int $user   If not specified - current user assumed
	 *
	 * @return bool
	 */
	function del_access ($client = '', $user = false) {
		$user = (int)$user ?: User::instance()->id;
		if ($user == User::GUEST_ID) {
			return false;
		}
		$result = $client ? $this->db_prime()->q(
			[
				"DELETE FROM `[prefix]oauth2_clients_grant_access`
				WHERE
					`user`	= $user AND
					`id`	= '%s'
				LIMIT 1",
				"DELETE FROM `[prefix]oauth2_clients_sessions`
				WHERE
					`user`	= $user AND
					`id`	= '%s'"
			],
			$client
		) : $this->db_prime()->q(
			[
				"DELETE FROM `[prefix]oauth2_clients_grant_access`
				WHERE
					`user`	= $user",
				"DELETE FROM `[prefix]oauth2_clients_sessions`
				WHERE
					`user`	= $user"
			]
		);
		unset($this->cache->{"grant_access/$user"});
		return $result;
	}
	/**
	 * Adds new code for specified client, code is used to obtain token
	 *
	 * @param string $client
	 * @param string $response_type 'code' or 'token'
	 * @param string $redirect_uri
	 *
	 * @return false|string                    <i>false</i> on failure or code for token access otherwise
	 */
	function add_code ($client, $response_type, $redirect_uri = '') {
		$Session = Session::instance();
		$client  = $this->get_client($client);
		if (
			!$client ||
			(
				!$this->guest_tokens && !$Session->user()
			) ||
			!$this->get_access($client['id'])
		) {
			return false;
		}
		/**
		 * @var \cs\_SERVER $_SERVER
		 */
		$user_agent          = $_SERVER->user_agent;
		$current_session     = $Session->get_id();
		$_SERVER->user_agent = "OAuth2-$client[name]-$client[id]";
		$Session->add($Session->get_user(), false);
		$new_session         = $Session->get_id();
		$_SERVER->user_agent = $user_agent;
		$Session->load($current_session);
		unset($user_agent, $current_session);
		/** @noinspection LoopWhichDoesNotLoopInspection */
		while (true) {
			$access_token  = md5(random_bytes(1000));
			$refresh_token = md5($access_token.random_bytes(1000));
			$code          = md5($refresh_token.random_bytes(1000));
			if ($this->db_prime()->qf(
				"SELECT `id`
				FROM `[prefix]oauth2_clients_sessions`
				WHERE
					`access_token`	= '$access_token' OR
					`refresh_token`	= '$refresh_token' OR
					`code`			= '$code'
				LIMIT 1"
			)
			) {
				continue;
			}
			$result = $this->db_prime()->q(
				"INSERT INTO `[prefix]oauth2_clients_sessions`
					(
						`id`,
						`user`,
						`session`,
						`created`,
						`expire`,
						`access_token`,
						`refresh_token`,
						`code`,
						`type`,
						`redirect_uri`
					) VALUES (
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s'
					)",
				$client['id'],
				$Session->get_user(),
				$new_session,
				time(),
				time() + $this->expiration,
				$access_token,
				$refresh_token,
				$code,
				$response_type,
				md5($redirect_uri)
			);
			return $result ? $code : false;
		}
		return false;
	}
	/**
	 * Get code data (tokens)
	 *
	 * @param string $code
	 * @param string $client Client id
	 * @param string $secret Client secret
	 * @param string $redirect_uri
	 *
	 * @return array|false                    <i>false</i> on failure, otherwise array
	 *                                        ['access_token' => md5, 'refresh_token' => md5, 'expires_in' => seconds, 'token_type' => 'bearer']<br>
	 *                                        <i>expires_in</i> may be negative
	 */
	function get_code ($code, $client, $secret, $redirect_uri = '') {
		$client = $this->get_client($client);
		if (!is_md5($code) || !$client || $client['secret'] != $secret) {
			return false;
		}
		$data = $this->db_prime()->qf(
			[
				"SELECT
					`access_token`,
					`refresh_token`,
					`expire`,
					`user`
				FROM `[prefix]oauth2_clients_sessions`
				WHERE
					`id`			= '%s' AND
					`code`			= '%s' AND
					`redirect_uri`	= '%s'
				LIMIT 1",
				$client['id'],
				$code,
				md5($redirect_uri)
			]
		);
		if (!$data) {
			return false;
		}
		$this->db_prime()->q(
			"UPDATE `[prefix]oauth2_clients_sessions`
			SET `code` = ''
			WHERE
				`id`			= '%s' AND
				`code`			= '%s' AND
				`redirect_uri`	= '%s'
			LIMIT 1",
			$client['id'],
			$code,
			md5($redirect_uri)
		);
		return [
			'access_token'  => $data['access_token'],
			'refresh_token' => $data['refresh_token'],
			'expires_in'    => $data['expire'] - time(),
			'token_type'    => 'bearer',
			'user_id'       => $data['user']
		];
	}
	/**
	 * Get token data
	 *
	 * @param string $access_token
	 *
	 * @return array|false                    <i>false</i> on failure, array ['user' => id, 'session' => id, 'expire' => unix time, 'type' => 'code'|'token']
	 */
	function get_token ($access_token) {
		if (!is_md5($access_token)) {
			return false;
		}
		$Cache = $this->cache;
		$data  = $Cache->get(
			"tokens/$access_token",
			function () use ($access_token) {
				return $this->db()->qf(
					[
						"SELECT
						`id` AS `client_id`,
						`user`,
						`session`,
						`expire`,
						`type`
					FROM `[prefix]oauth2_clients_sessions`
					WHERE
						`access_token`	= '%s'
					LIMIT 1",
						$access_token
					]
				);
			}
		);
		if ($data) {
			if ($data['expire'] < time()) {
				return false;
			}
			if (!$this->get_access($data['client_id'], $data['user'])) {
				$this->db_prime()->q(
					[
						"DELETE FROM `[prefix]oauth2_clients_sessions`
						WHERE
							`access_token`	= '%s'
						LIMIT 1",
						$access_token
					]
				);
				unset($Cache->{"tokens/$access_token"});
				$data = false;
				/**
				 * Automatic prolongation of tokens' expiration time if configured
				 */
			} elseif ($this->automatic_prolongation && $data['expire'] < time() - $this->expiration * Config::instance()->core['update_ratio'] / 100) {
				$data['expire'] = time() + $this->expiration;
				$this->db_prime()->q(
					"UPDATE `[prefix]oauth2_clients_sessions`
					SET `expire` = '%s'
					WHERE
						`access_token`	= '%s'
					LIMIT 1",
					$data['expire'],
					$access_token
				);
				$Cache->{"tokens/$access_token"} = $data;
			}
		}
		return $data;
	}
	/**
	 * Del token data (invalidate token)
	 *
	 * @param string $access_token
	 *
	 * @return bool
	 */
	function del_token ($access_token) {
		if (!is_md5($access_token)) {
			return false;
		}
		$session = $this->db_prime()->qfs(
			[
				"SELECT `session`
				FROM `[prefix]oauth2_clients_sessions`
				WHERE
					`access_token`	= '%s'
				LIMIT 1",
				$access_token
			]
		);
		if ($this->db_prime()->q(
			"DELETE FROM `[prefix]oauth2_clients_sessions`
			WHERE
				`access_token`	= '%s'
			LIMIT 1",
			$access_token
		)
		) {
			unset($this->cache->{"tokens/$access_token"});
			Session::instance()->del($session);
			return true;
		}
		return false;
	}
	/**
	 * Get new access_token with refresh_token
	 *
	 * @param string $refresh_token
	 * @param string $client Client id
	 * @param string $secret Client secret
	 *
	 * @return array|false <i>false</i> on failure, otherwise array ['access_token' => md5, 'refresh_token' => md5, 'expires_in' => seconds, 'token_type' =>
	 *                     'bearer']
	 */
	function refresh_token ($refresh_token, $client, $secret) {
		$client = $this->get_client($client);
		if (!is_md5($refresh_token) || !$client || $client['secret'] != $secret) {
			return false;
		}
		$data = $this->db_prime()->qf(
			[
				"SELECT
					`user`,
					`access_token`,
					`session`
				FROM `[prefix]oauth2_clients_sessions`
				WHERE
					`id`			= '%s' AND
					`refresh_token`	= '%s'
				LIMIT 1",
				$client['id'],
				$refresh_token
			]
		);
		if (!$data) {
			return false;
		}
		$this->db_prime()->q(
			"DELETE FROM `[prefix]oauth2_clients_sessions`
			WHERE
				`id`			= '%s' AND
				`refresh_token`	= '%s'
			LIMIT 1",
			$client['id'],
			$refresh_token
		);
		unset($this->cache->{"tokens/$data[access_token]"});
		$Session = Session::instance();
		$id      = $Session->load($data['session']);
		if ($id != $data['user']) {
			return false;
		}
		$Session->add($id);
		$code = $this->add_code($client['id'], 'code');
		if (!$code) {
			return false;
		}
		$result = $this->get_code($code, $client['id'], $client['secret']);
		$Session->del();
		return $result;
	}
}
