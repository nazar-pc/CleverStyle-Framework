<?php
/**
 * @package   Polls
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Config,
	cs\User,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Answers instance($check = false)
 */
class Answers {
	use
		CRUD,
		Singleton;

	protected $data_model = [
		'id'     => 'int',
		'option' => 'int',
		'user'   => 'int'
	];
	protected $table      = '[prefix]polls_options_answers';

	protected function cdb () {
		return Config::instance()->module('Polls')->db('polls');
	}
	/**
	 * Add new answer
	 *
	 * @param $poll
	 * @param $option
	 *
	 * @return bool
	 */
	function add ($poll, $option) {
		$User = User::instance();
		if ($User->guest()) {
			return false;
		}
		$result = $this->create([$poll, $option, $User->id]);
		if ($result) {
			Options::instance()->update_votes($option);
			return true;
		}
		return false;
	}
	/**
	 * Get answer
	 *
	 * @param int|int[] $poll
	 *
	 * @return int|int[]|false Option id
	 */
	function get ($poll) {
		if (is_array($poll)) {
			foreach ($poll as &$i) {
				$i = $this->get($i);
			}
			return $poll;
		}
		return $this->db()->qfs(
			[
				"SELECT `option`
				FROM `$this->table`
				WHERE
					`id`	= '%s' AND
					`user`	= '%s'
				LIMIT 1",
				$poll,
				User::instance()->id
			]
		);
	}
}
