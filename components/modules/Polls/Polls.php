<?php
/**
 * @package		Polls
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2014, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Polls;
use			cs\Cache\Prefix,
			cs\Config,
			cs\Text,
			cs\CRUD,
			cs\Singleton;
/**
 * @method static Polls instance($check = false)
 */
class Polls {
	use	CRUD,
		Singleton;

	/**
	 * @var Prefix
	 */
	protected $cache;
	protected $table		= '[prefix]polls';
	protected $data_model	= [
		'id'		=> 'int',
		'title'		=> 'string'
	];

	protected function construct () {
		$this->cache	= new Prefix('polls');
	}
	protected function cdb () {
		return Config::instance()->module('Polls')->db('polls');
	}
	/**
	 * Add new poll
	 *
	 * @param $title
	 *
	 * @return bool|int
	 */
	function add ($title) {
		$id	= $this->create_simple([
			$title
		]);
		if ($id && $this->set($id, $title)) {
			unset($this->cache->all);
			return $id;
		}
		return false;
	}
	/**
	 * Get poll
	 *
	 * @param int|int[]	$id
	 *
	 * @return array|array[]|bool
	 */
	function get ($id) {
		if (is_array($id)) {
			foreach ($id as &$i) {
				$i	= $this->get($i);;
			}
			return $id;
		}
		return $this->cache->get($id, function () use ($id) {
			$data			= $this->read_simple($id);
			$data['title']	= $this->ml_process($data['title']);
			return $data;
		});
	}
	/**
	 * Set poll
	 *
	 * @param $id
	 * @param $title
	 *
	 * @return bool|int
	 */
	function set ($id, $title) {
		$id		= (int)$id;
		if ($this->update_simple([
			$id,
			$this->ml_set('Polls/polls/title', $id, $title)
		])) {
			unset(
				$this->cache->$id,
				$this->cache->all
			);
			return true;
		}
		return false;
	}
	/**
	 * Get id of add polls
	 *
	 * @return bool|int[]
	 */
	function get_all () {
		return $this->cache->get('all', function () {
			return $this->db()->qfas([
				"SELECT `id`
				FROM `$this->table`
				ORDER BY `id` DESC"
			]);
		});
	}
	private function ml_process ($text, $auto_translation = true) {
		return Text::instance()->process($this->cdb(), $text, $auto_translation, true);
	}
	private function ml_set ($group, $label, $text) {
		return Text::instance()->set($this->cdb(), $group, $label, $text);
	}
}
