<?php
/**
 * @package		OAuth2
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\OAuth2;
use			cs\DB\Accessor;
class OAuth2 extends Accessor {
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		global $Config;
		return $Config->module(basename(__DIR__))->db('oauth2');
	}
	/**
	 * Add new client
	 *
	 * @param string	$name
	 * @param string	$domain
	 * @param int		$active
	 *
	 * @return bool|int					<i>false</i> on failure, id of created client otherwise
	 */
	function add_client ($name, $domain, $active) {
		if (
			!$domain ||
			strpos($domain, '/') !== false
		) {
			return false;
		}
		$this->db_prime()->q(
			"INSERT INTO `[prefix]oauth2_clients`
				(
					`secret`,
					`name`,
					`domain`,
					`active`
				) VALUES (
					'%s',
					'%s',
					'%s',
					'%s'
				)",
			md5(MICROTIME+uniqid('oauth2', true)),
			xap($name),
			xap($domain),
			(int)(bool)$active
		);
		return $this->db_prime()->id();
	}
	/**
	 * Get client data
	 *
	 * @param int				$id
	 *
	 * @return array|bool
	 */
	function get_client ($id) {
		$id	= (int)$id;
		if (!$id) {
			return false;
		}
		global $Cache;
		if (!($data = $Cache->{'OAuth2/'.$id})) {
			$data	= $this->db()->qf([
				"SELECT *
				FROM `[prefix]oauth2_clients`
				WHERE `id`	= '%s'
				LIMIT 1",
				$id
			]);
			$Cache->{'OAuth2/'.$id}	= $data;
		}
		return $data;
	}
	/**
	 * Set client data
	 *
	 * @param int		$id
	 * @param string	$secret
	 * @param string	$name
	 * @param string	$domain
	 * @param int		$active
	 *
	 * @return bool
	 */
	function set_client ($id, $secret, $name, $domain, $active) {
		$id	= (int)$id;
		if (!$id) {
			return false;
		}
		if (
			!preg_match('/[0-9a-z]{32}/', $secret) ||
			!$domain ||
			strpos($domain, '/') !== false
		) {
			return false;
		}
		global $Cache;
		unset($Cache->{'OAuth2/'.$id});
		return $this->db_prime()->q(
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
	}
	/**
	 * Delete client
	 *
	 * @param int				$id
	 *
	 * @return bool
	 */
	function del_client ($id) {
		$id	= (int)$id;
		if (!$id) {
			return false;
		}
		global $Cache;
		unset($Cache->{'OAuth2/'.$id});
		return $this->db_prime()->q(
			"DELETE FROM `[prefix]oauth2_clients`
			WHERE `id` = '%s'
			LIMIT 1",
			$id
		);
	}
	/**
	 * Get clients list in form of associative array
	 *
	 * @return array|bool
	 */
	function clients_list () {
		return $this->db()->qfa(
			"SELECT *
			FROM `[prefix]oauth2_clients`"
		);
	}
}
if (false) {
	global $OAuth2;
	$OAuth2	= new OAuth2;
}