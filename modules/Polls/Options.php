<?php
/**
 * @package  Polls
 * @category modules
 * @author   Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @license  0BSD
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
class Options {
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
		'poll'  => 'int',
		'title' => 'ml:string',
		'votes' => 'int'
	];
	protected $table               = '[prefix]polls_options';
	protected $data_model_ml_group = 'Polls/options';

	protected function construct () {
		$this->cache = new Prefix('Polls/options');
	}
	protected function cdb () {
		return Config::instance()->module('Polls')->db('polls');
	}
	/**
	 * Add new option
	 *
	 * @param int    $poll
	 * @param string $title
	 *
	 * @return false|int
	 */
	public function add ($poll, $title) {
		$id = $this->create($poll, $title, 0);
		if ($id) {
			unset($this->cache->{"poll/$poll"});
			return $id;
		}
		return false;
	}
	/**
	 * Get option
	 *
	 * @param int|int[] $id
	 *
	 * @return array|array[]|false
	 */
	public function get ($id) {
		return $this->get_common($id);
	}
	/**
	 * Set option
	 *
	 * @param $id
	 * @param $poll
	 * @param $title
	 *
	 * @return bool
	 */
	public function set ($id, $poll, $title) {
		$id   = (int)$id;
		$data = $this->get($id);
		if ($this->update($id, $poll, $title, $data['votes'])) {
			unset($this->cache->$id);
			return true;
		}
		return false;
	}
	/**
	 * Del option
	 *
	 * @param int|int[] $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		$return = $this->delete($id);
		if ($return) {
			foreach (_int((array)$id) as $i) {
				unset($this->cache->$i);
			}
		}
		return $return;
	}
	/**
	 * Update count of votes
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function update_votes ($id) {
		$id     = (int)$id;
		$result = (bool)$this->db_prime()->q(
			"UPDATE `$this->table`
			SET `votes` = (
				SELECT COUNT(`id`)
				FROM `[prefix]polls_options_answers`
				WHERE `option` = '%1\$d'
			)
			WHERE `id` = '%1\$d'",
			$id
		);
		if ($result) {
			unset($this->cache->$id);
		}
		return $result;
	}
	/**
	 * Get id of all options for specified poll
	 *
	 * @param $poll
	 *
	 * @return false|int[]
	 */
	public function get_all_for_poll ($poll) {
		$poll = (int)$poll;
		return $this->cache->get(
			"poll/$poll",
			function () use ($poll) {
				return $this->search(['poll' => $poll], 1, PHP_INT_MAX, 'id', true);
			}
		);
	}
}
