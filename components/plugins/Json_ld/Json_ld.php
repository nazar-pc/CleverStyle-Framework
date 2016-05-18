<?php
/**
 * @package   Json_ld
 * @category  plugins
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */

namespace cs\plugins\Json_ld;
use
	cs\User;

/**
 * Class generates array with structure, that is necessary for JSON-LD based on CleverStyle Framework-specific data
 *
 * Methods names are the same as on <http://schema.org/docs/full.html> except some utility methods which have `snake_case` formatting
 */
class Json_ld {
	const SCHEMA_ORG = 'https://schema.org';
	/**
	 * @param array $data
	 *
	 * @return string[]
	 */
	static function context_stub ($data = []) {
		$context = [
			'@vocab' => self::SCHEMA_ORG.'/'
		];
		$context += array_combine(
			array_keys($data),
			array_fill(0, count($data), null)
		);
		return $context;
	}
	/**
	 * @param int $timestamp
	 *
	 * @return string
	 */
	static function Date ($timestamp) {
		return date('c', $timestamp);
	}
	/**
	 * @param int $user_id
	 *
	 * @return string[]
	 */
	static function Person ($user_id) {
		$user_data = new User\Properties($user_id);
		return [
			'@context' => self::SCHEMA_ORG,
			'@type'    => 'Person',
			'name'     => $user_data->username(),
			'image'    => $user_data->avatar()
		];
	}
}
