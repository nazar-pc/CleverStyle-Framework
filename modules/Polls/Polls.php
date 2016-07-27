<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2016, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Polls;
use
	cs\Cache\Prefix,
	cs\Config,
	cs\CRUD_helpers,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Polls {
	use
		Common_actions,
		CRUD_helpers,
		Singleton;
	/**
	 * @var Prefix
	 */
	protected $cache;
	protected $data_model          = [
		'id'    => 'int',
		'title' => 'ml:string'
	];
	protected $table               = '[prefix]polls';
	protected $data_model_ml_group = 'Polls/polls';

	protected function construct () {
		$this->cache = new Prefix('Polls');
	}
	protected function cdb () {
		return Config::instance()->module('Polls')->db('polls');
	}
	/**
	 * Add new poll
	 *
	 * @param string $title
	 *
	 * @return false|int
	 */
	public function add ($title) {
		$id = $this->create($title);
		if ($id) {
			unset($this->cache->all);
			return $id;
		}
		return false;
	}
	/**
	 * Get poll
	 *
	 * @param int|int[] $id
	 *
	 * @return array|array[]|false
	 */
	public function get ($id) {
		return $this->get_common($id);
	}
	/**
	 * Get ids of add polls
	 *
	 * @return false|int[]
	 */
	public function get_all () {
		return $this->cache->get(
			'all',
			function () {
				return $this->search([], 1, PHP_INT_MAX, 'id', false);
			}
		);
	}
	/**
	 * Set poll
	 *
	 * @param int    $id
	 * @param string $title
	 *
	 * @return false|int
	 */
	public function set ($id, $title) {
		$id     = (int)$id;
		$result = $this->update($id, $title);
		if ($result) {
			unset(
				$this->cache->$id,
				$this->cache->all
			);
			return true;
		}
		return false;
	}
	/**
	 * Del poll
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		$id = (int)$id;
		if (!$this->delete($id)) {
			return false;
		}
		$Options = Options::instance();
		$Options->del(
			$Options->get_all_for_poll($id) ?: []
		);
		if (!$this->db_prime()->q(
			"DELETE FROM `[prefix]polls_options_answers`
			WHERE `id` = $id"
		)
		) {
			return false;
		}
		unset(
			$this->cache->all,
			$this->cache->$id,
			$this->cache->{"options/poll/$id"}
		);
		return true;
	}
}
