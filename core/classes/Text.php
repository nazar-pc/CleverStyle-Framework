<?php
/**
 * @package		CleverStyle CMS
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2011-2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace cs;

/**
 * @method static Text instance($check = false)
 */
class Text {
	use Singleton;
	/**
	 * Gets text on current language
	 *
	 * @param int			$database
	 * @param string		$group
	 * @param string		$label
	 * @param int|null		$id					Getting may be done with group and label or with id
	 * @param bool			$store_in_cache		If <b>true</b> - text will be stored in cache
	 *
	 * @return bool|string
	 */
	function get ($database, $group, $label, $id = null, $store_in_cache = false) {
		$Cache		= Cache::instance();
		$L			= Language::instance();
		$id			= (int)$id;
		$cache_key	= "texts/$database/".($id ?: md5($group).md5($label))."_$L->clang";
		if ($store_in_cache && ($text = $Cache->$cache_key) !== false) {
			return $text;
		}
		$db			= DB::instance();
		$cdb		= $db->$database;
		if ($id) {
			$text = $cdb->qf([
				"SELECT
					`d`.`id`,
					`d`.`lang`,
					`d`.`text`
				FROM `[prefix]texts` AS `t`
					LEFT JOIN `[prefix]texts_data` AS `d`
				ON `t`.`id` = `d`.`id`
				WHERE
					`t`.`id`	= $id AND
					`d`.`lang`	= '%s'
				LIMIT 1",
				$L->clang
			]);
			if (!$text) {
				$text = $cdb->qf([
					"SELECT
						`d`.`id`,
						`d`.`lang`,
						`d`.`text`
					FROM `[prefix]texts` AS `t`
						LEFT JOIN `[prefix]texts_data` AS `d`
					ON `t`.`id` = `d`.`id`
					WHERE `t`.`id` = $id
					LIMIT 1",
					$L->clang
				]);
			}
		} else {
			$text = $cdb->qf([
				"SELECT
					`t`.`id`,
					`d`.`lang`,
					`d`.`text`
				FROM `[prefix]texts` AS `t`
					LEFT JOIN `[prefix]texts_data` AS `d`
				ON `t`.`id` = `d`.`id`
				WHERE
					`t`.`group`	= '%s' AND
					`t`.`label`	= '%s' AND
					`d`.`lang`	= '%s'
				LIMIT 1",
				$group,
				$label,
				$L->clang
			]);
			if (!$text) {
				$text = $cdb->qf([
					"SELECT
						`t`.`id`,
						`d`.`lang`,
						`d`.`text`
					FROM `[prefix]texts` AS `t`
						LEFT JOIN `[prefix]texts_data` AS `d`
					ON `t`.`id` = `d`.`id`
					WHERE
						`t`.`group`	= '%s' AND
						`t`.`label`	= '%s'
					LIMIT 1",
					$group,
					$label,
					$L->clang
				]);
			}
		}
		if (!$text) {
			return false;
		}
		if ($store_in_cache) {
			$Cache->$cache_key	= $text['text'];
		}
		return $text['text'];
	}
	/**
	 * Search for text regardless language
	 *
	 * @param int			$database
	 * @param string		$group
	 * @param string		$label
	 * @param string		$text
	 *
	 * @return array[]|bool				Array of items ['id' => <i>id</i>, 'lang' => <i>lang</i>] on success, <i>false</i> otherwise
	 */
	function search ($database, $group, $label, $text) {
		return DB::instance()->$database->qfa([
			"SELECT
				`t`.`id`,
				`d`.`lang`
			FROM `[prefix]texts` AS `t`
				INNER JOIN `[prefix]texts_data` AS `d`
			ON `t`.`id` = `d`.`id`
			WHERE
				`t`.`group`		= '%s' AND
				`t`.`label`		= '%s' AND
				`d`.`text_md5`	= '%s'",
			$group,
			$label,
			md5($text)
		]);
	}
	/**
	 * Sets text on current language
	 *
	 * @param int			$database
	 * @param string		$group
	 * @param string		$label
	 * @param string		$text
	 *
	 * @return bool|string				If multilingual support enabled or was enabled and then disabled but translations remains - returns {¶<i>id</i>}<br>
	 * 									otherwise returns original text
	 */
	function set ($database, $group, $label, $text) {
		$Cache	= Cache::instance();
		$Config	= Config::instance();
		$L		= Language::instance();
		$cdb	= DB::instance()->$database();
		/**
		 * @var \cs\DB\_Abstract $cdb
		 */
		$text	= str_replace('{¶', '{&para;', $text);
		$id		= $cdb->qfs([
			"SELECT `id`
			FROM `[prefix]texts`
			WHERE
				`label`	= '%s' AND
				`group`	= '%s'
			LIMIT 1",
			$label,
			$group
		]);
		if (!$id) {
			if (!$Config->core['multilingual']) {
				return $text;
			} else {
				$cdb->q(
					"INSERT INTO `[prefix]texts`
						(
							`label`,
							`group`
						) VALUES (
							'%s',
							'%s'
						)",
					$label,
					$group
				);
				if (!($id = $cdb->id())) {
					return $text;
				}
			}
		}
		unset(
			$Cache->{"texts/$database/{$id}_$L->clang"},
			$Cache->{"texts/$database/".md5($group).md5($label)."_$L->clang"}
		);
		if ($cdb->qfs([
			"SELECT `id`
			FROM `[prefix]texts_data`
			WHERE
				`id`	= '%s' AND
				`lang`	= '%s'
			LIMIT 1",
			$id,
			$L->clang
		])) {
			if ($cdb->q(
				"UPDATE `[prefix]texts_data`
				SET `text` = '%s'
				WHERE
					`id` = '%s' AND
					`lang` = '%s'
				LIMIT 1",
				$text,
				$id,
				$L->clang
			)) {
				return "{¶$id}";
			} else {
				return $text;
			}
		} elseif ($Config->core['multilingual']) {
			if (!$cdb->q(
				"INSERT INTO `[prefix]texts_data`
					(
						`id`,
						`id_`,
						`lang`,
						`text`
					) VALUES (
						'%s',
						'%s',
						'%s',
						'%s'
					)",
				$id,
				"{¶$id}",
				$L->clang,
				$text
			)) {
				$cdb->q(
					"DELETE FROM `[prefix]texts`
					WHERE `id` = $id"
				);
				return $text;
			}
			if ($id) {
				return "{¶$id}";
			} else {
				return $text;
			}
		} else {
			return $text;
		}
	}
	/**
	 * Deletes text on all languages
	 *
	 * @param int        $database
	 * @param string     $group
	 * @param string     $label
	 *
	 * @return bool
	 */
	function del ($database, $group, $label) {
		$Cache	= Cache::instance();
		$db		= DB::instance();
		$id		= $db->$database()->qfs([
			"SELECT `id`
			FROM `[prefix]texts`
			WHERE
				`group`	= '%s' AND
				`label`	= '%s'
			LIMIT 1",
			$group,
			$label
		]);
		if ($id) {
			$L		= Language::instance();
			unset(
				$Cache->{"texts/$database/{$id}_$L->clang"},
				$Cache->{"texts/$database/".md5($group).md5($label)."_$L->clang"}
			);
			return $db->$database()->q(
				[
					"DELETE FROM `[prefix]texts`
					WHERE `id` = '%s'",
					"DELETE FROM `[prefix]texts_data`
					WHERE `id` = '%s'"
				],
				$id
			);
		} else {
			return true;
		}
	}
	/**
	 * Process text, and replace {¶([0-9]+)} on real text, is used before showing multilingual information
	 *
	 * @param int					$database
	 * @param string|string[]		$data
	 * @param bool					$store_in_cache		If <b>true</b> - text will be stored in cache
	 *
	 * @return bool|string|string[]
	 */
	function process ($database, $data, $store_in_cache = false) {
		if (empty($data)) {
			return '';
		}
		if (is_array($data)) {
			foreach ($data as &$d) {
				$d	= $this->process($database, $d);
			}
			return $data;
		}
		return preg_replace_callback(
			'/^\{¶([0-9]+)\}$/',
			function ($input) use ($database, $store_in_cache) {
				return $this->get($database, null, null, $input[1], $store_in_cache);
			},
			$data
		);
	}
}
