<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Polls;

/**
 * @method array|false read(int|int[]|string|string[] $id)
 */
trait Common_actions {
	/**
	 * @param int|int[] $id
	 *
	 * @return array|array[]|false
	 */
	public function get_common ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get_common($i);
			}
			return $id;
		}
		return $this->cache->get(
			(int)$id,
			function () use ($id) {
				return $this->read($id);
			}
		);
	}
}
