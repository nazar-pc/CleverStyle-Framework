<?php
/**
 * @package        Polls
 * @category       modules
 * @author         Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright      Copyright (c) 2014-2015, Nazar Mokrynskyi
 * @license        MIT License, see license.txt
 */
namespace cs\modules\Polls;

use
	cs\Cache\Prefix,
	cs\Config,
	cs\Text,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static Options instance($check = false)
 */
class Options {
	use
		CRUD,
		Singleton;

	/**
	 * @var Prefix
	 */
	protected $cache;
	protected $table      = '[prefix]polls_options';
	protected $data_model = [
		'id'    => 'int',
		'poll'  => 'int',
		'title' => 'string',
		'votes' => 'int'
	];

	protected function construct () {
		$this->cache = new Prefix('polls/options');
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
	function add ($poll, $title) {
		$id = $this->create([
			$poll,
			$title,
			0
		]);
		if ($id && $this->set($id, $poll, $title)) {
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
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i = $this->get($i);;
			}
			return $id;
		}
		return $this->cache->get($id, function () use ($id) {
			$data          = $this->read($id);
			$data['title'] = $this->ml_process($data['title']);
			return $data;
		});
	}
	/**
	 * Set option
	 *
	 * @param $id
	 * @param $poll
	 * @param $title
	 *
	 * @return false|int
	 */
	function set ($id, $poll, $title) {
		$id   = (int)$id;
		$poll = (int)$poll;
		$data = $this->get($id);
		if ($this->update([
			$id,
			$poll,
			$this->ml_set("Polls/polls/$poll/options/title", $id, $title),
			$data['votes']
		])
		) {
			unset($this->cache->$id);
			return true;
		}
		return false;
	}
	/**
	 * Update count of votes
	 *
	 * @param $id
	 *
	 * @return false|int
	 */
	function update_votes ($id) {
		$id = (int)$id;
		if ($this->db_prime()->q(
			"UPDATE `$this->table`
			SET `votes` = (
				SELECT COUNT(`id`)
				FROM `[prefix]polls_options_answers`
				WHERE `option` = '%s'
			)
			WHERE `id` = '%s'
			LIMIT 1",
			$id,
			$id
		)
		) {
			unset($this->cache->$id);
			return true;
		}
		return false;
	}
	/**
	 * Get id of all options for specified poll
	 *
	 * @param $poll
	 *
	 * @return false|int[]
	 */
	function get_all_for_poll ($poll) {
		$poll = (int)$poll;
		return $this->cache->get("poll/$poll", function () use ($poll) {
			return $this->db()->qfas(
				"SELECT `id`
				FROM `$this->table`
				WHERE `poll` = $poll"
			);
		});
	}
	private function ml_process ($text, $auto_translation = false) {
		return Text::instance()->process($this->cdb(), $text, $auto_translation, true);
	}
	private function ml_set ($group, $label, $text) {
		return Text::instance()->set($this->cdb(), $group, $label, $text);
	}
}
