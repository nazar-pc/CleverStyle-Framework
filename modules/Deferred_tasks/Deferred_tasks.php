<?php
/**
 * @package   Deferred tasks
 * @category  modules
 * @author    Nazar Mokrynskyi <nazar@mokrynskyi.com>
 * @copyright Copyright (c) 2013-2017, Nazar Mokrynskyi
 * @license   MIT License, see license.txt
 */
namespace cs\modules\Deferred_tasks;
use
	cs\Config,
	cs\Event,
	cs\ExitException,
	cs\CRUD,
	cs\Singleton;

/**
 * @method static $this instance($check = false)
 */
class Deferred_tasks {
	use
		CRUD,
		Singleton;
	protected $data_model = [
		'id'           => 'int',
		'begin'        => 'int:0',
		'started'      => 'int:0',
		'started_hash' => 'text',
		'expected'     => 'int:1',
		'priority'     => 'int:0..2',
		'module'       => 'text',
		'data'         => 'json'
	];
	protected $table      = '[prefix]deferred_tasks_tasks';
	/**
	 * @var int
	 */
	protected $max_number_of_workers;
	/**
	 * @var string
	 */
	protected $security_key;
	/**
	 * @var string
	 */
	protected $core_url;

	protected function construct () {
		$Config                      = Config::instance();
		$module_data                 = $Config->module('Deferred_tasks');
		$this->max_number_of_workers = $module_data->max_number_of_workers;
		$this->security_key          = $module_data->security_key;
		$this->core_url              = $Config->core_url();
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
	 * @param string $module   Module, to what task belongs
	 * @param mixed  $data     Any data, needed for task execution. Can be array, string, number...
	 * @param int    $expected Max time in seconds, during which task is expected to be finished
	 * @param int    $begin    Unix timestamp, task will not be executed until this time
	 * @param int    $priority Priority 0..2, higher number - higher priority
	 *
	 * @return false|int Id of created task or `false` on failure
	 */
	public function add ($module, $data, $expected, $begin = 0, $priority = 1) {
		return $this->create($begin, 0, '', $expected, $priority, $module, $data);
	}
	/**
	 * Get task
	 *
	 * @param int $id
	 *
	 * @return array|false
	 */
	protected function get ($id) {
		return $this->read($id);
	}
	/**
	 * Delete task
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function del ($id) {
		return $this->delete($id);
	}
	/**
	 * Run specified task
	 *
	 * @param int $task
	 *
	 * @throws ExitException
	 */
	public function run_task ($task) {
		$data = $this->read($task);
		if (!$data) {
			throw new ExitException(404);
		}
		Event::instance()->fire(
			"Deferred_tasks/$data[module]",
			[
				'id'   => $data['id'],
				'data' => $data['data']
			]
		);
		$this->del($data['id']);
	}
	/**
	 * Run worker
	 */
	public function run_tasks () {
		/**
		 * Disable time limit
		 */
		set_time_limit(0);
		@ini_set('max_input_time', 900);
		while ($this->tasks_running() < $this->max_number_of_workers) {
			$id = $this->next_task();
			if (!$id) {
				return;
			}
			if (!$this->started($id)) {
				continue;
			}
			file_get_contents("$this->core_url/Deferred_tasks/$this->security_key/$id");
		}
	}
	/**
	 * Update time of task start
	 *
	 * @param int $id
	 *
	 * @return bool `false` if another worker occupied this task in the meantime
	 */
	protected function started ($id) {
		$hash                 = md5(random_bytes(1000));
		$data                 = $this->read($id);
		$data['started']      = time();
		$data['started_hash'] = $hash;
		$this->update($data);
		$new_data = $this->read($id);
		return $new_data && $hash === $new_data['started_hash'];
	}
	/**
	 * Get number of runned workers
	 *
	 * @return int
	 */
	protected function tasks_running () {
		return $this->db()->qfs(
			"SELECT COUNT(`id`)
			FROM `$this->table`
			WHERE
				`started` > 0 AND
				`started` + `expected` < %d",
			time()
		) ?: 0;
	}
	/**
	 * Get id of next task that should be executed
	 *
	 * @return false|int
	 */
	protected function next_task () {
		return $this->db()->qfs(
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
		);
	}
}
