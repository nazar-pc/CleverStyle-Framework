<?php
/**
 * @package   Tags
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Tags;
use
	cs\DB\Accessor;

/**
 * Tags trait
 *
 * Provides methods for working with tags for faster development
 *
 * @property \cs\Cache\Prefix $cache
 * @property string           $table
 */
trait Tags {
	use
		Accessor;
	/**
	 * Get tag
	 *
	 * @param int|int[] $id
	 *
	 * @return array|array[]|false
	 */
	function get ($id) {
		if (!$id) {
			return false;
		}
		if (is_array($id)) {
			return array_map([$this, 'get'], $id);
		}
		$id = (int)$id;
		return $this->cache->get(
			$id,
			function () use ($id) {
				return $this->db()->qf(
					"SELECT
						`id`,
						`text`
					FROM `$this->table`
					WHERE `id` = '%s'
					LIMIT 1",
					$id
				);
			}
		);
	}
	/**
	 * Get tag by its text
	 *
	 * @param string|string[] $text
	 *
	 * @return false|int|int[]
	 */
	function get_by_text ($text) {
		if (!$text) {
			return false;
		}
		if (is_array($text)) {
			return array_map([$this, 'get_by_text'], $text);
		}
		return $this->db()->qfs(
			"SELECT `id`
			FROM  `$this->table`
			WHERE `text` = '%s'
			LIMIT 1",
			trim(xap($text))
		);
	}
	/**
	 * Add tags (existing tags will not be recreated)
	 *
	 * @param string[] $tags
	 *
	 * @return false|int[] Array of tags ids (regardless of whether tag existed before or not, ids will be returned for all of them)
	 */
	function add ($tags) {
		if (!$tags) {
			return false;
		}
		$tags = array_unique(array_filter(_trim(xap($tags))));
		if (!$tags) {
			return false;
		}
		$cdb = $this->db_prime();
		$cdb->insert(
			"INSERT IGNORE INTO `$this->table`
				(
					`text`
				) VALUES (
					'%s'
				)",
			$tags,
			true
		);
		$in = array_map([$cdb, 's'], $tags);
		$in = implode(',', $in);
		return $cdb->qfas(
			"SELECT `id`
			FROM `$this->table`
			WHERE `text` IN($in)"
		);
	}
}
