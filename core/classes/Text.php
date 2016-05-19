<?php
/**
 * @package   CleverStyle Framework
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2011-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs;

/**
 * @method static $this instance($check = false)
 */
class Text {
	use Singleton;
	/**
	 * Gets text on current language
	 *
	 * @param int      $database
	 * @param string   $group
	 * @param string   $label
	 * @param int|null $id             Getting may be done with group and label or with id
	 * @param bool     $store_in_cache If `true` - text will be stored in cache
	 *
	 * @return false|string
	 */
	function get ($database, $group, $label, $id = null, $store_in_cache = false) {
		$Cache     = Cache::instance();
		$L         = Language::instance();
		$id        = (int)$id;
		$cache_key = "texts/$database/".($id ?: md5($group).md5($label))."_$L->clang";
		if ($store_in_cache && ($text = $Cache->$cache_key) !== false) {
			return $text;
		}
		$cdb = DB::instance()->db($database);
		if ($id) {
			$text = $this->get_text_by_id($id, $cdb, $L);
		} else {
			$text = $this->get_text_by_group_and_label($group, $label, $cdb, $L);
		}
		if (!$text) {
			return false;
		}
		if ($store_in_cache) {
			$Cache->$cache_key = $text['text'];
		}
		return $text['text'];
	}
	/**
	 * @param int          $id
	 * @param DB\_Abstract $cdb
	 * @param Language     $L
	 *
	 * @return false|string[]
	 */
	protected function get_text_by_id ($id, $cdb, $L) {
		$text = $cdb->qf(
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
		);
		if (!$text) {
			$text = $cdb->qf(
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
			);
		}
		return $text;
	}
	/**
	 * @param string       $group
	 * @param string       $label
	 * @param DB\_Abstract $cdb
	 * @param Language     $L
	 *
	 * @return false|string[]
	 */
	protected function get_text_by_group_and_label ($group, $label, $cdb, $L) {
		$text = $cdb->qf(
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
		);
		if (!$text) {
			$text = $cdb->qf(
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
			);
		}
		return $text;
	}
	/**
	 * Search for text regardless language
	 *
	 * @param int    $database
	 * @param string $group
	 * @param string $label
	 * @param string $text
	 *
	 * @return array[]|false Array of items `['id' => id, 'lang' => lang]` on success, `false` otherwise
	 */
	function search ($database, $group, $label, $text) {
		return DB::instance()->db($database)->qfa(
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
		);
	}
	/**
	 * Sets text on current language
	 *
	 * @param int    $database
	 * @param string $group
	 * @param string $label
	 * @param string $text
	 *
	 * @return false|string If multilingual support enabled or was enabled and then disabled but translations remains - returns {¶<i>id</i>}, otherwise returns
	 *                      original text
	 */
	function set ($database, $group, $label, $text) {
		$Cache  = Cache::instance();
		$Config = Config::instance();
		$L      = Language::instance();
		$cdb    = DB::instance()->db_prime($database);
		/**
		 * Security check, do not allow to silently substitute text from another item
		 */
		if (preg_match('/^\{¶(\d+)\}$/', $text)) {
			return false;
		}
		/**
		 * @var \cs\DB\_Abstract $cdb
		 */
		// Find existing text id
		$id = $cdb->qfs(
			"SELECT `id`
			FROM `[prefix]texts`
			WHERE
				`label`	= '%s' AND
				`group`	= '%s'
			LIMIT 1",
			$label,
			$group
		);
		if (!$id) {
			// If not found - either return text directly or add new text entry and obtain id
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
				$id = $cdb->id();
				if (!$id) {
					return $text;
				}
			}
		}
		unset(
			$Cache->{"texts/$database/{$id}_$L->clang"},
			$Cache->{"texts/$database/".md5($group).md5($label)."_$L->clang"}
		);
		return $this->set_text($id, $text, $cdb, $Config, $L);
	}
	/**
	 * @param int          $id
	 * @param string       $text
	 * @param DB\_Abstract $cdb
	 * @param Config       $Config
	 * @param Language     $L
	 *
	 * @return mixed
	 */
	protected function set_text ($id, $text, $cdb, $Config, $L) {
		$exists_for_current_language = $cdb->qfs(
			"SELECT `id`
			FROM `[prefix]texts_data`
			WHERE
				`id`	= '%s' AND
				`lang`	= '%s'
			LIMIT 1",
			$id,
			$L->clang
		);
		if ($exists_for_current_language) {
			if ($cdb->q(
				"UPDATE `[prefix]texts_data`
				SET
					`text`		= '%s',
					`text_md5`	= '%s'
				WHERE
					`id` = '%s' AND
					`lang` = '%s'",
				$text,
				md5($text),
				$id,
				$L->clang
			)
			) {
				return "{¶$id}";
			}
		} elseif ($Config->core['multilingual']) {
			if ($cdb->q(
				"INSERT INTO `[prefix]texts_data`
					(
						`id`,
						`id_`,
						`lang`,
						`text`,
						`text_md5`
					) VALUES (
						'%s',
						'%s',
						'%s',
						'%s',
						'%s'
					)",
				$id,
				"{¶$id}",
				$L->clang,
				$text,
				md5($text)
			)
			) {
				return "{¶$id}";
			}
		}
		return $text;
	}
	/**
	 * Deletes text on all languages
	 *
	 * @param int    $database
	 * @param string $group
	 * @param string $label
	 *
	 * @return bool
	 */
	function del ($database, $group, $label) {
		$Cache = Cache::instance();
		$cdb   = DB::instance()->db_prime($database);
		$id    = $cdb->qfs(
			[
				"SELECT `id`
				FROM `[prefix]texts`
				WHERE
					`group`	= '%s' AND
					`label`	= '%s'
				LIMIT 1",
				$group,
				$label
			]
		);
		if ($id) {
			$L = Language::instance();
			unset(
				$Cache->{"texts/$database/{$id}_$L->clang"},
				$Cache->{"texts/$database/".md5($group).md5($label)."_$L->clang"}
			);
			return $cdb->q(
				[
					"DELETE FROM `[prefix]texts`
					WHERE `id` = '%s'",
					"DELETE FROM `[prefix]texts_data`
					WHERE `id` = '%s'"
				],
				$id
			);
		}
		return true;
	}
	/**
	 * Process text, and replace {¶([0-9]+)} on real text, is used before showing multilingual information
	 *
	 * @param int             $database
	 * @param string|string[] $data
	 * @param bool            $store_in_cache If <b>true</b> - text will be stored in cache
	 *
	 * @return string|string[]
	 */
	function process ($database, $data, $store_in_cache = false) {
		if (is_array($data)) {
			foreach ($data as &$d) {
				$d = $this->process($database, $d);
			}
			return $data;
		}
		return preg_replace_callback(
			'/^\{¶(\d+)\}$/',
			function ($input) use ($database, $store_in_cache) {
				return $this->get($database, null, null, $input[1], $store_in_cache);
			},
			$data
		);
	}
}
