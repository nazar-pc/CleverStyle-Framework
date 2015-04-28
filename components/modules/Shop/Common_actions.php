<?php
/**
 * @package   Shop
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Shop;
use
	cs\CRUD,
	cs\Language;
/**
 * @property \cs\Cache\Prefix $cache
 * @property string           $table
 */
trait Common_actions {
	use
		CRUD;
	/**
	 * Get item
	 *
	 * @param int|int[] $id
	 *
	 * @return array|false
	 */
	protected function get_common ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get_common($i);
			}
			return $id;
		}
		$L  = Language::instance();
		$id = (int)$id;
		return $this->cache->get(
			"$id/$L->clang",
			function () use ($id) {
				return $this->read($id);
			}
		);
	}
	/**
	 * Get array of all items
	 *
	 * @return int[] Array of attributes ids
	 */
	protected function get_all_common () {
		return $this->cache->get(
			'all',
			function () {
				return $this->db()->qfas(
					"SELECT `id`
				FROM `$this->table`"
				) ?: [];
			}
		);
	}
	/**
	 * Add new item
	 *
	 * @param array $arguments
	 *
	 * @return false|int Id of created item on success of <b>false</> on failure
	 */
	protected function add_common ($arguments) {
		$id = $this->create($arguments);
		if ($id) {
			unset($this->cache->all);
		}
		return $id;
	}
	/**
	 * Set data of specified item
	 *
	 * @param array $arguments
	 *
	 * @return bool
	 */
	protected function set_common ($arguments) {
		$result = $this->update($arguments);
		if ($result) {
			$L  = Language::instance();
			$id = (int)$arguments[0];
			unset(
				$this->cache->{"$id/$L->clang"},
				$this->cache->all
			);
		}
		return $result;
	}
	/**
	 * Delete specified item
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	protected function del_common ($id) {
		$id     = (int)$id;
		$result = $this->delete($id);
		if ($result) {
			unset(
				$this->cache->$id,
				$this->cache->all
			);
		}
		return $result;
	}
}
