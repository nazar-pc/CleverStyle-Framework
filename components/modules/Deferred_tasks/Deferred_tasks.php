<?php
/**
 * @package		Deferred tasks
 * @category	modules
 * @author		Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright	Copyright (c) 2013, Nazar Mokrynskyi
 * @license		MIT License, see license.txt
 */
namespace	cs\modules\Deferred_tasks;
use			cs\Config,
			cs\Trigger,
			cs\CRUD,
			cs\Singleton;
/**
 * @method static \cs\modules\Deferred_tasks\Deferred_tasks instance($check = false)
 */
class Deferred_tasks {
	use			Singleton,
				CRUD;
	protected	$data_model			= [
					'id'			=> 'int',
					'begin'			=> 'int:0',
					'started'		=> 'int:0',
					'started_hash'	=> 'text',
					'expected'		=> 'int:0',
					'priority'		=> 'int:0..2',
					'module'		=> 'text',
					'data'			=> null			//Is set in constructor
				];
	protected	$table				= '[prefix]deferred_tasks_tasks';
	/**
	 * @var int
	 */
	protected	$max_number_of_workers;
	/**
	 * @var string
	 */
	protected	$security_key;
	/**
	 * @var string
	 */
	protected	$base_url;

	protected function construct () {
		$this->data_model['data']		= function ($data) {
			return _json_encode($data);
		};
		$Config							= Config::instance();
		$module_data					= $Config->module('Deferred_tasks');
		$this->max_number_of_workers	= $module_data->max_number_of_workers;
		$this->security_key				= $module_data->security_key;
		$this->base_url					= $Config->base_url();
	}
	/**
	 * Returns database index
	 *
	 * @return int
	 */
	protected function cdb () {
		return Config::instance()->module('Deferred_tasks')->db('tasks');
	}
	/**
	 * Add new task
	 *
	 * @param string	$module		Module, to what task belongs
	 * @param mixed		$data		Any data, needed for task execution. Can be array, string, number...
	 * @param int		$expected	Max time in seconds, during which task is expected to be finished
	 * @param int		$begin		Unix timestamp, task will not be executed until this time
	 * @param int		$priority	Priority 0..2, higher number - higher priority
	 *
	 * @return bool|int				Id of created task or <i>false</i> on failure
	 */
	function add ($module, $data, $expected, $begin = 0, $priority = 1) {
		return $this->create(
			$this->table,
			$this->data_model,
			[
				$begin,
				0,
				'',
				$expected,
				$priority,
				$module,
				$data
			]
		);
	}
	/**
	 * Get task
	 *
	 * @param int			$id
	 *
	 * @return bool|mixed
	 */
	protected function get ($id) {
		$data	= $this->read(
			$this->table,
			$this->data_model,
			$id
		) ?: false;
		if ($data) {
			$data['data']	= _json_decode($data['data']);
		}
		return $data;
	}
	/**
	 * Delete task
	 *
	 * @param int			$id
	 *
	 * @return bool|mixed
	 */
	function del ($id) {
		return $this->delete($this->table, $id);
	}
	/**
	 * Run tasks execution
	 *
	 * @param bool|int	$task
	 */
	function run ($task) {
		/**
		 * If task is running
		 */
		if ($task) {
			$data	= $this->get($task);
			if (!$data) {
				error_code(404);
				return;
			}
			Trigger::instance()->run(
				"Deferred_tasks/$data[module]",
				[
					'id'		=> $data['id'],
					'data'		=> $data['data']
				]
			);
			$this->del($data['id']);
		/**
		 * If just worker is running
		 */
		} else {
			/**
			 * Disable time limit
			 */
			set_time_limit(0);
			@ini_set('max_input_time', 900);
			while ($this->runned_workers() < $this->max_number_of_workers) {
				$id	= $this->next_task();
				if (!$id) {
					return;
				}
				if (!$this->started($id)) {
					continue;
				}
				file_get_contents("$this->base_url/Deferred_tasks/$this->security_key/$id");
			}
		}
	}
	/**
	 * Update time of task start
	 *
	 * @param int	$id
	 *
	 * @return bool		<i>false</i> if another worker occupied this task
	 */
	protected function started ($id) {
		$this->db_prime()->q(
			 "UPDATE `$this->table`
			SET
				`started`		= '%s',
				`started_hash`	= '%s'
			WHERE `id` = '%s'
			LIMIT 1",
			time(),
			$hash = md5(uniqid(microtime(true))),
			$id
		);
		return $hash === $this->db_prime()->qfs(
			"SELECT `started_hash`
			FROM `$this->table`
			WHERE `id` = '%s'
			LIMIT 1"
		);
	}
	/**
	 * Get number of runned workers
	 *
	 * @return int
	 */
	protected function runned_workers () {
		return $this->db()->qfs([
			"SELECT COUNT(`id`)
			FROM `$this->table`
			WHERE
				`started` > 0 AND
				`started` + `expected` < %d",
			time()
		]) ?: 0;
	}
	/**
	 * Get id of next task that should be executed
	 *
	 * @return bool|int
	 */
	protected function next_task () {
		return $this->db()->qfs([
			"SELECT `id`
			FROM `$this->table`
			WHERE
				(
					`started` = 0 OR
					`started` + `expected` > %1\$d
				) AND
				`begin`	> %1\$d
			ORDER BY
				`priority` DESC,
				`id` ASC,
				`started` ASC
			LIMIT 1",
			time()
		]);
	}
}