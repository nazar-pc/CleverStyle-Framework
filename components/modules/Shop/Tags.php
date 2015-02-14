<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\DB\Accessor,
	cs\Singleton;

/**
 * @method static Tags instance($check = false)
 */
class Tags {
	use
		Accessor,
		Singleton;

	/**
	 * @var Prefix
	 */
	protected $cache;

	protected function construct () {
		$this->cache = new Prefix('Shop/tags');
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Shop')->db('shop');
	}
	/**
	 * Get tag text
	 *
	 * @param int|int[] $id
	 *
	 * @return string|string[]
	 */
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get($i);
			}
			return $id;
		}
		$id = (int)$id;
		return $this->cache->get("tags/$id", function () use ($id) {
			return $this->db()->qfs([
				"SELECT `text`
				FROM `[prefix]shop_tags`
				WHERE `id` = '%s'
				LIMIT 1",
				$id
			]);
		});
	}
	/**
	 * Accepts array of string tags and returns corresponding array of id's of these tags, new tags will be added automatically
	 *
	 * @param string[] $tags
	 *
	 * @return int[]
	 */
	function process ($tags) {
		if (!$tags) {
			return [];
		}
		$tags = xap($tags);
		$tags = array_filter($tags);
		$cdb  = $this->db_prime();
		$cdb->insert(
			"INSERT IGNORE INTO `[prefix]shop_tags`
				(`text`)
			VALUES
				('%s')",
			array_map(
				function ($tag) {
					return [$tag];
				},
				$tags
			),
			true
		);
		$in = [];
		foreach ($tags as $tag) {
			$in[] = $cdb->s($tag);
		}
		$in = implode(',', $in);
		return $cdb->qfas(
			"SELECT `id`
			FROM `[prefix]shop_tags`
			WHERE `text` IN($in)"
		);
	}
}
